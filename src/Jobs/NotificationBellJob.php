<?php

namespace CaiqueBispo\NotificationBell\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use CaiqueBispo\NotificationBell\Models\Notification;

class NotificationBellJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $notifications) {}

    public function handle(): void
    {
        $rows = array_map(function ($notification) {
            if (isset($notification['data']) && is_array($notification['data'])) {
                $notification['data'] = json_encode($notification['data']);
            }

            return $notification;
        }, $this->notifications);

        Notification::insert($rows);
    }
}
