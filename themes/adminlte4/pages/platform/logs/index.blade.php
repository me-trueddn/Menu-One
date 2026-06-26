@extends('theme::layouts.app')

@section('title', __('menu.log_management'))
@section('page-title', __('menu.log_management'))

@section('content')
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'platform' ? 'active' : '' }}"
           href="{{ route('platform.logs.index', array_merge(request()->except('page'), ['tab' => 'platform'])) }}">
            <i class="bi bi-shield-check me-1"></i>{{ __('menu.log_platform_tab') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'cafe' ? 'active' : '' }}"
           href="{{ route('platform.logs.index', array_merge(request()->except('page'), ['tab' => 'cafe'])) }}">
            <i class="bi bi-shop me-1"></i>{{ __('menu.log_cafe_tab') }}
        </a>
    </li>
</ul>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-md-2">
                <label class="form-label small">{{ __('menu.date_from') }}</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('menu.date_to') }}</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('menu.user') }}</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">{{ __('menu.all_users') }}</option>
                    @foreach($filterUsers as $staff)
                        <option value="{{ $staff->id }}" @selected((string) $filters['user_id'] === (string) $staff->id)>
                            {{ $staff->name }} ({{ $staff->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            @if($tab === 'cafe')
            <div class="col-md-3">
                <label class="form-label small">{{ __('menu.cafe') }}</label>
                <select name="tenant_id" class="form-select form-select-sm">
                    <option value="">{{ __('menu.all_cafes') }}</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" @selected($filters['tenant_id'] === $tenant->id)>
                            {{ $tenant->name }} ({{ $tenant->id }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label small">{{ __('menu.per_page') }}</label>
                <select name="per_page" class="form-select form-select-sm">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary">{{ __('menu.filter') }}</button>
                <a href="{{ route('platform.logs.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">{{ __('menu.reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">{{ __('menu.log_retention_settings') }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('platform.logs.settings.update') }}" class="row g-2 align-items-end">
            @csrf @method('PUT')
            <div class="col-md-3">
                <label class="form-label small">{{ __('menu.log_platform_retention_days') }}</label>
                <input type="number" name="log_platform_retention_days" class="form-control form-control-sm"
                       value="{{ old('log_platform_retention_days', $platformRetentionDays) }}" min="1" max="365" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('menu.log_cafe_retention_days') }}</label>
                <input type="number" name="log_cafe_retention_days" class="form-control form-control-sm"
                       value="{{ old('log_cafe_retention_days', $cafeRetentionDays) }}" min="1" max="365" required>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">{{ __('menu.log_retention_hint') }}</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">{{ __('menu.save') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm mb-0 align-middle">
            <thead>
                <tr>
                    <th>{{ __('menu.date') }}</th>
                    <th>{{ __('menu.user') }}</th>
                    @if($tab === 'cafe')<th>{{ __('menu.cafe') }}</th>@endif
                    <th>{{ __('menu.action') }}</th>
                    <th>{{ __('menu.ip_address') }}</th>
                    <th>{{ __('menu.description') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap small">{{ $log->created_at?->format('d.m.Y H:i:s') }}</td>
                        <td class="small">
                            @if($log->user)
                                {{ $log->user->name }}<br><span class="text-muted">{{ $log->user->email }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if($tab === 'cafe')
                            <td class="small">{{ $log->tenant?->name ?? $log->tenant_id }}</td>
                        @endif
                        <td class="small">{{ \App\Support\AuditActionLabel::label($log->action) }}</td>
                        <td class="text-nowrap small text-muted">{{ $log->ip_address ?? '—' }}</td>
                        <td class="small">{{ $log->summary }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $tab === 'cafe' ? 6 : 5 }}" class="text-center text-muted py-4">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
</div>
@endsection
