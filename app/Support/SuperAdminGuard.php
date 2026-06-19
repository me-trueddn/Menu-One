<?php

namespace App\Support;

use App\Models\User;

class SuperAdminGuard
{
    public static function isProtected(User $user): bool
    {
        return (bool) $user->is_super_admin;
    }

    public static function abortIfProtected(User $user): void
    {
        abort_if(static::isProtected($user), 403, __('menu.super_admin_protected'));
    }
}
