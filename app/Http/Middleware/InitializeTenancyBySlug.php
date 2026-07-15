<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySlug
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->tenancy->initialized) {
            return $next($request);
        }

        $slug = (string) ($request->route('tenantSlug') ?? $request->route('tenantId'));
        $tenant = Tenant::query()
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        if (! $tenant->is_active) {
            abort(403, 'Bu cafe şu an aktif değil.');
        }

        $this->tenancy->initialize($tenant);

        return $next($request);
    }
}
