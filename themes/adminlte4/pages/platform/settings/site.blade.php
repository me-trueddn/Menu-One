@extends('theme::layouts.app')

@section('title', __('menu.site_management'))
@section('page-title', __('menu.site_management'))

@section('content')
<form method="POST" action="{{ route('platform.settings.site.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.site_settings') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.site_name') }}</label>
                    <input name="site_name" class="form-control @error('site_name') is-invalid @enderror"
                           value="{{ old('site_name', $settings['site_name']) }}" required>
                    @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.default_language') }}</label>
                    <select name="default_locale" class="form-select @error('default_locale') is-invalid @enderror" required>
                        @foreach(config('locale.available', []) as $code)
                            <option value="{{ $code }}" @selected(old('default_locale', $settings['default_locale']) === $code)>
                                {{ config('locale.names.'.$code, $code) }}
                            </option>
                        @endforeach
                    </select>
                    @error('default_locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.panel_url') }}</label>
                    <input name="panel_url" type="url" class="form-control @error('panel_url') is-invalid @enderror"
                           value="{{ old('panel_url', $settings['panel_url']) }}" required
                           placeholder="https://panel.example.com">
                    @error('panel_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('menu.panel_url_hint') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.main_site_url') }}</label>
                    <input name="main_site_url" type="url" class="form-control @error('main_site_url') is-invalid @enderror"
                           value="{{ old('main_site_url', $settings['main_site_url']) }}" required
                           placeholder="https://example.com">
                    @error('main_site_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('menu.main_site_url_hint') }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.contact_phone') }}</label>
                    <input name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                           value="{{ old('contact_phone', $settings['contact_phone']) }}">
                    @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.support_email') }}</label>
                    <input name="support_email" type="email" class="form-control @error('support_email') is-invalid @enderror"
                           value="{{ old('support_email', $settings['support_email']) }}">
                    @error('support_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.captcha_settings') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.captcha_provider') }}</label>
                    <select name="captcha_provider" class="form-select @error('captcha_provider') is-invalid @enderror" required>
                        <option value="none" @selected(old('captcha_provider', $settings['captcha_provider']) === 'none')>{{ __('menu.captcha_none') }}</option>
                        <option value="google" @selected(old('captcha_provider', $settings['captcha_provider']) === 'google')>Google reCAPTCHA</option>
                        <option value="turnstile" @selected(old('captcha_provider', $settings['captcha_provider']) === 'turnstile')>Cloudflare Turnstile</option>
                    </select>
                    @error('captcha_provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.captcha_site_key') }}</label>
                    <input name="captcha_site_key" class="form-control @error('captcha_site_key') is-invalid @enderror"
                           value="{{ old('captcha_site_key') }}"
                           placeholder="{{ $settings['has_captcha_site_key'] ? $settings['captcha_site_key_masked'] : '' }}"
                           autocomplete="off">
                    @error('captcha_site_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($settings['has_captcha_site_key'])
                        <div class="form-text">{{ __('menu.secret_configured', ['value' => $settings['captcha_site_key_masked']]) }}</div>
                    @else
                        <div class="form-text text-muted">{{ __('menu.secret_not_configured') }}</div>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.captcha_secret_key') }}</label>
                    <input type="password" name="captcha_secret_key" class="form-control @error('captcha_secret_key') is-invalid @enderror"
                           placeholder="{{ $settings['has_captcha_secret'] ? $settings['captcha_secret_key_masked'] : '' }}"
                           autocomplete="new-password">
                    @error('captcha_secret_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($settings['has_captcha_secret'])
                        <div class="form-text">{{ __('menu.secret_configured', ['value' => $settings['captcha_secret_key_masked']]) }}</div>
                    @else
                        <div class="form-text text-muted">{{ __('menu.secret_not_configured') }}</div>
                    @endif
                </div>
            </div>

            <p class="text-muted small">{{ __('menu.captcha_context_hint') }}</p>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="registration_enabled" id="registration_enabled" value="1"
                               @checked(old('registration_enabled', $settings['registration_enabled']))>
                        <label class="form-check-label" for="registration_enabled">{{ __('menu.registration_enabled') }}</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="captcha_login_enabled" id="captcha_login_enabled" value="1"
                               @checked(old('captcha_login_enabled', $settings['captcha_login_enabled']))>
                        <label class="form-check-label" for="captcha_login_enabled">{{ __('menu.captcha_on_login') }}</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="captcha_register_enabled" id="captcha_register_enabled" value="1"
                               @checked(old('captcha_register_enabled', $settings['captcha_register_enabled']))>
                        <label class="form-check-label" for="captcha_register_enabled">{{ __('menu.captcha_on_register') }}</label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="captcha_password_reset_enabled" id="captcha_password_reset_enabled" value="1"
                               @checked(old('captcha_password_reset_enabled', $settings['captcha_password_reset_enabled']))>
                        <label class="form-check-label" for="captcha_password_reset_enabled">{{ __('menu.captcha_on_password_reset') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.oauth_settings') }}</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">{{ __('menu.oauth_settings_hint') }}</p>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h6 class="mb-3">Google</h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="oauth_google_enabled" id="oauth_google_enabled" value="1"
                               @checked(old('oauth_google_enabled', $settings['oauth_google_enabled']))>
                        <label class="form-check-label" for="oauth_google_enabled">{{ __('menu.oauth_enabled') }}</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client ID</label>
                        <input name="oauth_google_client_id" class="form-control"
                               value="{{ old('oauth_google_client_id', $settings['oauth_google_client_id']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Secret</label>
                        <input type="password" name="oauth_google_client_secret" class="form-control"
                               placeholder="{{ $settings['has_oauth_google_secret'] ? '••••••••' : '' }}">
                    </div>
                    <div class="form-text">{{ __('menu.oauth_redirect') }}: <code>{{ $settings['oauth_google_redirect'] }}</code></div>
                </div>
                <div class="col-lg-6 mb-4">
                    <h6 class="mb-3">Microsoft</h6>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="oauth_microsoft_enabled" id="oauth_microsoft_enabled" value="1"
                               @checked(old('oauth_microsoft_enabled', $settings['oauth_microsoft_enabled']))>
                        <label class="form-check-label" for="oauth_microsoft_enabled">{{ __('menu.oauth_enabled') }}</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client ID</label>
                        <input name="oauth_microsoft_client_id" class="form-control"
                               value="{{ old('oauth_microsoft_client_id', $settings['oauth_microsoft_client_id']) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client Secret</label>
                        <input type="password" name="oauth_microsoft_client_secret" class="form-control"
                               placeholder="{{ $settings['has_oauth_microsoft_secret'] ? '••••••••' : '' }}">
                    </div>
                    <div class="form-text">{{ __('menu.oauth_redirect') }}: <code>{{ $settings['oauth_microsoft_redirect'] }}</code></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="oauth_allow_login" id="oauth_allow_login" value="1"
                               @checked(old('oauth_allow_login', $settings['oauth_allow_login']))>
                        <label class="form-check-label" for="oauth_allow_login">{{ __('menu.oauth_allow_login') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="oauth_allow_register" id="oauth_allow_register" value="1"
                               @checked(old('oauth_allow_register', $settings['oauth_allow_register']))>
                        <label class="form-check-label" for="oauth_allow_register">{{ __('menu.oauth_allow_register') }}</label>
                    </div>
                </div>
            </div>
            <hr>
            <h6 class="mb-3">{{ __('menu.branding') }}</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.site_logo') }}</label>
                    <input type="file" name="site_logo" class="form-control" accept="image/*">
                    @if($settings['site_logo_path'])<img src="{{ asset($settings['site_logo_path']) }}" class="mt-2" style="height:40px">@endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.site_favicon') }}</label>
                    <input type="file" name="site_favicon" class="form-control" accept="image/*,.ico">
                    @if($settings['site_favicon_path'])<img src="{{ asset($settings['site_favicon_path']) }}" class="mt-2" style="height:24px">@endif
                </div>
            </div>
            <hr>
            <h6 class="mb-3">{{ __('menu.default_company_info') }}</h6>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">{{ __('menu.company_name') }}</label><input name="default_company_name" class="form-control" value="{{ old('default_company_name', $settings['default_company_name']) }}"></div>
                <div class="col-md-6 mb-3"><label class="form-label">{{ __('menu.company_tax_number') }}</label><input name="default_company_tax_number" class="form-control" value="{{ old('default_company_tax_number', $settings['default_company_tax_number']) }}"></div>
                <div class="col-md-6 mb-3"><label class="form-label">{{ __('menu.company_phone') }}</label><input name="default_company_phone" class="form-control" value="{{ old('default_company_phone', $settings['default_company_phone']) }}"></div>
                <div class="col-md-6 mb-3"><label class="form-label">{{ __('menu.company_email') }}</label><input name="default_company_email" class="form-control" value="{{ old('default_company_email', $settings['default_company_email']) }}"></div>
                <div class="col-12 mb-3"><label class="form-label">{{ __('menu.company_address') }}</label><textarea name="default_company_address" class="form-control" rows="2">{{ old('default_company_address', $settings['default_company_address']) }}</textarea></div>
            </div>
            <hr>
            <h6 class="mb-3">{{ __('menu.email_verification_settings') }}</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.verification_link_expires_minutes') }}</label>
                    <input type="number" name="verification_link_expires_minutes" class="form-control" min="5" max="10080" value="{{ old('verification_link_expires_minutes', $settings['verification_link_expires_minutes']) }}">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">{{ __('menu.email_subject') }}</label>
                    <input name="email_verification_subject" class="form-control" value="{{ old('email_verification_subject', $settings['email_verification_subject']) }}">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">{{ __('menu.email_body') }}</label>
                    <textarea name="email_verification_body" class="form-control font-monospace" rows="14">{{ old('email_verification_body', $settings['email_verification_body']) }}</textarea>
                    <div class="form-text">{{ __('menu.email_template_hint') }}</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
        </div>
    </div>
</form>
@endsection
