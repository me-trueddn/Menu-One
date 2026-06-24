<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class AvailableCustomerEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = Str::lower(trim((string) $value));

        if ($email === '') {
            return;
        }

        $user = User::query()
            ->whereNull('tenant_id')
            ->where('email', $email)
            ->first();

        if (! $user) {
            return;
        }

        if ($user->isPlatformStaffMember()) {
            $fail(__('menu.platform_staff_registration_blocked'));

            return;
        }

        $fail(__('validation.unique', ['attribute' => 'email']));
    }
}
