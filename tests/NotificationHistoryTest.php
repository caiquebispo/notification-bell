<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Livewire\NotificationHistory;
use CaiqueBispo\NotificationBell\Models\Notification;
use Livewire\Livewire;

class NotificationHistoryTest extends TestCase
{
    private function notify($user, array $overrides = []): Notification
    {
        return Notification::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Message',
            'type' => 'info',
        ], $overrides));
    }

    public function test_history_page_renders_for_authenticated_user(): void
    {
        $user = $this->createUser();
        $this->notify($user, ['title' => 'Primeira notificação']);

        $this->actingAs($user)
            ->get('/notifications/history')
            ->assertOk()
            ->assertSee('Primeira notificação');
    }

    public function test_search_filters_results(): void
    {
        $user = $this->createUser();
        $this->notify($user, ['title' => 'Fatura vencida']);
        $this->notify($user, ['title' => 'Pedido enviado']);

        Livewire::actingAs($user)
            ->test(NotificationHistory::class)
            ->set('search', 'fatura')
            ->assertSee('Fatura vencida')
            ->assertDontSee('Pedido enviado');
    }

    public function test_status_filter_shows_archived(): void
    {
        $user = $this->createUser();
        $this->notify($user, ['title' => 'Ativa']);
        $this->notify($user, ['title' => 'Guardada', 'archived_at' => now()]);

        Livewire::actingAs($user)
            ->test(NotificationHistory::class)
            ->set('status', 'archived')
            ->assertSee('Guardada')
            ->assertDontSee('Ativa');
    }

    public function test_load_more_increases_page_size(): void
    {
        $user = $this->createUser();

        foreach (range(1, 25) as $i) {
            $this->notify($user, ['title' => "Notificação {$i}"]);
        }

        $component = Livewire::actingAs($user)->test(NotificationHistory::class);

        $this->assertSame(20, $component->instance()->perPage);

        $component->call('loadMore');

        $this->assertSame(40, $component->instance()->perPage);
    }

    public function test_only_own_notifications_are_listed(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $this->notify($other, ['title' => 'Segredo alheio']);

        Livewire::actingAs($user)
            ->test(NotificationHistory::class)
            ->assertDontSee('Segredo alheio');
    }
}
