<?php

return [
    // Página / navegação
    'page_title' => 'Painel de Notificações',
    'heading' => 'Notificações',
    'heading_subtitle' => 'Gerencie e acompanhe todas as notificações enviadas aos usuários.',
    'new_notification' => 'Nova notificação',
    'toggle_theme' => 'Alternar tema claro/escuro',
    'skip_to_content' => 'Pular para o conteúdo',

    // Cards de estatística
    'stat_total' => 'Total enviadas',
    'stat_unread' => 'Não lidas',
    'stat_success' => 'Sucesso',
    'stat_error' => 'Erro',

    // Filtros
    'filters' => 'Filtros',
    'filters_hide' => 'Ocultar filtros',
    'filters_show' => 'Mostrar filtros',
    'search_title_label' => 'Buscar por título',
    'search_title_placeholder' => 'Digite para buscar...',
    'user_label' => 'Usuário',
    'user_all' => 'Todos os usuários',
    'type_label' => 'Tipo',
    'type_all' => 'Todos os tipos',
    'clear_filters' => 'Limpar',
    'apply_filters' => 'Filtrar',

    // Tipos de notificação
    'type_info' => 'Informação',
    'type_success' => 'Sucesso',
    'type_warning' => 'Aviso',
    'type_error' => 'Erro',

    // Tabela
    'table_title' => 'Notificações',
    'table_items_count' => ':count itens',
    'col_notification' => 'Notificação',
    'col_user' => 'Usuário',
    'col_type' => 'Tipo',
    'col_status' => 'Status',
    'col_date' => 'Data',
    'col_actions' => 'Ações',
    'select_all' => 'Selecionar todas as notificações',
    'select_row' => 'Selecionar notificação :title',
    'status_read' => 'Lida',
    'status_unread' => 'Não lida',
    'recipient_all' => 'Todos',
    'pinned' => 'Fixada',
    'action_view' => 'Ver detalhes',
    'action_edit' => 'Editar',
    'action_delete' => 'Excluir',

    // Estado vazio
    'empty_title' => 'Nenhuma notificação encontrada',
    'empty_text' => 'Tente ajustar os filtros ou crie uma nova notificação.',

    // Seleção em massa
    'bulk_selected_count' => ':count selecionada(s)',
    'bulk_delete_selected' => 'Excluir selecionadas',
    'bulk_clear_selection' => 'Limpar seleção',
    'delete_all' => 'Excluir todas',

    // Paginação (rodapé de acessibilidade)
    'pagination_nav' => 'Paginação da tabela de notificações',

    // Formulário (criar/editar)
    'form_create_title' => 'Nova notificação',
    'form_edit_title' => 'Editar notificação',
    'form_create_subtitle' => 'Envie um alerta para um usuário específico ou para todos.',
    'form_edit_subtitle' => 'Atualize os dados desta notificação.',
    'field_title' => 'Título',
    'field_title_placeholder' => 'Ex.: Pagamento confirmado',
    'field_message' => 'Mensagem',
    'field_message_placeholder' => 'Escreva o conteúdo da notificação...',
    'field_type' => 'Tipo',
    'field_recipient' => 'Destinatário',
    'field_recipient_hint' => 'Deixe em branco para enviar a todos os usuários.',
    'field_url' => 'URL de ação (opcional)',
    'field_url_placeholder' => 'https://exemplo.com/acao',
    'field_processing' => 'Processamento',
    'processing_immediate' => 'Envio imediato',
    'processing_queue' => 'Agendar na fila',
    'cancel' => 'Cancelar',
    'save_create' => 'Criar notificação',
    'save_update' => 'Salvar alterações',
    'saving' => 'Salvando...',

    // Modal de exclusão
    'delete_title' => 'Confirmar exclusão',
    'delete_confirm_text' => 'Tem certeza que deseja excluir esta notificação? Esta ação não pode ser desfeita.',
    'delete_confirm_bulk_text' => 'Tem certeza que deseja excluir :count notificação(ões) selecionada(s)? Esta ação não pode ser desfeita.',
    'delete_confirm_all_text' => 'Tem certeza que deseja excluir TODAS as notificações? Esta ação não pode ser desfeita.',
    'delete_confirm_button' => 'Excluir definitivamente',
    'deleting' => 'Excluindo...',

    // Modal de visualização
    'view_title' => 'Detalhes da notificação',
    'view_sent_to' => 'Enviado para',
    'view_action_url' => 'URL de ação',
    'view_edit_button' => 'Editar',

    // Mensagens / toasts
    'close' => 'Fechar',
    'loading' => 'Carregando...',
    'error_generic' => 'Ocorreu um erro inesperado. Tente novamente.',
    'error_loading_details' => 'Erro ao carregar os detalhes da notificação.',
    'error_not_found' => 'Notificação não encontrada.',
    'error_select_at_least_one' => 'Selecione ao menos uma notificação.',
    'toast_success_title' => 'Sucesso',
    'toast_error_title' => 'Erro',
    'toast_info_title' => 'Informação',
    'validation_error' => 'Verifique os campos destacados e tente novamente.',
];
