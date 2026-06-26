@extends('theme::layouts.app')

@section('title', __('menu.ticket_management'))
@section('page-title', __('menu.ticket_management'))

@section('content')
@include('theme::partials.platform.ticket-nav')

@if($filterTags->isNotEmpty())
<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="text-muted small">{{ __('menu.ticket_tags') }}:</span>
    <a href="{{ route('platform.tickets.index', request()->except('tag', 'page')) }}"
       class="badge rounded-pill text-decoration-none {{ empty($activeTagSlug) ? 'text-bg-dark' : 'text-bg-light text-dark border' }}">
        {{ __('menu.all') }}
    </a>
    @foreach($filterTags as $tag)
        <a href="{{ route('platform.tickets.index', array_merge(request()->except('page'), ['tag' => $tag->slug])) }}"
           class="badge rounded-pill text-decoration-none {{ $activeTagSlug === $tag->slug ? '' : 'opacity-75' }}"
           style="background-color: {{ $tag->color }}; color: #111;">
            {{ $tag->name }}
        </a>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('menu.search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('menu.all_statuses') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary">{{ __('menu.search') }}</button>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('menu.ticket_subject') }}</th>
                    <th>{{ __('menu.ticket_category') }}</th>
                    <th>{{ __('menu.ticket_cafe') }}</th>
                    <th>{{ __('menu.customer') }}</th>
                    <th>{{ __('menu.ticket_assignee') }}</th>
                    <th>{{ __('menu.status') }}</th>
                    <th>{{ __('menu.ticket_started_at') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td><code>{{ $ticket->number }}</code></td>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ $ticket->category?->name }}</td>
                        <td>{{ $ticket->tenant?->name ?? '—' }}@if($ticket->tenant)<br><small class="text-muted"><code>{{ $ticket->tenant->id }}</code></small>@endif</td>
                        <td>{{ $ticket->user?->name }}<br><small class="text-muted">{{ $ticket->user?->email }}</small></td>
                        <td>{{ $ticket->assignee?->name ?? '—' }}</td>
                        <td><span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                        <td>{{ $ticket->started_at?->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('platform.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.view') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">{{ __('menu.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())<div class="card-footer">{{ $tickets->links() }}</div>@endif
</div>
@endsection
