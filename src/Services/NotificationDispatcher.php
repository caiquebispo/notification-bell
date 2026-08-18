<?php

namespace CaiqueBispo\NotificationBell\Services;

use CaiqueBispo\NotificationBell\Events\NotificationCreated;
use CaiqueBispo\NotificationBell\Models\Notification;
use CaiqueBispo\NotificationBell\Models\NotificationPreference;
use Illuminate\Support\Facades\Cache;

/**
 * Ponto único de entrega das notificações. Aplica as travas de proteção
 * (deduplicação, rate limit e preferências do usuário) antes de persistir.
 */
class NotificationDispatcher
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created: int, skipped_dedup: int, skipped_rate_limit: int, skipped_muted: int}
     */
    public function dispatch(array $rows): array
    {
        $result = [
            'created' => 0,
            'skipped_dedup' => 0,
            'skipped_rate_limit' => 0,
            'skipped_muted' => 0,
        ];

        foreach ($rows as $row) {
            $userId = $row['user_id'] ?? null;

            if ($userId === null) {
                continue;
            }

            if ($this->isCategoryMuted($userId, $row['category'] ?? null)) {
                $result['skipped_muted']++;
                continue;
            }

            if ($this->isDuplicate($row)) {
                $result['skipped_dedup']++;
                continue;
            }

            if ($this->hitRateLimit($userId)) {
                $result['skipped_rate_limit']++;
                continue;
            }

            $notification = Notification::create($this->normalize($row));
            $result['created']++;

            NotificationCreated::dispatch($notification);
        }

        return $result;
    }

    private function normalize(array $row): array
    {
        // O Job envia `data` como JSON quando vem de insert em massa;
        // o cast 'array' do model espera o valor já decodificado.
        if (isset($row['data']) && is_string($row['data'])) {
            $decoded = json_decode($row['data'], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $row['data'] = $decoded;
            }
        }

        unset($row['created_at'], $row['updated_at']);

        return $row;
    }

    private function isCategoryMuted($userId, ?string $category): bool
    {
        if (!config('notifications.preferences.enabled', true) || $category === null) {
            return false;
        }

        try {
            return NotificationPreference::forUser($userId)->isCategoryMuted($category);
        } catch (\Illuminate\Database\QueryException $e) {
            // Tabela de preferências ainda não migrada: não silenciar nada.
            return false;
        }
    }

    private function isDuplicate(array $row): bool
    {
        if (!config('notifications.deduplication.enabled', true)) {
            return false;
        }

        $key = $row['dedup_key'] ?? null;

        if (!$key) {
            return false;
        }

        $window = (int) config('notifications.deduplication.window', 300);

        return Notification::withTrashed()
            ->where('user_id', $row['user_id'])
            ->where('dedup_key', $key)
            ->where('created_at', '>=', now()->subSeconds($window))
            ->exists();
    }

    private function hitRateLimit($userId): bool
    {
        if (!config('notifications.rate_limit.enabled', true)) {
            return false;
        }

        $max = (int) config('notifications.rate_limit.max_per_minute', 30);

        if ($max <= 0) {
            return false;
        }

        $cacheKey = "notification-bell:rate:{$userId}:" . now()->format('YmdHi');
        $count = (int) Cache::get($cacheKey, 0);

        if ($count >= $max) {
            return true;
        }

        Cache::put($cacheKey, $count + 1, now()->addMinutes(2));

        return false;
    }
}
