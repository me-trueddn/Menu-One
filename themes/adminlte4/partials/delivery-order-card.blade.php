@php($status = $order->integration_status)
@php($itemsReadyForCourier = $order->items->every(fn ($item) => in_array($item->status->value, ['ready', 'served'], true)))
<div class="col-lg-6" data-delivery-order="{{ $order->id }}">
    <div class="card h-100 border-start border-4 border-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                @if($order->integration_provider)
                    <span class="badge {{ $order->integration_provider->badgeClass() }}">{{ $order->integration_provider->label() }}</span>
                @endif
                <strong class="ms-1">#{{ $order->external_order_id }}</strong>
            </div>
            @if($status)
                <span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span>
            @endif
        </div>
        <div class="card-body">
            <div class="small mb-2">
                <div><strong>{{ __('menu.tables') }}:</strong> {{ $order->cafeTable?->name ?? '—' }}</div>
                @if($order->customer_name)
                    <div><strong>{{ __('menu.customer') }}:</strong> {{ $order->customer_name }}</div>
                @endif
                @if($order->customer_phone)
                    <div><strong>{{ __('menu.phone') }}:</strong> {{ $order->customer_phone }}</div>
                @endif
                @if($order->delivery_note)
                    <div><strong>{{ __('menu.note') }}:</strong> {{ $order->delivery_note }}</div>
                @endif
                @if($order->payment_collected_externally)
                    <span class="badge text-bg-info mt-1">{{ __('menu.platform_prepaid') }}</span>
                @endif
            </div>
            <ul class="list-unstyled small mb-3">
                @foreach($order->items as $item)
                    <li class="d-flex justify-content-between border-bottom py-1">
                        <span>{{ $item->product?->name }} × {{ $item->qty }}</span>
                        <span>{{ number_format((float) $item->unit_price * $item->qty, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="fw-semibold mb-3">{{ __('menu.total') }}: {{ number_format((float) $order->total, 2) }}</div>

            <div class="d-flex flex-wrap gap-2">
                @if($status === \App\Enums\IntegrationOrderStatus::PendingAcceptance)
                    <form method="POST" action="{{ route('admin.delivery-orders.accept', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">{{ __('menu.integration_accept_order') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.delivery-orders.reject', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('menu.integration_reject_order') }}</button>
                    </form>
                @endif

                @if(in_array($status, [\App\Enums\IntegrationOrderStatus::Accepted, \App\Enums\IntegrationOrderStatus::Preparing], true)
                    && $status !== \App\Enums\IntegrationOrderStatus::Preparing)
                    <form method="POST" action="{{ route('admin.delivery-orders.preparing', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('menu.integration_mark_preparing') }}</button>
                    </form>
                @endif

                @if(in_array($status, [\App\Enums\IntegrationOrderStatus::Accepted, \App\Enums\IntegrationOrderStatus::Preparing], true)
                    && $itemsReadyForCourier)
                    <form method="POST" action="{{ route('admin.delivery-orders.ready-for-courier', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">{{ __('menu.integration_ready_for_courier') }}</button>
                    </form>
                @endif

                @if($status === \App\Enums\IntegrationOrderStatus::ReadyForCourier)
                    <form method="POST" action="{{ route('admin.delivery-orders.hand-to-courier', $order) }}">
                        @csrf
                        <button type="submit" class="btn btn-dark btn-sm">{{ __('menu.integration_hand_to_courier') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
