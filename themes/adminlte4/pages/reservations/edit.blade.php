@extends('theme::layouts.app')
@section('title', __('menu.edit_reservation'))
@section('page-title', __('menu.edit_reservation'))
@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('reservations.update', $reservation) }}">
            @csrf @method('PUT')
            @include('theme::pages.reservations._form', ['tables' => $tables, 'reservation' => $reservation])
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">{{ __('menu.update') }}</button>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('reservations.index') }}"
                   class="btn btn-secondary">{{ __('menu.cancel') }}</a>
            </div>
        </form>
        <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" class="mt-3"
              onsubmit="return confirm('{{ __('menu.confirm_cancel_reservation') }}')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">{{ __('menu.reservation_cancel') }}</button>
        </form>
    </div>
</div>
@endsection
