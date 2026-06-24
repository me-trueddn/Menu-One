@extends('theme::layouts.app')

@section('title', __('menu.tenants'))
@section('page-title', __('menu.tenants'))

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row g-2 align-items-center">
            <div class="col-md-4 d-flex align-items-center gap-2">
                <h5 class="mb-0">{{ __('menu.registered_cafes') }}</h5>
                <a href="{{ route('platform.tenants.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('menu.add') }}</a>
            </div>
            <div class="col-md-8">
                <form method="GET" action="{{ route('platform.tenants.index') }}" class="row g-2 justify-content-end">
                    <div class="col-auto flex-grow-1" style="min-width:12rem">
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('menu.tenant_search_placeholder') }}">
                    </div>
                    <div class="col-auto"><button class="btn btn-sm btn-outline-primary">{{ __('menu.search') }}</button></div>
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
                    <th>{{ __('menu.owner_email') }}</th>
                    <th>{{ __('menu.account_type') }}</th>
                    <th>{{ __('menu.created_at') }}</th>
                    <th>{{ __('menu.license_expires') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th class="text-end">{{ __('menu.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td><code class="text-nowrap user-select-all">{{ $tenant->id }}</code></td>
                        <td>
                            <img src="{{ \App\Support\Branding::cafeLogoUrl($tenant) }}" alt="" style="height:24px" class="me-1">
                            {{ $tenant->name }}
                        </td>
                        <td>{{ $tenant->owner?->email ?? '—' }}</td>
                        <td>@include('theme::partials.cafe-subscription-badge', ['cafe' => $tenant])</td>
                        <td>{{ $tenant->created_at?->format('d.m.Y') ?? '—' }}</td>
                        <td>{{ $tenant->currentLicense?->expires_at?->format('d.m.Y') ?? '—' }}</td>
                        <td>
                            @if($tenant->isStopped())
                                <span class="badge text-bg-danger">{{ __('menu.stopped') }}</span>
                            @elseif($tenant->is_active)
                                <span class="badge text-bg-success">{{ __('menu.active') }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ __('menu.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('platform.tenants.connect', $tenant) }}" method="POST" class="d-inline">@csrf
                                <button class="btn btn-sm btn-outline-info" title="{{ __('menu.connect_support') }}"><i class="bi bi-box-arrow-in-right"></i></button>
                            </form>
                            <a href="{{ route('platform.tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
                            <form action="{{ route('platform.tenants.destroy', $tenant) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())<div class="card-footer">{{ $tenants->links() }}</div>@endif
</div>
@endsection
