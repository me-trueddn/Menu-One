@extends('theme::layouts.app')

@section('title', __('menu.users'))
@section('page-title', __('menu.users'))

@section('page-actions')
@if($currentUser->canPlatformModule('users', 'edit'))
<div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-diagram-3"></i> {{ __('menu.user_groups') }}
    </button>
    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 260px;">
        <a href="{{ route('platform.user-groups.create') }}" class="btn btn-primary btn-sm w-100 mb-2">{{ __('menu.create_group') }}</a>
        <a href="{{ route('platform.user-groups.index') }}" class="btn btn-outline-secondary btn-sm w-100">{{ __('menu.manage_groups') }}</a>
    </div>
</div>
@endif
@if($currentUser->canPlatformModule('users', 'view'))
<a href="{{ route('platform.users.security') }}" class="btn btn-outline-warning btn-sm">
    <i class="bi bi-shield-lock"></i> {{ __('menu.security_settings') }}
</a>
@endif
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('menu.platform_users') }}</h5>
        <a href="{{ route('platform.users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> {{ __('menu.add') }}
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>{{ __('menu.user_id') }}</th>
                    <th>{{ __('menu.full_name') }}</th>
                    <th>{{ __('menu.email') }}</th>
                    <th>{{ __('menu.phone') }}</th>
                    <th>{{ __('menu.role') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><code>{{ $user->public_id }}</code></td>
                        <td>{{ $user->name }} @if($user->is_super_admin)<span class="badge text-bg-dark">{{ __('menu.super_admin') }}</span>@endif</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>{{ $user->roleLabel() }}</td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $user->is_active ? __('menu.active') : __('menu.inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm flex-wrap justify-content-end">
                                @unless($user->is_super_admin)
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#resetPwd{{ $user->id }}">{{ __('menu.reset_password') }}</button>
                                    <form action="{{ route('platform.users.toggle-2fa', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-secondary" disabled title="{{ __('menu.two_factor_disabled_globally') }}">
                                            2FA {{ $user->two_factor_enabled ? 'ON' : 'OFF' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changeEmail{{ $user->id }}">{{ __('menu.change_email') }}</button>
                                    <form action="{{ route('platform.users.send-reset-link', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-info">{{ __('menu.send_reset_link') }}</button>
                                    </form>
                                    <form action="{{ route('platform.users.toggle-active', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-outline-warning">{{ $user->is_active ? __('menu.deactivate') : __('menu.activate') }}</button>
                                    </form>
                                    <a href="{{ route('platform.users.edit', $user) }}" class="btn btn-outline-primary">{{ __('menu.edit') }}</a>
                                @else
                                    <span class="text-muted small">{{ __('menu.super_admin_protected') }}</span>
                                @endunless
                            </div>

                            @unless($user->is_super_admin)
                            <div class="modal fade" id="resetPwd{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('platform.users.reset-password', $user) }}">
                                            @csrf
                                            <div class="modal-header"><h5 class="modal-title">{{ __('menu.reset_password') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <div class="mb-3"><label class="form-label">{{ __('menu.password') }}</label><input type="password" name="password" class="form-control" required></div>
                                                <div class="mb-3"><label class="form-label">{{ __('menu.password_confirm') }}</label><input type="password" name="password_confirmation" class="form-control" required></div>
                                            </div>
                                            <div class="modal-footer"><button class="btn btn-primary">{{ __('menu.save') }}</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="changeEmail{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('platform.users.change-email', $user) }}">
                                            @csrf
                                            <div class="modal-header"><h5 class="modal-title">{{ __('menu.change_email') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                            </div>
                                            <div class="modal-footer"><button class="btn btn-primary">{{ __('menu.save') }}</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection
