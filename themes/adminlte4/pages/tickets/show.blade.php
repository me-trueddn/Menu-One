@extends('theme::layouts.app')

@section('title', $ticket->number)
@section('page-title', $ticket->subject)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <code>{{ $ticket->number }}</code>
                    <span class="badge {{ $ticket->status->badgeClass() }} ms-2">{{ $ticket->status->label() }}</span>
                </div>
                <small class="text-muted">{{ $ticket->started_at?->format('d.m.Y H:i') }}</small>
            </div>
            <div class="card-body">
                @foreach($ticket->messages as $message)
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>
                                <strong>{{ $message->user?->name }}</strong>
                                @if($message->is_staff)<span class="badge text-bg-primary ms-1">{{ __('menu.support_team') }}</span>@endif
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
                <form method="POST" action="{{ route('ticket.reply', $ticket) }}" enctype="multipart/form-data">
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
        @else
            <div class="alert alert-secondary">{{ __('menu.ticket_closed_notice') }}</div>
        @endunless
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">{{ __('menu.ticket_details') }}</div>
            <div class="card-body small">
                <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_category') }}:</span><br><strong>{{ $ticket->category?->name }}</strong></p>
                <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_assignee') }}:</span><br>
                    @if($ticket->assignee)
                        <strong>{{ $ticket->assignee->name }}</strong><br>{{ $ticket->assignee->email }}
                    @else
                        <span class="text-muted">{{ __('menu.ticket_unassigned') }}</span>
                    @endif
                </p>
                <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_started_at') }}:</span><br>{{ $ticket->started_at?->format('d.m.Y H:i') }}</p>
                <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_closed_at') }}:</span><br>{{ $ticket->closed_at?->format('d.m.Y H:i') ?? '—' }}</p>
                @if($ticket->tags->isNotEmpty())
                    <p class="text-muted mb-1">{{ __('menu.ticket_tags') }}</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($ticket->tags as $tag)
                            <span class="badge rounded-pill" style="background-color: {{ $tag->color }}; color: #111;">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
