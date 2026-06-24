<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PlatformModules
{
    public static function all(): array
    {
        return config('platform_modules.modules', []);
    }

    public static function keys(): array
    {
        return array_keys(static::all());
    }

    public static function permissionName(string $module, string $action): string
    {
        return "platform.{$module}.{$action}";
    }

    public static function viewPermission(string $module): string
    {
        return static::permissionName($module, 'view');
    }

    public static function editPermission(string $module): string
    {
        return static::permissionName($module, 'edit');
    }

    public static function allPermissionNames(): array
    {
        $names = [];

        foreach (static::keys() as $module) {
            $names[] = static::viewPermission($module);
            $names[] = static::editPermission($module);
        }

        return $names;
    }

    public static function syncPermissions(): void
    {
        foreach (static::allPermissionNames() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }

    public static function moduleForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach (static::all() as $key => $module) {
            foreach ($module['route_patterns'] ?? [] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function userCan(User $user, string $module, string $action = 'view'): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($user->tenant_id !== null) {
            return false;
        }

        return $user->can(static::permissionName($module, $action));
    }

    public static function userCanAnyModule(User $user): bool
    {
        if ($user->is_super_admin) {
            return true;
        }

        if ($user->tenant_id !== null) {
            return false;
        }

        foreach (static::keys() as $module) {
            if ($user->can(static::viewPermission($module))) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, array{view: bool, edit: bool}> */
    public static function emptyPermissionMatrix(): array
    {
        $matrix = [];

        foreach (static::keys() as $module) {
            $matrix[$module] = ['view' => false, 'edit' => false];
        }

        return $matrix;
    }

    public static function firstAccessibleRoute(User $user): ?string
    {
        if ($user->is_super_admin) {
            return 'platform.settings.site';
        }

        foreach (static::all() as $key => $module) {
            if (static::userCan($user, $key, 'view')) {
                return $module['route'];
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function permissionsFromRequest(array $input): array
    {
        $granted = [];

        foreach (static::keys() as $module) {
            $view = ! empty($input[$module]['view']);
            $edit = ! empty($input[$module]['edit']);

            if ($edit) {
                $view = true;
            }

            if ($view) {
                $granted[] = static::viewPermission($module);
            }

            if ($edit) {
                $granted[] = static::editPermission($module);
            }
        }

        return $granted;
    }

    public static function syncRolePermissions(\Spatie\Permission\Models\Role $role, array $input): void
    {
        static::syncPermissions();

        $role->syncPermissions(static::permissionsFromRequest($input));
    }

    /** @return array<string, array{view: bool, edit: bool}> */
    public static function permissionsForRole(\Spatie\Permission\Models\Role $role): array
    {
        $names = $role->permissions->pluck('name')->all();
        $matrix = [];

        foreach (static::keys() as $module) {
            $matrix[$module] = [
                'view' => in_array(static::viewPermission($module), $names, true),
                'edit' => in_array(static::editPermission($module), $names, true),
            ];
        }

        return $matrix;
    }
}
