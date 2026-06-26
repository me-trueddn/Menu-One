<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function index(): View
    {
        return view('theme::pages.platform.tickets.categories.index', [
            'categories' => TicketCategory::query()->orderBy('sort_order')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('theme::pages.platform.tickets.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('ticket_categories', 'slug')],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        TicketCategory::create([
            ...$validated,
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('platform.ticket-categories.index')->with('success', __('menu.messages.saved'));
    }

    public function edit(TicketCategory $ticketCategory): View
    {
        return view('theme::pages.platform.tickets.categories.edit', ['category' => $ticketCategory]);
    }

    public function update(Request $request, TicketCategory $ticketCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('ticket_categories', 'slug')->ignore($ticketCategory->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $ticketCategory->update([
            ...$validated,
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('platform.ticket-categories.index')->with('success', __('menu.messages.updated'));
    }

    public function destroy(TicketCategory $ticketCategory): RedirectResponse
    {
        abort_if($ticketCategory->tickets()->exists(), 422, __('menu.ticket_category_in_use'));

        $ticketCategory->delete();

        return back()->with('success', __('menu.messages.deleted'));
    }
}
