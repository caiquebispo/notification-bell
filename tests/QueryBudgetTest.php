<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Livewire\NotificationBell;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Support\SchemaReadiness;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * O sino renderiza em TODA página do app hospedeiro. Cada query aqui é paga
 * pelo site inteiro, em toda navegação, por todo usuário logado — por isso
 * o custo é orçado, não deixado ao acaso.
 */
class QueryBudgetTest extends TestCase
{
    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        return $count;
    }

    public function test_rendering_the_bell_stays_within_budget(): void
    {
        $user = $this->createUser();

        foreach (range(1, 5) as $i) {
            Notification::create([
                'user_id' => $user->id,
                'title' => "N{$i}",
                'message' => 'm',
                'type' => 'info',
            ]);
        }

        // Cache frio: a checagem de schema ainda não foi memoizada.
        SchemaReadiness::reset();
        $this->app['cache']->forget('notification-bell:schema-ready');

        $cold = $this->countQueries(function () use ($user) {
            Livewire::actingAs($user)->test(NotificationBell::class);
        });

        // Orçamento frio = 4 de introspecção + 3 de negócio.
        // A introspecção é 1 chamada por tabela (notifications e
        // notification_preferences), mas o SQLite gasta 2 queries em cada
        // getColumnListing (pragma + sqlite_master); em MySQL/Postgres é
        // menos. As 3 de negócio são o piso real: preferências, contagem de
        // não lidas e a lista.
        $this->assertLessThanOrEqual(7, $cold, "Render frio usou {$cold} queries.");

        $warm = $this->countQueries(function () use ($user) {
            Livewire::actingAs($user)->test(NotificationBell::class);
        });

        // Quente, a introspecção sai do caminho (memoizada em cache) e sobram
        // só as 3 de negócio — é isso que o host paga em toda página.
        $this->assertLessThanOrEqual(3, $warm, "Render quente usou {$warm} queries.");
    }

    public function test_query_count_does_not_grow_with_more_notifications(): void
    {
        $user = $this->createUser();

        $measure = function () use ($user) {
            SchemaReadiness::reset();
            $this->app['cache']->forget('notification-bell:schema-ready');

            return $this->countQueries(function () use ($user) {
                Livewire::actingAs($user)->test(NotificationBell::class);
            });
        };

        foreach (range(1, 3) as $i) {
            Notification::create([
                'user_id' => $user->id, 'title' => "A{$i}", 'message' => 'm', 'type' => 'info',
            ]);
        }

        $few = $measure();

        foreach (range(1, 20) as $i) {
            Notification::create([
                'user_id' => $user->id, 'title' => "B{$i}", 'message' => 'm', 'type' => 'info',
                'group_key' => 'g' . ($i % 4),
            ]);
        }

        // N+1: o custo tem de ser constante, não proporcional à lista.
        $this->assertSame($few, $measure(), 'O número de queries cresceu com a lista.');
    }
}
