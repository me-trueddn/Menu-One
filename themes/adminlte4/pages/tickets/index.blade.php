@extends('theme::layouts.app')

@section('title', __('menu.ticket'))
@section('page-title', __('menu.ticket'))

@section('page-actions')
<a href="{{ route('ticket.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('menu.ticket_open_new') }}</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('menu.ticket_subject') }}</th>
                    <th>{{ __('menu.ticket_category') }}</th>
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
                        <td>{{ $ticket->assignee?->name ?? __('menu.ticket_unassigned') }}</td>
                        <td><span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span></td>
                        <td>{{ $ticket->started_at?->format('d.m.Y H:i') }}</td>
                        <td class="text-end"><a href="{{ route('ticket.show', $ticket) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.view') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">{{ __('menu.ticket_none_yet') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())<div class="card-footer">{{ $tickets->links() }}</div>@endif
</div>
@endsection
