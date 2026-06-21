<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CafeStaffService;
use App\Services\UserImpersonationService;
use App\Support\TenantAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantStaffController extends Controller
{
    public function __construct(
        private CafeStaffService $staff,
        private UserImpersonationService $impersonation,
    ) {}

    public function lookup(Request $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $user = $this->staff->findByEmail($validated['email']);

        return response()->json($this->staff->lookupMessage($user));
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:'.implode(',', CafeStaffService::assignableRoles())],
        ]);

        $user = $this->staff->findByEmail($validated['email']);

        if (! $user) {
            return back()->withInput()->withErrors(['email' => __('menu.staff_user_not_found')]);
        }

        $this->staff->attach($tenant, $user, $validated['role']);

        return back()->with('success', __('menu.staff_attached'));
    }

    public function update(Request $request, Tenant $tenant, User $user): RedirectResponse
    {
        abort_unless($user->tenant_id === $tenant->id, 403);

        $validated = $request->validate([
            'role' => ['required', 'in:'.implode(',', CafeStaffService::assignableRoles())],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', __('menu.messages.updated'));
    }

    public function destroy(Tenant $tenant, User $user): RedirectResponse
    {
        abort_unless($user->tenant_id === $tenant->id, 403);
        $this->staff->detach($tenant, $user);

        return back()->with('success', __('menu.staff_removed'));
    }

    public function impersonate(Tenant $tenant, User $user): RedirectResponse
    {
        abort_unless($this->authUser()->isSuperAdmin() || $this->authUser()->canAccessPlatformPanel(), 403);
        abort_unless($user->tenant_id === $tenant->id, 403);
        abort_unless($user->hasAnyRole(CafeStaffService::assignableRoles()), 403);

        $admin = $this->authUser();
        TenantAccess::setActiveTenant($admin, $tenant->id, support: true);
        app(\App\Services\SupportSessionService::class)->connect($tenant, $admin);
        $this->impersonation->start($admin, $user);

        return redirect()
            ->route('dashboard')
            ->with('success', __('menu.impersonation_started', ['name' => $user->name]));
    }
}
