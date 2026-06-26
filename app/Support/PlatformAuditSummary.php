<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class PlatformAuditSummary
{
    public static function describe(Request $request): string
    {
        $route = $request->route()?->getName();

        return match ($route) {
            'platform.customers.tenants.attach' => self::customerTenantAttach($request),
            'platform.customers.tenants.detach' => self::customerTenantDetach($request),
            'platform.customers.tenants.transfer-ownership' => self::customerTenantTransfer($request),
            'platform.customers.tenants.make-owner' => self::customerTenantMakeOwner($request),
            'platform.customers.toggle-active' => self::customerToggleActive($request),
            'platform.customers.reset-password' => self::customerResetPassword($request),
            'platform.customers.change-email' => self::customerChangeEmail($request),
            'platform.customers.destroy' => self::customerDestroy($request),
            'platform.users.tenants.attach' => self::userTenantAttach($request),
            'platform.users.tenants.detach' => self::userTenantDetach($request),
            'platform.users.store' => __('menu.log_user_created', ['user' => $request->input('email', '—')]),
            'platform.users.update' => self::userUpdated($request),
            'platform.users.destroy' => self::userDestroyed($request),
            'platform.users.reset-password' => self::platformUserResetPassword($request),
            'platform.users.change-email' => self::platformUserChangeEmail($request),
            'platform.users.toggle-active' => self::platformUserToggleActive($request),
            'platform.tenants.store' => __('menu.log_tenant_created', ['name' => $request->input('name', '—')]),
            'platform.tenants.update' => self::tenantUpdated($request),
            'platform.tenants.destroy' => self::tenantDestroyed($request),
            'platform.tenants.connect' => self::tenantConnect($request),
            'platform.tenants.stop' => self::tenantStop($request),
            'platform.tenants.resume' => self::tenantResume($request),
            'platform.tenants.staff.store' => self::tenantStaffStore($request),
            'platform.tenants.staff.update' => self::tenantStaffUpdate($request),
            'platform.tenants.staff.destroy' => self::tenantStaffDestroy($request),
            'platform.tenants.staff.impersonate' => self::tenantStaffImpersonate($request),
            'platform.support.disconnect' => __('menu.log_support_disconnected'),
            'platform.settings.site.update' => __('menu.log_site_settings_updated'),
            'platform.settings.mail.update' => __('menu.log_mail_settings_updated'),
            'platform.settings.seo.update' => __('menu.log_seo_settings_updated'),
            'platform.user-groups.store' => __('menu.log_group_created', ['name' => $request->input('name', '—')]),
            'platform.user-groups.update' => self::groupUpdated($request),
            'platform.user-groups.destroy' => self::groupDestroyed($request),
            'platform.licenses.store' => __('menu.log_license_created', ['name' => $request->input('name', '—')]),
            'platform.licenses.update' => self::licenseUpdated($request),
            'platform.licenses.destroy' => self::licenseDestroyed($request),
            'platform.tickets.reply' => self::ticketReply($request),
            'platform.tickets.update' => self::ticketUpdate($request),
            'platform.ticket-categories.store' => __('menu.log_ticket_category_created', ['name' => $request->input('name', '—')]),
            'platform.ticket-categories.update' => self::ticketCategoryUpdated($request),
            'platform.ticket-categories.destroy' => self::ticketCategoryDestroyed($request),
            'platform.ticket-tags.store' => __('menu.log_ticket_tag_created', ['name' => $request->input('name', '—')]),
            'platform.ticket-tags.destroy' => self::ticketTagDestroyed($request),
            'platform.tickets.settings.update' => __('menu.log_ticket_settings_updated'),
            'platform.logs.settings.update' => __('menu.log_settings_updated'),
            default => self::fallback($route, $request),
        };
    }

    public static function actionLabel(string $action): string
    {
        return AuditActionLabel::label($action);
    }

    private static function fallback(?string $route, Request $request): string
    {
        $parts = explode('.', (string) $route);
        $module = $parts[1] ?? 'platform';
        $moduleLabel = Lang::has('menu.'.$module) ? __('menu.'.$module) : $module;

        return __('menu.log_platform_fallback', [
            'module' => $moduleLabel,
            'verb' => match ($request->method()) {
                'POST' => __('menu.log_verb_created'),
                'PUT', 'PATCH' => __('menu.log_verb_updated'),
                'DELETE' => __('menu.log_verb_deleted'),
                default => $request->method(),
            },
        ]);
    }

    private static function customerTenantAttach(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');
        $tenant = Tenant::query()->find($request->input('tenant_id'));

        return __('menu.log_customer_tenant_attached', [
            'customer' => self::userLabel($customer),
            'tenant' => $tenant?->name ?? $request->input('tenant_id', '—'),
        ]);
    }

    private static function customerTenantDetach(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_customer_tenant_detached', [
            'customer' => self::userLabel($customer),
            'tenant' => $tenant?->name ?? '—',
        ]);
    }

    private static function customerTenantTransfer(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_customer_tenant_transferred', [
            'from' => self::userLabel($customer),
            'tenant' => $tenant?->name ?? '—',
            'to' => $request->input('new_owner', '—'),
        ]);
    }

    private static function customerTenantMakeOwner(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_customer_tenant_owner', [
            'customer' => self::userLabel($customer),
            'tenant' => $tenant?->name ?? '—',
        ]);
    }

    private static function customerToggleActive(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');

        return __('menu.log_customer_toggled', ['customer' => self::userLabel($customer)]);
    }

    private static function customerResetPassword(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');

        return __('menu.log_customer_password_reset', ['customer' => self::userLabel($customer)]);
    }

    private static function customerChangeEmail(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');

        return __('menu.log_customer_email_changed', [
            'customer' => self::userLabel($customer),
            'email' => $request->input('email', '—'),
        ]);
    }

    private static function customerDestroy(Request $request): string
    {
        /** @var User|null $customer */
        $customer = $request->route('customer');

        return __('menu.log_customer_deleted', ['customer' => self::userLabel($customer)]);
    }

    private static function userTenantAttach(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');
        $tenant = Tenant::query()->find($request->input('tenant_id'));

        return __('menu.log_user_tenant_attached', [
            'user' => self::userLabel($user),
            'tenant' => $tenant?->name ?? $request->input('tenant_id', '—'),
        ]);
    }

    private static function userTenantDetach(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_user_tenant_detached', [
            'user' => self::userLabel($user),
            'tenant' => $tenant?->name ?? '—',
        ]);
    }

    private static function userUpdated(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_user_updated', ['user' => self::userLabel($user)]);
    }

    private static function userDestroyed(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_user_deleted', ['user' => self::userLabel($user)]);
    }

    private static function platformUserResetPassword(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_platform_user_password_reset', ['user' => self::userLabel($user)]);
    }

    private static function platformUserChangeEmail(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_platform_user_email_changed', [
            'user' => self::userLabel($user),
            'email' => $request->input('email', '—'),
        ]);
    }

    private static function platformUserToggleActive(Request $request): string
    {
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_platform_user_toggled', ['user' => self::userLabel($user)]);
    }

    private static function tenantUpdated(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_updated', ['tenant' => $tenant?->name ?? '—']);
    }

    private static function tenantDestroyed(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_deleted', ['tenant' => $tenant?->name ?? '—']);
    }

    private static function tenantConnect(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_support_connected', ['tenant' => $tenant?->name ?? '—']);
    }

    private static function tenantStop(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_stopped', ['tenant' => $tenant?->name ?? '—']);
    }

    private static function tenantResume(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_resumed', ['tenant' => $tenant?->name ?? '—']);
    }

    private static function tenantStaffStore(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');

        return __('menu.log_tenant_staff_invited', [
            'tenant' => $tenant?->name ?? '—',
            'email' => $request->input('email', '—'),
            'role' => $request->input('role', '—'),
        ]);
    }

    private static function tenantStaffUpdate(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_tenant_staff_updated', [
            'tenant' => $tenant?->name ?? '—',
            'user' => self::userLabel($user),
            'role' => $request->input('role', '—'),
        ]);
    }

    private static function tenantStaffDestroy(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_tenant_staff_removed', [
            'tenant' => $tenant?->name ?? '—',
            'user' => self::userLabel($user),
        ]);
    }

    private static function tenantStaffImpersonate(Request $request): string
    {
        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant');
        /** @var User|null $user */
        $user = $request->route('user');

        return __('menu.log_tenant_staff_impersonated', [
            'tenant' => $tenant?->name ?? '—',
            'user' => self::userLabel($user),
        ]);
    }

    private static function groupUpdated(Request $request): string
    {
        $group = $request->route('group');

        return __('menu.log_group_updated', ['name' => $group?->name ?? $request->input('name', '—')]);
    }

    private static function groupDestroyed(Request $request): string
    {
        $group = $request->route('group');

        return __('menu.log_group_deleted', ['name' => $group?->name ?? '—']);
    }

    private static function licenseUpdated(Request $request): string
    {
        $license = $request->route('license');

        return __('menu.log_license_updated', ['name' => $license?->name ?? '—']);
    }

    private static function licenseDestroyed(Request $request): string
    {
        $license = $request->route('license');

        return __('menu.log_license_deleted', ['name' => $license?->name ?? '—']);
    }

    private static function ticketReply(Request $request): string
    {
        /** @var Ticket|null $ticket */
        $ticket = $request->route('ticket');

        return __('menu.log_ticket_replied', ['number' => $ticket?->number ?? '—']);
    }

    private static function ticketUpdate(Request $request): string
    {
        /** @var Ticket|null $ticket */
        $ticket = $request->route('ticket');

        return __('menu.log_ticket_updated', [
            'number' => $ticket?->number ?? '—',
            'status' => $request->input('status', '—'),
        ]);
    }

    private static function ticketCategoryUpdated(Request $request): string
    {
        $category = $request->route('ticket_category');

        return __('menu.log_ticket_category_updated', ['name' => $category?->name ?? '—']);
    }

    private static function ticketCategoryDestroyed(Request $request): string
    {
        $category = $request->route('ticket_category');

        return __('menu.log_ticket_category_deleted', ['name' => $category?->name ?? '—']);
    }

    private static function ticketTagDestroyed(Request $request): string
    {
        $tag = $request->route('ticketTag');

        return __('menu.log_ticket_tag_deleted', ['name' => $tag?->name ?? '—']);
    }

    private static function userLabel(?User $user): string
    {
        if ($user === null) {
            return '—';
        }

        return trim($user->name.' ('.$user->email.')');
    }
}
