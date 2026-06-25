@extends('theme::layouts.app')

@section('title', __('menu.delivery_orders'))
@section('page-title', __('menu.delivery_orders'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">{{ __('menu.delivery_orders_intro') }}</p>
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-plug"></i> {{ __('menu.integrations') }}
    </a>
</div>

<div id="delivery-orders-list" class="row g-3">
    @forelse($orders as $order)
        @include('theme::partials.delivery-order-card', ['order' => $order])
    @empty
        <div class="col-12">
            <div class="alert alert-secondary mb-0" id="delivery-orders-empty">{{ __('menu.no_delivery_orders') }}</div>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
const pollDeliveryOrders = async () => {
    try {
        const res = await fetch('{{ route('admin.delivery-orders.poll') }}', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if ((data.orders || []).length === 0) return;
        // Soft refresh when counts change — full page reload keeps blade actions simple
        const current = document.querySelectorAll('[data-delivery-order]').length;
        if (current !== data.orders.length) {
            window.location.reload();
        }
    } catch (e) {}
};
setInterval(pollDeliveryOrders, 15000);
</script>
@endpush
