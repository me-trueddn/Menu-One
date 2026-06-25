@extends('theme::layouts.app')

@section('title', __('menu.integrations'))
@section('page-title', __('menu.integrations'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ __('menu.integrations_intro') }}</p>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.integrations.billing.edit') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-receipt"></i> {{ __('menu.integration_billing_defaults') }}
        </a>
        <a href="{{ route('admin.delivery-orders.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-inbox"></i> {{ __('menu.delivery_orders') }}
        </a>
    </div>
</div>

<div class="row g-3">
    @foreach($cards as $card)
        @php($provider = $card['provider'])
        @php($integration = $card['integration'])
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <i class="bi {{ $provider->icon() }} fs-4 me-1"></i>
                            <strong>{{ $provider->label() }}</strong>
                        </div>
                        @if($integration?->is_enabled)
                            <span class="badge text-bg-success">{{ __('menu.active') }}</span>
                        @else
                            <span class="badge text-bg-secondary">{{ __('menu.inactive') }}</span>
                        @endif
                    </div>
                    @if($integration?->last_error)
                        <div class="small text-danger mb-2">{{ \Illuminate\Support\Str::limit($integration->last_error, 80) }}</div>
                    @endif
                    <div class="small text-muted mb-2 text-break">{{ $card['webhook_url'] }}</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.integrations.edit', $provider->slug()) }}" class="btn btn-sm btn-outline-primary">
                            {{ __('menu.settings') }}
                        </a>
                        <a href="{{ route('admin.integrations.mappings', $provider->slug()) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('menu.integration_product_mappings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
