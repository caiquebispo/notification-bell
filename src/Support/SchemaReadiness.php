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

            // Uma única query de introspecção, não seis: getColumnListing já
            // traz todas as colunas da tabela, e hasTable('notification_
            // preferences') é dispensável — só a coluna interessa, e ela não
            // existe sem a tabela. O sino renderiza em TODA página do host:
            // cada query aqui é paga pelo site inteiro.
            $columns = Schema::getColumnListing('notifications');

            $isReady = in_array('deleted_at', $columns, true)
                && in_array('archived_at', $columns, true)
                && in_array(
                    'toasts_enabled',
                    Schema::getColumnListing('notification_preferences'),
                    true
                );

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
