@php($nameColumn = $nameColumn ?? config('notifications.user_columns.name', 'name'))
@php($nbLocale = config('notifications.locale'))
@php($t = fn (string $key, array $replace = []) => __("notification-bell::panel.{$key}", $replace, $nbLocale))
@php($locale = str_replace('_', '-', $nbLocale ?: app()->getLocale()))
@php($nbpRoutes = [
    'index' => route('notifications.index'),
    'store' => route('notifications.store'),
    'show' => route('notifications.show', ['id' => '__ID__']),
    'update' => route('notifications.update', ['notification' => '__ID__']),
    'destroy' => route('notifications.destroy', ['notification' => '__ID__']),
    'destroyAll' => route('notifications.destroy.all'),
    'destroySelected' => route('notifications.destroy.selected'),
])
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $t('page_title') }}</title>

    <link rel="stylesheet" href="{{ \CaiqueBispo\NotificationBell\Http\Controllers\AssetController::url('notification-panel.css') }}">
</head>
<body
    class="nbp-root nbp-theme-auto"
    data-nbp-root
    data-name-column="{{ $nameColumn }}"
    data-routes='{{ json_encode($nbpRoutes, JSON_HEX_APOS | JSON_HEX_QUOT) }}'
>
    <a href="#nbp-main" class="nbp-skip-link">{{ $t('skip_to_content') }}</a>

    <div class="nbp-page">
        <header class="nbp-topbar">
            <div class="nbp-brand">
                <span class="nbp-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <span>{{ $t('heading') }}</span>
            </div>
            <div class="nbp-topbar-actions">
                <button type="button" class="nbp-btn nbp-btn-icon nbp-btn-ghost" data-nbp-theme-toggle aria-label="{{ $t('toggle_theme') }}" title="{{ $t('toggle_theme') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
        </header>

        <main id="nbp-main" class="nbp-shell">
            <div class="nbp-header">
                <div>
                    <h1 class="nbp-title">{{ $t('heading') }}</h1>
                    <p class="nbp-subtitle">{{ $t('heading_subtitle') }}</p>
                </div>
                <button type="button" class="nbp-btn nbp-btn-primary" data-nbp-open="nbp-modal-create">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    {{ $t('new_notification') }}
                </button>
            </div>

            <section class="nbp-stats" aria-label="{{ $t('table_title') }}">
                <div class="nbp-stat nbp-stat-total">
                    <div>
                        <p class="nbp-stat-label">{{ $t('stat_total') }}</p>
                        <p class="nbp-stat-value">{{ $stats['total'] }}</p>
                    </div>
                    <span class="nbp-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </span>
                </div>

                <div class="nbp-stat nbp-stat-unread">
                    <div>
                        <p class="nbp-stat-label">{{ $t('stat_unread') }}</p>
                        <p class="nbp-stat-value">{{ $stats['unread'] }}</p>
                    </div>
                    <span class="nbp-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                </div>

                <div class="nbp-stat nbp-stat-success">
                    <div>
                        <p class="nbp-stat-label">{{ $t('stat_success') }}</p>
                        <p class="nbp-stat-value">{{ $stats['success'] }}</p>
                    </div>
                    <span class="nbp-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>

                <div class="nbp-stat nbp-stat-error">
                    <div>
                        <p class="nbp-stat-label">{{ $t('stat_error') }}</p>
                        <p class="nbp-stat-value">{{ $stats['error'] }}</p>
                    </div>
                    <span class="nbp-stat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
            </section>

            <section class="nbp-panel">
                <button type="button" class="nbp-panel-header nbp-panel-header-btn" id="nbp-filters-toggle" aria-expanded="false" aria-controls="nbp-filters-body">
                    <span class="nbp-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        {{ $t('filters') }}
                    </span>
                    <svg class="nbp-chevron" id="nbp-filters-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div class="nbp-panel-body nbp-visually-collapsed" id="nbp-filters-body">
                    <form id="nbp-filter-form">
                        <div class="nbp-filters-grid">
                            <div class="nbp-field" style="margin-bottom:0;">
                                <label class="nbp-label" for="nbp-search-title">{{ $t('search_title_label') }}</label>
                                <div class="nbp-search-field">
                                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/></svg>
                                    <input type="text" class="nbp-input" id="nbp-search-title" name="search_title" placeholder="{{ $t('search_title_placeholder') }}">
                                </div>
                            </div>

                            <div class="nbp-field" style="margin-bottom:0;">
                                <label class="nbp-label" for="nbp-filter-user">{{ $t('user_label') }}</label>
                                <select class="nbp-select" id="nbp-filter-user" name="user_id">
                                    <option value="">{{ $t('user_all') }}</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->{$nameColumn} }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="nbp-field" style="margin-bottom:0;">
                                <label class="nbp-label" for="nbp-filter-type">{{ $t('type_label') }}</label>
                                <select class="nbp-select" id="nbp-filter-type" name="type">
                                    <option value="">{{ $t('type_all') }}</option>
                                    <option value="info">{{ $t('type_info') }}</option>
                                    <option value="success">{{ $t('type_success') }}</option>
                                    <option value="warning">{{ $t('type_warning') }}</option>
                                    <option value="error">{{ $t('type_error') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="nbp-filters-actions">
                            <button type="button" class="nbp-btn nbp-btn-ghost" data-nbp-clear-filters>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                {{ $t('clear_filters') }}
                            </button>
                            <button type="submit" class="nbp-btn nbp-btn-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                {{ $t('apply_filters') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="nbp-panel">
                <div class="nbp-panel-header">
                    <span class="nbp-panel-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        {{ $t('table_title') }}
                    </span>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span class="nbp-panel-meta" id="nbp-items-count">{{ $t('table_items_count', ['count' => $notifications->total()]) }}</span>
                        <button type="button" class="nbp-btn nbp-btn-ghost nbp-btn-sm" data-nbp-delete-all>
                            {{ $t('delete_all') }}
                        </button>
                    </div>
                </div>
                <div id="nbp-table-container">
                    @include('notification-bell::partials.notifications-table')
                </div>
            </section>
        </main>
    </div>

    {{-- ================= Modal: Criar notificação ================= --}}
    <div id="nbp-modal-create" class="nbp-overlay nbp-hidden" role="dialog" aria-modal="true" aria-labelledby="nbp-modal-create-title" aria-hidden="true">
        <div class="nbp-modal">
            <div class="nbp-modal-header">
                <div class="nbp-modal-heading">
                    <span class="nbp-modal-icon nbp-modal-icon-primary" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <h2 class="nbp-modal-title" id="nbp-modal-create-title">{{ $t('form_create_title') }}</h2>
                        <p class="nbp-modal-subtitle">{{ $t('form_create_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" class="nbp-modal-close" data-nbp-close data-reset-form="nbp-create-form" aria-label="{{ $t('close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="nbp-create-form">
                <div class="nbp-modal-body">
                    @include('notification-bell::partials.notification-form', ['idPrefix' => 'nbp-create-', 'isEdit' => false])
                </div>
                <div class="nbp-modal-footer">
                    <button type="button" class="nbp-btn nbp-btn-ghost" data-nbp-close data-reset-form="nbp-create-form">
                        {{ $t('cancel') }}
                    </button>
                    <button type="submit" class="nbp-btn nbp-btn-primary">
                        {{ $t('save_create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= Modal: Editar notificação ================= --}}
    <div id="nbp-modal-edit" class="nbp-overlay nbp-hidden" role="dialog" aria-modal="true" aria-labelledby="nbp-modal-edit-title" aria-hidden="true">
        <div class="nbp-modal">
            <div class="nbp-modal-header">
                <div class="nbp-modal-heading">
                    <span class="nbp-modal-icon nbp-modal-icon-primary" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </span>
                    <div>
                        <h2 class="nbp-modal-title" id="nbp-modal-edit-title">{{ $t('form_edit_title') }}</h2>
                        <p class="nbp-modal-subtitle">{{ $t('form_edit_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" class="nbp-modal-close" data-nbp-close data-reset-form="nbp-edit-form" aria-label="{{ $t('close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="nbp-edit-form">
                <input type="hidden" id="nbp-edit-id" name="notification_id" value="">
                <div class="nbp-modal-body">
                    @include('notification-bell::partials.notification-form', ['idPrefix' => 'nbp-edit-', 'isEdit' => true])
                </div>
                <div class="nbp-modal-footer">
                    <button type="button" class="nbp-btn nbp-btn-ghost" data-nbp-close data-reset-form="nbp-edit-form">
                        {{ $t('cancel') }}
                    </button>
                    <button type="submit" class="nbp-btn nbp-btn-primary">
                        {{ $t('save_update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= Modal: Confirmar exclusão ================= --}}
    <div id="nbp-modal-delete" class="nbp-overlay nbp-hidden" role="alertdialog" aria-modal="true" aria-labelledby="nbp-modal-delete-title" aria-hidden="true">
        <div class="nbp-modal nbp-modal-sm">
            <div class="nbp-modal-header">
                <div class="nbp-modal-heading">
                    <span class="nbp-modal-icon nbp-modal-icon-danger" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </span>
                    <h2 class="nbp-modal-title" id="nbp-modal-delete-title">{{ $t('delete_title') }}</h2>
                </div>
                <button type="button" class="nbp-modal-close" data-nbp-close aria-label="{{ $t('close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="nbp-modal-body">
                <p id="nbp-delete-text" style="margin:0; color: var(--nbp-text-muted); font-size:0.9rem;">
                    {{ $t('delete_confirm_text') }}
                </p>
            </div>
            <div class="nbp-modal-footer">
                <button type="button" class="nbp-btn nbp-btn-ghost" data-nbp-close>
                    {{ $t('cancel') }}
                </button>
                <button type="button" class="nbp-btn nbp-btn-danger" id="nbp-delete-confirm-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    {{ $t('delete_confirm_button') }}
                </button>
            </div>
        </div>
    </div>

    {{-- ================= Modal: Visualizar notificação ================= --}}
    <div id="nbp-modal-view" class="nbp-overlay nbp-hidden" role="dialog" aria-modal="true" aria-labelledby="nbp-modal-view-title" aria-hidden="true">
        <div class="nbp-modal">
            <div class="nbp-modal-header">
                <div class="nbp-modal-heading">
                    <span class="nbp-modal-icon nbp-modal-icon-info" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </span>
                    <h2 class="nbp-modal-title" id="nbp-modal-view-title">{{ $t('view_title') }}</h2>
                </div>
                <button type="button" class="nbp-modal-close" data-nbp-close aria-label="{{ $t('close') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="nbp-modal-body">
                <input type="hidden" id="nbp-view-id" value="">
                <div class="nbp-view-meta">
                    <span id="nbp-view-type-badge" class="nbp-badge nbp-badge-info"></span>
                    <span id="nbp-view-status" class="nbp-status nbp-status-read"></span>
                </div>
                <h3 id="nbp-view-title" class="nbp-view-title"></h3>
                <div id="nbp-view-message" class="nbp-view-message"></div>

                <dl class="nbp-view-grid">
                    <div>
                        <dt>{{ $t('view_sent_to') }}</dt>
                        <dd>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span id="nbp-view-user"></span>
                        </dd>
                    </div>
                    <div>
                        <dt id="nbp-view-date-label">{{ $t('col_date') }}</dt>
                        <dd id="nbp-view-date"></dd>
                    </div>
                    <div id="nbp-view-url-container" class="nbp-hidden">
                        <dt>{{ $t('view_action_url') }}</dt>
                        <dd>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <a id="nbp-view-url" href="#" target="_blank" rel="noopener noreferrer"></a>
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="nbp-modal-footer nbp-modal-footer-split">
                <button type="button" class="nbp-btn nbp-btn-ghost" data-nbp-close>
                    {{ $t('close') }}
                </button>
                <button type="button" class="nbp-btn nbp-btn-primary" id="nbp-view-edit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ $t('view_edit_button') }}
                </button>
            </div>
        </div>
    </div>

    <div id="nbp-toasts" class="nbp-toasts" aria-live="polite" aria-atomic="true"></div>

    <script type="application/json" id="nbp-i18n">
        {!! json_encode([
            'close' => $t('close'),
            'saving' => $t('saving'),
            'deleting' => $t('deleting'),
            'error_generic' => $t('error_generic'),
            'error_loading_details' => $t('error_loading_details'),
            'error_not_found' => $t('error_not_found'),
            'error_select_at_least_one' => $t('error_select_at_least_one'),
            'toast_success_title' => $t('toast_success_title'),
            'toast_error_title' => $t('toast_error_title'),
            'toast_info_title' => $t('toast_info_title'),
            'validation_error' => $t('validation_error'),
            'delete_confirm_text' => $t('delete_confirm_text'),
            'delete_confirm_bulk_text' => $t('delete_confirm_bulk_text'),
            'delete_confirm_all_text' => $t('delete_confirm_all_text'),
            'bulk_selected_count' => $t('bulk_selected_count'),
            'table_items_count' => $t('table_items_count'),
            'status_read' => $t('status_read'),
            'status_unread' => $t('status_unread'),
            'recipient_all' => $t('recipient_all'),
            'type_info' => $t('type_info'),
            'type_success' => $t('type_success'),
            'type_warning' => $t('type_warning'),
            'type_error' => $t('type_error'),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) !!}
    </script>

    <script>
        (function () {
            var toggle = document.getElementById('nbp-filters-toggle');
            var panelBody = document.getElementById('nbp-filters-body');
            var chevron = document.getElementById('nbp-filters-chevron');
            if (!toggle || !panelBody) return;

            toggle.addEventListener('click', function () {
                var isOpen = !panelBody.classList.contains('nbp-visually-collapsed');
                panelBody.classList.toggle('nbp-visually-collapsed', isOpen);
                toggle.setAttribute('aria-expanded', String(!isOpen));
                if (chevron) chevron.classList.toggle('nbp-chevron-open', !isOpen);
            });
        })();
    </script>

    <script src="{{ \CaiqueBispo\NotificationBell\Http\Controllers\AssetController::url('notification-panel.js') }}"></script>
</body>
</html>
