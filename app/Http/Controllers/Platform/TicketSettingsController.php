<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\TicketSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketSettingsController extends Controller
{
    public function edit(): View
    {
        return view('theme::pages.platform.tickets.settings', [
            'extensions' => implode(', ', TicketSettings::allowedExtensions()),
            'maxSizeMb' => (int) Setting::getFilled(TicketSettings::MAX_SIZE_MB_KEY, 10),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_file_extensions' => ['required', 'string', 'max:500'],
            'ticket_max_file_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        Setting::set(TicketSettings::EXTENSIONS_KEY, strtolower(str_replace(' ', '', $validated['ticket_file_extensions'])), 'tickets');
        Setting::set(TicketSettings::MAX_SIZE_MB_KEY, (string) $validated['ticket_max_file_size_mb'], 'tickets');

        return back()->with('success', __('menu.messages.updated'));
    }
}
