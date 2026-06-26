@php($cafe = $ticket->tenant)
@php($license = $cafe?->currentLicense)

<div class="card mb-3">
    <div class="card-header">{{ __('menu.ticket_customer_info') }}</div>
    <div class="card-body small">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small mb-2">{{ __('menu.ticket_opened_by') }}</h6>
                <p class="mb-1"><strong>{{ $ticket->user?->name ?? '—' }}</strong></p>
                <p class="mb-0 text-muted">{{ $ticket->user?->email }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small mb-2">{{ __('menu.ticket_contact_info') }}</h6>
                <p class="mb-1">{{ __('menu.email') }}: <strong>{{ $ticket->user?->email ?? '—' }}</strong></p>
                <p class="mb-0">{{ __('menu.phone') }}: <strong>{{ $ticket->user?->phone ?: '—' }}</strong></p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small mb-2">{{ __('menu.ticket_cafe_info') }}</h6>
                @if($cafe)
                    <p class="mb-1"><strong>{{ $cafe->name }}</strong> <code>{{ $cafe->id }}</code></p>
                    @if($cafe->company_name)
                        <p class="mb-1">{{ __('menu.company_name') }}: {{ $cafe->company_name }}</p>
                    @endif
                    @if($cafe->company_phone)
                        <p class="mb-1">{{ __('menu.phone') }}: {{ $cafe->company_phone }}</p>
                    @endif
                    @if($cafe->company_email)
                        <p class="mb-0">{{ __('menu.email') }}: {{ $cafe->company_email }}</p>
                    @endif
                @else
                    <p class="mb-0 text-muted">—</p>
                @endif
            </div>
            <div class="col-md-6">
                <h6 class="text-muted text-uppercase small mb-2">{{ __('menu.ticket_license_info') }}</h6>
                @if($cafe)
                    <p class="mb-2">
                        <span class="badge {{ $cafe->isPremiumLicensed() ? 'text-bg-warning' : 'text-bg-secondary' }}">
                            {{ $cafe->subscriptionLabel() }}
                        </span>
                    </p>
                    @if($license)
                        <p class="mb-1">{{ __('menu.license_type') }}: <strong>{{ $license->licenseType?->name ?? '—' }}</strong></p>
                        <p class="mb-1">{{ __('menu.license_starts_at') }}: {{ $license->starts_at?->format('d.m.Y H:i') ?? '—' }}</p>
                        <p class="mb-0">{{ __('menu.license_expires_at') }}: {{ $license->expires_at?->format('d.m.Y H:i') ?? '—' }}</p>
                    @else
                        <p class="mb-0 text-muted">{{ __('menu.account_type_free') }}</p>
                    @endif
                @else
                    <p class="mb-0 text-muted">{{ __('menu.account_type_free') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
