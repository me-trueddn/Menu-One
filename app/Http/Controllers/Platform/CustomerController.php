<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MailConfigService;
use App\Services\PasswordLifecycleService;
use App\Support\MailExceptionFormatter;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(private PasswordLifecycleService $passwordLifecycle) {}

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->query('per_page'), [20, 50, 100, 200], true)
            ? (int) $request->query('per_page')
            : 20;

        $searchField = in_array($request->query('search_field'), ['email', 'phone', 'tenant'], true)
            ? $request->query('search_field')
            : 'email';

        $query = User::query()
            ->customers()
            ->with(['assignedTenants', 'tenant', 'ownedTenants']);

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($searchField, $search) {
                if ($searchField === 'email') {
                    $builder->where('email', 'like', '%'.$search.'%');
                } elseif ($searchField === 'phone') {
                    $builder->where('phone', 'like', '%'.$search.'%');
                } else {
                    $builder->where(function ($tenantBuilder) use ($search) {
                        $tenantBuilder
                            ->whereHas('assignedTenants', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search))
                            ->orWhereHas('ownedTenants', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search))
                            ->orWhereHas('tenant', fn ($tenantQuery) => $this->applyTenantSearch($tenantQuery, $search));
                    });
                }
            });
        }

        $customers = $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('theme::pages.platform.customers.index', compact('customers', 'perPage', 'searchField'));
    }

    public function toggleActive(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', __('menu.messages.updated'));
    }

    public function sendResetLink(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        try {
            MailConfigService::runWithTimeout(function () use ($customer) {
                $status = Password::sendResetLink(['email' => $customer->email]);

                if ($status !== Password::RESET_LINK_SENT) {
                    throw new \RuntimeException(__($status));
                }
            });
        } catch (\RuntimeException $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', MailExceptionFormatter::toUserMessage($exception));
        }

        return back()->with('success', __('menu.reset_link_sent'));
    }

    public function resetPassword(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'password' => ['required', SecurityPolicy::passwordRules(), 'confirmed'],
        ]);

        $oldHash = $customer->getRawOriginal('password');
        $this->passwordLifecycle->assertNotInHistory($customer, $validated['password']);
        $customer->update(['password' => $validated['password']]);
        $this->passwordLifecycle->recordChange($customer->fresh(), $oldHash);

        return back()->with('success', __('menu.password_reset_done'));
    }

    public function toggleTwoFactor(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        if (! SecurityPolicy::bool('security_2fa_enabled_globally')) {
            return back()->with('error', __('menu.two_factor_disabled_globally'));
        }

        $customer->update(['two_factor_enabled' => ! $customer->two_factor_enabled]);

        return back()->with('success', __('menu.messages.updated'));
    }

    public function changeEmail(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('tenant_id')->ignore($customer->id),
            ],
        ]);

        $customer->update(['email' => $validated['email']]);

        return back()->with('success', __('menu.email_updated'));
    }

    public function attachTenant(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);

        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
        ]);

        if ($customer->assignedTenants()->whereKey($validated['tenant_id'])->exists()
            || $customer->tenant_id === $validated['tenant_id']
            || $customer->ownedTenants()->whereKey($validated['tenant_id'])->exists()) {
            return back()->with('error', __('menu.tenant_already_assigned'));
        }

        $customer->assignedTenants()->attach($validated['tenant_id']);

        return back()->with('success', __('menu.tenant_assigned'));
    }

    public function detachTenant(User $customer, Tenant $tenant): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_unless($customer->isLinkedToTenant($tenant), 404);

        $customer->unlinkTenant($tenant);

        return back()->with('success', __('menu.tenant_removed'));
    }

    public function destroy(User $customer): RedirectResponse
    {
        abort_unless($customer->isCustomer(), 404);
        abort_if($customer->id === $this->authUser()->id, 403);

        $customer->assignedTenants()->detach();
        $customer->loginToken()?->delete();
        $customer->syncRoles([]);
        $customer->delete();

        return back()->with('success', __('menu.messages.deleted'));
    }

    private function applyTenantSearch($query, string $search): void
    {
        $query->where(function ($builder) use ($search) {
            $builder
                ->where('tenants.id', 'like', '%'.$search.'%')
                ->orWhere('tenants.name', 'like', '%'.$search.'%');
        });
    }
}
