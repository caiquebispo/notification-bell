<?php

namespace CaiqueBispo\NotificationBell\Events;

use CaiqueBispo\NotificationBell\Models\Notification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationArchived
{
    use Dispatchable, SerializesModels;

    public function __construct(public Notification $notification) {}
}
