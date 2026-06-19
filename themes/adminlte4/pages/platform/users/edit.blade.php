@extends('theme::layouts.app')

@section('title', __('menu.edit_user'))
@section('page-title', __('menu.edit_user'))

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">{{ __('menu.user_id') }}</label>
                <input class="form-control" value="{{ $user->public_id }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.full_name') }}</label>
                <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.email') }}</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.phone') }}</label>
                <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.new_password_optional') }}</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.role') }}</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach($groups as $group)
                        @if($group->name !== 'user')
                            <option value="{{ $group->name }}" @selected(old('role', $user->getRoleNames()->first()) === $group->name)>{{ $group->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">{{ __('menu.update') }}</button>
            <a href="{{ route('platform.users.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ __('menu.assigned_tenants') }}</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">{{ __('menu.staff_tenant_hint') }}</p>

        @if($user->assignedTenants->isNotEmpty())
            <ul class="list-group mb-3">
                @foreach($user->assignedTenants as $assignedTenant)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $assignedTenant->name }}</strong>
                            <code class="ms-2">{{ $assignedTenant->id }}</code>
                        </div>
                        <form action="{{ route('platform.users.tenants.detach', [$user, $assignedTenant]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('menu.remove_tenant') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">{{ __('menu.no_tenant') }}</p>
        @endif

        <form method="POST" action="{{ route('platform.users.tenants.attach', $user) }}">
            @csrf
            <label class="form-label">{{ __('menu.add_tenant_assignment') }}</label>
            <div class="input-group">
                <input type="text" name="tenant_id" class="form-control font-monospace"
                       placeholder="123-456" pattern="\d{3}-\d{3}" maxlength="7">
                <button class="btn btn-primary">{{ __('menu.add') }}</button>
            </div>
            <div class="form-text">{{ __('menu.assign_tenant_id_hint') }}</div>
        </form>
    </div>
</div>
@endsection
