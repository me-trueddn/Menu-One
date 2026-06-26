<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\TicketTag;
use App\Support\TicketTagColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketTagController extends Controller
{
    public function index(): View
    {
        return view('theme::pages.platform.tickets.tags.index', [
            'tags' => TicketTag::query()->orderBy('name')->paginate(30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('ticket_tags', 'name')],
        ]);

        TicketTag::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => TicketTagColor::next(),
        ]);

        return back()->with('success', __('menu.messages.saved'));
    }

    public function destroy(TicketTag $ticketTag): RedirectResponse
    {
        $ticketTag->delete();

        return back()->with('success', __('menu.messages.deleted'));
    }
}
