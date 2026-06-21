<?php

namespace App\Services;

use App\Models\User;
use App\Support\TenantAccess;
use Illuminate\Support\Facades\Auth;

class UserImpersonationService
{
    public function isImpersonating(): bool
    {
        return session()->has('impersonator_id');
    }

    public function impersonator(): ?User
    {
        $id = session('impersonator_id');

        return $id ? User::find($id) : null;
    }

    public function start(User $admin, User $target): void
    {
        abort_unless($admin->isSuperAdmin() || $admin->canAccessPlatformPanel(), 403);
        abort_if($target->isSuperAdmin(), 403);
        abort_unless($target->tenant_id, 403);

        session([
            'impersonator_id' => $admin->id,
            'impersonator_name' => $admin->name,
            'impersonator_email' => $admin->email,
            'impersonator_active_tenant_id' => session('active_tenant_id'),
            'impersonator_support_tenant_mode' => session('support_tenant_mode'),
        ]);

        Auth::login($target);
    }

    public function stop(): User
    {
        $admin = $this->impersonator();
        abort_unless($admin, 403);

        $activeTenantId = session('impersonator_active_tenant_id');
        $supportMode = session('impersonator_support_tenant_mode');

        session()->forget([
            'impersonator_id',
            'impersonator_name',
            'impersonator_email',
            'impersonator_active_tenant_id',
            'impersonator_support_tenant_mode',
        ]);

        Auth::login($admin);

        if ($supportMode && $activeTenantId) {
            TenantAccess::setActiveTenant($admin, (string) $activeTenantId, support: true);
        }

        return $admin;
    }
}
