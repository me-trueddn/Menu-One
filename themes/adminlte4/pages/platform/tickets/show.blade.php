@extends('theme::layouts.app')

@section('title', $ticket->number)
@section('page-title', $ticket->subject)

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <code>{{ $ticket->number }}</code>
                    <span class="badge {{ $ticket->status->badgeClass() }} ms-2">{{ $ticket->status->label() }}</span>
                </div>
                <small class="text-muted">{{ __('menu.ticket_started_at') }}: {{ $ticket->started_at?->format('d.m.Y H:i') }}</small>
            </div>
            <div class="card-body">
                @foreach($ticket->messages as $message)
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>
                                <strong>{{ $message->user?->name }}</strong>
                                @if($message->is_staff)<span class="badge text-bg-dark ms-1">{{ __('menu.staff') }}</span>@endif
                            </span>
                            <span>{{ $message->created_at?->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="ticket-message-body">{!! $message->body !!}</div>
                        @if($message->attachments->isNotEmpty())
                            <ul class="list-unstyled small mt-2 mb-0">
                                @foreach($message->attachments as $file)
                                    <li><a href="{{ $file->url() }}" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> {{ $file->original_name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @unless($ticket->isClosed())
        <div class="card">
            <div class="card-header">{{ __('menu.ticket_reply') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.tickets.reply', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    @include('theme::partials.ticket-rich-editor', ['name' => 'body'])
                    <div class="mb-3 mt-3">
                        <label class="form-label">{{ __('menu.ticket_attachments') }}</label>
                        <input type="file" name="attachments[]" class="form-control" multiple>
                    </div>
                    <button class="btn btn-primary">{{ __('menu.ticket_send_reply') }}</button>
                </form>
            </div>
        </div>
        @endunless
    </div>

    <div class="col-lg-4">
        @include('theme::partials.platform.ticket-customer-info', ['ticket' => $ticket])

        <div class="card">
            <div class="card-header">{{ __('menu.ticket_details') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.tickets.update', $ticket) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.status') }}</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected($ticket->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.ticket_assignee') }}</label>
                        <select name="assigned_to_user_id" class="form-select">
                            <option value="">{{ __('menu.unassigned') }}</option>
                            @foreach($staffUsers as $staff)
                                <option value="{{ $staff->id }}" @selected($ticket->assigned_to_user_id === $staff->id)>{{ $staff->name }} ({{ $staff->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.ticket_tags') }}</label>
                        <select name="tag_ids[]" class="form-select" multiple size="5">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @selected($ticket->tags->contains('id', $tag->id))>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="small text-muted mb-1">{{ __('menu.ticket_category') }}: <strong>{{ $ticket->category?->name }}</strong></p>
                    <p class="small text-muted mb-3">{{ __('menu.ticket_closed_at') }}: {{ $ticket->closed_at?->format('d.m.Y H:i') ?? '—' }}</p>
                    @if($ticket->tags->isNotEmpty())
                        <div class="mb-3 d-flex flex-wrap gap-1">
                            @foreach($ticket->tags as $tag)
                                <a href="{{ route('platform.tickets.index', ['tag' => $tag->slug]) }}"
                                   class="badge rounded-pill text-decoration-none"
                                   style="background-color: {{ $tag->color }}; color: #111;">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                    <button class="btn btn-primary w-100">{{ __('menu.update') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
