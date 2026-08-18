@php
    use CaiqueBispo\NotificationBell\Support\Theme;

    // Locale do pacote: config > locale do app (fallback: en).
    $nbLocale = config('notifications.locale');
    $t = fn (string $key, array $replace = []) => __("notification-bell::bell.{$key}", $replace, $nbLocale);
    $nbDateLocale = $nbLocale ?: app()->getLocale();

    $categories = config('notifications.categories', []);
    $types = config('notifications.types', []);
@endphp

<div class="nb-root {{ Theme::modeClass() }} nb-history" style="{{ Theme::inlineVariables() }}; display: block;">
    <div class="nb-history-toolbar">
        <input type="search" class="nb-input nb-history-search"
               placeholder="{{ $t('search_placeholder') }}"
               wire:model.live.debounce.400ms="search">

        <select class="nb-input" wire:model.live="status" aria-label="{{ $t('tab_all') }}">
            <option value="">{{ $t('tab_all') }}</option>
            <option value="unread">{{ $t('unread') }}</option>
            <option value="read">{{ $t('read') }}</option>
            <option value="pinned">{{ $t('pin') }}</option>
            <option value="archived">{{ $t('tab_archived') }}</option>
        </select>

        <select class="nb-input" wire:model.live="type" aria-label="{{ $t('filter_type') }}">
            <option value="">{{ $t('filter_type') }}: {{ $t('filter_all') }}</option>
            @foreach($types as $key => $definition)
                <option value="{{ $key }}">{{ ucfirst($key) }}</option>
            @endforeach
        </select>

        @if(count($categories) > 0)
            <select class="nb-input" wire:model.live="category" aria-label="{{ $t('filter_category') }}">
                <option value="">{{ $t('filter_category') }}: {{ $t('filter_all') }}</option>
                @foreach($categories as $key => $definition)
                    <option value="{{ $key }}">{{ $definition['label'] ?? ucfirst($key) }}</option>
                @endforeach
            </select>
        @endif

        <select class="nb-input" wire:model.live="period" aria-label="{{ $t('filter_period') }}">
            <option value="">{{ $t('filter_period') }}: {{ $t('filter_all') }}</option>
            <option value="today">Hoje</option>
            <option value="week">7 dias</option>
            <option value="month">30 dias</option>
        </select>

        <button type="button" class="nb-btn nb-btn-ghost" wire:click="markAllAsRead">
            {{ $t('mark_all_read') }}
        </button>
    </div>

    <div class="nb-history-list">
        @forelse($items as $notification)
            <div class="nb-item {{ $notification->read_at ? 'nb-item-read' : '' }} {{ $notification->pinned_at ? 'nb-item-pinned' : '' }}">
                @if(!$notification->read_at)
                    <span class="nb-unread-dot" aria-hidden="true"></span>
                @endif

                @if($notification->image_url)
                    <img src="{{ $notification->image_url }}" alt="" class="nb-item-avatar">
                @else
                    <span class="nb-item-icon" style="background-color: var(--nb-type-{{ $notification->type ?: 'info' }}, var(--nb-type-info));">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                    </span>
                @endif

                <div class="nb-item-body">
                    <div class="nb-item-head">
                        <p class="nb-item-title">{{ $notification->title }}</p>
                        <span class="nb-item-controls">
                            @if(!$notification->read_at)
                                <button type="button" class="nb-icon-btn" title="{{ $t('mark_as_read') }}"
                                        wire:click="markAsRead({{ $notification->id }})">
                                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </button>
                            @endif
                            <button type="button" class="nb-icon-btn {{ $notification->pinned_at ? 'nb-icon-btn-active' : '' }}"
                                    title="{{ $notification->pinned_at ? $t('unpin') : $t('pin') }}"
                                    wire:click="togglePin({{ $notification->id }})">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l2 5h5l-4 3.5L14.5 16 10 12.8 5.5 16 7 10.5 3 7h5l2-5z"/></svg>
                            </button>
                            <button type="button" class="nb-icon-btn"
                                    title="{{ $notification->archived_at ? $t('unarchive') : $t('archive') }}"
                                    wire:click="toggleArchive({{ $notification->id }})">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v1H3V5zM3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm7 1l-3 3h2v2h2v-2h2l-3-3z"/></svg>
                            </button>
                            <button type="button" class="nb-icon-btn nb-icon-btn-danger" title="{{ $t('delete') }}"
                                    x-data="{ armed: false }"
                                    :class="armed ? 'nb-icon-btn-armed' : ''"
                                    x-on:click="
                                        if (armed) { $wire.deleteNotification({{ $notification->id }}); armed = false }
                                        else { armed = true; setTimeout(() => armed = false, 3000) }
                                    ">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </span>
                    </div>

                    <p class="nb-item-message" style="-webkit-line-clamp: 4;">{!! $notification->message !!}</p>

                    <p class="nb-item-meta">
                        {{ $notification->created_at->locale($nbDateLocale)->diffForHumans() }}
                        @if($notification->category && isset($categories[$notification->category]))
                            · {{ $categories[$notification->category]['label'] ?? $notification->category }}
                        @endif
                        @if($notification->archived_at)
                            · {{ $t('tab_archived') }}
                        @endif
                    </p>

                    @if($notification->action_url)
                        <span class="nb-item-actions">
                            <a href="{{ $notification->action_url }}" class="nb-btn nb-btn-primary">
                                {{ $t('view_details') }}
                            </a>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="nb-empty">
                <h4 class="nb-empty-title">{{ $t('no_results') }}</h4>
            </div>
        @endforelse
    </div>

    @if($hasMore)
        <div class="nb-panel-footer" style="background: transparent; border: 0;">
            <button type="button" class="nb-btn nb-btn-block" wire:click="loadMore" wire:loading.attr="disabled">
                {{ $t('load_more') }}
            </button>
        </div>
    @endif
</div>
