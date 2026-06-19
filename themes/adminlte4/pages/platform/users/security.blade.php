@extends('theme::layouts.app')

@section('title', __('menu.security_settings'))
@section('page-title', __('menu.security_settings'))

@section('content')
<div class="alert alert-secondary small mb-3">
    {{ __('menu.security_iso_intro') }}
</div>

<form method="POST" action="{{ route('platform.users.security.update') }}">
    @csrf @method('PUT')

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.security_section_password') }}</h5>
            <small class="text-muted">ISO 27001 · A.5.17 · 8.5</small>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.password_min_length') }}</label>
                    <input type="number" name="security_password_min_length" class="form-control" min="4" max="64"
                           value="{{ old('security_password_min_length', $settings['security_password_min_length']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.password_expiry_days') }}</label>
                    <div class="input-group">
                        <input type="number" name="security_password_expiry_days" class="form-control" min="0" max="365"
                               value="{{ old('security_password_expiry_days', $settings['security_password_expiry_days']) }}" required>
                        <span class="input-group-text">{{ __('menu.days') }}</span>
                    </div>
                    <div class="form-text">{{ __('menu.password_expiry_hint') }}</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.password_min_age_days') }}</label>
                    <div class="input-group">
                        <input type="number" name="security_password_min_age_days" class="form-control" min="0" max="30"
                               value="{{ old('security_password_min_age_days', $settings['security_password_min_age_days']) }}" required>
                        <span class="input-group-text">{{ __('menu.days') }}</span>
                    </div>
                    <div class="form-text">{{ __('menu.password_min_age_hint') }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.password_history_count') }}</label>
                    <input type="number" name="security_password_history_count" class="form-control" min="0" max="24"
                           value="{{ old('security_password_history_count', $settings['security_password_history_count']) }}" required>
                    <div class="form-text">{{ __('menu.password_history_hint') }}</div>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label d-block">{{ __('menu.password_complexity') }}</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="security_password_require_uppercase" value="1"
                               @checked(old('security_password_require_uppercase', $settings['security_password_require_uppercase'] ?? false))>
                        <label class="form-check-label">{{ __('menu.require_uppercase') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="security_password_require_lowercase" value="1"
                               @checked(old('security_password_require_lowercase', $settings['security_password_require_lowercase'] ?? true))>
                        <label class="form-check-label">{{ __('menu.require_lowercase') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="security_password_require_number" value="1"
                               @checked(old('security_password_require_number', $settings['security_password_require_number'] ?? true))>
                        <label class="form-check-label">{{ __('menu.require_number') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="security_password_require_symbol" value="1"
                               @checked(old('security_password_require_symbol', $settings['security_password_require_symbol'] ?? false))>
                        <label class="form-check-label">{{ __('menu.require_symbol') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.security_section_lockout') }}</h5>
            <small class="text-muted">ISO 27001 · A.5.3 · 8.5</small>
        </div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('menu.lockout_attempts') }}</label>
                <input type="number" name="security_lockout_attempts" class="form-control" min="3" max="20"
                       value="{{ old('security_lockout_attempts', $settings['security_lockout_attempts']) }}" required>
                <div class="form-text">{{ __('menu.lockout_attempts_hint') }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('menu.lockout_minutes') }}</label>
                <div class="input-group">
                    <input type="number" name="security_lockout_minutes" class="form-control" min="1" max="1440"
                           value="{{ old('security_lockout_minutes', $settings['security_lockout_minutes']) }}" required>
                    <span class="input-group-text">{{ __('menu.minutes') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.security_section_session') }}</h5>
            <small class="text-muted">ISO 27001 · A.5.15 · 8.2</small>
        </div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('menu.session_idle_minutes') }}</label>
                <div class="input-group">
                    <input type="number" name="security_session_idle_minutes" class="form-control" min="5" max="480"
                           value="{{ old('security_session_idle_minutes', $settings['security_session_idle_minutes']) }}" required>
                    <span class="input-group-text">{{ __('menu.minutes') }}</span>
                </div>
                <div class="form-text">{{ __('menu.session_idle_hint') }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('menu.reset_link_minutes') }}</label>
                <div class="input-group">
                    <input type="number" name="security_reset_link_minutes" class="form-control" min="5" max="1440"
                           value="{{ old('security_reset_link_minutes', $settings['security_reset_link_minutes']) }}" required>
                    <span class="input-group-text">{{ __('menu.minutes') }}</span>
                </div>
                <div class="form-text">{{ __('menu.reset_link_hint') }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.security_section_account') }}</h5>
            <small class="text-muted">ISO 27001 · A.5.16</small>
        </div>
        <div class="card-body">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('menu.inactive_account_days') }}</label>
                <div class="input-group">
                    <input type="number" name="security_inactive_account_days" class="form-control" min="0" max="365"
                           value="{{ old('security_inactive_account_days', $settings['security_inactive_account_days']) }}" required>
                    <span class="input-group-text">{{ __('menu.days') }}</span>
                </div>
                <div class="form-text">{{ __('menu.inactive_account_hint') }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('menu.security_section_2fa') }}</h5>
            <small class="text-muted">ISO 27001 · A.5.17 · 8.5</small>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">{{ __('menu.two_factor_temp_disabled') }}</div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="security_2fa_required" value="1" disabled
                               @checked(old('security_2fa_required', $settings['security_2fa_required'] ?? false))>
                        <label class="form-check-label">{{ __('menu.two_factor_required') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="security_2fa_enabled_globally" value="1" disabled
                               @checked(old('security_2fa_enabled_globally', $settings['security_2fa_enabled_globally'] ?? false))>
                        <label class="form-check-label">{{ __('menu.two_factor_global') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary">{{ __('menu.save') }}</button>
    <a href="{{ route('platform.users.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
</form>
@endsection
