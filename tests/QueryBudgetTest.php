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

        // Processo novo, como no primeiro request de um worker.
        SchemaReadiness::reset();

        $cold = $this->countQueries(function () use ($user) {
            Livewire::actingAs($user)->test(NotificationBell::class);
        });

        // UMA query com o painel fechado — a contagem do badge. Sem
        // introspecção de schema (verificação otimista), sem preferências
        // (sob demanda) e sem a lista (só ao abrir o dropdown). Este é o
        // custo que o site inteiro paga em toda navegação.
        $this->assertLessThanOrEqual(1, $cold, "Render frio usou {$cold} queries.");

        $warm = $this->countQueries(function () use ($user) {
            Livewire::actingAs($user)->test(NotificationBell::class);
        });

        $this->assertLessThanOrEqual(1, $warm, "Render quente usou {$warm} queries.");

        // Abrir o dropdown: aí sim a lista é buscada.
        $open = $this->countQueries(function () use ($user) {
            Livewire::actingAs($user)->test(NotificationBell::class)->call('openPanel');
        });

        $this->assertLessThanOrEqual(3, $open, "Abrir o painel usou {$open} queries.");
    }

    public function test_query_count_does_not_grow_with_more_notifications(): void
    {
        $user = $this->createUser();

        $measure = function () use ($user) {
            SchemaReadiness::reset();
            $this->app['cache']->forget('notification-bell:schema-ready');

            return $this->countQueries(function () use ($user) {
                // Com o painel aberto: é o caminho que de fato lê a lista, e
                // portanto o que poderia sofrer N+1.
                Livewire::actingAs($user)->test(NotificationBell::class)->call('openPanel');
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
