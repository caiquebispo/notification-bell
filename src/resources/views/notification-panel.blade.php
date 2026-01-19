<!DOCTYPE html>
<html lang="pt-BR" x-data="tallstackui_darkTheme()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de NotificaÃ§Ãµes</title>
    
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
        
        /* Cursor pointer para todos os botÃµes */
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
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">NotificaÃ§Ãµes</h2>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar py-4">
                    <nav class="px-4 space-y-1">
                        <a href="#" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 cursor-pointer btn-hover">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Gerenciar NotificaÃ§Ãµes
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
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gerenciar NotificaÃ§Ãµes</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">VisÃ£o geral do sistema de notificaÃ§Ãµes</p>
                        </div>
                        <div class="flex gap-3">
                            <button class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200 flex items-center space-x-2 cursor-pointer btn-hover" @click="$dispatch('open-modal', 'createModal')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                <span>Nova NotificaÃ§Ã£o</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <!-- Total -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Enviadas</p>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</h3>
                            </div>
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>

                        <!-- Success -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Sucesso</p>
                                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['success'] }}</h3>
                            </div>
                            <div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Errors -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Erros</p>
                                <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['error'] }}</h3>
                            </div>
                            <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Unread -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">NÃ£o Lidas</p>
                                <h3 class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ $stats['unread'] }}</h3>
                            </div>
                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 overflow-hidden animate-in" x-data="{ expanded: false }">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-600 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                        <h5 class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtros
                        </h5>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="p-6" x-show="expanded" x-collapse style="display: none;">
                        <form id="filterForm" method="GET" action="{{ route('notifications.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="col-span-1 md:col-span-2">
                                    <label for="search_title" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Buscar TÃ­tulo</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <input type="text" class="pl-9 w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" id="search_title" name="search_title" value="{{ request('search_title') }}" placeholder="Buscar por tÃ­tulo...">
                                    </div>
                                </div>
                                <div>
                                    <label for="user_id" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">UsuÃ¡rio</label>
                                    <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="user_id" name="user_id">
                                        <option value="">Todos</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="type" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Tipo</label>
                                    <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="type" name="type">
                                        <option value="">Todos</option>
                                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>InformaÃ§Ã£o</option>
                                        <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Sucesso</option>
                                        <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Aviso</option>
                                        <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Erro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-end mt-4 space-x-2">
                                <a href="{{ route('notifications.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Limpar
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm shadow-sm transition-colors duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabela de NotificaÃ§Ãµes -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden animate-in">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-600 flex items-center justify-between">
                        <h5 class="font-semibold text-gray-700 dark:text-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            NotificaÃ§Ãµes
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
    
    <!-- Modal de CriaÃ§Ã£o -->
    <div id="createModal" x-data="{ open: false }" x-show="open" @open-modal.window="if ($event.detail === 'createModal') open = true" @close-modal.window="if ($event.detail === 'createModal') open = false" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
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
                        Criar Nova NotificaÃ§Ã£o
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
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TÃ­tulo *</label>
                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" id="title" name="title" required placeholder="Digite o tÃ­tulo da notificaÃ§Ã£o">
                            <p id="title-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mensagem *</label>
                            <div id="quill-editor-container" style="height: 150px;" class="mb-1 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700/50 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 focus-within:bg-white dark:focus-within:bg-gray-700 transition-all duration-200">
                                <!-- Quill editor serÃ¡ inicializado aqui via JavaScript -->
                            </div>
                            <input type="hidden" id="message" name="message" required>
                            <p id="message-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo *</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="type" name="type" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="info">InformaÃ§Ã£o</option>
                                    <option value="success">Sucesso</option>
                                    <option value="warning">Aviso</option>
                                    <option value="error">Erro</option>
                                </select>
                                <p id="type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                            
                            <div>
                                <label for="recipientUser" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">UsuÃ¡rio (opcional)</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="recipientUser" name="user_id">
                                    <option value="">Todos os usuÃ¡rios</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <p id="recipientUser-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL de AÃ§Ã£o (opcional)</label>
                                <input type="url" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" id="url" name="action_url" placeholder="https://exemplo.com/acao">
                                <p id="url-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                            
                            <div>
                                <label for="processing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Processamento *</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="processing_type" name="processing_type" required>
                                    <option value="immediate">Envio Imediato</option>
                                    <option value="queue">Agendar na Fila</option>
                                </select>
                                <p id="processing_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Cancelar</button>
                    <button type="submit" form="notificationForm" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Criar NotificaÃ§Ã£o</button>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Modal de EdiÃ§Ã£o -->
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
                        Editar NotificaÃ§Ã£o
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
                            <label for="edit_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TÃ­tulo *</label>
                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" id="edit_title" name="title" required placeholder="Digite o tÃ­tulo da notificaÃ§Ã£o">
                            <p id="edit_title-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div>
                            <label for="edit_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mensagem *</label>
                            <div id="edit-quill-editor-container" style="height: 150px;" class="mb-1 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700/50 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 focus-within:bg-white dark:focus-within:bg-gray-700 transition-all duration-200">
                                <!-- Quill editor serÃ¡ inicializado aqui via JavaScript -->
                            </div>
                            <input type="hidden" id="edit_message" name="message" required>
                            <p id="edit_message-error" class="text-red-500 text-sm mt-1 hidden"></p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo *</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="edit_type" name="type" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="info">InformaÃ§Ã£o</option>
                                    <option value="success">Sucesso</option>
                                    <option value="warning">Aviso</option>
                                    <option value="error">Erro</option>
                                </select>
                                <p id="edit_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                            
                            <div>
                                <label for="edit_recipientUser" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">UsuÃ¡rio (opcional)</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="edit_recipientUser" name="user_id">
                                    <option value="">Todos os usuÃ¡rios</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <p id="edit_recipientUser-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL de AÃ§Ã£o (opcional)</label>
                                <input type="url" class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200" id="edit_url" name="action_url" placeholder="https://exemplo.com/acao">
                                <p id="edit_url-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                            
                            <div>
                                <label for="edit_processing_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Processamento *</label>
                                <select class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-gray-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all duration-200" id="edit_processing_type" name="processing_type" required>
                                    <option value="immediate">Envio Imediato</option>
                                    <option value="queue">Agendar na Fila</option>
                                </select>
                                <p id="edit_processing_type-error" class="text-red-500 text-sm mt-1 hidden"></p>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Cancelar</button>
                    <button type="submit" form="editNotificationForm" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Salvar AlteraÃ§Ãµes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de ConfirmaÃ§Ã£o de ExclusÃ£o -->
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
                        Confirmar ExclusÃ£o
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
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Excluir NotificaÃ§Ã£o</h3>
                            <p class="text-gray-600 dark:text-gray-300">Tem certeza que deseja excluir esta notificaÃ§Ã£o? Esta aÃ§Ã£o nÃ£o pode ser desfeita e a notificaÃ§Ã£o serÃ¡ removida permanentemente.</p>
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

    <!-- Modal de VisualizaÃ§Ã£o -->
    <div id="viewModal" x-data="{ open: false }" x-show="open" @open-modal.window="if ($event.detail === 'viewModal') open = true" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
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
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-teal-400 to-emerald-500 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        Detalhes da NotificaÃ§Ã£o
                    </h5>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500 focus:outline-none rounded-full p-2 hover:bg-gray-100/50 dark:hover:bg-gray-700/50 transition-all duration-200 cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="px-6 py-6 backdrop-blur-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <span id="view_type" class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Tipo
                            </span>
                            <span id="view_date" class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                Data
                            </span>
                        </div>
                        <div id="view_status" class="text-sm">
                            Status
                        </div>
                    </div>
                    
                    <div>
                        <h3 id="view_title" class="text-xl font-bold text-gray-900 dark:text-white mb-2">TÃ­tulo da NotificaÃ§Ã£o</h3>
                        <div class="prose dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700/50">
                            <div id="view_message">ConteÃºdo da mensagem...</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200/30 dark:border-gray-700/30 pt-4">
                        <div>
                            <span class="block font-medium text-gray-900 dark:text-white mb-1">Enviado para:</span>
                            <span id="view_user" class="flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Nome do UsuÃ¡rio
                            </span>
                        </div>
                        <div id="view_url_container" style="display: none;">
                            <span class="block font-medium text-gray-900 dark:text-white mb-1">URL de AÃ§Ã£o:</span>
                            <a id="view_url" href="#" target="_blank" class="flex items-center text-blue-600 hover:text-blue-500 hover:underline truncate">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                <span class="truncate">Link</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/20 backdrop-blur-sm flex justify-end space-x-3 border-t border-gray-200/30 dark:border-gray-700/30">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-600/50 dark:hover:bg-gray-500/50 text-gray-800 dark:text-white rounded-lg transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Fechar</button>
                    <button type="button" @click="$dispatch('close-modal', 'viewModal'); setTimeout(() => { $('#edit_notification_id').val($('#view_id').val()); $('.edit-notification[data-id=\'' + $('#view_id').val() + '\']').click(); }, 300)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-sm transition-all duration-200 cursor-pointer btn-hover backdrop-blur-sm">Editar</button>
                </div>
                <input type="hidden" id="view_id">
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
        // Configurar AJAX para enviar o token CSRF em todas as requisiÃ§Ãµes
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        $(document).ready(function() {
            // Visualizar notificaÃ§Ã£o
            $(document).on('click', '.view-notification', function() {
                const notificationId = $(this).attr('data-id') || $(this).closest('tr').find('.edit-notification').data('id');
                const $btn = $(this);
                
                // Mudar Ã­cone para loading
                const originalContent = $btn.html();
                $btn.html('<svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
                $btn.prop('disabled', true);

                $.ajax({
                    url: '/notifications/' + notificationId,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const n = response.notification;
                            
                            // Popula os campos
                            $('#view_id').val(n.id);
                            $('#view_title').text(n.title);
                            
                            // Renderiza HTML da mensagem com seguranÃ§a
                            // Suporta Quill JSON ou HTML puro
                            try {
                                const delta = JSON.parse(n.message);
                                const tempCont = document.createElement('div');
                                const quillTemp = new Quill(tempCont);
                                quillTemp.setContents(delta);
                                $('#view_message').html(quillTemp.root.innerHTML);
                            } catch (e) {
                                $('#view_message').html(n.message);
                            }
                            
                            // Formata a data
                            const date = new Date(n.created_at);
                            $('#view_date').text(date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR'));
                            
                            // Status e Tipo
                            const typeColors = {
                                'info': 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
                                'success': 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
                                'warning': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
                                'error': 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'
                            };
                            
                            const typeLabels = {
                                'info': 'InformaÃ§Ã£o',
                                'success': 'Sucesso',
                                'warning': 'Aviso',
                                'error': 'Erro'
                            };
                            
                            $('#view_type')
                                .removeClass()
                                .addClass('px-2.5 py-0.5 rounded-full text-xs font-medium ' + (typeColors[n.type] || 'bg-gray-100 text-gray-800'))
                                .text(typeLabels[n.type] || n.type);
                                
                            $('#view_status')
                                .text(n.read_at ? 'Lida' : 'NÃ£o lida')
                                .removeClass()
                                .addClass(n.read_at ? 'text-green-600 dark:text-green-400 font-medium' : 'text-blue-600 dark:text-blue-400 font-medium');

                            // UsuÃ¡rio
                            $('#view_user').html(`
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                ${n.notifiable?.name || 'Todos os usuÃ¡rios'}
                            `);

                            // URL
                            if (n.data && n.data.action_url) {
                                $('#view_url_container').show();
                                $('#view_url').attr('href', n.data.action_url).find('span').text(n.data.action_url);
                            } else {
                                $('#view_url_container').hide();
                            }

                            // Abre o modal
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'viewModal' }));
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'Erro ao carregar detalhes da notificaÃ§Ã£o.');
                    },
                    complete: function() {
                        $btn.html(originalContent);
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Alpine.js jÃ¡ gerencia os modais, nÃ£o precisamos inicializÃ¡-los com Bootstrap
            
            // Inicializar Quill Editor para criaÃ§Ã£o
            var quillCreate = new Quill('#quill-editor-container', {
                theme: 'snow',
                placeholder: 'Digite a mensagem da notificaÃ§Ã£o...',
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
            
            // Inicializar Quill Editor para ediÃ§Ã£o
            var quillEdit = new Quill('#edit-quill-editor-container', {
                theme: 'snow',
                placeholder: 'Digite a mensagem da notificaÃ§Ã£o...',
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
            
            // Atualizar campo hidden quando o conteÃºdo do editor mudar
            quillCreate.on('text-change', function() {
                $('#message').val(quillCreate.root.innerHTML);
            });
            
            quillEdit.on('text-change', function() {
                $('#edit_message').val(quillEdit.root.innerHTML);
            });
            
            // FormulÃ¡rio de criaÃ§Ã£o
            $('#createModal form').on('submit', function(e) {
                e.preventDefault();
                
                // Garantir que o conteÃºdo do Quill Editor seja capturado
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
            
            // Editar notificaÃ§Ã£o
            $(document).on('click', '.edit-notification', function() {
                const notificationId = $(this).data('id');
                
                // Limpar erros anteriores
                clearFormErrors();
                
                // Carregar dados da notificaÃ§Ã£o
                $.ajax({
                    url: `/notifications/${notificationId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const notification = response.notification;
                            
                            // Preencher o formulÃ¡rio
                            $('#edit_notification_id').val(notification.id);
                            $('#edit_title').val(notification.title);
                            $('#edit_message').val(notification.message);
                            $('#edit_type').val(notification.type);
                            $('#edit_recipientUser').val(notification.user_id || '');
                            $('#edit_url').val(notification.action_url || '');
                            // Definir o tipo de processamento como imediato por padrÃ£o
                            $('#edit_processing_type').val('immediate');
                            
                            // Atualizar o conteÃºdo do Quill Editor
                            setTimeout(function() {
                                quillEdit.root.innerHTML = notification.message;
                            }, 300); // Pequeno delay para garantir que o editor esteja inicializado
                            
                            // Abrir o modal
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'editModal' }));
                        }
                    }
                });
            });
            
            // FormulÃ¡rio de ediÃ§Ã£o
            $('#editNotificationForm').on('submit', function(e) {
                e.preventDefault();
                
                // Garantir que o conteÃºdo do Quill Editor seja capturado
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
            
            // Excluir notificaÃ§Ã£o
            $(document).on('click', '.delete-notification', function() {
                const notificationId = $(this).data('id');
                $('#delete_notification_id').val(notificationId);
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'deleteModal' }));
            });
            
            // Confirmar exclusÃ£o
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
                        showAlert('danger', 'Erro ao excluir notificaÃ§Ã£o');
                    }
                });
            });
            
            // Filtro com AJAX
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                refreshNotificationsTable($(this).serialize());
            });
            
            // Excluir todas as notificaÃ§Ãµes
            $(document).on('click', '#delete-all-notifications', function() {
                if (confirm('Tem certeza que deseja excluir todas as notificaÃ§Ãµes?')) {
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
                            showAlert('danger', 'Erro ao excluir todas as notificaÃ§Ãµes');
                        }
                    });
                }
            });

            // Excluir selecionadas (Bulk Action)
            $(document).on('click', '#delete-selected-notifications', function() {
                var ids = [];
                $('#notifications-table-container tbody input[type="checkbox"]:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) {
                    alert('Nenhuma notificaÃ§Ã£o selecionada.');
                    return;
                }

                if (confirm('Tem certeza que deseja excluir as ' + ids.length + ' notificaÃ§Ãµes selecionadas?')) {
                    $.ajax({
                        url: '{{ route("notifications.destroy.selected") }}',
                        type: 'POST',
                        dataType: 'json',
                        data: { ids: ids },
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
                            showAlert('danger', 'Erro ao excluir notificaÃ§Ãµes selecionadas');
                        }
                    });
                }
            });
            
            // FunÃ§Ãµes auxiliares
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
            
            // Container para Toasts
            if ($('#toast-container').length === 0) {
                $('body').append('<div id="toast-container" class="fixed top-4 right-4 z-[60] flex flex-col gap-3 pointer-events-none"></div>');
            }

            function showAlert(type, message, title = '') {
                // Definir cores e Ã­cones baseados no tipo
                let colors = {
                    success: { border: 'border-green-500', icon: 'text-green-500', bg: 'bg-green-500' },
                    danger: { border: 'border-red-500', icon: 'text-red-500', bg: 'bg-red-500' },
                    warning: { border: 'border-yellow-500', icon: 'text-yellow-500', bg: 'bg-yellow-500' },
                    info: { border: 'border-blue-500', icon: 'text-blue-500', bg: 'bg-blue-500' }
                };
                
                // Fallback para info se tipo desconhecido
                let style = colors[type] || colors.info;
                
                // Ãcones SVG
                let icons = {
                    success: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
                    danger: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
                    warning: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" /></svg>`,
                    info: `<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
                };
                
                let icon = icons[type] || icons.info;
                let toastId = 'toast-' + Date.now();
                
                let toastHtml = `
                    <div id="${toastId}" class="pointer-events-auto w-96 overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 transform transition-all duration-300 translate-x-full border-l-4 ${style.border}">
                        <div class="p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 ${style.icon}">
                                    ${icon}
                                </div>
                                <div class="ml-3 w-0 flex-1 pt-0.5">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">${title || (type === 'danger' ? 'Erro' : (type === 'success' ? 'Sucesso' : 'InformaÃ§Ã£o'))}</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 break-words">${message}</p>
                                </div>
                                <div class="ml-4 flex flex-shrink-0">
                                    <button type="button" class="inline-flex rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" onclick="destroyToast('${toastId}')">
                                        <span class="sr-only">Fechar</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L10 10 5.707 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#toast-container').append(toastHtml);
                
                // Animate in
                setTimeout(() => {
                    $(`#${toastId}`).removeClass('translate-x-full');
                }, 10);
                
                // Auto dismiss
                setTimeout(() => {
                    destroyToast(toastId);
                }, 5000);
            }
            
            // Tornar a funÃ§Ã£o global para ser acessada pelo botÃ£o de fechar inline
            window.destroyToast = function(id) {
                let $toast = $(`#${id}`);
                $toast.addClass('translate-x-full opacity-0');
                setTimeout(() => {
                    $toast.remove();
                }, 300);
            };

            // FormulÃ¡rio de criaÃ§Ã£o
             $('#createModal form').on('submit', function(e) {
                e.preventDefault();
                
                // Garantir que o conteÃºdo do Quill Editor seja capturado
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
                            quillCreate.setContents([]); // Limpar o editor Quill
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
        });
    </script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
