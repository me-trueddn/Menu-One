<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantAccess;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenancy->initialized) {
            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            if (! $tenant->is_active) {
                abort(403, 'Bu cafe şu an aktif değil.');
            }

            $this->tenancy->initialize($tenant);
        }

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        if ($header = $request->header('X-Tenant-ID')) {
            return Tenant::find($header);
        }

        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        if (! in_array($host, $centralDomains, true)) {
            $subdomain = explode('.', $host)[0] ?? null;

            if ($subdomain) {
                $tenant = Tenant::where('slug', $subdomain)->first();

                if ($tenant) {
                    return $tenant;
                }
            }
        }

        $user = $request->user();

        if (! $user) {
            return null;
        }

        $activeTenantId = TenantAccess::resolveActiveTenantId($user);

        return $activeTenantId ? Tenant::find($activeTenantId) : null;
    }
}
