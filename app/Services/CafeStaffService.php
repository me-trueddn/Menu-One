<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CafeStaffService
{
    /** @return array<int, string> */
    public static function assignableRoles(): array
    {
        return ['cafe_admin', 'waiter', 'cashier', 'kitchen'];
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', Str::lower(trim($email)))
            ->first();
    }

    public function attach(Tenant $tenant, User $user, string $role): User
    {
        $this->assertCanAttach($tenant, $user);

        if ($user->tenant_id !== $tenant->id) {
            $user->update(['tenant_id' => $tenant->id]);
        }

        $user->syncRoles([$role]);

        return $user->fresh();
    }

    public function detach(Tenant $tenant, User $user): void
    {
        abort_unless($user->tenant_id === $tenant->id, 403);

        $user->syncRoles([]);
        $user->update(['tenant_id' => null]);
    }

    public function assertCanAttach(Tenant $tenant, User $user): void
    {
        if ($user->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'email' => __('menu.staff_cannot_attach_super_admin'),
            ]);
        }

        if ($user->canAccessPlatformPanel() && $user->tenant_id === null) {
            throw ValidationException::withMessages([
                'email' => __('menu.staff_cannot_attach_platform_user'),
            ]);
        }

        if ($user->tenant_id !== null && $user->tenant_id !== $tenant->id) {
            throw ValidationException::withMessages([
                'email' => __('menu.staff_already_assigned_other_cafe'),
            ]);
        }

        if ($user->tenant_id === $tenant->id && $user->hasAnyRole(self::assignableRoles())) {
            return;
        }
    }

    public function lookupMessage(?User $user): array
    {
        if (! $user) {
            return ['found' => false, 'message' => __('menu.staff_user_not_found')];
        }

        return [
            'found' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'current_role' => $user->getRoleNames()->first(),
            'tenant_id' => $user->tenant_id,
        ];
    }
}
