<?php

namespace CaiqueBispo\NotificationBell\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'toasts_enabled',
        'sound_enabled',
        'sound_volume',
        'muted_categories',
        'snoozed_until',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected $casts = [
        'toasts_enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'sound_volume' => 'integer',
        'muted_categories' => 'array',
        'snoozed_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(config('notifications.user_model'));
    }

    /**
     * Preferências do usuário SEM gravar nada: devolve uma instância com os
     * padrões quando ainda não existe registro.
     *
     * O sino renderiza em toda página, e a esmagadora maioria dos usuários
     * nunca abre o painel de preferências — criar a linha só para ler os
     * padrões custaria um INSERT por usuário e uma escrita no caminho de
     * leitura mais quente do host. A linha nasce em forUser(), na primeira
     * vez que o usuário de fato muda uma preferência.
     */
    public static function readForUser($userId): self
    {
        $existing = static::where('user_id', $userId)->first();

        if ($existing) {
            return $existing;
        }

        return static::make(static::defaults())->forceFill(['user_id' => $userId]);
    }

    /** @return array<string, mixed> */
    private static function defaults(): array
    {
        return [
            'toasts_enabled' => config('notifications.features.toasts.enabled', true),
            'sound_enabled' => config('notifications.features.sound.enabled', false),
            'sound_volume' => (int) round(config('notifications.features.sound.volume', 0.5) * 100),
            'muted_categories' => [],
        ];
    }

    /**
     * Preferências do usuário, criando o registro padrão na primeira leitura.
     */
    public static function forUser($userId): self
    {
        try {
            return static::firstOrCreate(['user_id' => $userId], static::defaults());
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Duas requisições simultâneas criaram ao mesmo tempo: o índice
            // único garantiu um só registro — basta reler.
            return static::where('user_id', $userId)->firstOrFail();
        }
    }

    public function isSnoozed(?Carbon $now = null): bool
    {
        $now = $now ?: now();

        if ($this->snoozed_until && $this->snoozed_until->greaterThan($now)) {
            return true;
        }

        return $this->isWithinQuietHours($now);
    }

    /**
     * Janela de silêncio diária. Suporta janelas que cruzam a meia-noite
     * (ex.: 22:00 até 08:00).
     */
    public function isWithinQuietHours(?Carbon $now = null): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = $now ?: now();
        $current = $now->format('H:i:s');
        $start = $this->normalizeTime($this->quiet_hours_start);
        $end = $this->normalizeTime($this->quiet_hours_end);

        if ($start === $end) {
            return false;
        }

        if ($start < $end) {
            return $current >= $start && $current < $end;
        }

        return $current >= $start || $current < $end;
    }

    public function isCategoryMuted(?string $category): bool
    {
        if ($category === null || $category === '') {
            return false;
        }

        return in_array($category, $this->muted_categories ?? [], true);
    }

    public function muteCategory(string $category): void
    {
        $muted = $this->muted_categories ?? [];

        if (!in_array($category, $muted, true)) {
            $muted[] = $category;
            $this->update(['muted_categories' => array_values($muted)]);
        }
    }

    public function unmuteCategory(string $category): void
    {
        $muted = array_values(array_filter(
            $this->muted_categories ?? [],
            fn ($item) => $item !== $category
        ));

        $this->update(['muted_categories' => $muted]);
    }

    public function snoozeFor(int $minutes): void
    {
        $this->update(['snoozed_until' => now()->addMinutes($minutes)]);
    }

    public function clearSnooze(): void
    {
        $this->update(['snoozed_until' => null]);
    }

    /**
     * Volume normalizado (0.0 a 1.0) para uso na Web Audio API.
     */
    public function getVolumeAttribute(): float
    {
        return max(0, min(100, $this->sound_volume)) / 100;
    }

    private function normalizeTime($value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i:s');
        }

        $parts = explode(':', (string) $value);
        $parts = array_pad($parts, 3, '00');

        return sprintf('%02d:%02d:%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
    }
}
