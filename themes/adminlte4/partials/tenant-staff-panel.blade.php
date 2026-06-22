<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">{{ __('menu.cafe_staff') }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('platform.tenants.staff.store', $tenant) }}" class="row g-2 align-items-end mb-3" id="platformStaffForm">
            @csrf
            <div class="col-md-5">
                <label class="form-label">{{ __('menu.email') }}</label>
                <div class="input-group">
                    <input type="email" name="email" id="platformStaffEmail" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    <button type="button" class="btn btn-outline-secondary" id="platformStaffLookup">{{ __('menu.search_user') }}</button>
                </div>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('menu.role') }}</label>
                <select name="role" class="form-select">
                    @foreach(\App\Services\CafeStaffService::assignableRoles() as $role)
                        <option value="{{ $role }}">{{ __('menu.role_'.$role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100" id="platformStaffSubmit" disabled>{{ __('menu.send_invitation') }}</button>
            </div>
        </form>
        <div id="platformStaffResult" class="alert d-none small"></div>

        @include('theme::partials.staff-invitations-panel', [
            'invitations' => $tenant->staffInvitations,
            'revokeRoute' => fn ($invitation) => route('platform.tenants.staff.invitations.revoke', [$tenant, $invitation]),
        ])

        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('menu.full_name') }}</th>
                        <th>{{ __('menu.email') }}</th>
                        <th>{{ __('menu.role') }}</th>
                        <th class="text-end">{{ __('menu.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenant->staffUsers as $member)
                        @if($member->hasAnyRole(\App\Services\CafeStaffService::assignableRoles()))
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>
                                    <form method="POST" action="{{ route('platform.tenants.staff.update', [$tenant, $member]) }}" class="d-flex gap-1">
                                        @csrf @method('PUT')
                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                            @foreach(\App\Services\CafeStaffService::assignableRoles() as $role)
                                                <option value="{{ $role }}" @selected($member->hasRole($role))>{{ __('menu.role_'.$role) }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('platform.tenants.staff.impersonate', [$tenant, $member]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-info" title="{{ __('menu.connect_as_user') }}"><i class="bi bi-box-arrow-in-right"></i></button>
                                    </form>
                                    <form action="{{ route('platform.tenants.staff.destroy', [$tenant, $member]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_remove_staff') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="4" class="text-muted text-center">{{ __('menu.no_staff') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailInput = document.getElementById('platformStaffEmail');
    const lookupBtn = document.getElementById('platformStaffLookup');
    const resultBox = document.getElementById('platformStaffResult');
    const submitBtn = document.getElementById('platformStaffSubmit');
    if (!emailInput || !lookupBtn) return;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    lookupBtn.addEventListener('click', async function () {
        resultBox.classList.add('d-none');
        submitBtn.disabled = true;
        const response = await fetch(@json(route('platform.tenants.staff.lookup', $tenant)), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ email: emailInput.value }),
        });
        const data = await response.json();
        resultBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        if (data.found) {
            resultBox.classList.add('alert-success');
            resultBox.textContent = data.name + ' (' + data.email + ')';
            submitBtn.disabled = false;
        } else {
            resultBox.classList.add('alert-danger');
            resultBox.textContent = data.message;
        }
    });
});
</script>
@endpush
