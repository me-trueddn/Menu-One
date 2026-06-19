@extends('theme::layouts.app')

@section('title', __('menu.create_group'))
@section('page-title', __('menu.create_group'))

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.user-groups.store') }}">
            @csrf
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('menu.group_name') }}</label>
                    <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">{{ __('menu.group_description') }}</label>
                    <input name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}">
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <h6 class="mb-3">{{ __('menu.module_permissions') }}</h6>
            @include('theme::partials.platform-module-permissions', ['modules' => $modules, 'permissions' => $permissions])

            <div class="mt-4">
                <button class="btn btn-primary">{{ __('menu.save') }}</button>
                <a href="{{ route('platform.user-groups.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
