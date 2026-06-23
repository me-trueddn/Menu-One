@php($licenseExpired = auth()->check() && \App\Support\TenantLicenseGate::licenseExpiredForUser(auth()->user()))
@if($licenseExpired)
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>
            <div class="fw-semibold">{{ __('menu.cafe_license_expired_title') }}</div>
            <div class="small">{{ __('menu.cafe_license_expired_profile') }}</div>
            <a href="{{ route('profile.edit', ['tab' => 'ticket']) }}" class="alert-link small">{{ __('menu.open_ticket') }}</a>
        </div>
    </div>
@endif
