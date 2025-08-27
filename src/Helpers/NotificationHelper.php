<?php

namespace CaiqueBispo\NotificationBell\Helpers;

use CaiqueBispo\NotificationBell\Jobs\NotificationBellJob;
use CaiqueBispo\NotificationBell\Models\Notification;

class NotificationHelper
{

    public static function create(mixed $userId, string $title, string $message, $type = 'info', $data = null, $actionUrl = null): void
    {
        if (is_array($userId)) {

            foreach ($userId as $id) {

                $notifications = [];

                foreach ($userId as $id) {
                    $notifications[] = [
                        'user_id' => $id,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'data' => $data,
                        'action_url' => $actionUrl,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                NotificationBellJob::dispatch($notifications);
            }
        } else {

            NotificationBellJob::dispatch([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'action_url' => $actionUrl,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }


    public static function success(mixed $userId, string $title, string $message, $data = null, $actionUrl = null)
    {
        return self::create($userId, $title, $message, 'success', $data, $actionUrl);
    }
    public static function error(mixed $userId, string $title, string $message, $data = null, $actionUrl = null)
    {
        return self::create($userId, $title, $message, 'error', $data, $actionUrl);
    }
    public static function warning(mixed $userId, string $title, string $message, $data = null, $actionUrl = null)
    {
        return self::create($userId, $title, $message, 'warning', $data, $actionUrl);
    }
    public static function info(mixed $userId, string $title, string $message, $data = null, $actionUrl = null)
    {
        return self::create($userId, $title, $message, 'info', $data, $actionUrl);
    }
    public static function getUnreadCount($userId)
    {
        return Notification::forUser($userId)->unread()->count();
    }
    public static function markAllAsRead($userId)
    {
        return Notification::forUser($userId)->unread()->update(['read_at' => now()]);
    }
    public static function cleanup($daysToKeep = 30)
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return Notification::where('read_at', '<', $cutoffDate)
            ->orWhere('created_at', '<', $cutoffDate->subDays(7))
            ->delete();
    }
    public static function getStats($userId)
    {
        $query = Notification::forUser($userId);

        return [
            'total' => $query->count(),
            'unread' => $query->unread()->count(),
            'read' => $query->read()->count(),
            'by_type' => $query->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];
    }
}
