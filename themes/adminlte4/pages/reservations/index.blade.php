@use(App\Enums\ReservationStatus)

@extends('theme::layouts.app')
@section('title', __('menu.reservations'))
@section('page-title', __('menu.reservations'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <p class="text-muted mb-0">{{ __('menu.reservations_hint') }}</p>
    <a href="{{ route('reservations.create') }}" class="btn btn-primary">{{ __('menu.add_reservation') }}</a>
</div>

<form method="GET" action="{{ route('reservations.index') }}" class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label" for="reservationSearch">{{ __('menu.search') }}</label>
                <input type="search" id="reservationSearch" name="search" class="form-control"
                       value="{{ $search }}" placeholder="{{ __('menu.reservation_search_placeholder') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="perPage">{{ __('menu.per_page') }}</label>
                <select id="perPage" name="per_page" class="form-select">
                    @foreach([10, 50, 150] as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">{{ __('menu.search') }}</button>
                @if($search !== '')
                    <a href="{{ route('reservations.index', ['per_page' => $perPage]) }}" class="btn btn-outline-secondary">{{ __('menu.clear') }}</a>
                @endif
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>{{ __('menu.table') }}</th>
                    <th>{{ __('menu.reservation_guest') }}</th>
                    <th>{{ __('menu.phone') }}</th>
                    <th>{{ __('menu.reservation_party_size') }}</th>
                    <th>{{ __('menu.reservation_period') }}</th>
                    <th>{{ __('menu.reservation_created_by') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td>
                            <a href="{{ route('waiter.tables.show', $reservation->cafeTable) }}">{{ $reservation->cafeTable->name }}</a>
                        </td>
                        <td>{{ $reservation->guest_name }}</td>
                        <td class="text-nowrap">
                            @if($reservation->guest_phone)
                                <div>{{ $reservation->guest_phone }}</div>
                                @include('theme::pages.reservations._call', ['reservation' => $reservation])
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $reservation->party_size }} {{ __('menu.seats') }}</td>
                        <td>@include('theme::pages.reservations._period', ['reservation' => $reservation])</td>
                        <td>{{ $reservation->user?->name ?? '—' }}</td>
                        <td>
                            @if($reservation->status === ReservationStatus::Completed)
                                <span class="badge text-bg-secondary">{{ __('menu.reservation_completed') }}</span>
                            @elseif($reservation->isCurrent())
                                <span class="badge text-bg-danger">{{ __('menu.reservation_in_progress') }}</span>
                            @else
                                <span class="badge text-bg-warning">{{ __('menu.reservation_upcoming') }}</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($reservation->status === ReservationStatus::Active)
                            <a href="{{ route('reservations.edit', $reservation) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            @include('theme::pages.reservations._complete', ['reservation' => $reservation])
                            <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" class="d-inline"
                                  onsubmit="return confirm('{{ __('menu.confirm_cancel_reservation') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('menu.reservation_cancel') }}</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @if($reservation->notes)
                        <tr>
                            <td colspan="8" class="pt-0 pb-3 text-muted small">{{ $reservation->notes }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="8" class="text-muted p-4">{{ __('menu.no_reservations') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages() || $reservations->total() > 0)
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">{{ __('menu.showing_results', ['from' => $reservations->firstItem() ?? 0, 'to' => $reservations->lastItem() ?? 0, 'total' => $reservations->total()]) }}</small>
            {{ $reservations->links() }}
        </div>
    @endif
</div>
@endsection
