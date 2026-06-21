@extends('theme::layouts.app')
@section('title', __('menu.edit_staff'))
@section('page-title', __('menu.edit_staff'))
@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">{{ __('menu.full_name') }}</label>
            <input class="form-control" value="{{ $staff->name }}" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('menu.email') }}</label>
            <input class="form-control" value="{{ $staff->email }}" disabled>
        </div>
        <form method="POST" action="{{ route('admin.staff.update', $staff) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ __('menu.role') }}</label>
                <select name="role" class="form-select">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected($staff->hasRole($role))>{{ __('menu.role_'.$role) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary">{{ __('menu.update') }}</button>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
