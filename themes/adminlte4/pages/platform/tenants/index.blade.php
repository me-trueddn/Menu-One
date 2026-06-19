@extends('theme::layouts.app')

@section('title', __('menu.tenants'))
@section('page-title', __('menu.tenants'))

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-center">
            <div class="col-md-4 d-flex align-items-center gap-2">
                <h5 class="mb-0">{{ __('menu.registered_cafes') }}</h5>
                <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i> {{ __('menu.add') }}
                </a>
            </div>
            <div class="col-md-8">
                <form method="GET" action="{{ route('platform.tenants.index') }}" class="row g-2 justify-content-end">
                    <div class="col-auto flex-grow-1" style="min-width: 12rem;">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="{{ __('menu.tenant_search_placeholder') }}">
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
                    <th>{{ __('menu.tenant_id') }}</th>
                    <th>{{ __('menu.site_name') }}</th>
                    <th>Slug</th>
                    <th>{{ __('menu.owner_email') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <code class="small tenant-id-text">{{ $tenant->id }}</code>
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-tenant-id" data-id="{{ $tenant->id }}" title="{{ __('menu.copy') }}">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </td>
                        <td>{{ $tenant->name }}</td>
                        <td><code>{{ $tenant->slug }}</code></td>
                        <td>{{ $tenant->owner?->email ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $tenant->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $tenant->is_active ? __('menu.active') : __('menu.inactive') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('platform.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            <form action="{{ route('platform.tenants.destroy', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
        <div class="card-footer">{{ $tenants->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.copy-tenant-id').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const id = btn.dataset.id;
        navigator.clipboard.writeText(id).then(function () {
            btn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(function () { btn.classList.replace('btn-success', 'btn-outline-secondary'); }, 1200);
        });
    });
});
</script>
@endpush
