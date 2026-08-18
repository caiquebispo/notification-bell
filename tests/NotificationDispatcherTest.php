<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Events\NotificationCreated;
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Models\NotificationPreference;
use CaiqueBispo\NotificationBell\Services\NotificationDispatcher;
use Illuminate\Support\Facades\Event;

class NotificationDispatcherTest extends TestCase
{
    private function row($userId, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $userId,
            'title' => 'Title',
            'message' => 'Message',
            'type' => 'info',
        ], $overrides);
    }

    public function test_creates_notifications_and_fires_created_event(): void
    {
        Event::fake([NotificationCreated::class]);

        $user = $this->createUser();
        $result = app(NotificationDispatcher::class)->dispatch([$this->row($user->id)]);

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseCount('notifications', 1);
        Event::assertDispatched(NotificationCreated::class);
    }

    public function test_deduplication_skips_repeated_key_within_window(): void
    {
        $user = $this->createUser();
        $dispatcher = app(NotificationDispatcher::class);

        $first = $dispatcher->dispatch([$this->row($user->id, ['dedup_key' => 'invoice-1'])]);
        $second = $dispatcher->dispatch([$this->row($user->id, ['dedup_key' => 'invoice-1'])]);

        $this->assertSame(1, $first['created']);
        $this->assertSame(1, $second['skipped_dedup']);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_rate_limit_blocks_flood(): void
    {
        config(['notifications.rate_limit.max_per_minute' => 3]);

        $user = $this->createUser();
        $rows = array_map(fn ($i) => $this->row($user->id, ['title' => "N{$i}"]), range(1, 5));

        $result = app(NotificationDispatcher::class)->dispatch($rows);

        $this->assertSame(3, $result['created']);
        $this->assertSame(2, $result['skipped_rate_limit']);
    }

    public function test_muted_category_is_skipped(): void
    {
        $user = $this->createUser();
        NotificationPreference::forUser($user->id)->muteCategory('marketing');

        $result = app(NotificationDispatcher::class)->dispatch([
            $this->row($user->id, ['category' => 'marketing']),
            $this->row($user->id, ['category' => 'orders']),
        ]);

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['skipped_muted']);
        $this->assertSame('orders', Notification::first()->category);
    }

    public function test_helper_create_persists_with_options(): void
    {
        $user = $this->createUser();

        NotificationHelper::create($user->id, 'Hello', 'World', 'success', ['k' => 'v'], '/orders/1', [
            'category' => 'orders',
            'group_key' => 'order-1',
            'image_url' => '/img.png',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Hello',
            'type' => 'success',
            'category' => 'orders',
            'group_key' => 'order-1',
            'action_url' => '/orders/1',
            'image_url' => '/img.png',
        ]);

        $this->assertSame(['k' => 'v'], Notification::first()->data);
    }

    public function test_helper_create_for_multiple_users(): void
    {
        $a = $this->createUser();
        $b = $this->createUser();

        NotificationHelper::create([$a->id, $b->id], 'Bulk', 'Message');

        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_cleanup_removes_old_but_keeps_pinned(): void
    {
        $user = $this->createUser();

        Notification::create([
            'user_id' => $user->id, 'title' => 'old', 'message' => 'm', 'type' => 'info',
            'read_at' => now()->subDays(60), 'created_at' => now()->subDays(60),
        ]);
        Notification::create([
            'user_id' => $user->id, 'title' => 'old-pinned', 'message' => 'm', 'type' => 'info',
            'read_at' => now()->subDays(60), 'created_at' => now()->subDays(60), 'pinned_at' => now(),
        ]);
        Notification::create([
            'user_id' => $user->id, 'title' => 'recent', 'message' => 'm', 'type' => 'info',
        ]);

        NotificationHelper::cleanup(30);

        $titles = Notification::pluck('title')->all();
        sort($titles);
        $this->assertSame(['old-pinned', 'recent'], $titles);
    }
}
