<?php

namespace CaiqueBispo\NotificationBell\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Proteção de produção: entre o composer update e o php artisan migrate
 * existe uma janela em que o código espera colunas que ainda não existem.
 * O sino aparece em todas as páginas — ele deve degradar para vazio,
 * nunca derrubar a página com uma QueryException.
 *
 * A verificação é OTIMISTA: o caminho normal (schema em dia) não paga NADA,
 * porque nada é checado antecipadamente. Só quando uma query realmente falha
 * é que se investiga o schema — e aí o resultado fica memoizado no processo.
 * O inverso (checar antes, em toda página) custava introspecção a cada
 * request sempre que o cache estivesse frio, e o sino renderiza no site
 * inteiro.
 */
class SchemaReadiness
{
    private static ?bool $ready = null;

    /**
     * Executa uma leitura do sino tolerante a schema desatualizado.
     *
     * @template T
     * @param  callable(): T  $callback
     * @param  T  $fallback  Valor devolvido quando o schema não está pronto.
     * @return T
     */
    public static function attempt(callable $callback, $fallback)
    {
        if (self::$ready === false) {
            return $fallback;
        }

        try {
            $result = $callback();
            self::$ready = true;

            return $result;
        } catch (\Throwable $e) {
            // Falhou: pode ser schema desatualizado (janela de deploy) ou um
            // erro real de banco. Só o primeiro caso é degradável.
            if (self::schemaIsStale()) {
                self::$ready = false;
                self::warnOnce();

                return $fallback;
            }

            throw $e;
        }
    }

    /**
     * Estado conhecido do schema. Sem chamada prévia a attempt(), investiga
     * uma vez — usado por caminhos que não têm uma query para embrulhar.
     */
    public static function ready(): bool
    {
        if (self::$ready !== null) {
            return self::$ready;
        }

        return self::$ready = !self::schemaIsStale();
    }

    /** Permite aos testes e ao migrate forçar a reavaliação. */
    public static function reset(): void
    {
        self::$ready = null;
    }

    private static function schemaIsStale(): bool
    {
        try {
            $columns = Schema::getColumnListing('notifications');

            if (!in_array('deleted_at', $columns, true) || !in_array('archived_at', $columns, true)) {
                return true;
            }

            return !in_array(
                'toasts_enabled',
                Schema::getColumnListing('notification_preferences'),
                true
            );
        } catch (\Throwable $e) {
            // Sem banco disponível (deploy, build de assets, testes de view
            // isolados): tratar como não pronto e degradar em silêncio.
            return true;
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
