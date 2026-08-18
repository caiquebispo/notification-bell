<?php

namespace CaiqueBispo\NotificationBell\Livewire;

use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Models\NotificationPreference;
use CaiqueBispo\NotificationBell\Support\SchemaReadiness;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    // ---- Overrides por instância (props): <livewire:notification-bell :limit="5" /> ----
    public ?int $limit = null;
    public ?bool $polling = null;
    public ?string $pollingInterval = null;
    public ?string $locale = null;

    public $unreadCount = 0;
    public $notifications = [];
    public $lastNotificationId = null;

    /** Aba ativa do dropdown: all | unread | archived */
    public string $tab = 'all';

    /** Painel de preferências aberto dentro do dropdown. */
    public bool $showPreferences = false;

    /** Preferências do usuário expostas para a view/Alpine. */
    public array $preferences = [];

    protected $listeners = [
        'notification-created' => 'loadNotifications',
        'notification-read' => 'loadNotifications',
        'notification-marked-all-read' => 'loadNotifications',
    ];

    public function mount()
    {
        $this->loadPreferences();
        $this->loadNotifications();
    }

    // -----------------------------------------------------------------
    // Carga
    // -----------------------------------------------------------------

    public function loadNotifications()
    {
        if (!auth()->check() || !SchemaReadiness::ready()) {
            return;
        }

        $userId = auth()->id();

        $this->unreadCount = Notification::forBell($userId)->unread()->count();

        $limit = $this->limit ?? config('notifications.dropdown_limit', 10);

        $query = match ($this->tab) {
            'unread' => Notification::forBell($userId)->unread(),
            'archived' => Notification::forUser($userId)
                ->deliverable()
                ->archived()
                ->orderBy('archived_at', 'desc'),
            default => Notification::forBell($userId),
        };

        $notifications = $query->limit($limit)->get();

        $this->notifications = $this->applyGrouping($notifications)->toArray();

        $this->detectNewNotifications($userId);
    }

    /**
     * Agrupa notificações com o mesmo group_key quando atingem o tamanho
     * mínimo configurado, virando um item único expansível.
     */
    private function applyGrouping($notifications)
    {
        if (!config('notifications.features.grouping.enabled', true)) {
            return $notifications->values();
        }

        $minSize = (int) config('notifications.features.grouping.min_size', 3);
        $grouped = collect();

        foreach ($notifications->groupBy(fn ($n) => $n->group_key ?: 'single:' . $n->id) as $key => $items) {
            if (str_starts_with((string) $key, 'single:') || $items->count() < $minSize) {
                $grouped = $grouped->merge($items);
                continue;
            }

            $first = $items->first();
            $groupItem = $first->replicate();
            $groupItem->id = $first->id;
            $groupItem->setAttribute('is_group', true);
            $groupItem->setAttribute('group_count', $items->count());
            $groupItem->setAttribute('group_unread', $items->whereNull('read_at')->count());
            $groupItem->setAttribute('group_ids', $items->pluck('id')->all());
            $groupItem->setAttribute('created_at', $first->created_at);
            $groupItem->setAttribute('read_at', $items->whereNull('read_at')->isEmpty() ? $first->created_at : null);

            $grouped->push($groupItem);
        }

        return $grouped
            ->sortBy([
                fn ($a, $b) => ($a->pinned_at === null) <=> ($b->pinned_at === null),
                fn ($a, $b) => $b->created_at <=> $a->created_at,
            ])
            ->values();
    }

    private function detectNewNotifications($userId): void
    {
        $latest = Notification::forBell($userId)->first();

        if (!$latest) {
            return;
        }

        if ($this->lastNotificationId !== null && $latest->id > $this->lastNotificationId) {
            // Preferências mandam: nada de toast/som em snooze ou silêncio.
            $preference = $this->preference();

            if (!$preference || !$preference->isSnoozed()) {
                $this->dispatch('new-notification', [
                    'title' => $latest->title,
                    'message' => $latest->message,
                    'type' => $latest->type,
                    'image_url' => $latest->image_url,
                ]);
            }
        }

        $this->lastNotificationId = $latest->id;
    }

    // -----------------------------------------------------------------
    // Ações sobre notificações
    // -----------------------------------------------------------------

    public function setTab(string $tab)
    {
        $this->tab = in_array($tab, ['all', 'unread', 'archived'], true) ? $tab : 'all';
        $this->loadNotifications();
    }

    public function markAsRead($notificationId)
    {
        $notification = $this->findOwn($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notification-read', $notificationId);
        }
    }

    public function markGroupAsRead(array $ids)
    {
        Notification::forUser(auth()->id())
            ->whereIn('id', $ids)
            ->unread()
            ->get()
            ->each->markAsRead();

        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        $this->dispatch('notification-marked-all-read');
    }

    public function togglePin($notificationId)
    {
        if (!config('notifications.features.pin', true)) {
            return;
        }

        $this->findOwn($notificationId)?->togglePin();
        $this->loadNotifications();
    }

    public function archiveNotification($notificationId)
    {
        if (!config('notifications.features.archive', true)) {
            return;
        }

        $this->findOwn($notificationId)?->archive();
        $this->loadNotifications();
    }

    public function unarchiveNotification($notificationId)
    {
        $this->findOwn($notificationId)?->unarchive();
        $this->loadNotifications();
    }

    public function clearAll()
    {
        if (!auth()->check() || !SchemaReadiness::ready() || !config('notifications.features.clear_all', true)) {
            return;
        }

        $query = Notification::forUser(auth()->id());

        $this->tab === 'archived' ? $query->archived() : $query->notArchived();

        // Exclusão em lote, mas via model para manter o soft delete e os
        // eventos NotificationDeleted.
        $query->chunkById(100, fn ($chunk) => $chunk->each->delete());

        $this->loadNotifications();
    }

    public function deleteNotification($notificationId)
    {
        $notification = $this->findOwn($notificationId);

        if (!$notification) {
            return;
        }

        $notification->delete(); // soft delete

        if (config('notifications.features.undo_delete.enabled', true)) {
            $this->dispatch('notification-deleted', [
                'id' => $notification->id,
                'window' => (int) config('notifications.features.undo_delete.window', 8000),
            ]);
        }

        $this->loadNotifications();
    }

    public function undoDelete($notificationId)
    {
        $notification = Notification::withTrashed()
            ->where('user_id', auth()->id())
            ->find($notificationId);

        if ($notification && $notification->trashed()) {
            $notification->restore();
            $this->loadNotifications();
        }
    }

    // -----------------------------------------------------------------
    // Preferências do usuário
    // -----------------------------------------------------------------

    public function togglePreferences()
    {
        $this->showPreferences = !$this->showPreferences;

        if ($this->showPreferences) {
            $this->loadPreferences();
        }
    }

    public function loadPreferences(): void
    {
        if (!auth()->check() || !config('notifications.preferences.enabled', true) || !SchemaReadiness::ready()) {
            $this->preferences = [];

            return;
        }

        $preference = NotificationPreference::forUser(auth()->id());

        $this->preferences = [
            'toasts_enabled' => $preference->toasts_enabled,
            'sound_enabled' => $preference->sound_enabled,
            'sound_volume' => $preference->sound_volume,
            'muted_categories' => $preference->muted_categories ?? [],
            'snoozed_until' => $preference->snoozed_until?->toIso8601String(),
            'snoozed_until_label' => $preference->snoozed_until?->format('d/m H:i'),
            'quiet_hours_start' => $preference->quiet_hours_start
                ? substr((string) $preference->quiet_hours_start, 0, 5)
                : null,
            'quiet_hours_end' => $preference->quiet_hours_end
                ? substr((string) $preference->quiet_hours_end, 0, 5)
                : null,
            'is_snoozed' => $preference->isSnoozed(),
        ];
    }

    public function updateToasts(bool $enabled)
    {
        $this->preference()?->update(['toasts_enabled' => $enabled]);
        $this->loadPreferences();
    }

    public function updateSound(bool $enabled)
    {
        $this->preference()?->update(['sound_enabled' => $enabled]);
        $this->loadPreferences();
    }

    public function updateVolume(int $volume)
    {
        $this->preference()?->update(['sound_volume' => max(0, min(100, $volume))]);
        $this->loadPreferences();
    }

    public function toggleCategory(string $category)
    {
        $preference = $this->preference();

        if (!$preference) {
            return;
        }

        $preference->isCategoryMuted($category)
            ? $preference->unmuteCategory($category)
            : $preference->muteCategory($category);

        $this->loadPreferences();
    }

    public function snooze(int $minutes)
    {
        $allowed = array_values(config('notifications.preferences.snooze_options', []));

        if (in_array($minutes, $allowed, true)) {
            $this->preference()?->snoozeFor($minutes);
            $this->loadPreferences();
        }
    }

    public function clearSnooze()
    {
        $this->preference()?->clearSnooze();
        $this->loadPreferences();
    }

    public function updateQuietHours(?string $start, ?string $end)
    {
        $valid = fn ($v) => $v === null || $v === '' || preg_match('/^\d{2}:\d{2}$/', $v);

        if (!$valid($start) || !$valid($end)) {
            return;
        }

        $this->preference()?->update([
            'quiet_hours_start' => $start ?: null,
            'quiet_hours_end' => $end ?: null,
        ]);

        $this->loadPreferences();
    }

    // -----------------------------------------------------------------

    private function findOwn($notificationId): ?Notification
    {
        if (!SchemaReadiness::ready()) {
            return null;
        }

        $notification = Notification::find($notificationId);

        return $notification && $notification->user_id === auth()->id()
            ? $notification
            : null;
    }

    private function preference(): ?NotificationPreference
    {
        if (!auth()->check() || !config('notifications.preferences.enabled', true) || !SchemaReadiness::ready()) {
            return null;
        }

        return NotificationPreference::forUser(auth()->id());
    }

    public function getPollingEnabledProperty(): bool
    {
        return $this->polling ?? config('notifications.polling.enabled', true);
    }

    public function getPollingIntervalProperty(): string
    {
        return $this->pollingInterval ?? config('notifications.polling.interval', '10s');
    }

    public function render(): View
    {
        return view('notification-bell::livewire.notifications.notification-bell');
    }
}
