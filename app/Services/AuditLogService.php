<?php

namespace App\Services;

use App\Models\CafeAuditLog;
use App\Models\PlatformAuditLog;
use App\Models\User;
use App\Support\ClientIp;
use App\Support\LogSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogService
{
    public function platform(
        ?User $user,
        string $action,
        string $summary,
        array $context = [],
        ?Request $request = null,
    ): void {
        $summary = Str::limit(trim($summary), 500, '…');

        PlatformAuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => Str::limit($action, 64, ''),
            'summary' => $summary,
            'ip_address' => ClientIp::resolve($request),
            'route_name' => $request?->route()?->getName(),
            'http_method' => $request?->method(),
            'context' => $context === [] ? null : $this->compactContext($context),
        ]);
    }

    public function cafe(
        string $tenantId,
        ?User $user,
        string $action,
        string $summary,
        array $context = [],
        ?Model $subject = null,
        ?string $ipAddress = null,
        ?Request $request = null,
    ): void {
        if ($tenantId === '') {
            return;
        }

        $summary = Str::limit(trim($summary), 500, '…');

        CafeAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $user?->id,
            'action' => Str::limit($action, 64, ''),
            'summary' => $summary,
            'ip_address' => $ipAddress ?? ClientIp::resolve($request),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'context' => $context === [] ? null : $this->compactContext($context),
        ]);
    }

    public function logAuthLogin(User $user, ?Request $request = null): void
    {
        $request ??= request();

        if ($user->isPlatformStaffMember()) {
            $this->platform(
                $user,
                'auth.login',
                __('menu.log_user_login', ['user' => $user->name, 'email' => $user->email]),
                [],
                $request,
            );

            return;
        }

        $tenantId = $this->primaryTenantIdForUser($user);

        if ($tenantId === null) {
            return;
        }

        $this->cafe(
            $tenantId,
            $user,
            'auth.login',
            __('menu.log_cafe_user_login', ['user' => $user->name, 'email' => $user->email]),
            request: $request,
        );
    }

    public function logAuthLogout(User $user, ?Request $request = null): void
    {
        $request ??= request();

        if ($user->isPlatformStaffMember()) {
            $this->platform(
                $user,
                'auth.logout',
                __('menu.log_user_logout', ['user' => $user->name, 'email' => $user->email]),
                [],
                $request,
            );

            return;
        }

        $tenantId = $this->primaryTenantIdForUser($user);

        if ($tenantId === null) {
            return;
        }

        $this->cafe(
            $tenantId,
            $user,
            'auth.logout',
            __('menu.log_cafe_user_logout', ['user' => $user->name, 'email' => $user->email]),
            request: $request,
        );
    }

    private function primaryTenantIdForUser(User $user): ?string
    {
        if ($user->tenant_id) {
            return (string) $user->tenant_id;
        }

        $linked = $user->linkedTenants()->first();

        return $linked ? (string) $linked->id : null;
    }

    /** @return array{platform: int, cafe: int} */
    public function purgeExpired(): array
    {
        $platformCutoff = now()->subDays(LogSettings::platformRetentionDays());
        $cafeCutoff = now()->subDays(LogSettings::cafeRetentionDays());

        $platformDeleted = 0;
        $cafeDeleted = 0;

        DB::transaction(function () use ($platformCutoff, $cafeCutoff, &$platformDeleted, &$cafeDeleted) {
            $platformDeleted = PlatformAuditLog::query()
                ->where('created_at', '<', $platformCutoff)
                ->limit(5000)
                ->delete();

            $cafeDeleted = CafeAuditLog::query()
                ->where('created_at', '<', $cafeCutoff)
                ->limit(5000)
                ->delete();
        });

        return [
            'platform' => $platformDeleted,
            'cafe' => $cafeDeleted,
        ];
    }

    private function compactContext(array $context): array
    {
        $encoded = json_encode($context, JSON_UNESCAPED_UNICODE);

        if ($encoded !== false && strlen($encoded) > 2000) {
            return ['truncated' => true];
        }

        return $context;
    }
}
