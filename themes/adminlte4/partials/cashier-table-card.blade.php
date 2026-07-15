@php($order = $table->payableOrder)
<div class="col-md-3 col-sm-6 mb-3">
    <a href="{{ $order ? route('cashier.tables.show', $table) : '#' }}"
       class="text-decoration-none {{ $order ? '' : 'pe-none' }}"
       @unless($order) tabindex="-1" @endunless>
        <div class="card h-100 border-{{ $order ? 'primary' : 'secondary' }} {{ $order ? '' : 'opacity-50' }}">
            <div class="card-body text-center">
                <h4 class="card-title mb-1">{{ $table->name }}</h4>
                <p class="mb-1">
                    <span class="badge {{ $table->displayStatus()->badgeClass() }}">{{ $table->displayStatus()->label() }}</span>
                </p>
                <small class="text-muted">{{ $table->capacity }} {{ __('menu.seats') }}</small>
                @if($order)
                    <div class="mt-2">
                        <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                        @if($order->isCarryOver())
                            <span class="badge text-bg-danger">{{ __('menu.order_from_previous_day') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 fw-semibold">{{ number_format($order->total, 2) }} ₺</div>
                    <div class="small text-muted">{{ $order->created_at?->format('d.m.Y H:i') }}</div>
                    <div class="mt-1"><span class="badge text-bg-success">{{ __('menu.take_payment') }}</span></div>
                @endif
            </div>
        </div>
    </a>
</div>
