<?php

namespace CaiqueBispo\NotificationBell\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Serve os assets do pacote diretamente do vendor, sem publicação
 * e sem etapa de build (npm) no projeto host.
 */
class AssetController extends Controller
{
    private const ASSETS = [
        'notification-bell.css' => ['path' => 'notification-bell.css', 'type' => 'text/css'],
        'notification-panel.css' => ['path' => 'notification-panel.css', 'type' => 'text/css'],
        'notification-panel.js' => ['path' => 'notification-panel.js', 'type' => 'application/javascript'],
    ];

    public function __invoke(string $asset): Response
    {
        abort_unless(isset(self::ASSETS[$asset]), 404);

        $file = __DIR__ . '/../../resources/assets/' . self::ASSETS[$asset]['path'];

        abort_unless(file_exists($file), 404);

        return response(file_get_contents($file), 200, [
            'Content-Type' => self::ASSETS[$asset]['type'],
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * URL versionada do asset: o hash do conteúdo garante cache busting
     * automático quando o pacote é atualizado.
     */
    public static function url(string $asset): string
    {
        $file = isset(self::ASSETS[$asset])
            ? __DIR__ . '/../../resources/assets/' . self::ASSETS[$asset]['path']
            : null;

        $version = $file && file_exists($file) ? substr(md5_file($file), 0, 8) : '0';

        return route('notification-bell.asset', ['asset' => $asset, 'v' => $version]);
    }
}
