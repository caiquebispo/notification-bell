<?php

namespace CaiqueBispo\NotificationBell\Models;

use CaiqueBispo\NotificationBell\Events\NotificationArchived;
use CaiqueBispo\NotificationBell\Events\NotificationDeleted;
use CaiqueBispo\NotificationBell\Events\NotificationRead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'category',
        'group_key',
        'dedup_key',
        'data',
        'read_at',
        'action_url',
        'image_url',
        'pinned_at',
        'archived_at',
        'scheduled_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'pinned_at' => 'datetime',
        'archived_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $notification) {
            NotificationDeleted::dispatch($notification);
        });
    }

    public function user()
    {
        return $this->belongsTo(config('notifications.user_model'));
    }

    public function markAsRead()
    {
        if ($this->isRead()) {
            return $this;
        }

        $this->update(['read_at' => now()]);
        NotificationRead::dispatch($this);

        return $this;
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);

        return $this;
    }

    public function archive()
    {
        if ($this->archived_at) {
            return $this;
        }

        $this->update(['archived_at' => now()]);
        NotificationArchived::dispatch($this);

        return $this;
    }

    public function unarchive()
    {
        $this->update(['archived_at' => null]);

        return $this;
    }

    public function pin()
    {
        $this->update(['pinned_at' => now()]);

        return $this;
    }

    public function unpin()
    {
        $this->update(['pinned_at' => null]);

        return $this;
    }

    public function togglePin()
    {
        return $this->pinned_at ? $this->unpin() : $this->pin();
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function isPinned(): bool
    {
        return !is_null($this->pinned_at);
    }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at !== null && $this->scheduled_at->isFuture();
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopePinned($query)
    {
        return $query->whereNotNull('pinned_at');
    }

    /**
     * Notificações efetivamente entregáveis: já liberadas pelo agendamento
     * e ainda dentro da validade.
     */
    public function scopeDeliverable($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * O que aparece no sino: entregável, não arquivada, fixadas primeiro.
     */
    public function scopeForBell($query, $userId)
    {
        return $query
            ->forUser($userId)
            ->deliverable()
            ->notArchived()
            ->orderByRaw('CASE WHEN pinned_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('created_at', 'desc');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
                ->orWhere('message', 'LIKE', "%{$term}%");
        });
    }
}
