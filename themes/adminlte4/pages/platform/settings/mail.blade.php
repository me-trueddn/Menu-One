@extends('theme::layouts.app')

@section('title', __('menu.mail_configuration'))
@section('page-title', __('menu.mail_configuration'))

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('menu.mail_settings') }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('platform.settings.mail.update') }}" id="mailSettingsForm">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="form-label">{{ __('menu.mail_provider_preset') }}</label>
                <select id="mailProviderPreset" class="form-select">
                    <option value="">{{ __('menu.mail_provider_custom') }}</option>
                    @foreach($mailProviders as $key => $provider)
                        <option value="{{ $key }}"
                                data-host="{{ $provider['host'] }}"
                                data-port="{{ $provider['port'] }}"
                                data-encryption="{{ $provider['encryption'] }}">
                            {{ $provider['label'] }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('menu.mail_provider_hint') }}</div>
                <div id="yandexSetupHint" class="alert alert-info small mt-3 mb-0 d-none">
                    <strong>{{ __('menu.mail_yandex_setup_title') }}</strong>
                    <p class="mb-0 mt-1">{{ __('menu.mail_yandex_setup_body') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.mail_driver') }}</label>
                    <select name="mail_mailer" id="mail_mailer" class="form-select @error('mail_mailer') is-invalid @enderror" required>
                        <option value="smtp" @selected(old('mail_mailer', $settings['mail_mailer']) === 'smtp')>SMTP</option>
                        <option value="log" @selected(old('mail_mailer', $settings['mail_mailer']) === 'log')>Log</option>
                        <option value="sendmail" @selected(old('mail_mailer', $settings['mail_mailer']) === 'sendmail')>Sendmail</option>
                    </select>
                    @error('mail_mailer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.mail_encryption') }}</label>
                    <select name="mail_encryption" id="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror">
                        <option value="tls" @selected(old('mail_encryption', $settings['mail_encryption'] ?: 'tls') === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('mail_encryption', $settings['mail_encryption']) === 'ssl')>SSL</option>
                        <option value="none" @selected(old('mail_encryption', $settings['mail_encryption']) === '' || old('mail_encryption', $settings['mail_encryption']) === 'none')>{{ __('menu.none') }}</option>
                    </select>
                    @error('mail_encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.mail_port') }}</label>
                    <input name="mail_port" id="mail_port" type="number" class="form-control @error('mail_port') is-invalid @enderror"
                           value="{{ old('mail_port', $settings['mail_port']) }}">
                    @error('mail_port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.mail_host') }}</label>
                    <input name="mail_host" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror"
                           value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="smtp.example.com">
                    @error('mail_host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.mail_username') }}</label>
                    <input name="mail_username" class="form-control @error('mail_username') is-invalid @enderror"
                           value="{{ old('mail_username', $settings['mail_username']) }}">
                    @error('mail_username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ __('menu.mail_password') }}</label>
                <input type="password" name="mail_password" class="form-control @error('mail_password') is-invalid @enderror"
                       placeholder="{{ $settings['has_password'] ? '••••••••' : '' }}" autocomplete="new-password">
                @error('mail_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($settings['has_password'])
                    <div class="form-text">{{ __('menu.mail_password_hint') }}</div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.mail_timeout_seconds') }}</label>
                    <input name="mail_timeout_seconds" type="number" min="5" max="120"
                           class="form-control @error('mail_timeout_seconds') is-invalid @enderror"
                           value="{{ old('mail_timeout_seconds', $settings['mail_timeout_seconds']) }}" required>
                    @error('mail_timeout_seconds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('menu.mail_timeout_hint') }}</div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.mail_from_address') }}</label>
                    <input name="mail_from_address" type="email" class="form-control @error('mail_from_address') is-invalid @enderror"
                           value="{{ old('mail_from_address', $settings['mail_from_address']) }}">
                    @error('mail_from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.mail_from_name') }}</label>
                    <input name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror"
                           value="{{ old('mail_from_name', $settings['mail_from_name']) }}">
                    @error('mail_from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0">{{ __('menu.mail_test_title') }}</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">{{ __('menu.mail_test_hint') }}</p>
        <form method="POST" action="{{ route('platform.settings.mail.test') }}" id="mailTestForm">
            @csrf
            <div class="row align-items-end g-3">
                <div class="col-md-8">
                    <label class="form-label" for="test_email">{{ __('menu.mail_test_recipient') }}</label>
                    <input type="email" id="test_email" name="test_email"
                           class="form-control @error('test_email') is-invalid @enderror"
                           value="{{ old('test_email', auth()->user()->email) }}"
                           placeholder="ornek@firma.com" required>
                    @error('test_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-send"></i> {{ __('menu.mail_test_send') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleYandexHint() {
    const host = (document.getElementById('mail_host')?.value || '').toLowerCase();
    const hint = document.getElementById('yandexSetupHint');
    if (!hint) return;
    hint.classList.toggle('d-none', !host.includes('yandex'));
}

document.getElementById('mailProviderPreset')?.addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    if (!opt || !opt.value) return;
    document.getElementById('mail_host').value = opt.dataset.host || '';
    document.getElementById('mail_port').value = opt.dataset.port || '';
    document.getElementById('mail_encryption').value = opt.dataset.encryption || 'tls';
    document.getElementById('mail_mailer').value = 'smtp';
    toggleYandexHint();
});

document.getElementById('mail_host')?.addEventListener('input', toggleYandexHint);
toggleYandexHint();

document.getElementById('mailTestForm')?.addEventListener('submit', function () {
    const src = document.getElementById('mailSettingsForm');
    const fields = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
    fields.forEach(function (name) {
        let input = document.querySelector('#mailTestForm input[name="' + name + '"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            document.getElementById('mailTestForm').appendChild(input);
        }
        const source = src.querySelector('[name="' + name + '"]');
        input.value = source ? source.value : '';
    });
});
</script>
@endpush
