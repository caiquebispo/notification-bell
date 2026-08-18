<?php

namespace CaiqueBispo\NotificationBell\Helpers;

use CaiqueBispo\NotificationBell\Jobs\NotificationBellJob;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Services\NotificationDispatcher;

class NotificationHelper
{
    /**
     * @param  mixed  $userId  Um id ou um array de ids.
     * @param  array<string, mixed>  $options
     *         category, group_key, dedup_key, image_url,
     *         scheduled_at, expires_at, actions, queue (bool)
     */
    public static function create(
        mixed $userId,
        string $title,
        string $message,
        $type = 'info',
        $data = null,
        $actionUrl = null,
        array $options = []
    ): void {
        $ids = is_array($userId) ? $userId : [$userId];

        $rows = array_map(fn ($id) => [
            'user_id' => $id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'category' => $options['category'] ?? null,
            'group_key' => $options['group_key'] ?? null,
            'dedup_key' => $options['dedup_key'] ?? null,
            'data' => $data,
            'action_url' => $actionUrl,
            'image_url' => $options['image_url'] ?? null,
            'scheduled_at' => $options['scheduled_at'] ?? null,
            'expires_at' => $options['expires_at'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $ids);

        if (($options['queue'] ?? true) === false) {
            app(NotificationDispatcher::class)->dispatch($rows);

            return;
        }

        NotificationBellJob::dispatch($rows);
    }

    public static function success(mixed $userId, string $title, string $message, $data = null, $actionUrl = null, array $options = [])
    {
        return self::create($userId, $title, $message, 'success', $data, $actionUrl, $options);
    }

    public static function error(mixed $userId, string $title, string $message, $data = null, $actionUrl = null, array $options = [])
    {
        return self::create($userId, $title, $message, 'error', $data, $actionUrl, $options);
    }

    public static function warning(mixed $userId, string $title, string $message, $data = null, $actionUrl = null, array $options = [])
    {
        return self::create($userId, $title, $message, 'warning', $data, $actionUrl, $options);
    }

    public static function info(mixed $userId, string $title, string $message, $data = null, $actionUrl = null, array $options = [])
    {
        return self::create($userId, $title, $message, 'info', $data, $actionUrl, $options);
    }

    public static function getUnreadCount($userId)
    {
        return Notification::forUser($userId)->deliverable()->notArchived()->unread()->count();
    }

    public static function markAllAsRead($userId)
    {
        return Notification::forUser($userId)->unread()->update(['read_at' => now()]);
    }

    public static function cleanup($daysToKeep = 30)
    {
        $cutoffDate = now()->subDays($daysToKeep);

        // Expurga também o que já está na lixeira (soft delete) há mais de um dia.
        $purgedTrashed = Notification::onlyTrashed()
            ->where('deleted_at', '<', now()->subDay())
            ->forceDelete();

        $query = Notification::where(function ($q) use ($cutoffDate) {
            $q->where('read_at', '<', $cutoffDate)
                ->orWhere('created_at', '<', (clone $cutoffDate)->subDays(7));
        });

        if (config('notifications.cleanup.keep_pinned', true)) {
            $query->whereNull('pinned_at');
        }

        if (config('notifications.cleanup.keep_archived', false)) {
            $query->whereNull('archived_at');
        }

        return $query->forceDelete() + $purgedTrashed;
    }

    public static function getStats($userId)
    {
        return [
            'total' => Notification::forUser($userId)->count(),
            'unread' => Notification::forUser($userId)->unread()->count(),
            'read' => Notification::forUser($userId)->read()->count(),
            'pinned' => Notification::forUser($userId)->pinned()->count(),
            'archived' => Notification::forUser($userId)->archived()->count(),
            'by_type' => Notification::forUser($userId)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_category' => Notification::forUser($userId)
                ->whereNotNull('category')
                ->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
        ];
    }
}
