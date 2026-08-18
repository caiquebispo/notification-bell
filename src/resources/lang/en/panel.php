<?php

return [
    // Page / navigation
    'page_title' => 'Notification Panel',
    'heading' => 'Notifications',
    'heading_subtitle' => 'Manage and track every notification sent to your users.',
    'new_notification' => 'New notification',
    'toggle_theme' => 'Toggle light/dark theme',
    'skip_to_content' => 'Skip to content',

    // Stat cards
    'stat_total' => 'Total sent',
    'stat_unread' => 'Unread',
    'stat_success' => 'Success',
    'stat_error' => 'Error',

    // Filters
    'filters' => 'Filters',
    'filters_hide' => 'Hide filters',
    'filters_show' => 'Show filters',
    'search_title_label' => 'Search by title',
    'search_title_placeholder' => 'Type to search...',
    'user_label' => 'User',
    'user_all' => 'All users',
    'type_label' => 'Type',
    'type_all' => 'All types',
    'clear_filters' => 'Clear',
    'apply_filters' => 'Filter',

    // Notification types
    'type_info' => 'Info',
    'type_success' => 'Success',
    'type_warning' => 'Warning',
    'type_error' => 'Error',

    // Table
    'table_title' => 'Notifications',
    'table_items_count' => ':count items',
    'col_notification' => 'Notification',
    'col_user' => 'User',
    'col_type' => 'Type',
    'col_status' => 'Status',
    'col_date' => 'Date',
    'col_actions' => 'Actions',
    'select_all' => 'Select all notifications',
    'select_row' => 'Select notification :title',
    'status_read' => 'Read',
    'status_unread' => 'Unread',
    'recipient_all' => 'Everyone',
    'pinned' => 'Pinned',
    'action_view' => 'View details',
    'action_edit' => 'Edit',
    'action_delete' => 'Delete',

    // Empty state
    'empty_title' => 'No notifications found',
    'empty_text' => 'Try adjusting the filters or create a new notification.',

    // Bulk selection
    'bulk_selected_count' => ':count selected',
    'bulk_delete_selected' => 'Delete selected',
    'bulk_clear_selection' => 'Clear selection',
    'delete_all' => 'Delete all',

    // Pagination (accessible footer)
    'pagination_nav' => 'Notifications table pagination',

    // Form (create/edit)
    'form_create_title' => 'New notification',
    'form_edit_title' => 'Edit notification',
    'form_create_subtitle' => 'Send an alert to a specific user or to everyone.',
    'form_edit_subtitle' => 'Update this notification\'s details.',
    'field_title' => 'Title',
    'field_title_placeholder' => 'E.g. Payment confirmed',
    'field_message' => 'Message',
    'field_message_placeholder' => 'Write the notification content...',
    'field_type' => 'Type',
    'field_recipient' => 'Recipient',
    'field_recipient_hint' => 'Leave blank to send it to every user.',
    'field_url' => 'Action URL (optional)',
    'field_url_placeholder' => 'https://example.com/action',
    'field_processing' => 'Processing',
    'processing_immediate' => 'Send immediately',
    'processing_queue' => 'Queue for later',
    'cancel' => 'Cancel',
    'save_create' => 'Create notification',
    'save_update' => 'Save changes',
    'saving' => 'Saving...',

    // Delete modal
    'delete_title' => 'Confirm deletion',
    'delete_confirm_text' => 'Are you sure you want to delete this notification? This action cannot be undone.',
    'delete_confirm_bulk_text' => 'Are you sure you want to delete :count selected notification(s)? This action cannot be undone.',
    'delete_confirm_all_text' => 'Are you sure you want to delete ALL notifications? This action cannot be undone.',
    'delete_confirm_button' => 'Delete permanently',
    'deleting' => 'Deleting...',

    // View modal
    'view_title' => 'Notification details',
    'view_sent_to' => 'Sent to',
    'view_action_url' => 'Action URL',
    'view_edit_button' => 'Edit',

    // Messages / toasts
    'close' => 'Close',
    'loading' => 'Loading...',
    'error_generic' => 'Something went wrong. Please try again.',
    'error_loading_details' => 'Error loading notification details.',
    'error_not_found' => 'Notification not found.',
    'error_select_at_least_one' => 'Select at least one notification.',
    'toast_success_title' => 'Success',
    'toast_error_title' => 'Error',
    'toast_info_title' => 'Info',
    'validation_error' => 'Check the highlighted fields and try again.',
];
