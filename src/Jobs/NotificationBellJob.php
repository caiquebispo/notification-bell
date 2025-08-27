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
        Notification::insert($this->notifications);
    }
}
