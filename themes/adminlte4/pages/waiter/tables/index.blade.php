@extends('theme::layouts.app')
@section('title', 'Masalar')
@section('page-title', __('menu.waiter').' — '.__('menu.tables'))
@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('reservations.index') }}" class="btn btn-outline-primary btn-sm">{{ __('menu.reservations') }}</a>
</div>
<div class="row">
@foreach($tables as $table)
    @php($reservation = $table->nextReservation())
    @php($displayStatus = $table->displayStatus())
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="{{ route('waiter.tables.show', $table) }}" class="text-decoration-none">
            <div class="card h-100 border-{{ $displayStatus->value === 'occupied' ? 'danger' : ($displayStatus->value === 'reserved' ? 'warning' : 'success') }}">
                <div class="card-body text-center">
                    <h4 class="card-title">{{ $table->name }}</h4>
                    <p class="mb-1"><span class="badge {{ $displayStatus->badgeClass() }}">{{ $displayStatus->label() }}</span></p>
                    <small class="text-muted">{{ $table->capacity }} {{ __('menu.seats') }}</small>
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
@endforeach
</div>
<div id="ready-alert" class="alert alert-info d-none"></div>
@endsection
@push('scripts')
<script>
setInterval(async () => {
    const res = await fetch('{{ route('waiter.ready-items.poll') }}');
    const data = await res.json();
    const el = document.getElementById('ready-alert');
    if (data.items.length) {
        el.classList.remove('d-none');
        el.textContent = '{{ __('menu.ready_orders') }}: ' + data.items.map(i => i.table + ' - ' + i.product).join(', ');
    } else { el.classList.add('d-none'); }
}, 5000);
</script>
@endpush
