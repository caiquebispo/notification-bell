<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Events\NotificationArchived;
use CaiqueBispo\NotificationBell\Events\NotificationDeleted;
use CaiqueBispo\NotificationBell\Events\NotificationRead;
use CaiqueBispo\NotificationBell\Models\Notification;
use Illuminate\Support\Facades\Event;

class NotificationModelTest extends TestCase
{
    private function makeNotification(array $attributes = []): Notification
    {
        $user = $this->createUser();

        return Notification::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Message',
            'type' => 'info',
        ], $attributes));
    }

    public function test_mark_as_read_sets_timestamp_and_fires_event(): void
    {
        Event::fake([NotificationRead::class]);

        $notification = $this->makeNotification();
        $this->assertTrue($notification->isUnread());

        $notification->markAsRead();

        $this->assertTrue($notification->fresh()->isRead());
        Event::assertDispatched(NotificationRead::class);
    }

    public function test_mark_as_read_twice_fires_event_once(): void
    {
        Event::fake([NotificationRead::class]);

        $notification = $this->makeNotification();
        $notification->markAsRead();
        $notification->markAsRead();

        Event::assertDispatchedTimes(NotificationRead::class, 1);
    }

    public function test_delete_is_soft_and_fires_event(): void
    {
        Event::fake([NotificationDeleted::class]);

        $notification = $this->makeNotification();
        $notification->delete();

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
        Event::assertDispatched(NotificationDeleted::class);

        $notification->restore();
        $this->assertNull($notification->fresh()->deleted_at);
    }

    public function test_archive_and_unarchive(): void
    {
        Event::fake([NotificationArchived::class]);

        $notification = $this->makeNotification();
        $notification->archive();

        $this->assertTrue($notification->fresh()->isArchived());
        Event::assertDispatched(NotificationArchived::class);

        $notification->unarchive();
        $this->assertFalse($notification->fresh()->isArchived());
    }

    public function test_pin_toggle(): void
    {
        $notification = $this->makeNotification();

        $notification->togglePin();
        $this->assertTrue($notification->fresh()->isPinned());

        $notification->togglePin();
        $this->assertFalse($notification->fresh()->isPinned());
    }

    public function test_deliverable_scope_excludes_scheduled_and_expired(): void
    {
        $user = $this->createUser();

        $visible = Notification::create([
            'user_id' => $user->id, 'title' => 'ok', 'message' => 'm', 'type' => 'info',
        ]);
        Notification::create([
            'user_id' => $user->id, 'title' => 'future', 'message' => 'm', 'type' => 'info',
            'scheduled_at' => now()->addHour(),
        ]);
        Notification::create([
            'user_id' => $user->id, 'title' => 'expired', 'message' => 'm', 'type' => 'info',
            'expires_at' => now()->subMinute(),
        ]);

        $deliverable = Notification::forUser($user->id)->deliverable()->pluck('id');

        $this->assertEquals([$visible->id], $deliverable->all());
    }

    public function test_for_bell_scope_orders_pinned_first(): void
    {
        $user = $this->createUser();

        $old = Notification::create([
            'user_id' => $user->id, 'title' => 'old-pinned', 'message' => 'm', 'type' => 'info',
            'pinned_at' => now(), 'created_at' => now()->subDay(),
        ]);
        $new = Notification::create([
            'user_id' => $user->id, 'title' => 'new-normal', 'message' => 'm', 'type' => 'info',
        ]);

        $ids = Notification::forBell($user->id)->pluck('id')->all();

        $this->assertEquals([$old->id, $new->id], $ids);
    }

    public function test_search_scope_matches_title_and_message(): void
    {
        $user = $this->createUser();

        Notification::create(['user_id' => $user->id, 'title' => 'Fatura vencida', 'message' => 'm', 'type' => 'warning']);
        Notification::create(['user_id' => $user->id, 'title' => 'Outro', 'message' => 'sua fatura chegou', 'type' => 'info']);
        Notification::create(['user_id' => $user->id, 'title' => 'Nada a ver', 'message' => 'm', 'type' => 'info']);

        $this->assertCount(2, Notification::forUser($user->id)->search('fatura')->get());
    }
}
