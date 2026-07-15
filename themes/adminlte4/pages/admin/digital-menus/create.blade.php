@extends('theme::layouts.app')
@section('title', __('menu.create_qr_menu'))
@section('page-title', __('menu.create_qr_menu'))
@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.digital-menus.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('menu.digital_menu_name') }}</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required maxlength="255"
                       placeholder="{{ __('menu.digital_menu_name_placeholder') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">{{ __('menu.digital_menu_name_hint') }}</div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                       @checked(old('is_active', true))>
                <label class="form-check-label" for="is_active">{{ __('menu.digital_menu_active') }}</label>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('menu.create_qr_menu') }}</button>
            <a href="{{ route('admin.digital-menus.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
