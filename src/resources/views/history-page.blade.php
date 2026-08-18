@php
    $nbLocale = config('notifications.locale');
    $t = fn (string $key, array $replace = []) => __("notification-bell::bell.{$key}", $replace, $nbLocale);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $nbLocale ?: app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $t('history_title') }}</title>
    <link rel="stylesheet" href="{{ \CaiqueBispo\NotificationBell\Http\Controllers\AssetController::url('notification-bell.css') }}">
    @livewireStyles
    <style>
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
        }
        @media (prefers-color-scheme: dark) {
            body { background-color: #030712; color: #f9fafb; }
        }
        .nb-history-page {
            max-width: 48rem;
            margin: 0 auto;
            padding: 2rem 1rem 4rem;
        }
        .nb-history-page > h1 {
            margin: 0 0 1.25rem;
            font-size: 1.35rem;
            font-weight: 700;
        }
        .nb-history-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .nb-history-toolbar .nb-input { width: auto; flex: 0 1 auto; }
        .nb-history-search { flex: 1 1 14rem !important; }
        .nb-history-list {
            background-color: var(--nb-surface, #ffffff);
            border: 1px solid var(--nb-border, rgba(0,0,0,0.08));
            border-radius: var(--nb-radius, 1rem);
            overflow: hidden;
        }
    </style>
</head>
<body>
    <main class="nb-history-page">
        <h1>{{ $t('history_title') }}</h1>
        <livewire:notification-bell-history />
    </main>
    @livewireScripts
</body>
</html>
