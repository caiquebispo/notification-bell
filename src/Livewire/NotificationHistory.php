<?php

namespace CaiqueBispo\NotificationBell\Livewire;

use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Support\SchemaReadiness;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Página "Todas as notificações": histórico completo do usuário logado
 * com busca, filtros e paginação incremental.
 */
class NotificationHistory extends Component
{
    public string $search = '';
    public string $type = '';
    public string $category = '';
    public string $period = ''; // today | week | month
    public string $status = ''; // unread | read | archived | pinned
    public int $perPage = 20;

    public function updated($property)
    {
        if (in_array($property, ['search', 'type', 'category', 'period', 'status'], true)) {
            $this->perPage = 20;
        }
    }

    public function loadMore()
    {
        $this->perPage += 20;
    }

    public function markAsRead($notificationId)
    {
        $this->findOwn($notificationId)?->markAsRead();
    }

    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())->unread()->update(['read_at' => now()]);
    }

    public function togglePin($notificationId)
    {
        $this->findOwn($notificationId)?->togglePin();
    }

    public function toggleArchive($notificationId)
    {
        $notification = $this->findOwn($notificationId);

        if ($notification) {
            $notification->isArchived() ? $notification->unarchive() : $notification->archive();
        }
    }

    public function deleteNotification($notificationId)
    {
        $this->findOwn($notificationId)?->delete();
    }

    public function getNotificationsProperty()
    {
        if (!auth()->check() || !SchemaReadiness::ready()) {
            return collect();
        }

        /** @var \Illuminate\Database\Eloquent\Builder<Notification> $query */
        $query = Notification::forUser(auth()->id())
            ->search($this->search ?: null)
            ->orderByRaw('CASE WHEN pinned_at IS NULL THEN 1 ELSE 0 END')
            ->latest();

        if ($this->type !== '') {
            $query->ofType($this->type);
        }

        if ($this->category !== '') {
            $query->ofCategory($this->category);
        }

        match ($this->status) {
            'unread' => $query->unread(),
            'read' => $query->read(),
            'archived' => $query->archived(),
            'pinned' => $query->pinned(),
            default => null,
        };

        match ($this->period) {
            'today' => $query->where('created_at', '>=', now()->startOfDay()),
            'week' => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            default => null,
        };

        return $query->limit($this->perPage + 1)->get();
    }

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

    public function render(): View
    {
        $all = $this->notifications;

        return view('notification-bell::livewire.notifications.notification-history', [
            'items' => $all->take($this->perPage),
            'hasMore' => $all->count() > $this->perPage,
        ]);
    }
}
