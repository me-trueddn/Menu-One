<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

class AuditActionLabel
{
    public static function label(string $action): string
    {
        $key = 'log_action_'.str_replace(['.', '-'], '_', $action);

        if (Lang::has('menu.'.$key)) {
            return __('menu.'.$key);
        }

        return ucfirst(str_replace(['.', '_', '-'], ' ', $action));
    }
}
