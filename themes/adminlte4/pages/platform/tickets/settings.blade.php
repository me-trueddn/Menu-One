@extends('theme::layouts.app')

@section('title', __('menu.ticket_settings'))
@section('page-title', __('menu.ticket_settings'))

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.tickets.settings.update') }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_allowed_extensions') }}</label>
                <input type="text" name="ticket_file_extensions" class="form-control" value="{{ old('ticket_file_extensions', $extensions) }}" required>
                <div class="form-text">{{ __('menu.ticket_allowed_extensions_hint') }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_max_file_size_mb') }}</label>
                <input type="number" name="ticket_max_file_size_mb" class="form-control" value="{{ old('ticket_max_file_size_mb', $maxSizeMb) }}" min="1" max="50" required>
            </div>
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
        </form>
    </div>
</div>
@endsection
