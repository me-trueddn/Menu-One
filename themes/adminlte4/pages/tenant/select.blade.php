@extends('theme::layouts.profile')

@section('title', __('menu.select_cafe'))
@section('page-title', __('menu.select_cafe'))

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <p class="text-muted">{{ __('menu.select_cafe_hint') }}</p>

        <form method="POST" action="{{ route('tenant.select.store') }}">
            @csrf
            <div class="list-group mb-3">
                @foreach($tenants as $tenant)
                    <label class="list-group-item list-group-item-action d-flex gap-3 align-items-center">
                        <input class="form-check-input flex-shrink-0" type="radio" name="tenant_id"
                               value="{{ $tenant->id }}"
                               @checked(old('tenant_id', $activeTenantId ?? null) === $tenant->id || ($loop->first && ! old('tenant_id') && empty($activeTenantId))) required>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $tenant->name }}</div>
                            <code class="small">{{ $tenant->id }}</code>
                        </div>
                        @if(($activeTenantId ?? null) === $tenant->id)
                            <span class="badge text-bg-primary">{{ __('menu.active_cafe') }}</span>
                        @endif
                    </label>
                @endforeach
            </div>

            @error('tenant_id')
                <div class="text-danger small mb-3">{{ $message }}</div>
            @enderror

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">{{ __('menu.continue') }}</button>
                <a href="{{ route('profile.edit') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
