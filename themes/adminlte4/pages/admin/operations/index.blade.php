@extends('theme::layouts.app')
@section('title', __('menu.operations'))
@section('page-title', __('menu.operations'))
@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">{{ __('menu.tables') }} ({{ $tables->count() }})</h6></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($tables as $table)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $table->name }}</span>
                            <span class="badge text-bg-secondary">{{ $table->status->label() ?? $table->status }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('menu.no_tables') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">{{ __('menu.open_orders') }} ({{ $openOrders->count() }})</h6></div>
            <div class="card-body table-responsive p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('menu.tables') }}</th><th>{{ __('menu.status') }}</th><th>{{ __('menu.total') }}</th><th>{{ __('menu.staff') }}</th></tr></thead>
                    <tbody>
                        @forelse($openOrders as $order)
                            <tr>
                                <td>{{ $order->cafeTable?->name ?? '—' }}</td>
                                <td>{{ $order->status->label() ?? $order->status }}</td>
                                <td>{{ number_format((float) $order->total, 2) }}</td>
                                <td>{{ $order->user?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">{{ __('menu.no_open_orders') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ __('menu.kitchen') }}</h6></div>
            <div class="card-body">
                <h6 class="small text-muted">{{ __('menu.kitchen_pending') }} ({{ $kitchenItems->count() }})</h6>
                @forelse($kitchenItems as $item)
                    <div class="border rounded p-2 mb-2 small">
                        <strong>{{ $item->order->cafeTable?->name }}</strong> · {{ $item->product?->name }} × {{ $item->qty }}
                        <span class="badge text-bg-warning">{{ $item->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-muted small">{{ __('menu.no_kitchen_items') }}</p>
                @endforelse
                <h6 class="small text-muted mt-3">{{ __('menu.kitchen_ready') }} ({{ $readyItems->count() }})</h6>
                @forelse($readyItems as $item)
                    <div class="border rounded p-2 mb-2 small">
                        <strong>{{ $item->order->cafeTable?->name }}</strong> · {{ $item->product?->name }}
                        <span class="badge text-bg-success">{{ $item->status->label() }}</span>
                    </div>
                @empty
                    <p class="text-muted small">{{ __('menu.no_ready_items') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
