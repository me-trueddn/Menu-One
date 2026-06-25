@extends('theme::layouts.app')

@section('title', __('menu.licensegate_settings'))
@section('page-title', __('menu.licensegate_settings'))

@section('page-actions')
<a href="{{ route('platform.licenses.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> {{ __('menu.licenses') }}
</a>
@endsection

@section('content')
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('menu.licensegate_integration') }}</h6>
        <span class="badge {{ \App\Support\LicenseGateSettings::enabled() ? 'text-bg-success' : 'text-bg-secondary' }}">
            {{ \App\Support\LicenseGateSettings::enabled() ? __('menu.active') : __('menu.inactive') }}
        </span>
    </div>
    <div class="card-body">
        <p class="text-muted small">{{ __('menu.licensegate_intro') }}</p>
        <p class="small mb-0">
            <a href="https://docs.licensegate.io/api-reference#tag/admin/POST/admin/licenses" target="_blank" rel="noopener">
                LicenseGate API Reference
            </a>
        </p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.licenses.licensegate.update') }}" class="mb-4">
            @csrf @method('PUT')

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="licensegate_enabled" value="1" id="licensegateEnabled"
                    @checked(old('licensegate_enabled', $settings['licensegate_enabled'] ?? '0') === '1')>
                <label class="form-check-label" for="licensegateEnabled">{{ __('menu.licensegate_enable') }}</label>
                <div class="form-text">{{ __('menu.licensegate_enable_hint') }}</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.licensegate_user_id') }}</label>
                    <input type="text" name="licensegate_user_id" class="form-control"
                        value="{{ old('licensegate_user_id', $settings['licensegate_user_id'] ?? '') }}"
                        placeholder="LicenseGate account user ID">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.licensegate_base_url') }}</label>
                    <input type="url" name="licensegate_base_url" class="form-control"
                        value="{{ old('licensegate_base_url', $settings['licensegate_base_url'] ?? '') }}"
                        placeholder="https://api.licensegate.io">
                    <div class="form-text">{{ __('menu.licensegate_base_url_hint') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('menu.licensegate_admin_token') }}</label>
                    <input type="password" name="licensegate_admin_token" class="form-control" autocomplete="new-password"
                        placeholder="{{ $hasAdminToken ? '••••••••' : '' }}">
                    <div class="form-text">{{ __('menu.licensegate_admin_token_hint') }}</div>
                </div>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="licensegate_verify_on_access" value="1" id="licensegateVerify"
                    @checked(old('licensegate_verify_on_access', $settings['licensegate_verify_on_access'] ?? '1') === '1')>
                <label class="form-check-label" for="licensegateVerify">{{ __('menu.licensegate_verify_on_access') }}</label>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="licensegate_strict_mode" value="1" id="licensegateStrict"
                    @checked(old('licensegate_strict_mode', $settings['licensegate_strict_mode'] ?? '0') === '1')>
                <label class="form-check-label" for="licensegateStrict">{{ __('menu.licensegate_strict_mode') }}</label>
                <div class="form-text">{{ __('menu.licensegate_strict_mode_hint') }}</div>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
        </form>

        <form method="POST" action="{{ route('platform.licenses.licensegate.test') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">{{ __('menu.licensegate_test_connection') }}</button>
        </form>
    </div>
</div>
@endsection
