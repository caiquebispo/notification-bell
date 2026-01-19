<?php

namespace CaiqueBispo\NotificationBell\Traits;

use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Helpers\NotificationHelper;

trait HasNotifications
{

    public function bellNotifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function unreadBellNotifications()
    {
        return $this->hasMany(Notification::class)->unread();
    }
    public function readBellNotifications()
    {
        return $this->hasMany(Notification::class)->read();
    }
    public function bellNotify(mixed $usersId, $title, $message, $type = 'info', $data = null, $actionUrl = null)
    {
        return NotificationHelper::create($usersId, $title, $message, $type, $data, $actionUrl);
    }
    public function success(mixed $usersId, $title, $message, $data = null, $actionUrl = null)
    {
        return NotificationHelper::success($usersId, $title, $message, $data, $actionUrl);
    }
    public function error(mixed $usersId, $title, $message, $data = null, $actionUrl = null)
    {
        return NotificationHelper::error($usersId, $title, $message, $data, $actionUrl);
    }
    public function warning(mixed $usersId, $title, $message, $data = null, $actionUrl = null)
    {
        return NotificationHelper::warning($usersId, $title, $message, $data, $actionUrl);
    }
    public function info(mixed $usersId, $title, $message, $data = null, $actionUrl = null)
    {
        return NotificationHelper::info($usersId, $title, $message, $data, $actionUrl);
    }
}
