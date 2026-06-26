@if($linkedTenantsForTickets->isEmpty())
    <div class="alert alert-warning">
        {{ __('menu.ticket_requires_cafe') }}
        <a href="{{ route('profile.edit', ['tab' => 'cafe']) }}" class="alert-link">{{ __('menu.create_cafe_tab') }}</a>
    </div>
@elseif($ticketView === 'create')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('menu.ticket_open_new') }}</h5>
        <a href="{{ route('profile.edit', ['tab' => 'ticket']) }}" class="btn btn-outline-secondary btn-sm">{{ __('menu.back') }}</a>
    </div>
    <form method="POST" action="{{ route('profile.tickets.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">{{ __('menu.ticket_cafe') }} <span class="text-danger">*</span></label>
            <select name="tenant_id" class="form-select" required>
                <option value="">{{ __('menu.select_cafe') }}</option>
                @foreach($linkedTenantsForTickets as $cafe)
                    <option value="{{ $cafe->id }}" @selected(old('tenant_id') === $cafe->id)>
                        {{ $cafe->name }} ({{ $cafe->id }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('menu.ticket_category') }}</label>
            <select name="category_id" class="form-select" required>
                @foreach($ticketCategories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('menu.ticket_subject') }}</label>
            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required maxlength="255">
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('menu.ticket_message') }}</label>
            @include('theme::partials.ticket-rich-editor', ['name' => 'body', 'value' => old('body', '')])
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('menu.ticket_attachments') }}</label>
            <input type="file" name="attachments[]" class="form-control" multiple>
            <div class="form-text">{{ __('menu.ticket_attachments_hint') }}</div>
        </div>
        <button class="btn btn-primary">{{ __('menu.ticket_submit') }}</button>
        <a href="{{ route('profile.edit', ['tab' => 'ticket']) }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
    </form>
@elseif($ticketView === 'show' && $activeTicket)
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">{{ $activeTicket->subject }}</h5>
            <code>{{ $activeTicket->number }}</code>
            <span class="badge {{ $activeTicket->status->badgeClass() }} ms-1">{{ $activeTicket->status->label() }}</span>
        </div>
        <a href="{{ route('profile.edit', ['tab' => 'ticket']) }}" class="btn btn-outline-secondary btn-sm">{{ __('menu.back') }}</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    @foreach($activeTicket->messages as $message)
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

            @unless($activeTicket->isClosed())
            <div class="card">
                <div class="card-header">{{ __('menu.ticket_reply') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.tickets.reply', $activeTicket) }}" enctype="multipart/form-data">
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
                    <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_cafe') }}:</span><br><strong>{{ $activeTicket->tenant?->name ?? '—' }}</strong>@if($activeTicket->tenant)<br><code>{{ $activeTicket->tenant->id }}</code>@endif</p>
                    <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_category') }}:</span><br><strong>{{ $activeTicket->category?->name }}</strong></p>
                    <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_assignee') }}:</span><br>
                        @if($activeTicket->assignee)
                            <strong>{{ $activeTicket->assignee->name }}</strong><br>{{ $activeTicket->assignee->email }}
                        @else
                            <span class="text-muted">{{ __('menu.ticket_unassigned') }}</span>
                        @endif
                    </p>
                    <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_started_at') }}:</span><br>{{ $activeTicket->started_at?->format('d.m.Y H:i') }}</p>
                    <p class="mb-2"><span class="text-muted">{{ __('menu.ticket_closed_at') }}:</span><br>{{ $activeTicket->closed_at?->format('d.m.Y H:i') ?? '—' }}</p>
                    @if($activeTicket->tags->isNotEmpty())
                        <p class="text-muted mb-1">{{ __('menu.ticket_tags') }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($activeTicket->tags as $tag)
                                <span class="badge rounded-pill" style="background-color: {{ $tag->color }}; color: #111;">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">{{ __('menu.ticket_profile_hint') }}</p>
        <a href="{{ route('profile.edit', ['tab' => 'ticket', 'ticket_action' => 'create']) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> {{ __('menu.ticket_open_new') }}
        </a>
    </div>

    @if($myTickets->isEmpty())
        <p class="text-muted">{{ __('menu.ticket_none_yet') }}</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('menu.ticket_subject') }}</th>
                        <th>{{ __('menu.ticket_cafe') }}</th>
                        <th>{{ __('menu.ticket_category') }}</th>
                        <th>{{ __('menu.ticket_assignee') }}</th>
                        <th>{{ __('menu.status') }}</th>
                        <th>{{ __('menu.ticket_started_at') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($myTickets as $myTicket)
                        <tr>
                            <td><code>{{ $myTicket->number }}</code></td>
                            <td>{{ $myTicket->subject }}</td>
                            <td>{{ $myTicket->tenant?->name ?? '—' }}</td>
                            <td>{{ $myTicket->category?->name }}</td>
                            <td>{{ $myTicket->assignee?->name ?? __('menu.ticket_unassigned') }}</td>
                            <td><span class="badge {{ $myTicket->status->badgeClass() }}">{{ $myTicket->status->label() }}</span></td>
                            <td>{{ $myTicket->started_at?->format('d.m.Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('profile.edit', ['tab' => 'ticket', 'ticket_id' => $myTicket->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($myTickets->hasPages())
            <div class="mt-3">{{ $myTickets->links() }}</div>
        @endif
    @endif
@endif
