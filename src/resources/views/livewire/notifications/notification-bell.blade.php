@php
    use CaiqueBispo\NotificationBell\Support\Theme;

    // Locale do componente: prop da instância > config > locale do app (fallback: en).
    $nbLocale = $this->locale ?? config('notifications.locale');
    $t = fn (string $key, array $replace = []) => __("notification-bell::bell.{$key}", $replace, $nbLocale);
    $nbDateLocale = $nbLocale ?: app()->getLocale();

    $badgeStyle = Theme::badgeStyle();
    $badgePosition = Theme::badgePosition();
    $toastPosition = Theme::toastPosition();
    $categories = config('notifications.categories', []);
    $prefsConfig = config('notifications.preferences', []);
    $soundFile = config('notifications.features.sound.file');

    // Ícones dos tipos declarados no config. Tipos sem ícone conhecido caem no sino.
    $iconPaths = [
        'info-circle' => 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z',
        'check-circle' => 'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z',
        'exclamation-triangle' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
        'x-circle' => 'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z',
        'bell' => 'M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z',
    ];

    $typeIcon = function ($type) use ($iconPaths) {
        $icon = config("notifications.types.{$type}.icon", 'bell');
        return $iconPaths[$icon] ?? $iconPaths['bell'];
    };
@endphp

<div
    class="nb-root {{ Theme::modeClass() }}"
    style="{{ Theme::inlineVariables() }}"
    x-data="{
        confirmClear: false,
        injectStyles() {
            if (document.getElementById('nb-styles')) return;
            const link = document.createElement('link');
            link.id = 'nb-styles';
            link.rel = 'stylesheet';
            link.href = @js(\CaiqueBispo\NotificationBell\Http\Controllers\AssetController::url('notification-bell.css'));
            document.head.appendChild(link);
        },
        open: false,
        modalOpen: false,
        selectedNotification: null,
        toasts: [],
        toastId: 0,

        {{-- Preferências são carregadas sob demanda (ver mount): enquanto não
             chegam, valem os padrões do config, que é o que a linha do banco
             conteria de qualquer forma para quem nunca mexeu nelas. --}}
        prefs: @js([
            'toasts' => $preferences['toasts_enabled'] ?? config('notifications.features.toasts.enabled', true),
            'sound' => $preferences['sound_enabled'] ?? config('notifications.features.sound.enabled', false),
            'volume' => ($preferences['sound_volume'] ?? (config('notifications.features.sound.volume', 0.5) * 100)) / 100,
            'snoozed' => $preferences['is_snoozed'] ?? false,
        ]),

        showToast(data) {
            // O dispatch() do Livewire embrulha payloads posicionais em array.
            if (Array.isArray(data)) data = data[0] ?? {};
            if (this.prefs.snoozed) return;
            if (this.prefs.sound) this.playSound(data.type || 'info');
            if (!this.prefs.toasts) return;

            const id = ++this.toastId;
            this.toasts.push({ id, kind: 'info', ...data });

            const max = {{ (int) config('notifications.features.toasts.max_stack', 3) }};
            if (this.toasts.length > max) this.toasts.splice(0, this.toasts.length - max);

            setTimeout(() => this.dismissToast(id), {{ (int) config('notifications.features.toasts.duration', 5000) }});
        },

        showUndoToast(data) {
            if (Array.isArray(data)) data = data[0] ?? {};
            const id = ++this.toastId;
            this.toasts.push({
                id,
                kind: 'undo',
                title: @js($t('deleted')),
                notificationId: data.id,
            });
            setTimeout(() => this.dismissToast(id), data.window || 8000);
        },

        dismissToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        undo(toast) {
            $wire.undoDelete(toast.notificationId);
            this.dismissToast(toast.id);
        },

        playSound(type) {
            @if($soundFile)
                try {
                    const audio = new Audio(@js($soundFile));
                    audio.volume = this.prefs.volume;
                    audio.play().catch(() => {});
                } catch (e) {}
            @else
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;

                    const ctx = new AudioCtx();
                    const vol = this.prefs.volume;

                    // [freqHz, delay s, dur s, wave, ganho relativo]
                    const tones = {
                        success: [[523.25, 0, 0.25, 'sine', 0.4], [659.25, 0.12, 0.28, 'sine', 0.4]],
                        error:   [[329.63, 0, 0.2, 'triangle', 0.5], [261.63, 0.15, 0.25, 'triangle', 0.5]],
                        warning: [[440, 0, 0.12, 'square', 0.15], [440, 0.18, 0.12, 'square', 0.15]],
                        info:    [[783.99, 0, 0.3, 'sine', 0.3]],
                    };

                    (tones[type] || tones.info).forEach(([freq, delay, dur, wave, gainMul]) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = wave;
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + delay);
                        gain.gain.setValueAtTime(0.001, ctx.currentTime);
                        gain.gain.setValueAtTime(vol * gainMul, ctx.currentTime + delay);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + dur);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + delay);
                        osc.stop(ctx.currentTime + delay + dur);
                    });

                    setTimeout(() => ctx.close(), 1000);
                } catch (e) {}
            @endif
        },

        openModal(notification) {
            this.selectedNotification = notification;
            this.open = false;
            this.modalOpen = true;
            @if(config('notifications.auto_mark_as_read', true))
                if (!notification.read_at && !notification.is_group) {
                    $wire.markAsRead(notification.id);
                }
            @endif
        },

        closeModal() {
            this.modalOpen = false;
            this.selectedNotification = null;
        },

        initEcho() {
            @if(config('notifications.broadcasting.enabled', false) && auth()->check())
                // Assinatura GLOBAL única por aba: com wire:navigate o componente
                // renasce a cada página, mas o canal é um só. A assinatura
                // re-emite um evento de janela e cada instância viva reage via
                // x-on (limpo pelo Alpine junto com o componente) — sem
                // listeners duplicados nem closures presas a componentes mortos.
                if (typeof window.Echo === 'undefined' || window.__nbEchoBound) return;
                window.__nbEchoBound = true;

                const channel = @js(str_replace('{user_id}', (string) auth()->id(), config('notifications.broadcasting.channel', 'notifications.{user_id}')));
                const eventName = '.' + @js(config('notifications.broadcasting.event', 'NotificationCreated'));
                const source = @js(config('notifications.broadcasting.private', true)) ? window.Echo.private(channel) : window.Echo.channel(channel);

                source.listen(eventName, () => {
                    window.dispatchEvent(new CustomEvent('nb-broadcast'));
                });
            @endif
        },
    }"
    x-init="injectStyles(); initEcho()"
    x-effect="if (!open) confirmClear = false"
    x-on:new-notification.window="showToast($event.detail)"
    x-on:notification-deleted.window="showUndoToast($event.detail)"
    {{-- Recarrega ao receber broadcast; o toast/som sai do detector de novas
         notificações do próprio loadNotifications, com os dados corretos. --}}
    x-on:nb-broadcast.window="$wire.loadNotifications()"
    x-on:keydown.escape.window="modalOpen ? closeModal() : (open = false)"
    @if($this->resolvePollingEnabled())
        wire:poll.{{ $this->resolvePollingInterval() }}="loadNotifications"
    @endif
>
    {{-- Região aria-live: leitores de tela anunciam novas notificações --}}
    <div class="nb-sr-only" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <p x-text="toast.title"></p>
        </template>
    </div>

    {{-- Pilha de toasts --}}
    <div class="nb-toasts nb-toasts-{{ $toastPosition }}">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="nb-toast"
                 :style="toast.type ? `border-left-color: var(--nb-type-${toast.type}, var(--nb-type-info))` : ''"
                 x-transition:enter="nb-toast-enter"
                 x-transition:enter-start="nb-toast-enter-start"
                 x-transition:enter-end="nb-toast-enter-end"
                 x-transition:leave="nb-toast-leave"
                 x-transition:leave-start="nb-toast-leave-start"
                 x-transition:leave-end="nb-toast-leave-end">
                <div class="nb-toast-body">
                    <p class="nb-toast-title" x-text="toast.title"></p>
                    <p class="nb-toast-message" x-show="toast.message" x-text="toast.message"></p>
                </div>
                <button type="button" x-show="toast.kind === 'undo'" class="nb-btn nb-btn-ghost" x-on:click="undo(toast)">
                    {{ $t('undo') }}
                </button>
                <button type="button" class="nb-icon-btn" x-on:click="dismissToast(toast.id)" aria-label="{{ $t('close') }}">
                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ $iconPaths['x-circle'] }}" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Botão do sino --}}
    <button
        type="button"
        class="nb-trigger"
        x-on:click="open = !open"
        aria-label="{{ $t('notifications') }}"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        @if($iconView = config('notifications.theme.bell_icon'))
            @include($iconView)
        @else
            <svg class="nb-trigger-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        @endif

        @if($unreadCount > 0)
            <span class="nb-badge nb-badge-{{ $badgePosition }} {{ $badgeStyle === 'dot' ? 'nb-badge-dot' : '' }} {{ $badgeStyle === 'pulse' ? 'nb-badge-pulse' : '' }}">
                @if($badgeStyle !== 'dot')
                    {{ Theme::badgeLabel($unreadCount) }}
                @endif
            </span>
        @endif
    </button>

    {{-- Backdrop mobile --}}
    <div x-show="open" x-transition.opacity class="nb-backdrop" x-on:click="open = false" style="display: none;"></div>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="nb-enter" x-transition:enter-start="nb-enter-start" x-transition:enter-end="nb-enter-end"
        x-transition:leave="nb-leave" x-transition:leave-start="nb-leave-start" x-transition:leave-end="nb-leave-end"
        x-on:click.away="open = false"
        class="nb-panel"
        style="display: none;"
    >
        <div class="nb-panel-header">
            <h3 class="nb-panel-title">
                @if($showPreferences)
                    {{ $t('preferences') }}
                @else
                    {{ $t('notifications') }}
                @endif
            </h3>
            <div class="nb-panel-actions">
                @if(!$showPreferences && $unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead" class="nb-btn nb-btn-ghost">
                        {{ $t('mark_all_read') }}
                    </button>
                @endif

                @if(!$showPreferences && count($notifications) > 0 && config('notifications.features.clear_all', true))
                    <button type="button"
                            x-on:click="confirmClear = !confirmClear"
                            class="nb-icon-btn nb-icon-btn-danger"
                            title="{{ $t('clear_all') }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif

                @if(config('notifications.preferences.enabled', true))
                    <button type="button" wire:click="togglePreferences" class="nb-icon-btn {{ $showPreferences ? 'nb-icon-btn-active' : '' }}"
                            title="{{ $showPreferences ? $t('back') : $t('preferences') }}">
                        @if($showPreferences)
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        @else
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                        @endif
                    </button>
                @endif
            </div>
        </div>

        {{-- Confirmação inline para "Limpar todas" --}}
        @if(!$showPreferences && config('notifications.features.clear_all', true))
            <div x-show="confirmClear" x-transition.opacity.duration.150ms class="nb-confirm" style="display: none;">
                <span>{{ $t('clear_all_confirm') }}</span>
                <span class="nb-confirm-actions">
                    <button type="button" class="nb-btn nb-btn-danger"
                            x-on:click="$wire.clearAll(); confirmClear = false">
                        {{ $t('delete') }}
                    </button>
                    <button type="button" class="nb-btn" x-on:click="confirmClear = false">
                        {{ $t('cancel') }}
                    </button>
                </span>
            </div>
        @endif

        @if($showPreferences)
            {{-- ===================== PREFERÊNCIAS ===================== --}}
            <div class="nb-prefs nb-list">
                @if(($preferences['is_snoozed'] ?? false) && ($preferences['snoozed_until_label'] ?? null))
                    <div class="nb-prefs-group">
                        <p class="nb-prefs-hint">{{ $t('snooze_active', ['time' => $preferences['snoozed_until_label']]) }}</p>
                        <button type="button" wire:click="clearSnooze" class="nb-btn nb-btn-primary nb-btn-block" style="margin-top: 0.5rem;">
                            {{ $t('snooze_cancel') }}
                        </button>
                    </div>
                @endif

                @if($prefsConfig['allow_toast_control'] ?? true)
                    <div class="nb-prefs-group">
                        <div class="nb-prefs-row">
                            <span>{{ $t('toasts') }}</span>
                            <button type="button"
                                    class="nb-switch {{ ($preferences['toasts_enabled'] ?? true) ? 'nb-switch-on' : '' }}"
                                    wire:click="updateToasts({{ ($preferences['toasts_enabled'] ?? true) ? 'false' : 'true' }})"
                                    role="switch" aria-checked="{{ ($preferences['toasts_enabled'] ?? true) ? 'true' : 'false' }}"
                                    aria-label="{{ $t('toasts') }}"></button>
                        </div>
                    </div>
                @endif

                @if($prefsConfig['allow_sound_control'] ?? true)
                    <div class="nb-prefs-group">
                        <div class="nb-prefs-row">
                            <span>{{ $t('sound') }}</span>
                            <button type="button"
                                    class="nb-switch {{ ($preferences['sound_enabled'] ?? false) ? 'nb-switch-on' : '' }}"
                                    wire:click="updateSound({{ ($preferences['sound_enabled'] ?? false) ? 'false' : 'true' }})"
                                    role="switch" aria-checked="{{ ($preferences['sound_enabled'] ?? false) ? 'true' : 'false' }}"
                                    aria-label="{{ $t('sound') }}"></button>
                        </div>
                        @if($preferences['sound_enabled'] ?? false)
                            <label class="nb-prefs-label" style="margin-top: 0.5rem;">{{ $t('sound_volume') }}</label>
                            <input type="range" min="0" max="100" step="5" class="nb-range"
                                   value="{{ $preferences['sound_volume'] ?? 50 }}"
                                   wire:change="updateVolume($event.target.value)">
                        @endif
                    </div>
                @endif

                @if(($prefsConfig['allow_category_control'] ?? true) && count($categories) > 0)
                    <div class="nb-prefs-group">
                        <label class="nb-prefs-label">{{ $t('categories') }}</label>
                        <div class="nb-chip-group">
                            @foreach($categories as $key => $category)
                                @php $muted = in_array($key, $preferences['muted_categories'] ?? [], true); @endphp
                                <button type="button"
                                        class="nb-chip {{ $muted ? '' : 'nb-chip-on' }}"
                                        wire:click="toggleCategory('{{ $key }}')">
                                    {{ $category['label'] ?? ucfirst($key) }}
                                </button>
                            @endforeach
                        </div>
                        <p class="nb-prefs-hint">{{ $t('categories_hint') }}</p>
                    </div>
                @endif

                @if(($prefsConfig['allow_snooze'] ?? true) && !($preferences['is_snoozed'] ?? false))
                    <div class="nb-prefs-group">
                        <label class="nb-prefs-label">{{ $t('do_not_disturb') }}</label>
                        <div class="nb-chip-group">
                            @foreach($prefsConfig['snooze_options'] ?? [] as $label => $minutes)
                                <button type="button" class="nb-chip" wire:click="snooze({{ $minutes }})">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($prefsConfig['allow_quiet_hours'] ?? true)
                    <div class="nb-prefs-group"
                         x-data="{ start: @js($preferences['quiet_hours_start'] ?? ''), end: @js($preferences['quiet_hours_end'] ?? '') }">
                        <label class="nb-prefs-label">{{ $t('quiet_hours') }}</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <label style="flex: 1; font-size: 0.72rem; color: var(--nb-text-subtle);">
                                {{ $t('quiet_hours_start') }}
                                <input type="time" class="nb-input" x-model="start"
                                       x-on:change="$wire.updateQuietHours(start, end)">
                            </label>
                            <label style="flex: 1; font-size: 0.72rem; color: var(--nb-text-subtle);">
                                {{ $t('quiet_hours_end') }}
                                <input type="time" class="nb-input" x-model="end"
                                       x-on:change="$wire.updateQuietHours(start, end)">
                            </label>
                        </div>
                        <p class="nb-prefs-hint">{{ $t('quiet_hours_hint') }}</p>
                    </div>
                @endif
            </div>
        @else
            {{-- ===================== LISTA ===================== --}}
            @if(config('notifications.features.archive', true))
                <div class="nb-tabs" role="tablist">
                    @foreach(['all' => 'tab_all', 'unread' => 'tab_unread', 'archived' => 'tab_archived'] as $tabKey => $labelKey)
                        <button type="button" role="tab"
                                class="nb-tab {{ $tab === $tabKey ? 'nb-tab-active' : '' }}"
                                aria-selected="{{ $tab === $tabKey ? 'true' : 'false' }}"
                                wire:click="setTab('{{ $tabKey }}')">
                            {{ $t($labelKey) }}
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="nb-list">
                @forelse($notifications as $notification)
                    @if($itemView = config('notifications.theme.item_view'))
                        @include($itemView, ['notification' => $notification])
                    @else
                        <div class="nb-item {{ $notification['read_at'] ? 'nb-item-read' : '' }} {{ !empty($notification['pinned_at']) ? 'nb-item-pinned' : '' }}"
                             role="button" tabindex="0"
                             x-on:click="openModal({{ json_encode($notification) }})"
                             x-on:keydown.enter="openModal({{ json_encode($notification) }})">

                            @if(!$notification['read_at'])
                                <span class="nb-unread-dot" aria-hidden="true"></span>
                            @endif

                            @if(!empty($notification['image_url']))
                                <img src="{{ $notification['image_url'] }}" alt="" class="nb-item-avatar">
                            @else
                                <span class="nb-item-icon" style="background-color: var(--nb-type-{{ $notification['type'] ?: 'info' }}, var(--nb-type-info));">
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="{{ $typeIcon($notification['type'] ?: 'info') }}" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            @endif

                            <div class="nb-item-body">
                                <div class="nb-item-head">
                                    <p class="nb-item-title">
                                        {{ $notification['title'] }}
                                        @if(!empty($notification['is_group']))
                                            <span class="nb-tag">{{ $t('grouped_count', ['count' => $notification['group_count']]) }}</span>
                                        @endif
                                    </p>

                                    <span class="nb-item-controls">
                                        @if(!empty($notification['is_group']))
                                            @if(!empty($notification['group_unread']))
                                                <button type="button" class="nb-icon-btn" title="{{ $t('mark_as_read') }}"
                                                        wire:click="markGroupAsRead({{ json_encode($notification['group_ids']) }})" x-on:click.stop>
                                                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ $iconPaths['check-circle'] }}" clip-rule="evenodd"/></svg>
                                                </button>
                                            @endif
                                        @else
                                            @if(!$notification['read_at'])
                                                <button type="button" class="nb-icon-btn" title="{{ $t('mark_as_read') }}"
                                                        wire:click="markAsRead({{ $notification['id'] }})" x-on:click.stop>
                                                    <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ $iconPaths['check-circle'] }}" clip-rule="evenodd"/></svg>
                                                </button>
                                            @endif

                                            @if(config('notifications.features.pin', true) && $tab !== 'archived')
                                                <button type="button" class="nb-icon-btn {{ !empty($notification['pinned_at']) ? 'nb-icon-btn-active' : '' }}"
                                                        title="{{ !empty($notification['pinned_at']) ? $t('unpin') : $t('pin') }}"
                                                        wire:click="togglePin({{ $notification['id'] }})" x-on:click.stop>
                                                    <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 2l2 5h5l-4 3.5L14.5 16 10 12.8 5.5 16 7 10.5 3 7h5l2-5z"/></svg>
                                                </button>
                                            @endif

                                            @if(config('notifications.features.archive', true))
                                                @if($tab === 'archived')
                                                    <button type="button" class="nb-icon-btn" title="{{ $t('unarchive') }}"
                                                            wire:click="unarchiveNotification({{ $notification['id'] }})" x-on:click.stop>
                                                        <svg fill="currentColor" viewBox="0 0 20 20"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v1H3V5zM3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm7 6l3-3h-2V9H9v2H7l3 3z"/></svg>
                                                    </button>
                                                @else
                                                    <button type="button" class="nb-icon-btn" title="{{ $t('archive') }}"
                                                            wire:click="archiveNotification({{ $notification['id'] }})" x-on:click.stop>
                                                        <svg fill="currentColor" viewBox="0 0 20 20"><path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v1H3V5zM3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm7 1l-3 3h2v2h2v-2h2l-3-3z"/></svg>
                                                    </button>
                                                @endif
                                            @endif

                                            <button type="button" class="nb-icon-btn nb-icon-btn-danger" title="{{ $t('delete') }}"
                                                    wire:click="deleteNotification({{ $notification['id'] }})" x-on:click.stop>
                                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ $iconPaths['x-circle'] }}" clip-rule="evenodd"/></svg>
                                            </button>
                                        @endif
                                    </span>
                                </div>

                                <p class="nb-item-message">{!! $notification['message'] !!}</p>

                                <p class="nb-item-meta">
                                    {{ \Carbon\Carbon::parse($notification['created_at'])->locale($nbDateLocale)->diffForHumans() }}
                                    @if(!empty($notification['category']) && isset($categories[$notification['category']]))
                                        · {{ $categories[$notification['category']]['label'] ?? $notification['category'] }}
                                    @endif
                                </p>

                                @if(isset($notification['data']['actions']) && is_array($notification['data']['actions']))
                                    <span class="nb-item-actions">
                                        @foreach($notification['data']['actions'] as $action)
                                            <a href="{{ $action['url'] ?? '#' }}" x-on:click.stop
                                               class="nb-btn {{ ($action['style'] ?? 'neutral') === 'primary' ? 'nb-btn-primary' : (($action['style'] ?? 'neutral') === 'danger' ? 'nb-btn-danger' : '') }}">
                                                {{ $action['label'] }}
                                            </a>
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="nb-empty">
                        <span class="nb-empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </span>
                        <h4 class="nb-empty-title">{{ $t('empty_title') }}</h4>
                        <p class="nb-empty-text">{{ $t('empty_text') }}</p>
                    </div>
                @endforelse
            </div>

            @if(config('notifications.features.history_page', true))
                <div class="nb-panel-footer">
                    <a href="{{ route(config('notifications.route.name', 'notifications.') . 'history') }}" class="nb-btn nb-btn-ghost nb-btn-block">
                        {{ $t('view_all') }}
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="modalOpen" x-transition.opacity class="nb-overlay" x-on:click="closeModal()" style="display: none;"
         role="dialog" aria-modal="true">
        <div class="nb-modal" x-on:click.stop x-show="modalOpen"
             x-transition:enter="nb-enter" x-transition:enter-start="nb-enter-start" x-transition:enter-end="nb-enter-end">
            <div class="nb-modal-header">
                <h3 class="nb-modal-title">
                    <span class="nb-item-icon" x-show="selectedNotification"
                          :style="`background-color: var(--nb-type-${selectedNotification?.type || 'info'}, var(--nb-type-info))`">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="{{ $iconPaths['bell'] }}" clip-rule="evenodd"/></svg>
                    </span>
                    <span x-text="selectedNotification?.title"></span>
                </h3>
                <button type="button" class="nb-icon-btn" x-on:click="closeModal()" aria-label="{{ $t('close') }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="nb-modal-body">
                <div x-html="selectedNotification?.message"></div>

                <template x-if="selectedNotification?.data?.actions">
                    <div class="nb-item-actions">
                        <template x-for="action in selectedNotification.data.actions">
                            <a :href="action.url || '#'" x-text="action.label"
                               :class="'nb-btn ' + ((action.style || 'neutral') === 'primary' ? 'nb-btn-primary' : (action.style === 'danger' ? 'nb-btn-danger' : ''))"></a>
                        </template>
                    </div>
                </template>

                <div class="nb-modal-meta">
                    <span x-text="selectedNotification?.created_at ? new Date(selectedNotification.created_at).toLocaleDateString(@js(str_replace('_', '-', $nbDateLocale)), {
                        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    }) : ''"></span>
                    <span class="nb-tag" :class="selectedNotification?.read_at ? '' : 'nb-tag-unread'"
                          x-text="selectedNotification?.read_at ? @js($t('read')) : @js($t('unread'))"></span>
                </div>
            </div>

            <div class="nb-modal-footer">
                <div style="display: flex; gap: 0.5rem;">
                    <template x-if="selectedNotification && !selectedNotification.read_at && !selectedNotification.is_group">
                        <button type="button" class="nb-btn nb-btn-primary"
                                x-on:click="$wire.markAsRead(selectedNotification.id); closeModal()">
                            {{ $t('mark_as_read') }}
                        </button>
                    </template>
                    <template x-if="selectedNotification?.action_url">
                        <a :href="selectedNotification.action_url" class="nb-btn nb-btn-primary">
                            {{ $t('view_details') }}
                        </a>
                    </template>
                </div>
                <button type="button" class="nb-btn nb-btn-danger" x-show="!selectedNotification?.is_group"
                        x-on:click="$wire.deleteNotification(selectedNotification?.id); closeModal()">
                    {{ $t('delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
