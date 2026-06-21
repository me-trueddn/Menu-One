@php($license = $cafe->currentLicense)
<span class="badge {{ $cafe->isPremiumLicensed() ? 'text-bg-warning' : 'text-bg-info' }}">{{ $cafe->subscriptionLabel() }}</span>
@if($license)
    <div class="small text-muted mt-1">
        <div>{{ __('menu.license_type') }}: <strong>{{ $license->licenseType?->name ?? '—' }}</strong></div>
        <div>{{ __('menu.license_starts_at') }}: {{ $license->starts_at?->format('d.m.Y H:i') ?? '—' }}</div>
        <div>{{ __('menu.license_expires_at') }}: {{ $license->expires_at?->format('d.m.Y H:i') ?? '—' }}</div>
    </div>
@else
    <div class="small text-muted mt-1">{{ __('menu.no_license') }}</div>
@endif
