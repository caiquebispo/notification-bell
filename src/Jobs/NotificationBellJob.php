<?php

namespace CaiqueBispo\NotificationBell\Jobs;

use CaiqueBispo\NotificationBell\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotificationBellJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $notifications) {}

    public function handle(NotificationDispatcher $dispatcher): void
    {
        $dispatcher->dispatch($this->notifications);
    }
}
