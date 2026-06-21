@extends('theme::layouts.app')

@section('title', __('menu.cashier'))

@section('page-title', __('menu.cashier_tables'))

@section('content')

<div class="card mb-3">

    <div class="card-body">

        <form method="GET" action="{{ route('cashier.tables.index') }}" class="row g-2 align-items-center">

            <div class="col-md-6">

                <input type="search" name="q" value="{{ $search }}" class="form-control"

                       placeholder="{{ __('menu.search_table') }}" autofocus>

            </div>

            <div class="col-auto">

                <button class="btn btn-primary">{{ __('menu.search') }}</button>

                @if($search)

                    <a href="{{ route('cashier.tables.index') }}" class="btn btn-outline-secondary">{{ __('menu.clear') }}</a>

                @endif

            </div>

        </form>

    </div>

</div>



<div class="row">

    @forelse($tables as $table)

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

                            </div>

                            <div class="mt-2 fw-semibold">{{ number_format($order->total, 2) }} ₺</div>

                            <div class="mt-1"><span class="badge text-bg-success">{{ __('menu.take_payment') }}</span></div>

                        @endif

                    </div>

                </div>

            </a>

        </div>

    @empty

        <div class="col-12"><p class="text-muted">{{ __('menu.no_tables_found') }}</p></div>

    @endforelse

</div>

@endsection

