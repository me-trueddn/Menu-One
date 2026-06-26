<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use App\Support\PlatformAuditSummary;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPlatformActivity
{
    public function __construct(private AuditLogService $logs) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $user = $request->user();

        if (! $user || ! $user->isPlatformStaffMember()) {
            return $response;
        }

        if ($response->isClientError() || $response->isServerError()) {
            return $response;
        }

        $routeName = $request->route()?->getName() ?? 'unknown';
        $action = str_replace('platform.', '', $routeName);

        $this->logs->platform(
            $user,
            $action,
            PlatformAuditSummary::describe($request),
            ['route' => $routeName],
            $request,
        );

        return $response;
    }
}
