<?php

namespace CaiqueBispo\NotificationBell\Support;

/**
 * Traduz a seção `theme` do config em CSS variables consumidas pelo
 * stylesheet do pacote. Nenhuma etapa de build é necessária: as variáveis
 * são injetadas inline e o CSS é servido pelo próprio pacote.
 */
class Theme
{
    /** @var array<string, string> Mapa de nomes amigáveis para cores CSS. */
    private const NAMED_COLORS = [
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'violet' => '#8b5cf6',
        'purple' => '#a855f7',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'yellow' => '#eab308',
        'amber' => '#f59e0b',
        'orange' => '#f97316',
        'red' => '#ef4444',
        'rose' => '#f43f5e',
        'pink' => '#ec4899',
        'gray' => '#6b7280',
        'slate' => '#64748b',
    ];

    /**
     * String pronta para o atributo style do elemento raiz do componente.
     */
    public static function inlineVariables(array $overrides = []): string
    {
        $vars = static::variables($overrides);

        return implode(';', array_map(
            fn ($key, $value) => "{$key}:{$value}",
            array_keys($vars),
            $vars
        ));
    }

    /**
     * @return array<string, string>
     */
    public static function variables(array $overrides = []): array
    {
        $theme = array_merge(config('notifications.theme', []), array_filter($overrides, fn ($v) => $v !== null));

        $vars = [
            '--nb-primary' => static::color($theme['primary'] ?? '#3b82f6'),
            '--nb-badge-bg' => static::color($theme['badge_background'] ?? '#ef4444'),
            '--nb-badge-text' => static::color($theme['badge_text'] ?? '#ffffff'),
            '--nb-radius' => $theme['radius'] ?? '1rem',
            '--nb-dropdown-width' => $theme['dropdown_width'] ?? '20rem',
        ];

        foreach (static::typeColors() as $type => $color) {
            $vars["--nb-type-{$type}"] = $color;
        }

        return $vars;
    }

    /**
     * Cores por tipo, resolvidas a partir da seção `types` do config.
     * Assim tipos customizados registrados pelo usuário aparecem no visual.
     *
     * @return array<string, string>
     */
    public static function typeColors(): array
    {
        $colors = [];

        foreach (config('notifications.types', []) as $type => $definition) {
            $colors[$type] = static::color($definition['color'] ?? 'gray');
        }

        return $colors;
    }

    /**
     * Aceita nomes amigáveis ('blue'), hex, rgb(), oklch() ou var().
     */
    public static function color(?string $value): string
    {
        if ($value === null || $value === '') {
            return self::NAMED_COLORS['gray'];
        }

        return self::NAMED_COLORS[strtolower($value)] ?? $value;
    }

    /**
     * Classe de modo de tema aplicada ao elemento raiz.
     *
     * - auto (padrão): segue apenas marcadores explícitos do site host
     *   (.dark, [data-theme="dark"], [data-bs-theme="dark"]). Nunca segue o
     *   sistema operacional sozinho — um site claro com usuário de OS escuro
     *   deve continuar claro.
     * - system: além dos marcadores, segue prefers-color-scheme.
     * - dark / light: força o modo, ignorando o host.
     */
    public static function modeClass(): string
    {
        return match (config('notifications.theme.mode', 'auto')) {
            'system' => 'nb-theme-auto',
            'dark' => 'nb-dark',
            'light' => 'nb-light',
            default => '',
        };
    }

    public static function badgeStyle(): string
    {
        $style = config('notifications.theme.badge_style', 'count');

        return in_array($style, ['count', 'dot', 'pulse'], true) ? $style : 'count';
    }

    public static function badgePosition(): string
    {
        $position = config('notifications.theme.badge_position', 'top-right');
        $allowed = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];

        return in_array($position, $allowed, true) ? $position : 'top-right';
    }

    public static function toastPosition(): string
    {
        $position = config('notifications.theme.toast_position', 'bottom-right');
        $allowed = ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'];

        return in_array($position, $allowed, true) ? $position : 'bottom-right';
    }

    /**
     * Badge formatado respeitando o badge_limit do config.
     */
    public static function badgeLabel(int $count): string
    {
        $limit = (int) config('notifications.badge_limit', 99);

        return $count > $limit ? "{$limit}+" : (string) $count;
    }
}
