<!DOCTYPE html>
<html lang="pt-BR" x-data="tallstackui_darkTheme()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Notificações</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        /* Cursor pointer para todos os botões */
        button,
        .cursor-pointer,
        [role="button"],
        a[href],
        select,
        input[type="submit"],
        input[type="button"] {
            cursor: pointer !important;
        }
        
        .animate-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .sidebar-transition {
            transition: transform 0.3s ease;
        }
        
        .notification-card {
            transition: all 0.2s ease;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-error { background-color: #fee2e2; color: #b91c1c; }
        
        .dark .badge-info { background-color: #1e3a8a; color: #dbeafe; }
        .dark .badge-success { background-color: #065f46; color: #d1fae5; }
        .dark .badge-warning { background-color: #92400e; color: #fef3c7; }
        .dark .badge-error { background-color: #7f1d1d; color: #fee2e2; }
        
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }
        
        /* Glass effect for modals */
        .glass-modal {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dark .glass-modal {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-content {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .dark .glass-content {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(75, 85, 99, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        /* Button hover effects */
        .btn-hover {
            transition: all 0.2s ease;
        }
        
        .btn-hover:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-hover:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" x-cloak x-bind:class="{ 'dark': darkTheme }">
    <!-- Mobile menu button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 rounded-md bg-white dark:bg-gray-800 shadow-md text-gray-600 dark:text-gray-300 btn-hover cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    
    <!-- Theme toggle button -->
    <div class="fixed top-4 right-4 z-50">
        <button @click="toggle()" class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-md text-gray-600 dark:text-gray-300 btn-hover cursor-pointer">
            <svg x-show="!darkTheme" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg x-show="darkTheme" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>
    
    <div class="flex min-h-screen" x-data="{ isSidebarOpen: false }">
        <!-- Sidebar Overlay -->
        <div x-show="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 md:hidden cursor-pointer"></div>
        
        <!-- Sidebar -->
        <div class="w-64 hidden md:flex flex-col shrink-0 sidebar-transition bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg z-30 fixed h-full"
             :class="{ 'flex transform translate-x-0': isSidebarOpen, 'hidden md:flex': !isSidebarOpen }"
             @click.away="isSidebarOpen = false">
            <div class="flex flex-col h-full">
                <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Notificações</h2>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar py-4">
                    <nav class="px-4 space-y-1">
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 cursor-pointer btn-hover">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Gerenciar Notificações
                        </a>
                    </nav>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 md:ml-64">
            <div class="py-6 px-4 sm:p-6 md:py-8 md:px-8">
                <!-- Header -->
                <div class="mb-8 animate-in">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gerenciar Notificações</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Crie e gerencie notificações para os usuários</p>
                        </div>
                        <button class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 flex items-center space-x-2 cursor-pointer btn-hover" @click="$dispatch('open-modal', 'createModal')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            <span>Nova Notificação</span>
                        </button>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden animate-in">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-600">
                        <h5 class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtros
                        </h5>
                    </div>
                    <div class="p-6">
                        <form id="filterForm" method="GET" action="{{ route('notifications.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="search_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input type="text" class="pl-10 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-text" id="search_title" name="search_title" value="{{ request('search_title') }}" placeholder="Buscar por título">
                                    </div>
                                </div>
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuário</label>
                                    <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="user_id" name="user_id">
                                        <option value="">Todos os usuários</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo</label>
                                    <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="type" name="type">
                                        <option value="">Todos os tipos</option>
                                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Informação</option>
                                        <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Sucesso</option>
                                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Aviso</option>
                                        <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Erro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end mt-6 space-x-3">
                                <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg transition-colors duration-200 flex items-center cursor-pointer btn-hover">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    Limpar
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-colors duration-200 flex items-center cursor-pointer btn-hover">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Aplicar Filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabela de Notificações -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden animate-in">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-600 flex items-center justify-between">
                        <h5 class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notificações
                        </h5>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $notifications->total() }} itens</span>
                    </div>
                    <div class="p-0" id="notifications-table-container">
                        @include('notification-bell::partials.notifications-table')
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Criação -->
    <div id="createModal" x-data="{ open: false }" x-show="open" @open-modal.window="if ($event.detail === 'createModal') open = true" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 glass-modal transition-opacity" x-show="open" @click="open = false"></div>
            
            <div class="glass-content rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full mx-4" 
                 x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="border-b border-gray-200/30 dark:border-gray-700/30 px-6 py-4 flex justify-between items-center backdrop-blur-sm">
                    <h5 class="font-semibold text-gray-800 dark:text-white text-lg flex items-center">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Criar Nova Notificação
                    </h5>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500 focus:outline-none rounded-full p-2 hover:bg-gray-100/50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-4 backdrop-blur-sm">
                    <form id="notificationForm" class="space-y-4">
                        <input type="hidden" id="notification_id" name="notification_id">
                        <input type="hidden" id="formMethod" name="_method" value="POST">
                        @csrf
                        
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título *</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-text backdrop-blur-sm" id="title" name="title" required placeholder="Digite o título da notificação">
                            <p id="title-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mensagem *</label>
                            <div id="quill-editor-container" style="height: 200px;" class="mb-2 border rounded-lg">
                                <!-- Quill editor será inicializado aqui via JavaScript -->
                            </div>
                            <input type="hidden" id="message" name="message" required>
                            <p id="message-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo *</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="type" name="type" required>
                                <option value="">Selecione o tipo</option>
                                <option value="info">Informação</option>
                                <option value="success">Sucesso</option>
                                <option value="warning">Aviso</option>
                                <option value="error">Erro</option>
                            </select>
                            <p id="type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="recipientUser" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuário (opcional)</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="recipientUser" name="user_id">
                                <option value="">Todos os usuários</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <p id="recipientUser-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL de Ação (opcional)</label>
                            <input type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-text backdrop-blur-sm" id="url" name="action_url" placeholder="https://exemplo.com/acao">
                        </div>
                        
                        <div>
                            <label for="processing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Processamento *</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="processing_type" name="processing_type" required>
                                <option value="immediate">Envio Imediato</option>
                                <option value="queue">Processar pela Fila</option>
                            </select>
                            <p id="processing_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            <p id="url-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                    </form>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Cancelar</button>
                    <button type="submit" form="notificationForm" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Criar Notificação</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Edição -->
    <div id="editModal" x-data="{ open: false }" x-show="open" @open-modal.window="if ($event.detail === 'editModal') open = true" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 glass-modal transition-opacity" x-show="open" @click="open = false"></div>
            
            <div class="glass-content rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full mx-4" 
                 x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="border-b border-gray-200/30 dark:border-gray-700/30 px-6 py-4 flex justify-between items-center backdrop-blur-sm">
                    <h5 class="font-semibold text-gray-800 dark:text-white text-lg flex items-center">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        Editar Notificação
                    </h5>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500 focus:outline-none rounded-full p-2 hover:bg-gray-100/50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-4 backdrop-blur-sm">
                    <form id="editNotificationForm" class="space-y-4">
                        <input type="hidden" id="edit_notification_id" name="notification_id">
                        <input type="hidden" name="_method" value="PUT">
                        @csrf
                        
                        <div>
                            <label for="edit_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título *</label>
                            <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-text backdrop-blur-sm" id="edit_title" name="title" required placeholder="Digite o título da notificação">
                            <p id="edit_title-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mensagem *</label>
                            <div id="edit-quill-editor-container" style="height: 200px;" class="mb-2 border rounded-lg">
                                <!-- Quill editor será inicializado aqui via JavaScript -->
                            </div>
                            <input type="hidden" id="edit_message" name="message" required>
                            <p id="edit_message-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo *</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="edit_type" name="type" required>
                                <option value="">Selecione o tipo</option>
                                <option value="info">Informação</option>
                                <option value="success">Sucesso</option>
                                <option value="warning">Aviso</option>
                                <option value="error">Erro</option>
                            </select>
                            <p id="edit_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_recipientUser" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuário (opcional)</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="edit_recipientUser" name="user_id">
                                <option value="">Todos os usuários</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <p id="edit_recipientUser-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">URL de Ação (opcional)</label>
                            <input type="url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-text backdrop-blur-sm" id="edit_url" name="action_url" placeholder="https://exemplo.com/acao">
                            <p id="edit_url-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_processing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Processamento *</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer backdrop-blur-sm" id="edit_processing_type" name="processing_type" required>
                                <option value="immediate">Envio Imediato</option>
                                <option value="queue">Processar pela Fila</option>
                            </select>
                            <p id="edit_processing_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                    </form>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Cancelar</button>
                    <button type="submit" form="editNotificationForm" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div id="deleteModal" x-data="{ open: false }" x-show="open" @open-modal.window="if ($event.detail === 'deleteModal') open = true" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 glass-modal transition-opacity" x-show="open" @click="open = false"></div>
            
            <div class="glass-content rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full mx-4" 
                 x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="border-b border-gray-200/30 dark:border-gray-700/30 px-6 py-4 flex justify-between items-center backdrop-blur-sm">
                    <h5 class="font-semibold text-gray-800 dark:text-white text-lg flex items-center">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-red-500 to-pink-600 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        Confirmar Exclusão
                    </h5>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500 focus:outline-none rounded-full p-2 hover:bg-gray-100/50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 backdrop-blur-sm">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Excluir Notificação</h3>
                            <p class="text-gray-600 dark:text-gray-300">Tem certeza que deseja excluir esta notificação? Esta ação não pode ser desfeita e a notificação será removida permanentemente.</p>
                        </div>
                    </div>
                    <input type="hidden" id="delete_notification_id">
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Cancelar</button>
                    <button type="button" id="confirmDelete" class="px-4 py-2 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Excluir Definitivamente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Tailwind Dark Mode Helper -->
    <script>
        function tallstackui_darkTheme() {
            return {
                dark: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                toggle() {
                    this.dark = !this.dark;
                    localStorage.theme = this.dark ? 'dark' : 'light';
                    document.documentElement.classList.toggle('dark', this.dark);
                },
                init() {
                    document.documentElement.classList.toggle('dark', this.dark);
                }
            }
        }
        
        // Inicializar Alpine.js quando o documento estiver pronto
        document.addEventListener('alpine:init', () => {
            Alpine.data('tallstackui_darkTheme', tallstackui_darkTheme);
        });
    </script>

    <script>
        // Configurar AJAX para enviar o token CSRF em todas as requisições
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $(document).ready(function() {
            // Alpine.js já gerencia os modais, não precisamos inicializá-los com Bootstrap
            
            // Inicializar Quill Editor para criação
            var quillCreate = new Quill('#quill-editor-container', {
                theme: 'snow',
                placeholder: 'Digite a mensagem da notificação...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['clean']
                    ]
                }
            });
            
            // Inicializar Quill Editor para edição
            var quillEdit = new Quill('#edit-quill-editor-container', {
                theme: 'snow',
                placeholder: 'Digite a mensagem da notificação...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['clean']
                    ]
                }
            });
            
            // Atualizar campo hidden quando o conteúdo do editor mudar
            quillCreate.on('text-change', function() {
                $('#message').val(quillCreate.root.innerHTML);
            });
            
            quillEdit.on('text-change', function() {
                $('#edit_message').val(quillEdit.root.innerHTML);
            });
            
            // Formulário de criação
            $('#createModal form').on('submit', function(e) {
                e.preventDefault();
                
                // Garantir que o conteúdo do Quill Editor seja capturado
                $('#message').val(quillCreate.root.innerHTML);
                
                const formData = $(this).serialize();
                
                $.ajax({
                    url: '{{ route("notifications.store") }}',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'createModal' }));
                            $('#notificationForm')[0].reset();
                            refreshNotificationsTable();
                            showAlert('success', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON.errors;
                        displayFormErrors(errors);
                    }
                });
            });
            
            // Editar notificação
            $(document).on('click', '.edit-notification', function() {
                const notificationId = $(this).data('id');
                
                // Limpar erros anteriores
                clearFormErrors();
                
                // Carregar dados da notificação
                $.ajax({
                    url: `/notifications/${notificationId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const notification = response.notification;
                            
                            // Preencher o formulário
                            $('#edit_notification_id').val(notification.id);
                            $('#edit_title').val(notification.title);
                            $('#edit_message').val(notification.message);
                            $('#edit_type').val(notification.type);
                            $('#edit_recipientUser').val(notification.user_id || '');
                            $('#edit_url').val(notification.action_url || '');
                            // Definir o tipo de processamento como imediato por padrão
                            $('#edit_processing_type').val('immediate');
                            
                            // Atualizar o conteúdo do Quill Editor
                            setTimeout(function() {
                                quillEdit.root.innerHTML = notification.message;
                            }, 300); // Pequeno delay para garantir que o editor esteja inicializado
                            
                            // Abrir o modal
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editModal' }));
                        }
                    }
                });
            });
            
            // Formulário de edição
            $('#editNotificationForm').on('submit', function(e) {
                e.preventDefault();
                
                // Garantir que o conteúdo do Quill Editor seja capturado
                $('#edit_message').val(quillEdit.root.innerHTML);
                
                const notificationId = $('#edit_notification_id').val();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: `/notifications/${notificationId}`,
                    type: 'PUT',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'editModal' }));
                            refreshNotificationsTable();
                            showAlert('success', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON.errors;
                        displayFormErrors(errors, 'edit_');
                    }
                });
            });
            
            // Excluir notificação
            $(document).on('click', '.delete-notification', function() {
                const notificationId = $(this).data('id');
                $('#delete_notification_id').val(notificationId);
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteModal' }));
            });
            
            // Confirmar exclusão
            $('#confirmDelete').on('click', function() {
                const notificationId = $('#delete_notification_id').val();
                
                $.ajax({
                    url: `/notifications/${notificationId}`,
                    type: 'DELETE',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'deleteModal' }));
                            refreshNotificationsTable();
                            showAlert('success', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Erro ao excluir notificação');
                    }
                });
            });
            
            // Filtro com AJAX
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                refreshNotificationsTable($(this).serialize());
            });
            
            // Excluir todas as notificações
            $(document).on('click', '#delete-all-notifications', function() {
                if (confirm('Tem certeza que deseja excluir todas as notificações?')) {
                    $.ajax({
                        url: '{{ route("notifications.destroy.all") }}',
                        type: 'DELETE',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                refreshNotificationsTable();
                                showAlert('success', response.message);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'Erro ao excluir todas as notificações');
                        }
                    });
                }
            });
            
            // Funções auxiliares
            function refreshNotificationsTable(filterParams = '') {
                $.ajax({
                    url: '{{ route("notifications.index") }}' + (filterParams ? '?' + filterParams : ''),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#notifications-table-container').html(response.html);
                        }
                    }
                });
            }
            
            function displayFormErrors(errors, prefix = '') {
                // Limpar erros anteriores
                clearFormErrors(prefix);
                
                // Exibir novos erros
                $.each(errors, function(field, messages) {
                    const fieldId = prefix + field;
                    const inputField = $(`#${fieldId}`);
                    inputField.addClass('border-red-500');
                    
                    // Adicionar mensagem de erro
                    const errorElement = $(`#${fieldId}-error`);
                    errorElement.text(messages[0]);
                    errorElement.removeClass('hidden');
                });
            }
            
            function clearFormErrors(prefix = '') {
                if (prefix) {
                    $(`input[id^="${prefix}"], select[id^="${prefix}"], textarea[id^="${prefix}"]`).removeClass('border-red-500');
                    $(`[id^="${prefix}"][id$="-error"]`).addClass('hidden').text('');
                } else {
                    $('input:not([id^="edit_"]), select:not([id^="edit_"]), textarea:not([id^="edit_"])').
                        removeClass('border-red-500');
                    $('[id$="-error"]:not([id^="edit_"])').addClass('hidden').text('');
                }
            }
            
            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 
                                  type === 'danger' ? 'bg-red-100 border-red-400 text-red-700' : 
                                  type === 'warning' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 
                                  'bg-blue-100 border-blue-400 text-blue-700';
                
                const alertHtml = `
                    <div class="${alertClass} px-4 py-3 rounded-lg relative border mb-4 animate-in" role="alert">
                        <span class="block sm:inline">${message}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                            <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <title>Close</title>
                                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                            </svg>
                        </button>
                    </div>
                `;
                
                // Adicionar alerta no topo da página
                $('.main-content').prepend(alertHtml);
                
                // Remover alerta após 5 segundos
                setTimeout(function() {
                    $('.main-content .border').first().remove();
                }, 5000);
            }
        });
    </script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>