<div class="overflow-x-auto p-4">
    <div class="flex justify-end mb-4">
        <button id="delete-all-notifications" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Excluir Todas
        </button>
    </div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border-separate border-spacing-y-3">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-l-lg">#</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Título</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuário</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider rounded-r-lg">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900">
            @forelse ($notifications as $notification)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 shadow-sm">
                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 rounded-l-lg border-l border-t border-b border-gray-200 dark:border-gray-700">{{ $notification->id }}</td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-700">{{ $notification->title }}</td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-700">{{ $notification->user ? $notification->user->name : 'Todos os usuários' }}</td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-700">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $notification->type == 'info' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                              ($notification->type == 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                              ($notification->type == 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')) }}">
                            {{ ucfirst($notification->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-700">
                        @if ($notification->read_at)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Lida</span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Não lida</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-700">{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-5 whitespace-nowrap text-sm font-medium space-x-2 bg-white dark:bg-gray-900 rounded-r-lg border-r border-t border-b border-gray-200 dark:border-gray-700">
                        <button class="edit-notification inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200" data-id="{{ $notification->id }}">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </button>
                        <button class="delete-notification inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200" data-id="{{ $notification->id }}">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Excluir
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-5 text-center text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">Nenhuma notificação encontrada</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center p-4 ">
    {{ $notifications->links() }}
</div>