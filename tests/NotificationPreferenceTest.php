<?php

namespace CaiqueBispo\NotificationBell\Tests;

use Carbon\Carbon;
use CaiqueBispo\NotificationBell\Models\NotificationPreference;

class NotificationPreferenceTest extends TestCase
{
    public function test_for_user_creates_defaults_once(): void
    {
        $user = $this->createUser();

        $a = NotificationPreference::forUser($user->id);
        $b = NotificationPreference::forUser($user->id);

        $this->assertSame($a->id, $b->id);
        $this->assertTrue($a->toasts_enabled);
        $this->assertDatabaseCount('notification_preferences', 1);
    }

    public function test_mute_and_unmute_category(): void
    {
        $user = $this->createUser();
        $preference = NotificationPreference::forUser($user->id);

        $preference->muteCategory('marketing');
        $preference->muteCategory('marketing'); // idempotente
        $this->assertTrue($preference->fresh()->isCategoryMuted('marketing'));

        $preference->fresh()->unmuteCategory('marketing');
        $this->assertFalse($preference->fresh()->isCategoryMuted('marketing'));
    }

    public function test_snooze_silences_until_expiry(): void
    {
        $user = $this->createUser();
        $preference = NotificationPreference::forUser($user->id);

        $preference->snoozeFor(60);
        $this->assertTrue($preference->fresh()->isSnoozed());

        $preference->fresh()->clearSnooze();
        $this->assertFalse($preference->fresh()->isSnoozed());
    }

    public function test_quiet_hours_within_same_day(): void
    {
        $user = $this->createUser();
        $preference = NotificationPreference::forUser($user->id);
        $preference->update(['quiet_hours_start' => '13:00', 'quiet_hours_end' => '15:00']);
        $preference = $preference->fresh();

        $this->assertTrue($preference->isWithinQuietHours(Carbon::parse('2026-08-18 14:00')));
        $this->assertFalse($preference->isWithinQuietHours(Carbon::parse('2026-08-18 16:00')));
    }

    public function test_quiet_hours_crossing_midnight(): void
    {
        $user = $this->createUser();
        $preference = NotificationPreference::forUser($user->id);
        $preference->update(['quiet_hours_start' => '22:00', 'quiet_hours_end' => '08:00']);
        $preference = $preference->fresh();

        $this->assertTrue($preference->isWithinQuietHours(Carbon::parse('2026-08-18 23:30')));
        $this->assertTrue($preference->isWithinQuietHours(Carbon::parse('2026-08-18 06:00')));
        $this->assertFalse($preference->isWithinQuietHours(Carbon::parse('2026-08-18 12:00')));
    }

    public function test_volume_accessor_normalizes_to_unit_range(): void
    {
        $user = $this->createUser();
        $preference = NotificationPreference::forUser($user->id);
        $preference->update(['sound_volume' => 75]);

        $this->assertSame(0.75, $preference->fresh()->volume);
    }
}
