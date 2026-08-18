@php($nameColumn = $nameColumn ?? config('notifications.user_columns.name', 'name'))
@php($t = $t ?? fn (string $key, array $replace = []) => __("notification-bell::panel.{$key}", $replace, config('notifications.locale')))
{{--
    Partial reutilizada nos modais de criação e edição.
    Parâmetros esperados via @include:
    - $formId: id do <form>
    - $idPrefix: prefixo dos ids dos campos (ex.: 'nbp-create-' ou 'nbp-edit-')
    - $isEdit: bool
--}}
@php($idPrefix = $idPrefix ?? 'nbp-create-')
@php($isEdit = $isEdit ?? false)

<div class="nbp-field" data-field="title">
    <label class="nbp-label" for="{{ $idPrefix }}title">
        {{ $t('field_title') }} <span class="nbp-required">*</span>
    </label>
    <input
        type="text"
        class="nbp-input"
        id="{{ $idPrefix }}title"
        name="title"
        required
        maxlength="255"
        placeholder="{{ $t('field_title_placeholder') }}"
    >
    <p class="nbp-error-text"></p>
</div>

<div class="nbp-field" data-field="message">
    <label class="nbp-label" for="{{ $idPrefix }}message">
        {{ $t('field_message') }} <span class="nbp-required">*</span>
    </label>
    <textarea
        class="nbp-textarea"
        id="{{ $idPrefix }}message"
        name="message"
        required
        placeholder="{{ $t('field_message_placeholder') }}"
    ></textarea>
    <p class="nbp-error-text"></p>
</div>

<div class="nbp-grid-2">
    <div class="nbp-field" data-field="type">
        <label class="nbp-label" for="{{ $idPrefix }}type">
            {{ $t('field_type') }} <span class="nbp-required">*</span>
        </label>
        <select class="nbp-select" id="{{ $idPrefix }}type" name="type" required>
            <option value="info">{{ $t('type_info') }}</option>
            <option value="success">{{ $t('type_success') }}</option>
            <option value="warning">{{ $t('type_warning') }}</option>
            <option value="error">{{ $t('type_error') }}</option>
        </select>
        <p class="nbp-error-text"></p>
    </div>

    <div class="nbp-field" data-field="recipientUser">
        <label class="nbp-label" for="{{ $idPrefix }}recipient">
            {{ $t('field_recipient') }}
            <span class="nbp-label-optional">({{ $t('user_all') }})</span>
        </label>
        <select class="nbp-select" id="{{ $idPrefix }}recipient" name="recipientUser">
            <option value="">{{ $t('user_all') }}</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->{$nameColumn} }}</option>
            @endforeach
        </select>
        <p class="nbp-hint">{{ $t('field_recipient_hint') }}</p>
        <p class="nbp-error-text"></p>
    </div>
</div>

<div class="nbp-grid-2">
    <div class="nbp-field" data-field="url">
        <label class="nbp-label" for="{{ $idPrefix }}url">
            {{ $t('field_url') }}
        </label>
        <input
            type="url"
            class="nbp-input"
            id="{{ $idPrefix }}url"
            name="url"
            maxlength="500"
            placeholder="{{ $t('field_url_placeholder') }}"
        >
        <p class="nbp-error-text"></p>
    </div>

    <div class="nbp-field" data-field="processing_type">
        <label class="nbp-label" for="{{ $idPrefix }}processing">
            {{ $t('field_processing') }} <span class="nbp-required">*</span>
        </label>
        <select class="nbp-select" id="{{ $idPrefix }}processing" name="processing_type" required>
            <option value="immediate">{{ $t('processing_immediate') }}</option>
            <option value="queue">{{ $t('processing_queue') }}</option>
        </select>
        <p class="nbp-error-text"></p>
    </div>
</div>
