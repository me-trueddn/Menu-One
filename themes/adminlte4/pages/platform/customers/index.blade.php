@extends('theme::layouts.app')

@section('title', __('menu.customers'))
@section('page-title', __('menu.customers'))

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">{{ __('menu.registered_customers') }}</h5>
            </div>
            <div class="col-md-8">
                <form method="GET" action="{{ route('platform.customers.index') }}" class="row g-2 justify-content-end">
                    <div class="col-auto">
                        <select name="search_field" class="form-select form-select-sm">
                            <option value="email" @selected($searchField === 'email')>{{ __('menu.email') }}</option>
                            <option value="phone" @selected($searchField === 'phone')>{{ __('menu.phone') }}</option>
                            <option value="tenant" @selected($searchField === 'tenant')>{{ __('menu.tenant') }}</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="{{ __('menu.search') }}">
                    </div>
                    <div class="col-auto">
                        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach([20, 50, 100, 200] as $size)
                                <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-outline-primary">{{ __('menu.search') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>{{ __('menu.user_id') }}</th>
                    <th>{{ __('menu.full_name') }}</th>
                    <th>{{ __('menu.email') }}</th>
                    <th>{{ __('menu.phone') }}</th>
                    <th>{{ __('menu.tenant') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th>{{ __('menu.date') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td><code>{{ $customer->public_id }}</code></td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>
                            @if($customer->linkedTenants()->isEmpty())
                                <span class="text-muted">{{ __('menu.no_tenant') }}</span>
                            @else
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($customer->linkedTenants() as $linkedTenant)
                                        <li class="mb-1">
                                            <span>{{ $linkedTenant->name }}</span>
                                            <code class="ms-1">{{ $linkedTenant->id }}</code>
                                            @if($customer->ownsTenant($linkedTenant))
                                                <span class="badge text-bg-primary ms-1">{{ __('menu.tenant_owner') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $customer->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $customer->is_active ? __('menu.active') : __('menu.inactive') }}
                            </span>
                        </td>
                        <td>{{ $customer->created_at?->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm flex-wrap justify-content-end">
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#resetPwd{{ $customer->id }}">{{ __('menu.reset_password') }}</button>
                                <form action="{{ route('platform.customers.toggle-2fa', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-secondary" @disabled(!\App\Support\SecurityPolicy::bool('security_2fa_enabled_globally'))>
                                        2FA {{ $customer->two_factor_enabled ? 'ON' : 'OFF' }}
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changeEmail{{ $customer->id }}">{{ __('menu.change_email') }}</button>
                                <form action="{{ route('platform.customers.send-reset-link', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-info">{{ __('menu.send_reset_link') }}</button>
                                </form>
                                <form action="{{ route('platform.customers.toggle-active', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-warning">{{ $customer->is_active ? __('menu.deactivate') : __('menu.activate') }}</button>
                                </form>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#tenantModal{{ $customer->id }}">{{ __('menu.tenant') }}</button>
                                <form action="{{ route('platform.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger">{{ __('menu.delete') }}</button>
                                </form>
                            </div>

                            <div class="modal fade" id="resetPwd{{ $customer->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('platform.customers.reset-password', $customer) }}">
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

                            <div class="modal fade" id="changeEmail{{ $customer->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('platform.customers.change-email', $customer) }}">
                                            @csrf
                                            <div class="modal-header"><h5 class="modal-title">{{ __('menu.change_email') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <input type="email" name="email" class="form-control" value="{{ $customer->email }}" required>
                                            </div>
                                            <div class="modal-footer"><button class="btn btn-primary">{{ __('menu.save') }}</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="tenantModal{{ $customer->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('menu.assign_tenant') }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small">{{ __('menu.assign_tenant_hint') }}</p>

                                            @php($linkedTenants = $customer->linkedTenants())
                                            @if($linkedTenants->isNotEmpty())
                                                <ul class="list-group mb-3">
                                                    @foreach($linkedTenants as $linkedTenant)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $linkedTenant->name }}</strong>
                                                                <code class="ms-2">{{ $linkedTenant->id }}</code>
                                                                @if($customer->ownsTenant($linkedTenant))
                                                                    <span class="badge text-bg-primary ms-1">{{ __('menu.tenant_owner') }}</span>
                                                                @endif
                                                            </div>
                                                            @if($customer->isLinkedToTenant($linkedTenant))
                                                                <form action="{{ route('platform.customers.tenants.detach', [$customer, $linkedTenant]) }}" method="POST"
                                                                      onsubmit="return confirm('{{ __('menu.confirm_remove_tenant') }}')">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-sm btn-outline-danger">{{ __('menu.remove_tenant') }}</button>
                                                                </form>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-muted">{{ __('menu.no_tenant') }}</p>
                                            @endif

                                            <form method="POST" action="{{ route('platform.customers.tenants.attach', $customer) }}">
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
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
