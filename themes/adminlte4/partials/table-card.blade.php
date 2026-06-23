@php($reservation = $table->nextReservation())
@php($displayStatus = $table->displayStatus())
@php($readyCount = ($readyCountsByTable ?? [])[$table->id] ?? 0)
@php($hasReady = $readyCount > 0)
@php($borderClass = $hasReady ? 'warning' : ($displayStatus->value === 'occupied' ? 'danger' : ($displayStatus->value === 'reserved' ? 'warning' : 'success')))
<div class="col-md-3 col-sm-6 mb-3" data-waiter-table-card data-table-id="{{ $table->id }}" data-table-status="{{ $displayStatus->value }}">
    <a href="{{ route('waiter.tables.show', $table) }}" class="text-decoration-none">
        <div class="card h-100 border-{{ $borderClass }} {{ $hasReady ? 'bg-warning-subtle' : '' }}" data-table-card>
            <div class="card-body text-center">
                <h4 class="card-title">{{ $table->name }}</h4>
                <p class="mb-1"><span class="badge {{ $displayStatus->badgeClass() }}">{{ $displayStatus->label() }}</span></p>
                <small class="text-muted">{{ $table->capacity }} {{ __('menu.seats') }}</small>
                <div data-ready-banner class="{{ $hasReady ? '' : 'd-none' }} mt-2">
                    <span class="badge text-bg-warning">{{ __('menu.table_orders_ready') }}</span>
                    <div class="small text-muted mt-1" data-ready-count>
                        @if($hasReady)
                            {{ trans_choice('menu.table_ready_items_count', $readyCount, ['count' => $readyCount]) }}
                        @endif
                    </div>
                </div>
                @if($reservation)
                    <div class="mt-2 small text-start border-top pt-2">
                        <span class="badge text-bg-warning">{{ __('menu.reserved') }}</span>
                        <div class="mt-1"><strong>{{ $reservation->guest_name }}</strong></div>
                        <div>{{ $reservation->party_size }} {{ __('menu.seats') }}</div>
                        <div class="text-muted">
                            {{ $reservation->starts_at->format('d.m.Y H:i') }}
                            – {{ $reservation->ends_at->format('H:i') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </a>
</div>
