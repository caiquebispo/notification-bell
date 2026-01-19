<?php

namespace CaiqueBispo\NotificationBell\Livewire;

use Livewire\Component;
use CaiqueBispo\NotificationBell\Models\Notification;
use Illuminate\Contracts\View\View;

class NotificationBell extends Component
{
    public $showPanel = false;
    public $unreadCount = 0;
    public $notifications = [];
    public $lastNotificationId = null;

    protected $listeners = [
        'notification-created' => 'loadNotifications',
        'notification-read' => 'loadNotifications',
        'notification-marked-all-read' => 'loadNotifications'
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (!auth()->check()) {
            return;
        }

        $this->unreadCount = Notification::forUser(auth()->id())
            ->unread()
            ->count();

        $limit = config('notifications.dropdown_limit', 10);

        $notifications = Notification::forUser(auth()->id())
            ->latest()
            ->limit($limit)
            ->get();

        $this->notifications = $notifications->toArray();

        // Check for new notifications to trigger alerts
        if ($notifications->isNotEmpty()) {
            $latestId = $notifications->first()->id;
            
            // If we have a last ID and the new latest is greater, we have new notifications
            if ($this->lastNotificationId !== null && $latestId > $this->lastNotificationId) {
                // Find the new notification(s)
                $newNotification = $notifications->first(); // Simplification: just take the latest
                
                // Dispatch event for Toast/Sound
                $this->dispatch('new-notification', [
                    'title' => $newNotification->title,
                    'message' => $newNotification->message,
                    'type' => $newNotification->type
                ]);
            }
            
            $this->lastNotificationId = $latestId;
        }
    }

    public function togglePanel()
    {
        $this->showPanel = !$this->showPanel;

        if ($this->showPanel) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notification-read', $notificationId);
        }
    }

    public function markAllAsRead()
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        $this->dispatch('notification-marked-all-read');
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id === auth()->id()) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    public function render(): View
    {
        return view('notification-bell::livewire.notifications.notification-bell');
    }
}
