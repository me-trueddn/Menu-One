@props([
    'expiresName' => null,
    'expiresValue' => null,
    'subjectName',
    'subjectValue',
    'bodyName',
    'bodyValue',
    'hint',
    'previewKey' => null,
])

<div class="row notification-template-fields" @if($previewKey) data-preview-key="{{ $previewKey }}" @endif>
    @if($expiresName)
        <div class="col-md-4 mb-3">
            <label class="form-label" for="{{ $expiresName }}">{{ __('menu.link_expires_minutes') }}</label>
            <input type="number" id="{{ $expiresName }}" name="{{ $expiresName }}" class="form-control notification-expires-input"
                   min="5" max="10080" value="{{ old($expiresName, $expiresValue) }}">
        </div>
        <div class="col-md-8 mb-3">
            <label class="form-label" for="{{ $subjectName }}">{{ __('menu.email_subject') }}</label>
            <input id="{{ $subjectName }}" name="{{ $subjectName }}" class="form-control notification-subject-input"
                   value="{{ old($subjectName, $subjectValue) }}">
        </div>
    @else
        <div class="col-12 mb-3">
            <label class="form-label" for="{{ $subjectName }}">{{ __('menu.email_subject') }}</label>
            <input id="{{ $subjectName }}" name="{{ $subjectName }}" class="form-control notification-subject-input"
                   value="{{ old($subjectName, $subjectValue) }}">
        </div>
    @endif
    <div class="col-lg-6 mb-3">
        <label class="form-label" for="{{ $bodyName }}">{{ __('menu.email_body') }}</label>
        <textarea id="{{ $bodyName }}" name="{{ $bodyName }}" class="form-control font-monospace notification-body-input" rows="14">{{ old($bodyName, $bodyValue) }}</textarea>
        <div class="form-text">{{ $hint }}</div>
    </div>
    @if($previewKey)
        <div class="col-lg-6 mb-3">
            <label class="form-label text-muted small">{{ __('menu.email_template_preview') }}</label>
            <div class="email-template-preview-panel border rounded bg-body-tertiary p-3 h-100">
                <div class="small text-muted mb-2">
                    <span class="fw-semibold">{{ __('menu.email_template_preview_subject') }}:</span>
                    <span class="notification-preview-subject" data-preview-subject></span>
                </div>
                <div class="notification-preview-body bg-white border rounded p-3 overflow-auto" data-preview-body style="min-height: 320px; max-height: 420px;"></div>
            </div>
        </div>
    @endif
</div>
