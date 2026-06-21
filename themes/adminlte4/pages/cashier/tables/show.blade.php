@extends('theme::layouts.app')
@section('title', __('menu.take_payment'))
@section('page-title', $table->name.' — '.__('menu.take_payment'))
@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">{{ __('menu.order_summary') }}</h5>
                <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('menu.products') }}</th><th>{{ __('menu.qty') }}</th><th>{{ __('menu.total') }}</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->lineTotal(), 2) }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="2">{{ __('menu.grand_total') }}</th><th>{{ number_format($order->total, 2) }} ₺</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('menu.payment') }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('cashier.orders.pay', $order) }}" id="paymentForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.payment_method') }}</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($paymentMethods as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_{{ $value }}"
                                           value="{{ $value }}" @checked(old('payment_method', 'cash') === $value) required>
                                    <label class="form-check-label" for="pay_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('payment_method')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.split_count') }}</label>
                        <input type="number" name="split_count" id="splitCount" class="form-control" min="0" max="99"
                               value="{{ old('split_count', $table->capacity) }}" required>
                        <div class="form-text">{{ __('menu.split_count_hint') }}</div>
                        @error('split_count')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('menu.grand_total') }}</span>
                            <strong>{{ number_format($order->total, 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span id="paymentAmountLabel">{{ __('menu.per_person') }}</span>
                            <strong id="perPersonAmount">{{ number_format((float) $order->total / max(1, (int) old('split_count', $table->capacity)), 2) }} ₺</strong>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('{{ __('menu.confirm_payment') }}')">
                        {{ __('menu.complete_payment') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<a href="{{ route('cashier.tables.index') }}" class="btn btn-secondary mt-3">← {{ __('menu.back_to_tables') }}</a>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const total = {{ (float) $order->total }};
    const splitInput = document.getElementById('splitCount');
    const perPerson = document.getElementById('perPersonAmount');
    const amountLabel = document.getElementById('paymentAmountLabel');
    const perPersonLabel = @json(__('menu.per_person'));
    const fullTotalLabel = @json(__('menu.payment_full_total'));

    function updatePerPerson() {
        const raw = parseInt(splitInput.value, 10);
        const split = Number.isNaN(raw) ? 1 : raw;

        if (split <= 0) {
            amountLabel.textContent = fullTotalLabel;
            perPerson.textContent = total.toFixed(2) + ' ₺';
            return;
        }

        amountLabel.textContent = perPersonLabel;
        perPerson.textContent = (total / split).toFixed(2) + ' ₺';
    }

    splitInput.addEventListener('input', updatePerPerson);
    updatePerPerson();
});
</script>
@endpush
