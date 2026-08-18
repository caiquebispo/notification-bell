<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Livewire\NotificationBell;
use CaiqueBispo\NotificationBell\Models\Notification;
use Livewire\Livewire;

class NotificationBellComponentTest extends TestCase
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

    public function test_loads_unread_count_for_authenticated_user(): void
    {
        $user = $this->createUser();
        $this->notify($user);
        $this->notify($user, ['read_at' => now()]);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 1);
    }

    public function test_mark_as_read_only_touches_own_notifications(): void
    {
        $owner = $this->createUser();
        $intruder = $this->createUser();
        $notification = $this->notify($owner);

        Livewire::actingAs($intruder)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notification->id);

        $this->assertTrue($notification->fresh()->isUnread());

        Livewire::actingAs($owner)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notification->id);

        $this->assertTrue($notification->fresh()->isRead());
    }

    public function test_delete_soft_deletes_and_undo_restores(): void
    {
        $user = $this->createUser();
        $notification = $this->notify($user);

        $component = Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('deleteNotification', $notification->id);

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);

        $component->call('undoDelete', $notification->id);

        $this->assertNull($notification->fresh()->deleted_at);
    }

    public function test_archived_tab_lists_only_archived(): void
    {
        $user = $this->createUser();
        $this->notify($user, ['title' => 'Ativa']);
        $this->notify($user, ['title' => 'Guardada', 'archived_at' => now()]);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('setTab', 'archived')
            ->assertSee('Guardada')
            ->assertDontSee('Ativa');
    }

    public function test_archived_notifications_leave_the_bell(): void
    {
        $user = $this->createUser();
        $notification = $this->notify($user);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('archiveNotification', $notification->id)
            ->assertSet('unreadCount', 0);

        $this->assertTrue($notification->fresh()->isArchived());
    }

    public function test_preferences_toggle_persists(): void
    {
        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('updateSound', true)
            ->call('updateVolume', 80);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'sound_enabled' => true,
            'sound_volume' => 80,
        ]);
    }

    public function test_snooze_only_accepts_configured_options(): void
    {
        $user = $this->createUser();

        $component = Livewire::actingAs($user)->test(NotificationBell::class);

        $component->call('snooze', 999999);
        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id, 'snoozed_until' => null]);

        $component->call('snooze', 60);
        $this->assertNotNull($user->bellPreferences()->fresh()->snoozed_until);
    }

    public function test_clear_all_soft_deletes_only_current_tab(): void
    {
        $user = $this->createUser();
        $active = $this->notify($user, ['title' => 'Ativa']);
        $archived = $this->notify($user, ['title' => 'Guardada', 'archived_at' => now()]);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('clearAll');

        $this->assertSoftDeleted('notifications', ['id' => $active->id]);
        $this->assertNull($archived->fresh()->deleted_at);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('setTab', 'archived')
            ->call('clearAll');

        $this->assertSoftDeleted('notifications', ['id' => $archived->id]);
    }

    public function test_clear_all_does_not_touch_other_users(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        $this->notify($user);
        $foreign = $this->notify($other);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('clearAll');

        $this->assertNull($foreign->fresh()->deleted_at);
    }

    public function test_english_is_the_default_language(): void
    {
        $user = $this->createUser();
        $this->notify($user);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSee('Notifications')
            ->assertDontSee('Notificações');
    }

    public function test_locale_config_forces_language_regardless_of_app_locale(): void
    {
        config(['notifications.locale' => 'pt_BR']);

        $user = $this->createUser();
        $this->notify($user);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSee('Notificações');
    }

    public function test_locale_prop_overrides_config(): void
    {
        $user = $this->createUser();
        $this->notify($user);

        Livewire::actingAs($user)
            ->test(NotificationBell::class, ['locale' => 'pt_BR'])
            ->assertSee('Notificações');
    }

    public function test_grouping_collapses_same_group_key(): void
    {
        config(['notifications.features.grouping.min_size' => 3]);

        $user = $this->createUser();
        foreach (range(1, 3) as $i) {
            $this->notify($user, ['title' => "Comentário {$i}", 'group_key' => 'post-1']);
        }
        $this->notify($user, ['title' => 'Avulsa']);

        $component = Livewire::actingAs($user)->test(NotificationBell::class);

        $this->assertCount(2, $component->get('notifications'));
    }
}
