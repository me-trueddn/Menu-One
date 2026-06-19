@if(!empty($canSwitchTenants) && $canSwitchTenants)
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-shop"></i>
            {{ $activeTenant?->name ?? __('menu.select_cafe') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @foreach($selectableTenants as $tenant)
                <li>
                    <form method="POST" action="{{ route('tenant.select.store') }}">
                        @csrf
                        <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                        <input type="hidden" name="redirect" value="{{ url()->current() }}">
                        <button type="submit"
                                class="dropdown-item d-flex justify-content-between align-items-center {{ ($activeTenantId ?? null) === $tenant->id ? 'active' : '' }}">
                            <span>{{ $tenant->name }}</span>
                            <code class="small ms-2">{{ $tenant->id }}</code>
                        </button>
                    </form>
                </li>
            @endforeach
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('tenant.select') }}">{{ __('menu.manage_cafes') }}</a></li>
        </ul>
    </div>
@endif
