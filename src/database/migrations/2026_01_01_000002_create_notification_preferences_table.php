<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained(config('notifications.user_table', 'users'))
                    ->onDelete('cascade');

                $table->boolean('toasts_enabled')->default(true);
                $table->boolean('sound_enabled')->default(false);
                $table->unsignedTinyInteger('sound_volume')->default(50);

                // Categorias que o usuário optou por NÃO receber.
                $table->json('muted_categories')->nullable();

                // "Não perturbe" pontual: silencia até esta data/hora.
                $table->timestamp('snoozed_until')->nullable();

                // Janela diária de silêncio, ex: "22:00" até "08:00".
                $table->time('quiet_hours_start')->nullable();
                $table->time('quiet_hours_end')->nullable();

                $table->timestamps();

                $table->unique('user_id');
            });

            return;
        }

        // A tabela já existe (execução anterior parcial ou tabela de outra
        // origem): reconciliar, adicionando apenas as colunas que faltam.
        // Nunca falhar por estado herdado — isso quebraria o deploy.
        $columns = [
            'user_id' => fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->index(),
            'toasts_enabled' => fn (Blueprint $table) => $table->boolean('toasts_enabled')->default(true),
            'sound_enabled' => fn (Blueprint $table) => $table->boolean('sound_enabled')->default(false),
            'sound_volume' => fn (Blueprint $table) => $table->unsignedTinyInteger('sound_volume')->default(50),
            'muted_categories' => fn (Blueprint $table) => $table->json('muted_categories')->nullable(),
            'snoozed_until' => fn (Blueprint $table) => $table->timestamp('snoozed_until')->nullable(),
            'quiet_hours_start' => fn (Blueprint $table) => $table->time('quiet_hours_start')->nullable(),
            'quiet_hours_end' => fn (Blueprint $table) => $table->time('quiet_hours_end')->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn('notification_preferences', $name)) {
                continue;
            }

            try {
                Schema::table('notification_preferences', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            } catch (\Throwable $e) {
                // Coluna surgiu em paralelo ou driver sem suporte — seguir.
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
