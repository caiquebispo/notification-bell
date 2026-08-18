<?php

namespace CaiqueBispo\NotificationBell\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Proteção de produção: entre o composer update e o php artisan migrate
 * existe uma janela em que o código espera colunas que ainda não existem.
 * O sino aparece em todas as páginas — ele deve degradar para vazio,
 * nunca derrubar a página com uma QueryException.
 */
class SchemaReadiness
{
    private const CACHE_KEY = 'notification-bell:schema-ready';

    private static ?bool $ready = null;

    public static function ready(): bool
    {
        if (self::$ready === true) {
            return true;
        }

        try {
            // Só o estado "pronto" é cacheado: enquanto o schema estiver
            // incompleto, recheca a cada request para reagir ao migrate
            // imediatamente.
            if (Cache::get(self::CACHE_KEY) === true) {
                return self::$ready = true;
            }

            $isReady = Schema::hasTable('notifications')
                && Schema::hasColumn('notifications', 'deleted_at')
                && Schema::hasColumn('notifications', 'archived_at')
                && Schema::hasTable('notification_preferences')
                && Schema::hasColumn('notification_preferences', 'toasts_enabled')
                && Schema::hasColumn('notification_preferences', 'snoozed_until');

            if ($isReady) {
                Cache::put(self::CACHE_KEY, true, now()->addDay());

                return self::$ready = true;
            }

            self::warnOnce();

            return self::$ready = false;
        } catch (\Throwable $e) {
            // Sem banco disponível (deploy, testes de view isolados, etc.):
            // degradar em silêncio.
            return self::$ready = false;
        }
    }

    /**
     * Permite aos testes e ao migrate forçar a reavaliação.
     */
    public static function reset(): void
    {
        self::$ready = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // Cache indisponível — o estado estático já foi limpo.
        }
    }

    private static function warnOnce(): void
    {
        static $warned = false;

        if (!$warned) {
            $warned = true;
            Log::warning(
                '[notification-bell] Database schema is out of date — the bell is rendering empty. Run `php artisan migrate` to restore it.'
            );
        }
    }
}
