<?php

namespace App\Http\Controllers\Platform;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketTag;
use App\Models\User;
use App\Services\TicketService;
use App\Support\TicketSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private TicketService $tickets) {}

    public function index(Request $request): View
    {
        $query = Ticket::query()
            ->with(['user', 'category', 'assignee', 'tags', 'tenant.currentLicense.licenseType'])
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($tagSlug = trim((string) $request->query('tag'))) {
            $query->whereHas('tags', fn ($tagQuery) => $tagQuery->where('slug', $tagSlug));
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('number', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('email', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%'));
            });
        }

        return view('theme::pages.platform.tickets.index', [
            'tickets' => $query->paginate(20)->withQueryString(),
            'statuses' => TicketStatus::cases(),
            'filterTags' => TicketTag::query()->orderBy('name')->get(),
            'activeTagSlug' => $request->query('tag'),
        ]);
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'user',
            'tenant.currentLicense.licenseType',
            'category',
            'assignee',
            'tags',
            'messages.user',
            'messages.attachments',
        ]);

        return view('theme::pages.platform.tickets.show', [
            'ticket' => $ticket,
            'statuses' => TicketStatus::cases(),
            'tags' => TicketTag::query()->orderBy('name')->get(),
            'staffUsers' => User::query()->platformStaff()->orderBy('name')->get(),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:50000'],
            'body_format' => ['nullable', Rule::in(['html', 'bbcode'])],
            'attachments.*' => ['nullable', 'file', 'max:'.TicketSettings::maxSizeKb(), TicketSettings::extensionRule()],
        ]);

        $this->tickets->addStaffReply(
            $ticket,
            $this->authUser(),
            $validated['body'],
            $validated['body_format'] ?? 'html',
            $request->file('attachments', []),
        );

        return back()->with('success', __('menu.ticket_reply_sent'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(TicketStatus::adminValues())],
            'assigned_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:ticket_tags,id'],
        ]);

        $this->tickets->updateStatus($ticket, TicketStatus::from($validated['status']));

        $assignee = isset($validated['assigned_to_user_id'])
            ? User::query()->find($validated['assigned_to_user_id'])
            : null;
        $this->tickets->assign($ticket, $assignee);
        $this->tickets->syncTags($ticket, $validated['tag_ids'] ?? []);

        return back()->with('success', __('menu.messages.updated'));
    }
}
