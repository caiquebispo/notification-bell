<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Models\Notification;

class NotificationApiTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('notifications.api.enabled', true);
        $app['config']->set('notifications.api.middleware', ['web', 'auth']);
    }

    private function notify($user, array $overrides = []): Notification
    {
        return Notification::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Test',
            'message' => 'Message',
            'type' => 'info',
        ], $overrides));
    }

    public function test_index_returns_only_own_notifications(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $this->notify($user, ['title' => 'Minha']);
        $this->notify($other, ['title' => 'Alheia']);

        $this->actingAs($user)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Minha');
    }

    public function test_unread_count_endpoint(): void
    {
        $user = $this->createUser();
        $this->notify($user);
        $this->notify($user, ['read_at' => now()]);

        $this->actingAs($user)
            ->getJson('/api/notifications/unread-count')
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_mark_read_archive_pin_and_destroy(): void
    {
        $user = $this->createUser();
        $notification = $this->notify($user);

        $this->actingAs($user)->postJson("/api/notifications/{$notification->id}/read")->assertOk();
        $this->assertTrue($notification->fresh()->isRead());

        $this->actingAs($user)->postJson("/api/notifications/{$notification->id}/pin")->assertOk();
        $this->assertTrue($notification->fresh()->isPinned());

        $this->actingAs($user)->postJson("/api/notifications/{$notification->id}/archive")->assertOk();
        $this->assertTrue($notification->fresh()->isArchived());

        $this->actingAs($user)->deleteJson("/api/notifications/{$notification->id}")->assertOk();
        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);

        $this->actingAs($user)->postJson("/api/notifications/{$notification->id}/restore")->assertOk();
        $this->assertNull($notification->fresh()->deleted_at);
    }

    public function test_cannot_touch_other_users_notifications(): void
    {
        $owner = $this->createUser();
        $intruder = $this->createUser();
        $notification = $this->notify($owner);

        $this->actingAs($intruder)
            ->postJson("/api/notifications/{$notification->id}/read")
            ->assertNotFound();

        $this->actingAs($intruder)
            ->deleteJson("/api/notifications/{$notification->id}")
            ->assertNotFound();
    }

    public function test_preferences_endpoints(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->getJson('/api/notifications/preferences')
            ->assertOk()
            ->assertJsonPath('toasts_enabled', true);

        $this->actingAs($user)
            ->putJson('/api/notifications/preferences', [
                'sound_enabled' => true,
                'sound_volume' => 90,
                'muted_categories' => ['marketing'],
            ])
            ->assertOk()
            ->assertJsonPath('sound_volume', 90);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'sound_enabled' => true,
        ]);
    }

    public function test_guests_are_rejected(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
    }

    public function test_asset_route_serves_css_without_auth(): void
    {
        $this->get('/notification-bell/assets/notification-bell.css')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }
}
