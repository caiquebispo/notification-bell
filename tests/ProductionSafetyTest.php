<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Livewire\NotificationBell;
use CaiqueBispo\NotificationBell\Livewire\NotificationHistory;
use CaiqueBispo\NotificationBell\Support\SchemaReadiness;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

/**
 * Cenários da janela de deploy: código novo rodando contra schema antigo.
 * O sino aparece em todas as páginas — nunca pode derrubá-las.
 */
class ProductionSafetyTest extends TestCase
{
    public function test_bell_degrades_to_empty_when_preferences_table_is_missing(): void
    {
        Schema::drop('notification_preferences');
        SchemaReadiness::reset();

        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 0)
            ->assertSet('notifications', []);
    }

    public function test_bell_degrades_when_notifications_table_lacks_new_columns(): void
    {
        Schema::drop('notifications');
        Schema::create('notifications', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        SchemaReadiness::reset();

        $user = $this->createUser();

        // Sem deleted_at/archived_at o componente deve renderizar vazio,
        // jamais lançar QueryException.
        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 0);
    }

    public function test_history_degrades_when_schema_is_stale(): void
    {
        Schema::drop('notification_preferences');
        SchemaReadiness::reset();

        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(NotificationHistory::class)
            ->assertOk();
    }

    public function test_readiness_recovers_after_migration_without_manual_cache_clear(): void
    {
        Schema::drop('notification_preferences');
        SchemaReadiness::reset();

        $this->assertFalse(SchemaReadiness::ready());

        (require glob(__DIR__ . '/../src/database/migrations/*notification_preferences*.php')[0])->up();

        // Nada é persistido em cache: o estado vive só no processo PHP, então
        // o request seguinte (novo processo/worker) já enxerga o schema novo.
        // Aqui o reset() simula essa fronteira de request.
        SchemaReadiness::reset();

        $this->assertTrue(SchemaReadiness::ready());
    }

    public function test_preferences_migration_reconciles_legacy_table(): void
    {
        // Simula o cenário real reportado: uma tabela notification_preferences
        // pré-existente sem as colunas do pacote.
        Schema::drop('notification_preferences');
        Schema::create('notification_preferences', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });

        $migration = require glob(__DIR__ . '/../src/database/migrations/*notification_preferences*.php')[0];
        $migration->up();

        foreach (['toasts_enabled', 'sound_enabled', 'sound_volume', 'muted_categories', 'snoozed_until', 'quiet_hours_start', 'quiet_hours_end', 'created_at', 'updated_at'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('notification_preferences', $column),
                "Coluna {$column} deveria ter sido reconciliada."
            );
        }
    }

    public function test_control_columns_migration_is_rerunnable(): void
    {
        $migration = require glob(__DIR__ . '/../src/database/migrations/*add_control_columns*.php')[0];

        // Segunda execução sobre schema já migrado não pode lançar exceção.
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('notifications', 'deleted_at'));
    }
}
