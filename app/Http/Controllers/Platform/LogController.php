<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CafeAuditLog;
use App\Models\PlatformAuditLog;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Support\LogSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'cafe' ? 'cafe' : 'platform';
        $perPage = (int) $request->query('per_page', LogSettings::defaultPerPage());

        if (! in_array($perPage, LogSettings::perPageOptions(), true)) {
            $perPage = LogSettings::defaultPerPage();
        }

        $filters = [
            'user_id' => $request->query('user_id'),
            'tenant_id' => $request->query('tenant_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'per_page' => $perPage,
            'tab' => $tab,
        ];

        if ($tab === 'cafe') {
            $query = CafeAuditLog::query()
                ->with(['user:id,name,email', 'tenant:id,name'])
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            if ($filters['tenant_id']) {
                $query->where('tenant_id', $filters['tenant_id']);
            }

            if ($filters['user_id']) {
                $query->where('user_id', $filters['user_id']);
            }

            $this->applyDateFilters($query, $filters);

            $logs = $query->paginate($perPage)->withQueryString();
        } else {
            $query = PlatformAuditLog::query()
                ->with('user:id,name,email')
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            if ($filters['user_id']) {
                $query->where('user_id', $filters['user_id']);
            }

            $this->applyDateFilters($query, $filters);

            $logs = $query->paginate($perPage)->withQueryString();
        }

        return view('theme::pages.platform.logs.index', [
            'tab' => $tab,
            'logs' => $logs,
            'filters' => $filters,
            'perPageOptions' => LogSettings::perPageOptions(),
            'platformRetentionDays' => LogSettings::platformRetentionDays(),
            'cafeRetentionDays' => LogSettings::cafeRetentionDays(),
            'filterUsers' => $this->filterUsersForTab($tab),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function filterUsersForTab(string $tab)
    {
        if ($tab === 'cafe') {
            $userIds = CafeAuditLog::query()
                ->distinct()
                ->whereNotNull('user_id')
                ->pluck('user_id');

            return User::query()
                ->whereIn('id', $userIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return User::query()->platformStaff()->orderBy('name')->get(['id', 'name', 'email']);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'log_platform_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'log_cafe_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        Setting::set(LogSettings::PLATFORM_RETENTION_KEY, (string) $validated['log_platform_retention_days'], 'logs');
        Setting::set(LogSettings::CAFE_RETENTION_KEY, (string) $validated['log_cafe_retention_days'], 'logs');

        app(\App\Services\AuditLogService::class)->platform(
            $this->authUser(),
            'logs.settings.update',
            __('menu.log_settings_updated'),
        );

        return back()->with('success', __('menu.messages.updated'));
    }

    private function applyDateFilters($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
