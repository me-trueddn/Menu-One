<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\PlatformModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserGroupController extends Controller
{
    public function index(): View
    {
        $groups = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->paginate(10);

        return view('theme::pages.platform.user-groups.index', compact('groups'));
    }

    public function create(): View
    {
        abort_unless(PlatformModules::userCan($this->authUser(), 'users', 'edit'), 403);

        $modules = PlatformModules::all();
        $permissions = PlatformModules::emptyPermissionMatrix();

        return view('theme::pages.platform.user-groups.create', compact('modules', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(PlatformModules::userCan($this->authUser(), 'users', 'edit'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $group = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        $group->syncPermissions(
            PlatformModules::permissionsFromRequest($validated['permissions'] ?? [])
        );

        return redirect()
            ->route('platform.user-groups.index')
            ->with('success', __('menu.group_created'));
    }

    public function edit(Role $group): View
    {
        abort_unless(PlatformModules::userCan($this->authUser(), 'users', 'view'), 403);
        abort_if($group->is_system, 403);

        $group->load('permissions');
        $modules = PlatformModules::all();
        $permissions = PlatformModules::permissionsForRole($group);

        return view('theme::pages.platform.user-groups.edit', compact('group', 'modules', 'permissions'));
    }

    public function update(Request $request, Role $group): RedirectResponse
    {
        abort_unless(PlatformModules::userCan($this->authUser(), 'users', 'edit'), 403);
        abort_if($group->is_system, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('roles', 'name')->ignore($group->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $group->syncPermissions(
            PlatformModules::permissionsFromRequest($validated['permissions'] ?? [])
        );

        return redirect()
            ->route('platform.user-groups.index')
            ->with('success', __('menu.messages.updated'));
    }

    public function destroy(Role $group): RedirectResponse
    {
        abort_unless(PlatformModules::userCan($this->authUser(), 'users', 'edit'), 403);
        abort_if($group->is_system, 403);

        if ($group->users()->exists()) {
            return back()->with('error', __('menu.group_has_users'));
        }

        $group->delete();

        return back()->with('success', __('menu.messages.deleted'));
    }
}
