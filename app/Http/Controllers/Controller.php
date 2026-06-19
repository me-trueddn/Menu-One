<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function authUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    protected function tenantId(): string
    {
        if (tenancy()->initialized) {
            return (string) tenant()->getTenantKey();
        }

        $tenantId = $this->authUser()->tenant_id;

        if ($tenantId === null) {
            abort(403);
        }

        return $tenantId;
    }
}
