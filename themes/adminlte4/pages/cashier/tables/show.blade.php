@extends('theme::layouts.app')
@php
    $splitLocked = $order->splitPaidCount() > 0;
    $activeSplitCount = $splitLocked ? (int) $order->split_count : (int) old('split_count', $table->capacity);
    $activeDiscount = $splitLocked ? (float) $order->discount_percent : (float) old('discount_percent', 0);
    $nextPayment = $splitLocked ? $order->nextPaymentAmount() : $order->amountDue($activeDiscount);
    if (! $splitLocked && $activeSplitCount > 0) {
        $perPerson = round($order->amountDue($activeDiscount) / $activeSplitCount, 2);
        $nextPayment = $perPerson;
    } elseif (! $splitLocked && $activeSplitCount <= 0) {
        $nextPayment = $order->amountDue($activeDiscount);
    }
    $currentPayment = $splitLocked ? $order->splitPaidCount() + 1 : 1;
    $showSplitProgress = $splitLocked && $order->split_count > 0;
    $confirmSplitTemplate = __('menu.confirm_split_payment', [
        'amount' => ':amount',
        'current' => ':current',
        'total' => ':total',
    ]);
@endphp
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
                <table class="table mb-0 align-middle">
                    <thead><tr><th>{{ __('menu.products') }}</th><th>{{ __('menu.qty') }}</th><th>{{ __('menu.total') }}</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $item->product->imageUrl() }}" alt="{{ $item->product->name }}"
                                             class="payment-product-thumb rounded flex-shrink-0">
                                        <span>{{ $item->product->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->lineTotal(), 2) }} ₺</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><th colspan="2">{{ __('menu.subtotal') }}</th><th>{{ number_format($order->total, 2) }} ₺</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('menu.payment') }}</h5></div>
            <div class="card-body">
                @if($showSplitProgress)
                    <div class="alert alert-info py-2">
                        <div class="fw-semibold">{{ __('menu.split_payment_progress', ['paid' => $order->split_paid_count, 'total' => $order->split_count]) }}</div>
                    </div>
                @endif
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
                        <label class="form-label" for="discountPercent">{{ __('menu.discount_percent') }}</label>
                        <div class="input-group">
                            <input type="number" name="discount_percent" id="discountPercent" class="form-control"
                                   min="0" max="100" step="0.01"
                                   value="{{ $activeDiscount }}"
                                   @disabled($splitLocked)>
                            <span class="input-group-text">%</span>
                        </div>
                        @if($splitLocked)
                            <input type="hidden" name="discount_percent" value="{{ $activeDiscount }}">
                        @endif
                        <div class="form-text">{{ __('menu.discount_percent_hint') }}</div>
                        @error('discount_percent')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.split_count') }}</label>
                        <input type="number" name="split_count" id="splitCount" class="form-control" min="0" max="99"
                               value="{{ $activeSplitCount }}" required @disabled($splitLocked)>
                        @if($splitLocked)
                            <input type="hidden" name="split_count" value="{{ $activeSplitCount }}">
                        @endif
                        <div class="form-text">{{ __('menu.split_count_hint') }}</div>
                        @error('split_count')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('menu.subtotal') }}</span>
                            <strong id="subtotalAmount">{{ number_format($order->total, 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2 text-danger d-none" id="discountRow">
                            <span>{{ __('menu.discount_amount') }}</span>
                            <strong id="discountAmount">-0,00 ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2 border-top pt-2">
                            <span>{{ __('menu.amount_due') }}</span>
                            <strong id="amountDue">{{ number_format($order->amountDue($activeDiscount), 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span id="paymentAmountLabel">{{ $activeSplitCount > 0 ? __('menu.per_person') : __('menu.payment_full_total') }}</span>
                            <strong id="perPersonAmount">{{ number_format($nextPayment, 2) }} ₺</strong>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success w-100" id="paymentSubmitBtn">
                        @if($showSplitProgress)
                            {{ __('menu.collect_split_payment', ['current' => $currentPayment, 'total' => $order->split_count]) }}
                        @else
                            {{ __('menu.complete_payment') }}
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<a href="{{ route('cashier.tables.index') }}" class="btn btn-secondary mt-3">← {{ __('menu.back_to_tables') }}</a>
@endsection

@push('styles')
<style>
    .payment-product-thumb {
        width: 72px;
        height: 72px;
        object-fit: cover;
        background: var(--bs-tertiary-bg);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const splitLocked = @json($splitLocked);
    const subtotal = {{ (float) $order->total }};
    const splitInput = document.getElementById('splitCount');
    const discountInput = document.getElementById('discountPercent');
    const discountAmountEl = document.getElementById('discountAmount');
    const discountRow = document.getElementById('discountRow');
    const amountDueEl = document.getElementById('amountDue');
    const perPerson = document.getElementById('perPersonAmount');
    const amountLabel = document.getElementById('paymentAmountLabel');
    const paymentForm = document.getElementById('paymentForm');
    const perPersonLabel = @json(__('menu.per_person'));
    const fullTotalLabel = @json(__('menu.payment_full_total'));
    const confirmPayment = @json(__('menu.confirm_payment'));
    const confirmSplitTemplate = @json($confirmSplitTemplate);

    const formatMoney = (value) => value.toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' ₺';

    const clampPercent = (value) => Math.min(100, Math.max(0, value));

    const getDiscountPercent = () => {
        if (splitLocked) {
            return {{ $activeDiscount }};
        }
        const raw = parseFloat(discountInput.value);
        return Number.isNaN(raw) ? 0 : clampPercent(raw);
    };

    const getSplitCount = () => {
        if (splitLocked) {
            return {{ $activeSplitCount }};
        }
        const raw = parseInt(splitInput.value, 10);
        return Number.isNaN(raw) ? 0 : raw;
    };

    const getAmountDue = () => {
        const percent = getDiscountPercent();
        return Math.round(subtotal * (1 - percent / 100) * 100) / 100;
    };

    const getNextPaymentAmount = () => {
        const due = getAmountDue();
        const split = getSplitCount();

        if (split <= 0) {
            return due;
        }

        @if($showSplitProgress)
        const paid = {{ $order->split_paid_count }};
        const perPerson = Math.round((due / split) * 100) / 100;
        if (paid >= split - 1) {
            return Math.round((due - perPerson * Math.max(0, split - 1)) * 100) / 100;
        }
        return perPerson;
        @else
        return Math.round((due / split) * 100) / 100;
        @endif
    };

    function updateAmounts() {
        const amountDue = getAmountDue();
        const discountAmount = Math.round((subtotal - amountDue) * 100) / 100;
        const split = getSplitCount();
        const nextAmount = getNextPaymentAmount();

        amountDueEl.textContent = formatMoney(amountDue);

        if (discountAmount > 0) {
            discountRow.classList.remove('d-none');
            discountAmountEl.textContent = '-' + formatMoney(discountAmount);
        } else {
            discountRow.classList.add('d-none');
            discountAmountEl.textContent = '-0,00 ₺';
        }

        if (split <= 0) {
            amountLabel.textContent = fullTotalLabel;
            perPerson.textContent = formatMoney(amountDue);
            return;
        }

        amountLabel.textContent = perPersonLabel;
        perPerson.textContent = formatMoney(nextAmount);
    }

    if (! splitLocked) {
        splitInput.addEventListener('input', updateAmounts);
        discountInput.addEventListener('input', updateAmounts);
    }

    paymentForm.addEventListener('submit', function (event) {
        const split = getSplitCount();
        const nextAmount = formatMoney(getNextPaymentAmount());

        if (split > 0) {
            const current = {{ $showSplitProgress ? $currentPayment : 1 }};
            const message = confirmSplitTemplate
                .replace(':amount', nextAmount)
                .replace(':current', String(current))
                .replace(':total', String(split));

            if (! window.confirm(message)) {
                event.preventDefault();
            }
            return;
        }

        if (! window.confirm(confirmPayment)) {
            event.preventDefault();
        }
    });

    updateAmounts();
});
</script>
@endpush
