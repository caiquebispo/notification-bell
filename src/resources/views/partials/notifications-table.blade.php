@php($nameColumn = $nameColumn ?? config('notifications.user_columns.name', 'name'))
@php($t = $t ?? fn (string $key, array $replace = []) => __("notification-bell::panel.{$key}", $replace, config('notifications.locale')))
@php($hasSelection = $notifications->count() > 0)

<div data-nbp-bulkbar class="nbp-bulkbar nbp-hidden">
    <span data-nbp-bulk-count class="nbp-bulkbar-count"></span>
    <div class="nbp-bulkbar-actions">
        <button type="button" class="nbp-btn nbp-btn-ghost nbp-btn-sm" data-nbp-clear-selection>
            {{ $t('bulk_clear_selection') }}
        </button>
        <button type="button" class="nbp-btn nbp-btn-danger nbp-btn-sm" data-nbp-delete-selected>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            {{ $t('bulk_delete_selected') }}
        </button>
    </div>
</div>

<div class="nbp-table-scroll">
    <table class="nbp-table">
        <thead>
            <tr>
                <th scope="col" class="nbp-col-select">
                    <input
                        type="checkbox"
                        class="nbp-checkbox"
                        data-nbp-select-all
                        aria-label="{{ $t('select_all') }}"
                        @disabled(!$hasSelection)
                    >
                </th>
                <th scope="col">{{ $t('col_notification') }}</th>
                <th scope="col">{{ $t('col_user') }}</th>
                <th scope="col">{{ $t('col_type') }}</th>
                <th scope="col">{{ $t('col_status') }}</th>
                <th scope="col">{{ $t('col_date') }}</th>
                <th scope="col" class="nbp-col-actions">{{ $t('col_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($notifications as $notification)
                @php($typeClass = match($notification->type) {
                    'success' => 'nbp-badge-success',
                    'warning' => 'nbp-badge-warning',
                    'error' => 'nbp-badge-error',
                    default => 'nbp-badge-info',
                })
                @php($typeLabel = $t('type_' . ($notification->type ?? 'info')))
                @php($snippet = \Illuminate\Support\Str::limit(trim(strip_tags($notification->message ?? '')), 70))
                <tr>
                    <td>
                        <input
                            type="checkbox"
                            class="nbp-checkbox"
                            data-nbp-select-row
                            value="{{ $notification->id }}"
                            aria-label="{{ $t('select_row', ['title' => $notification->title]) }}"
                        >
                    </td>
                    <td class="nbp-cell-notification">
                        <p class="nbp-cell-title">
                            @if(!empty($notification->pinned_at))
                                <svg class="nbp-pin-icon" viewBox="0 0 24 24" fill="currentColor" aria-label="{{ $t('pinned') }}">
                                    <path d="M16 3a1 1 0 01.8 1.6l-.6.8 1.6 4.8a3 3 0 011.9 2.8 1 1 0 01-1 1h-4.6l-.3 5.9a1 1 0 01-2 0L11.5 14H7a1 1 0 01-1-1 3 3 0 011.9-2.8l1.6-4.8-.6-.8A1 1 0 019.7 3h6.3z"/>
                                </svg>
                            @endif
                            <span>{{ $notification->title }}</span>
                        </p>
                        <p class="nbp-cell-snippet">{{ $snippet }}</p>
                        @if(!empty($notification->category))
                            <span class="nbp-badge nbp-badge-category" style="margin-top:0.35rem;">{{ $notification->category }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="nbp-user-cell">
                            @if($notification->user)
                                <span class="nbp-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::substr($notification->user->{$nameColumn} ?? '?', 0, 2) }}</span>
                                <span class="nbp-user-name">{{ $notification->user->{$nameColumn} }}</span>
                            @else
                                <span class="nbp-avatar nbp-avatar-all" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8.13a4 4 0 110 8 4 4 0 010-8zm6 8a4 4 0 100-8"/></svg>
                                </span>
                                <span class="nbp-user-name nbp-user-name-muted">{{ $t('recipient_all') }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="nbp-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                    </td>
                    <td>
                        @if($notification->read_at)
                            <span class="nbp-status nbp-status-read">
                                <span class="nbp-status-dot"></span>{{ $t('status_read') }}
                            </span>
                        @else
                            <span class="nbp-status nbp-status-unread">
                                <span class="nbp-status-dot"></span>{{ $t('status_unread') }}
                            </span>
                        @endif
                    </td>
                    <td class="nbp-date-cell">{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                    <td class="nbp-col-actions">
                        <div class="nbp-row-actions">
                            <button type="button" class="nbp-icon-btn" data-nbp-view="{{ $notification->id }}" title="{{ $t('action_view') }}" aria-label="{{ $t('action_view') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button type="button" class="nbp-icon-btn" data-nbp-edit="{{ $notification->id }}" title="{{ $t('action_edit') }}" aria-label="{{ $t('action_edit') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button type="button" class="nbp-icon-btn nbp-icon-btn-danger" data-nbp-delete="{{ $notification->id }}" title="{{ $t('action_delete') }}" aria-label="{{ $t('action_delete') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="nbp-empty">
                            <span class="nbp-empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </span>
                            <p class="nbp-empty-title">{{ $t('empty_title') }}</p>
                            <p class="nbp-empty-text">{{ $t('empty_text') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($notifications->hasPages())
    <nav class="nbp-pagination-wrap" aria-label="{{ $t('pagination_nav') }}">
        {{ $notifications->links() }}
    </nav>
@endif
