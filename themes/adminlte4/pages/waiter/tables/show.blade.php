@extends('theme::layouts.app')
@section('title', $table->name)
@section('page-title', __('menu.table').': '.$table->name)
@section('content')
<div class="row">
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('menu.reservations') }}</h5>
                <a href="{{ route('reservations.create', ['table' => $table->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.add_reservation') }}</a>
            </div>
            <div class="card-body p-0">
                @forelse($upcomingReservations as $reservation)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="badge {{ $reservation->isCurrent() ? 'text-bg-danger' : 'text-bg-warning' }}">
                                    {{ $reservation->isCurrent() ? __('menu.reservation_in_progress') : __('menu.reservation_upcoming') }}
                                </span>
                                <div class="mt-2"><strong>{{ $reservation->guest_name }}</strong></div>
                                @if($reservation->guest_phone)
                                    <div class="mt-1">@include('theme::pages.reservations._call', ['reservation' => $reservation])</div>
                                @endif
                                <div>{{ $reservation->party_size }} {{ __('menu.seats') }}</div>
                                <div class="text-muted small">
                                    @include('theme::pages.reservations._period', ['reservation' => $reservation])
                                </div>
                                @if($reservation->notes)
                                    <div class="small text-muted mt-1">{{ $reservation->notes }}</div>
                                @endif
                                <div class="small text-muted mt-1">
                                    {{ __('menu.reservation_created_by') }}: {{ $reservation->user?->name ?? '—' }}
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                                @include('theme::pages.reservations._complete', ['reservation' => $reservation, 'block' => true])
                                <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                                      onsubmit="return confirm('{{ __('menu.confirm_cancel_reservation') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">{{ __('menu.reservation_cancel') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted p-3 mb-0">{{ __('menu.no_reservations_for_table') }}</p>
                @endforelse
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Adisyon</h5>
                @if(!$activeOrder)
                    <form method="POST" action="{{ route('waiter.orders.create', $table) }}">@csrf
                        <button class="btn btn-success btn-sm">Adisyon Aç</button>
                    </form>
                @endif
            </div>
            @if($activeOrder)
            <div class="card-body p-0">
                <div class="px-3 pt-2">
                    <span class="badge {{ $activeOrder->status->badgeClass() }}">{{ $activeOrder->status->label() }}</span>
                </div>
                <table class="table mb-0">
                    <thead><tr><th>Ürün</th><th>Adet</th><th>Durum</th><th>Tutar</th><th></th></tr></thead>
                    <tbody>
                    @foreach($activeOrder->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->qty }}</td>
                            <td><span class="badge text-bg-secondary">{{ $item->status->label() }}</span>
                                @if($item->status->value === 'ready')
                                <form method="POST" action="{{ route('waiter.orders.items.served', [$activeOrder, $item]) }}" class="d-inline">@csrf
                                    <button class="btn btn-xs btn-sm btn-outline-success">Servis</button>
                                </form>
                                @endif
                            </td>
                            <td>{{ number_format($item->lineTotal(), 2) }} ₺</td>
                            <td class="text-end">
                                @if($item->status->value === 'pending')
                                <form method="POST" action="{{ route('waiter.orders.items.destroy', [$activeOrder, $item]) }}" class="d-inline"
                                      onsubmit="return confirm('{{ __('menu.confirm_remove_item') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('menu.remove_item') }}">×</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="4">Toplam</th><th>{{ number_format($activeOrder->total, 2) }} ₺</th></tr></tfoot>
                </table>
            </div>
            <div class="card-footer d-flex gap-2 flex-wrap">
                @if($activeOrder->status->value === 'awaiting_payment')
                    <span class="text-muted">{{ __('menu.awaiting_cashier_payment') }}</span>
                @elseif($activeOrder->items->isEmpty())
                    <form method="POST" action="{{ route('waiter.orders.close', $activeOrder) }}"
                          onsubmit="return confirm('{{ __('menu.confirm_close_empty_bill') }}')">@csrf
                        <button type="submit" class="btn btn-outline-secondary">{{ __('menu.close_empty_bill') }}</button>
                    </form>
                @else
                <form method="POST" action="{{ route('waiter.orders.send', $activeOrder) }}">@csrf
                    <button class="btn btn-warning">{{ __('menu.send_to_kitchen') }}</button>
                </form>
                <form method="POST" action="{{ route('waiter.orders.request-payment', $activeOrder) }}"
                      onsubmit="return confirm('{{ __('menu.confirm_request_payment') }}')">@csrf
                    <button class="btn btn-primary">{{ __('menu.close_bill') }}</button>
                </form>
                @endif
            </div>
            @else
            <div class="card-body text-muted">Bu masada açık adisyon yok.</div>
            @endif
        </div>
    </div>
    @if($activeOrder && $activeOrder->status->value !== 'awaiting_payment')
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">{{ __('menu.add_product') }}</h5>
                <input type="search" id="productSearch" class="form-control form-control-sm" style="max-width:260px"
                       placeholder="{{ __('menu.search_product') }}" autocomplete="off">
            </div>
            <div class="card-body" id="productPicker">
                @foreach($categories as $category)
                    @if($category->products->count())
                    <div class="product-category mb-3" data-category="{{ $category->name }}">
                        <h6>{{ $category->name }}</h6>
                        <div class="row">
                        @foreach($category->products as $product)
                            <div class="col-md-6 mb-2 product-item" data-name="{{ mb_strtolower($product->name) }}" data-category="{{ mb_strtolower($category->name) }}">
                                <form method="POST" action="{{ route('waiter.orders.items.store', $activeOrder) }}" class="border rounded p-2 h-100">@csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                             class="product-picker-thumb flex-shrink-0 rounded">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-semibold text-truncate">{{ $product->name }}</div>
                                            <div class="small text-muted">{{ number_format($product->price, 2) }} ₺</div>
                                        </div>
                                        <div class="d-flex gap-1 flex-shrink-0">
                                            <input type="number" name="qty" value="1" min="1" max="99" class="form-control form-control-sm" style="width:60px">
                                            <button class="btn btn-sm btn-primary">+</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
                <p id="productSearchEmpty" class="text-muted d-none mb-0">{{ __('menu.no_products_found') }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
<a href="{{ route('waiter.tables.index') }}" class="btn btn-secondary mt-2">← Masalara Dön</a>
@endsection

@if($activeOrder && $activeOrder->status->value !== 'awaiting_payment')
@push('styles')
<style>
    .product-picker-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        background: var(--bs-tertiary-bg);
    }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('productSearch');
    const items = document.querySelectorAll('.product-item');
    const categories = document.querySelectorAll('.product-category');
    const empty = document.getElementById('productSearchEmpty');

    if (!input) return;

    input.addEventListener('input', function () {
        const q = input.value.trim().toLowerCase();
        let visible = 0;

        items.forEach(function (el) {
            const match = q === '' || el.dataset.name.includes(q) || el.dataset.category.includes(q);
            el.classList.toggle('d-none', !match);
            if (match) visible++;
        });

        categories.forEach(function (cat) {
            const hasVisible = cat.querySelector('.product-item:not(.d-none)');
            cat.classList.toggle('d-none', !hasVisible);
        });

        empty.classList.toggle('d-none', visible > 0);
    });
});
</script>
@endpush
@endif
