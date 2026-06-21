<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CafeStaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private CafeStaffService $staff) {}

    public function index(): View
    {
        $staff = User::query()
            ->where('tenant_id', $this->tenantId())
            ->whereHas('roles', fn ($q) => $q->whereIn('name', CafeStaffService::assignableRoles()))
            ->orderBy('name')
            ->paginate(20);

        return view('theme::pages.admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('theme::pages.admin.staff.create', [
            'roles' => CafeStaffService::assignableRoles(),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $this->staff->findByEmail($validated['email']);

        return response()->json($this->staff->lookupMessage($user));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:'.implode(',', CafeStaffService::assignableRoles())],
        ]);

        $user = $this->staff->findByEmail($validated['email']);

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => __('menu.staff_user_not_found'),
            ]);
        }

        $tenant = tenant();
        abort_unless($tenant, 403);

        $this->staff->attach($tenant, $user, $validated['role']);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', __('menu.staff_attached'));
    }

    public function edit(User $staff): View
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);

        return view('theme::pages.admin.staff.edit', [
            'staff' => $staff,
            'roles' => CafeStaffService::assignableRoles(),
        ]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:'.implode(',', CafeStaffService::assignableRoles())],
        ]);

        $staff->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', __('menu.messages.updated'));
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);
        abort_if($staff->id === $this->authUser()->id, 403, __('menu.staff_cannot_remove_self'));

        $tenant = tenant();
        abort_unless($tenant, 403);

        $this->staff->detach($tenant, $staff);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', __('menu.staff_removed'));
    }
}
