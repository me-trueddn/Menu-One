<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->paginate(20);

        return view('theme::pages.admin.staff.index', compact('staff'));
    }

    public function create(): View
    {
        return view('theme::pages.admin.staff.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:cafe_admin,waiter,kitchen'],
        ]);

        $user = User::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.staff.index')->with('success', 'Personel eklendi.');
    }

    public function edit(User $staff): View
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);

        return view('theme::pages.admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:cafe_admin,waiter,kitchen'],
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            ...($validated['password'] ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        $staff->syncRoles([$validated['role']]);

        return redirect()->route('admin.staff.index')->with('success', 'Personel güncellendi.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->tenant_id === $this->tenantId(), 403);
        abort_if($staff->id === $this->authUser()->id, 403, 'Kendi hesabınızı silemezsiniz.');

        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Personel silindi.');
    }
}
