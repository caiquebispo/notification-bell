<form id="notificationForm" method="POST" class="space-y-4">
    @csrf
    <input type="hidden" name="_method" id="formMethod" value="POST">
    <input type="hidden" name="notification_id" id="notification_id" value="">
    
    <div>
        <label for="recipientUser" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuário</label>
        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="recipientUser" name="recipientUser">
            <option value="">Todos os usuários</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
        <div class="text-sm text-red-600 mt-1 hidden" id="recipientUser-error"></div>
    </div>
    
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Notificação</label>
        <select class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="type" name="type" required>
            <option value="info">Informação</option>
            <option value="success">Sucesso</option>
            <option value="warning">Aviso</option>
            <option value="error">Erro</option>
        </select>
        <div class="text-sm text-red-600 mt-1 hidden" id="type-error"></div>
    </div>
    
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título</label>
        <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="title" name="title" required>
        <div class="text-sm text-red-600 mt-1 hidden" id="title-error"></div>
    </div>
    
    <div>
        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conteúdo da Notificação</label>
        <textarea class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="message" name="message" rows="3" required></textarea>
        <div class="text-sm text-red-600 mt-1 hidden" id="message-error"></div>
    </div>
    
    <div>
        <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL (opcional)</label>
        <input type="url" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" id="url" name="url" placeholder="https://exemplo.com">
        <div class="text-sm text-red-600 mt-1 hidden" id="url-error"></div>
    </div>
    
    <div class="flex justify-end space-x-3 pt-3">
        <button type="button" @click="open = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white rounded-md transition-colors duration-200">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-sm transition-colors duration-200">Salvar</button>
    </div>
</form>