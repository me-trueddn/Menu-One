@extends('theme::layouts.profile')

@section('title', __('menu.profile'))
@section('page-title', __('menu.profile'))

@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                     style="width: 56px; height: 56px; font-size: 1.5rem;">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
            <div class="col">
                <h4 class="mb-1">{{ $user->name }}</h4>
                <div class="text-muted small">{{ $user->email }}</div>
            </div>
            <div class="col-sm-auto text-sm-end">
                <div class="small text-muted">{{ __('menu.account_type') }}</div>
                <span class="badge text-bg-info">{{ __('menu.account_type_free') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header border-bottom-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'profile' ? 'active' : '' }}"
                   href="{{ route('profile.edit', ['tab' => 'profile']) }}">
                    <i class="bi bi-person me-1"></i>{{ __('menu.profile_tab') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'security' ? 'active' : '' }}"
                   href="{{ route('profile.edit', ['tab' => 'security']) }}">
                    <i class="bi bi-shield-lock me-1"></i>{{ __('menu.security_tab') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'cafe' ? 'active' : '' }}"
                   href="{{ route('profile.edit', ['tab' => 'cafe']) }}">
                    <i class="bi bi-shop me-1"></i>{{ __('menu.create_cafe_tab') }}
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        @if($tab === 'profile')
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label text-muted small">{{ __('menu.user_id') }}</label>
                    <div><code>{{ $user->public_id }}</code></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">{{ __('menu.language') }}</label>
                    <div class="d-flex gap-2">
                        @foreach(config('locale.available', []) as $code)
                            <a href="{{ route('locale.switch', $code) }}"
                               class="btn btn-sm {{ app()->getLocale() === $code ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ config('locale.names.'.$code, $code) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-12"><hr class="my-0"></div>

                <div class="col-12">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">{{ __('menu.full_name') }}</label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">{{ __('menu.phone') }}</label>
                                <input type="text" id="phone" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu.email') }}</label>
                                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                <div class="form-text">{{ __('menu.email_change_in_security') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('menu.account_type') }}</label>
                                <input type="text" class="form-control" value="{{ __('menu.account_type_free') }}" disabled>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('menu.save') }}</button>
                        </div>
                    </form>
                </div>

                @if($user->linkedTenants()->isNotEmpty())
                    <div class="col-12"><hr class="my-0"></div>
                    <div class="col-12">
                        <h6 class="fw-semibold">{{ __('menu.my_cafes') }}</h6>
                        <p class="text-muted small">{{ __('menu.my_cafes_hint') }}</p>
                        <ul class="list-group">
                            @foreach($user->linkedTenants() as $linkedTenant)
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <strong>{{ $linkedTenant->name }}</strong>
                                        <code class="ms-2">{{ $linkedTenant->id }}</code>
                                        @if(($activeTenantId ?? null) === $linkedTenant->id)
                                            <span class="badge text-bg-primary ms-1">{{ __('menu.active_cafe') }}</span>
                                        @endif
                                    </div>
                                    @if(($activeTenantId ?? null) !== $linkedTenant->id)
                                        <form method="POST" action="{{ route('tenant.select.store') }}">
                                            @csrf
                                            <input type="hidden" name="tenant_id" value="{{ $linkedTenant->id }}">
                                            <input type="hidden" name="redirect" value="{{ route('profile.edit', ['tab' => 'profile']) }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('menu.switch_cafe') }}</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @if($user->linkedTenants()->count() > 1 && $user->managesCafePanel())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary mt-3">{{ __('menu.go_to_cafe_panel') }}</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if($tab === 'security')
            <div class="row g-4">
                <div class="col-12">
                    <h6 class="fw-semibold">{{ __('menu.change_password') }}</h6>
                    <p class="text-muted small">{{ __('menu.change_password_hint') }}</p>
                    <form method="POST" action="{{ route('password.update') }}" class="row g-3">
                        @csrf
                        @method('put')
                        <div class="col-md-4">
                            <label class="form-label" for="current_password">{{ __('menu.current_password') }}</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="current-password">
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="password">{{ __('menu.new_password') }}</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                   autocomplete="new-password">
                            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="password_confirmation">{{ __('menu.password_confirm') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">{{ __('menu.change_password') }}</button>
                        </div>
                    </form>
                </div>

                <div class="col-12"><hr></div>

                <div class="col-12">
                    <h6 class="fw-semibold">{{ __('menu.change_email') }}</h6>
                    <p class="text-muted small">{{ __('menu.change_email_hint') }}</p>
                    <form method="POST" action="{{ route('profile.change-email') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label" for="email">{{ __('menu.new_email') }}</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email_password">{{ __('menu.current_password') }}</label>
                            <input type="password" id="email_password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">{{ __('menu.change_email') }}</button>
                        </div>
                    </form>
                </div>

                <div class="col-12"><hr></div>

                <div class="col-12">
                    <h6 class="fw-semibold">{{ __('menu.two_factor_auth') }}</h6>
                    <p class="text-muted small">{{ __('menu.two_factor_hint') }}</p>
                    <form method="POST" action="{{ route('profile.toggle-2fa') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary"
                                @disabled(!\App\Support\SecurityPolicy::bool('security_2fa_enabled_globally'))>
                            {{ $user->two_factor_enabled ? __('menu.disable_2fa') : __('menu.enable_2fa') }}
                            <span class="badge ms-1 {{ $user->two_factor_enabled ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $user->two_factor_enabled ? 'ON' : 'OFF' }}
                            </span>
                        </button>
                    </form>
                </div>

                <div class="col-12"><hr></div>

                <div class="col-12">
                    <h6 class="fw-semibold text-danger">{{ __('menu.delete_account') }}</h6>
                    @if($hasCafeLinks)
                        <p class="text-muted small">{{ __('menu.delete_account_cafe_warning') }}</p>
                    @else
                        <p class="text-muted small">{{ __('menu.delete_account_hint') }}</p>
                    @endif
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        {{ __('menu.delete_account') }}
                    </button>
                </div>
            </div>

            <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('menu.delete_account') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ __('menu.delete_account_confirm') }}</p>
                                <label class="form-label" for="delete_password">{{ __('menu.password') }}</label>
                                <input type="password" id="delete_password" name="password"
                                       class="form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                                @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('menu.cancel') }}</button>
                                <button type="submit" class="btn btn-danger">{{ __('menu.delete_account') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if($tab === 'cafe')
            @if($canCreateCafe)
                <h6 class="fw-semibold">{{ __('menu.create_cafe') }}</h6>
                <p class="text-muted small">{{ __('menu.create_cafe_hint') }}</p>
                <form method="POST" action="{{ route('profile.cafe.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label" for="cafe_name">{{ __('menu.cafe_name') }}</label>
                        <input type="text" id="cafe_name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="cafe_slug">Slug</label>
                        <input type="text" id="cafe_slug" name="slug"
                               class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug') }}" required pattern="[a-zA-Z0-9_-]+">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">{{ __('menu.cafe_slug_hint') }}</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('menu.create_cafe') }}</button>
                    </div>
                </form>
            @else
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">{{ __('menu.cafe_already_exists') }}</h6>
                    <p class="mb-2">{{ __('menu.cafe_already_exists_hint') }}</p>
                    @if($user->tenant_id)
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary">{{ __('menu.go_to_cafe_panel') }}</a>
                    @endif
                </div>
                @if($user->assignedTenants->isNotEmpty())
                    <ul class="list-group mt-3">
                        @foreach($user->assignedTenants as $assignedTenant)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $assignedTenant->name }} <code class="ms-1">{{ $assignedTenant->id }}</code></span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($tab === 'security' && $errors->userDeletion->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
});
</script>
@endif
@endpush
