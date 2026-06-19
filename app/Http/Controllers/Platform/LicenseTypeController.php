<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\LicenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseTypeController extends Controller
{
    public function index(): View
    {
        $licenses = LicenseType::query()->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('theme::pages.platform.licenses.index', compact('licenses'));
    }

    public function create(): View
    {
        return view('theme::pages.platform.licenses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->boolean('is_default')) {
            LicenseType::query()->update(['is_default' => false]);
        }

        LicenseType::create($validated);

        return redirect()->route('platform.licenses.index')->with('success', __('menu.messages.saved'));
    }

    public function edit(LicenseType $license): View
    {
        return view('theme::pages.platform.licenses.edit', compact('license'));
    }

    public function update(Request $request, LicenseType $license): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->boolean('is_default')) {
            LicenseType::query()->whereKeyNot($license->id)->update(['is_default' => false]);
        }

        $license->update($validated);

        return redirect()->route('platform.licenses.index')->with('success', __('menu.messages.updated'));
    }

    public function destroy(LicenseType $license): RedirectResponse
    {
        abort_if($license->is_default, 403, __('menu.cannot_delete_default_license'));

        $license->delete();

        return redirect()->route('platform.licenses.index')->with('success', __('menu.messages.deleted'));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ]) + [
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
