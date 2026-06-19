<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\TenantIdGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tenant::query()->with('owner');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('id', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($ownerQuery) => $ownerQuery->where('email', 'like', '%'.$search.'%'));
            });
        }

        $tenants = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('theme::pages.platform.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('theme::pages.platform.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tenants,slug', 'alpha_dash'],
        ]);

        Tenant::create([
            'id' => TenantIdGenerator::generate(),
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.saved'));
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load('owner');

        return view('theme::pages.platform.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug,'.$tenant->id],
            'is_active' => ['boolean'],
        ]);

        $tenant->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.updated'));
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->members()->detach();
        $tenant->delete();

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('menu.messages.deleted'));
    }
}
