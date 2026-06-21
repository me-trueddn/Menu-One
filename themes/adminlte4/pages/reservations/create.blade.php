@extends('theme::layouts.app')
@section('title', __('menu.add_reservation'))
@section('page-title', __('menu.add_reservation'))
@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('reservations.store') }}">
            @csrf
            @include('theme::pages.reservations._form', ['tables' => $tables, 'selectedTableId' => $selectedTableId])
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
                <a href="{{ route('reservations.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
