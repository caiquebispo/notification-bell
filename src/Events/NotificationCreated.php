<?php

namespace CaiqueBispo\NotificationBell\Events;

use CaiqueBispo\NotificationBell\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        if (!config('notifications.broadcasting.enabled', false)) {
            return [];
        }

        $channel = str_replace(
            '{user_id}',
            (string) $this->notification->user_id,
            config('notifications.broadcasting.channel', 'notifications.{user_id}')
        );

        return config('notifications.broadcasting.private', true)
            ? [new PrivateChannel($channel)]
            : [new Channel($channel)];
    }

    public function broadcastAs(): string
    {
        return config('notifications.broadcasting.event', 'NotificationCreated');
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'type' => $this->notification->type,
            'category' => $this->notification->category,
            'action_url' => $this->notification->action_url,
            'image_url' => $this->notification->image_url,
            'created_at' => optional($this->notification->created_at)->toIso8601String(),
        ];
    }
}
