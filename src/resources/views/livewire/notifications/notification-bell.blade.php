<div x-data="{ 
    open: false, 
    modalOpen: false,
    selectedNotification: null,
    openModal(notification) {
        this.selectedNotification = notification;
        this.modalOpen = true;
    },
    closeModal() {
        this.modalOpen = false;
        this.selectedNotification = null;
    }
}" x-on:keydown.escape="modalOpen ? closeModal() : (open = false)" class="relative">
    {{-- Botão de notificações --}}
    <button 
        x-on:click="open = !open"
        class="cursor-pointer relative p-2 group rounded-full transition-all duration-200 ease-out"
        aria-label="Notificações"
        aria-haspopup="true"
        :aria-expanded="open"
    >
        <div class="relative p-1.5">
            <svg class="w-[30px] h-[30px] text-gray-700 dark:text-gray-200 transition-colors" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            
            @if($unreadCount > 0)
                <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center 
                            w-4 h-4 text-[10px] font-semibold leading-none 
                            text-white bg-red-500 rounded-full
                            transform transition-transform group-hover:scale-110">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </div>
    </button>
    
    {{-- Overlay para o dropdown --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="sm:hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-40"
        x-on:click="open = false"
        style="display: none;"
    ></div>
    
    {{-- Dropdown de notificações --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        x-on:click.away="open = false"
        class="fixed sm:absolute 
               top-16 left-4 right-4 
               sm:top-full sm:left-auto sm:right-0 sm:left-auto
               mt-2 w-auto sm:w-80 z-50 
               bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl
               border border-gray-200/50 dark:border-gray-700/50 
               rounded-2xl shadow-2xl overflow-hidden"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200/50 dark:border-gray-700/50">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                <div class="w-6 h-6 mr-2 rounded-md bg-blue-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                    </svg>
                </div>
                Notificações
            </h3>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="cursor-pointer text-sm font-medium text-blue-500 hover:text-blue-600 
                           transition-colors px-2 py-1 rounded-md hover:bg-blue-50 dark:hover:bg-blue-500/10"
                >
                    Limpar
                </button>
            @endif
        </div>
        <div class="max-h-[60vh] sm:max-h-96 overflow-y-auto apple-scrollbar">
            @forelse($notifications as $notification)
                <div class="group relative border-b border-gray-200/30 dark:border-gray-700/30 last:border-b-0 
                          hover:bg-gray-50/50 dark:hover:bg-gray-800/50 
                          transition-colors duration-200 cursor-pointer"
                     x-on:click="openModal({{ json_encode($notification) }})">
                    
                    <div class="flex items-start px-4 py-3 {{ $notification['read_at'] ? 'opacity-60' : '' }}">
                        <div class="flex-shrink-0 mr-3 mt-0.5">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-medium
                                {{ $notification['type'] === 'success' ? 'bg-green-500' : '' }}
                                {{ $notification['type'] === 'warning' ? 'bg-orange-500' : '' }}
                                {{ $notification['type'] === 'error' ? 'bg-red-500' : '' }}
                                {{ $notification['type'] === 'info' ? 'bg-blue-500' : '' }}
                                {{ empty($notification['type']) ? 'bg-gray-500 dark:bg-gray-400' : '' }}">
                                @if($notification['type'] === 'success')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($notification['type'] === 'warning')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                @elseif($notification['type'] === 'error')
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex-1 min-w-0 pr-2">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">
                                        {{ $notification['title'] }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-1 opacity-70 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200 flex-shrink-0">
                                    @if(!$notification['read_at'])
                                        <button 
                                            wire:click="markAsRead({{ $notification['id'] }})"
                                            x-on:click.stop
                                            class="cursor-pointer w-6 h-6 rounded-full flex items-center justify-center
                                                   text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10
                                                   transition-all duration-150"
                                            title="Marcar como lida"
                                        >
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    @endif
                                    
                                    <button 
                                        wire:click="deleteNotification({{ $notification['id'] }})"
                                        x-on:click.stop
                                        class="cursor-pointer w-6 h-6 rounded-full flex items-center justify-center
                                               text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10
                                               transition-all duration-150"
                                        title="Excluir"
                                    >
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed mb-2">
                                {{ $notification['message'] }}
                            </p>
                            
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>
                        
                            @if(!$notification['read_at'])
                                <div class="absolute top-3 left-1 w-2 h-2 bg-blue-500 rounded-full"></div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <div class="mx-auto w-12 h-12 mb-4 rounded-full bg-gray-100 dark:bg-gray-800 
                               flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                        Nenhuma notificação
                    </h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Novas notificações aparecerão aqui
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal da Notificação --}}
    <div 
        x-show="modalOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] flex items-center justify-center p-4"
        x-on:click="closeModal()"
        style="display: none;"
    >
        <div 
            x-show="modalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-on:click.stop
            class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-hidden"
        >
            {{-- Header do Modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center">
                    <div x-show="selectedNotification" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3"
                         :class="{
                             'bg-green-500': selectedNotification?.type === 'success',
                             'bg-orange-500': selectedNotification?.type === 'warning',
                             'bg-red-500': selectedNotification?.type === 'error',
                             'bg-blue-500': selectedNotification?.type === 'info',
                             'bg-gray-500 dark:bg-gray-400': !selectedNotification?.type || selectedNotification?.type === ''
                         }">
                        <template x-if="selectedNotification?.type === 'success'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </template>
                        <template x-if="selectedNotification?.type === 'warning'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </template>
                        <template x-if="selectedNotification?.type === 'error'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </template>
                        <template x-if="!selectedNotification?.type || selectedNotification?.type === 'info' || selectedNotification?.type === ''">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </template>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="selectedNotification?.title"></h3>
                </div>
                <button 
                    x-on:click="closeModal()"
                    class="w-8 h-8 rounded-full flex items-center justify-center
                           text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                           hover:bg-gray-100 dark:hover:bg-gray-800
                           transition-colors duration-150"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Conteúdo do Modal --}}
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto apple-scrollbar">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-wrap"
                           x-text="selectedNotification?.message"></p>
                    </div>
                    
                    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 pt-2 border-t border-gray-200/30 dark:border-gray-700/30">
                        <span x-text="selectedNotification?.created_at ? new Date(selectedNotification.created_at).toLocaleDateString('pt-BR', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric', 
                            hour: '2-digit', 
                            minute: '2-digit' 
                        }) : ''"></span>
                        <div class="flex items-center space-x-2">
                            <span x-show="!selectedNotification?.read_at" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300">
                                Não lida
                            </span>
                            <span x-show="selectedNotification?.read_at" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                Lida
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Footer do Modal --}}
            <div class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50 flex justify-between items-center">
                <div class="flex space-x-2">
                    <template x-if="selectedNotification && !selectedNotification.read_at">
                        <button 
                            x-on:click="$wire.markAsRead(selectedNotification.id); closeModal()"
                            class="cursor-pointer inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg
                                   text-blue-700 bg-blue-100 hover:bg-blue-200
                                   dark:text-blue-300 dark:bg-blue-500/20 dark:hover:bg-blue-500/30
                                   transition-colors duration-150"
                        >
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Marcar como lida
                        </button>
                    </template>
                    
                    <template x-if="selectedNotification?.action_url">
                        <a :href="selectedNotification.action_url" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg
                                  text-white bg-blue-600 hover:bg-blue-700
                                  transition-colors duration-150">
                            Ver detalhes
                        </a>
                    </template>
                </div>
                
                <button 
                    x-on:click="$wire.deleteNotification(selectedNotification?.id); closeModal()"
                    class="cursor-pointer inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg
                           text-red-700 bg-red-100 hover:bg-red-200
                           dark:text-red-300 dark:bg-red-500/20 dark:hover:bg-red-500/30
                           transition-colors duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 7a1 1 0 112 0v4a1 1 0 11-2 0V7z" clip-rule="evenodd"></path>
                    </svg>
                    Excluir
                </button>
            </div>
        </div>
    </div>

    <style>
        .apple-scrollbar {
            scrollbar-width: none;
        }
        
        .apple-scrollbar::-webkit-scrollbar {
            width: 0;
            background: transparent;
        }
        
        .apple-scrollbar:hover {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        }
        
        .apple-scrollbar:hover::-webkit-scrollbar {
            width: 6px;
        }
        
        .apple-scrollbar:hover::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .apple-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
        
        .dark .apple-scrollbar:hover {
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }
        
        .dark .apple-scrollbar:hover::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        button:focus {
            outline: none;
        }
        
        @media (max-width: 640px) {
            .apple-scrollbar {
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</div>