<?php

use App\Http\Controllers\Admin\DigitalMenuController as AdminDigitalMenuController;
use App\Http\Controllers\Admin\DeliveryOrderController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DiningTableController as AdminDiningTableController;
use App\Http\Controllers\Admin\IntegrationBillingController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\OkcDeviceController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\TableCategoryController as AdminTableCategoryController;
use App\Http\Controllers\Cashier\PaymentController as CashierPaymentController;
use App\Http\Controllers\Cashier\TableController as CashierTableController;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\InitializeTenancyBySlug;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Platform\CustomerController;
use App\Http\Controllers\Platform\LicenseTypeController;
use App\Http\Controllers\Platform\LogController;
use App\Http\Controllers\Platform\MailSettingsController;
use App\Http\Controllers\Platform\SeoSettingsController;
use App\Http\Controllers\Platform\SiteSettingsController;
use App\Http\Controllers\Platform\TicketCategoryController;
use App\Http\Controllers\Platform\TicketController as PlatformTicketController;
use App\Http\Controllers\Platform\TicketSettingsController;
use App\Http\Controllers\Platform\TicketTagController;
use App\Http\Controllers\Platform\TenantController;
use App\Http\Controllers\Platform\TenantStaffController;
use App\Http\Controllers\Platform\UserController;
use App\Http\Controllers\Platform\UserGroupController;
use App\Http\Controllers\Platform\UserSecuritySettingsController;
use App\Models\Ticket;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\DigitalMenuController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StaffInvitationController;
use App\Http\Controllers\TenantSwitchController;
use App\Http\Controllers\Waiter\OrderController as WaiterOrderController;
use App\Http\Controllers\Waiter\TableController as WaiterTableController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::middleware([InitializeTenancyBySlug::class])->group(function () {
    Route::get('/dijital-menuler/{tenantId}/{menuPublicId}', [DigitalMenuController::class, 'show'])
        ->name('digital-menu.show');
});

Route::get('/', HomeController::class);

Route::get('/dashboard', HomeController::class)->middleware('auth')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');
    Route::post('/impersonation/leave', [ImpersonationController::class, 'leave'])->name('impersonation.leave');
    Route::get('/tenant/select', [TenantSwitchController::class, 'index'])->name('tenant.select');
    Route::post('/tenant/select', [TenantSwitchController::class, 'store'])->name('tenant.select.store');

    Route::middleware(['platform', 'log.platform'])->prefix('platform')->name('platform.')->group(function () {
        Route::get('users/security', [UserSecuritySettingsController::class, 'edit'])->name('users.security');
        Route::put('users/security', [UserSecuritySettingsController::class, 'update'])->name('users.security.update');
        Route::post('users/security/enforce-2fa', [UserSecuritySettingsController::class, 'enforceTwoFactor'])->name('users.security.enforce-2fa');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('users/{user}/toggle-2fa', [UserController::class, 'toggleTwoFactor'])->name('users.toggle-2fa');
        Route::post('users/{user}/reset-2fa', [UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
        Route::post('users/{user}/change-email', [UserController::class, 'changeEmail'])->name('users.change-email');
        Route::post('users/{user}/send-reset-link', [UserController::class, 'sendResetLink'])->name('users.send-reset-link');
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('user-groups', UserGroupController::class)->except(['show'])->parameters(['user-groups' => 'group']);
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
        Route::post('customers/{customer}/send-reset-link', [CustomerController::class, 'sendResetLink'])->name('customers.send-reset-link');
        Route::post('customers/{customer}/reset-password', [CustomerController::class, 'resetPassword'])->name('customers.reset-password');
        Route::post('customers/{customer}/toggle-2fa', [CustomerController::class, 'toggleTwoFactor'])->name('customers.toggle-2fa');
        Route::post('customers/{customer}/reset-2fa', [CustomerController::class, 'resetTwoFactor'])->name('customers.reset-2fa');
        Route::post('customers/{customer}/toggle-email-verification', [CustomerController::class, 'toggleEmailVerification'])->name('customers.toggle-email-verification');
        Route::post('customers/{customer}/change-email', [CustomerController::class, 'changeEmail'])->name('customers.change-email');
        Route::post('customers/{customer}/tenants', [CustomerController::class, 'attachTenant'])->name('customers.tenants.attach');
        Route::post('customers/{customer}/tenants/{tenant}/transfer-ownership', [CustomerController::class, 'transferTenantOwnership'])->name('customers.tenants.transfer-ownership');
        Route::post('customers/{customer}/tenants/{tenant}/make-owner', [CustomerController::class, 'makeTenantOwner'])->name('customers.tenants.make-owner');
        Route::delete('customers/{customer}/tenants/{tenant}', [CustomerController::class, 'detachTenant'])->name('customers.tenants.detach');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('users/{user}/tenants', [UserController::class, 'attachTenant'])->name('users.tenants.attach');
        Route::delete('users/{user}/tenants/{tenant}', [UserController::class, 'detachTenant'])->name('users.tenants.detach');
        Route::get('settings/site', [SiteSettingsController::class, 'edit'])->name('settings.site');
        Route::put('settings/site', [SiteSettingsController::class, 'update'])->name('settings.site.update');
        Route::get('settings/mail', [MailSettingsController::class, 'edit'])->name('settings.mail');
        Route::put('settings/mail', [MailSettingsController::class, 'update'])->name('settings.mail.update');
        Route::post('settings/mail/test', [MailSettingsController::class, 'test'])->name('settings.mail.test');
        Route::get('settings/seo', [SeoSettingsController::class, 'edit'])->name('settings.seo');
        Route::put('settings/seo', [SeoSettingsController::class, 'update'])->name('settings.seo.update');
        Route::resource('tenants', TenantController::class)->except(['show']);
        Route::post('tenants/{tenant}/connect', [TenantController::class, 'connect'])->name('tenants.connect');
        Route::post('support/disconnect', [TenantController::class, 'disconnectSupport'])->name('support.disconnect');
        Route::post('tenants/{tenant}/staff/lookup', [TenantStaffController::class, 'lookup'])->name('tenants.staff.lookup');
        Route::delete('tenants/{tenant}/staff/invitations/{invitation}', [TenantStaffController::class, 'revokeInvitation'])->name('tenants.staff.invitations.revoke');
        Route::post('tenants/{tenant}/staff', [TenantStaffController::class, 'store'])->name('tenants.staff.store');
        Route::put('tenants/{tenant}/staff/{user}', [TenantStaffController::class, 'update'])->name('tenants.staff.update');
        Route::delete('tenants/{tenant}/staff/{user}', [TenantStaffController::class, 'destroy'])->name('tenants.staff.destroy');
        Route::post('tenants/{tenant}/staff/{user}/impersonate', [TenantStaffController::class, 'impersonate'])->name('tenants.staff.impersonate');
        Route::post('tenants/{tenant}/stop', [TenantController::class, 'stop'])->name('tenants.stop');
        Route::post('tenants/{tenant}/resume', [TenantController::class, 'resume'])->name('tenants.resume');
        Route::get('licenses/issued', fn () => redirect()->route('platform.licenses.index'))->name('licenses.issued');
        Route::get('licenses/licensegate/settings', fn () => redirect()->route('platform.licenses.index'))->name('licenses.licensegate');
        Route::resource('licenses', LicenseTypeController::class)->except(['show'])->whereNumber('license');
        Route::get('tickets/settings', [TicketSettingsController::class, 'edit'])->name('tickets.settings');
        Route::put('tickets/settings', [TicketSettingsController::class, 'update'])->name('tickets.settings.update');
        Route::resource('ticket-categories', TicketCategoryController::class)->except(['show']);
        Route::get('ticket-tags', [TicketTagController::class, 'index'])->name('ticket-tags.index');
        Route::post('ticket-tags', [TicketTagController::class, 'store'])->name('ticket-tags.store');
        Route::delete('ticket-tags/{ticketTag}', [TicketTagController::class, 'destroy'])->name('ticket-tags.destroy');
        Route::get('tickets', [PlatformTicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}', [PlatformTicketController::class, 'show'])->name('tickets.show');
        Route::patch('tickets/{ticket}', [PlatformTicketController::class, 'update'])->name('tickets.update');
        Route::post('tickets/{ticket}/reply', [PlatformTicketController::class, 'reply'])->name('tickets.reply');
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');
        Route::put('logs/settings', [LogController::class, 'updateSettings'])->name('logs.settings.update');
    });

    Route::middleware(['tenant', 'cafe'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/operations', [OperationsController::class, 'index'])->name('operations.index');
        Route::post('staff/lookup', [AdminStaffController::class, 'lookup'])->name('staff.lookup');
        Route::delete('staff/invitations/{invitation}', [AdminStaffController::class, 'revokeInvitation'])->name('staff.invitations.revoke');
        Route::resource('tables', AdminDiningTableController::class)->except(['show']);
        Route::resource('table-categories', AdminTableCategoryController::class)->except(['show', 'index']);
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('dijital-menuler/olustur', [AdminDigitalMenuController::class, 'create'])->name('digital-menus.create');
        Route::post('dijital-menuler', [AdminDigitalMenuController::class, 'store'])->name('digital-menus.store');
        Route::get('dijital-menuler/{digitalMenu}/qr-indir', [AdminDigitalMenuController::class, 'downloadQr'])->name('digital-menus.qr-download');
        Route::resource('dijital-menuler', AdminDigitalMenuController::class)
            ->except(['create', 'store', 'edit', 'update'])
            ->parameters(['dijital-menuler' => 'digitalMenu'])
            ->names('digital-menus');
        Route::resource('staff', AdminStaffController::class)->except(['show']);
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

        Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::get('/integrations/billing/defaults', [IntegrationBillingController::class, 'edit'])->name('integrations.billing.edit');
        Route::put('/integrations/billing/defaults', [IntegrationBillingController::class, 'update'])->name('integrations.billing.update');
        Route::get('/integrations/{provider}/mappings', [IntegrationController::class, 'mappings'])->name('integrations.mappings');
        Route::post('/integrations/{provider}/mappings', [IntegrationController::class, 'storeMapping'])->name('integrations.mappings.store');
        Route::delete('/integrations/{provider}/mappings/{mapping}', [IntegrationController::class, 'destroyMapping'])->name('integrations.mappings.destroy');
        Route::get('/integrations/{provider}', [IntegrationController::class, 'edit'])->name('integrations.edit');
        Route::put('/integrations/{provider}', [IntegrationController::class, 'update'])->name('integrations.update');

        Route::get('/delivery-orders', [DeliveryOrderController::class, 'index'])->name('delivery-orders.index');
        Route::get('/delivery-orders/poll', [DeliveryOrderController::class, 'poll'])->name('delivery-orders.poll');
        Route::post('/delivery-orders/{order}/accept', [DeliveryOrderController::class, 'accept'])->name('delivery-orders.accept');
        Route::post('/delivery-orders/{order}/preparing', [DeliveryOrderController::class, 'preparing'])->name('delivery-orders.preparing');
        Route::post('/delivery-orders/{order}/ready-for-courier', [DeliveryOrderController::class, 'readyForCourier'])->name('delivery-orders.ready-for-courier');
        Route::post('/delivery-orders/{order}/hand-to-courier', [DeliveryOrderController::class, 'handToCourier'])->name('delivery-orders.hand-to-courier');
        Route::post('/delivery-orders/{order}/reject', [DeliveryOrderController::class, 'reject'])->name('delivery-orders.reject');

        Route::get('/okc-devices', [OkcDeviceController::class, 'index'])->name('okc-devices.index');
        Route::post('/okc-devices', [OkcDeviceController::class, 'store'])->name('okc-devices.store');
        Route::put('/okc-devices/{okcDevice}', [OkcDeviceController::class, 'update'])->name('okc-devices.update');
        Route::delete('/okc-devices/{okcDevice}', [OkcDeviceController::class, 'destroy'])->name('okc-devices.destroy');
    });

    Route::middleware(['tenant', 'cafe', 'role:waiter,cashier,cafe_admin'])->prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::get('/create', [ReservationController::class, 'create'])->name('create');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::get('/{reservation}/edit', [ReservationController::class, 'edit'])->name('edit');
        Route::put('/{reservation}', [ReservationController::class, 'update'])->name('update');
        Route::post('/{reservation}/complete', [ReservationController::class, 'complete'])->name('complete');
        Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['tenant', 'cafe', 'role:cashier,cafe_admin'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('/tables', [CashierTableController::class, 'index'])->name('tables.index');
        Route::get('/tables/{table}', [CashierTableController::class, 'show'])->name('tables.show');
        Route::post('/orders/{order}/pay', [CashierPaymentController::class, 'store'])->name('orders.pay');
    });

    Route::middleware(['tenant', 'cafe', 'role:waiter,cashier'])->prefix('waiter')->name('waiter.')->group(function () {
        Route::get('/tables', [WaiterTableController::class, 'index'])->name('tables.index');
        Route::get('/tables/{table}', [WaiterTableController::class, 'show'])->name('tables.show');
        Route::post('/tables/{table}/orders', [WaiterOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders/{order}/items', [WaiterOrderController::class, 'storeItem'])->name('orders.items.store');
        Route::patch('/orders/{order}/items/{item}', [WaiterOrderController::class, 'updateItem'])->name('orders.items.update');
        Route::delete('/orders/{order}/items/{item}', [WaiterOrderController::class, 'removeItem'])->name('orders.items.destroy');
        Route::post('/orders/{order}/send', [WaiterOrderController::class, 'sendToKitchen'])->name('orders.send');
        Route::post('/orders/{order}/request-payment', [WaiterOrderController::class, 'requestPayment'])->name('orders.request-payment');
        Route::post('/orders/{order}/close', [WaiterOrderController::class, 'close'])->name('orders.close');
        Route::post('/orders/{order}/items/{item}/served', [WaiterOrderController::class, 'markServed'])->name('orders.items.served');
        Route::get('/ready-items/poll', [WaiterOrderController::class, 'pollReadyItems'])->name('ready-items.poll');
    });

    Route::middleware(['tenant', 'cafe', 'role:kitchen'])->prefix('kitchen')->name('kitchen.')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/poll', [KitchenController::class, 'poll'])->name('poll');
        Route::patch('/items/{item}/status', [KitchenController::class, 'updateStatus'])->name('items.status');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/tickets', fn () => redirect()->route('profile.edit', ['tab' => 'ticket']))->name('ticket.index');
    Route::get('/tickets/create', fn () => redirect()->route('profile.edit', ['tab' => 'ticket', 'ticket_action' => 'create']))->name('ticket.create');
    Route::get('/tickets/{ticket}', function (Ticket $ticket) {
        abort_unless(Auth::check() && $ticket->isOwnedBy(Auth::user()), 403);

        return redirect()->route('profile.edit', ['tab' => 'ticket', 'ticket_id' => $ticket->id]);
    })->name('ticket.show');
    Route::post('/profile/tickets', [ProfileController::class, 'storeTicket'])->name('profile.tickets.store');
    Route::post('/profile/tickets/{ticket}/reply', [ProfileController::class, 'replyTicket'])->name('profile.tickets.reply');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/email', [ProfileController::class, 'changeEmail'])->name('profile.change-email');
    Route::post('/profile/toggle-2fa', [ProfileController::class, 'startTwoFactorSetup'])->name('profile.two-factor.setup');
    Route::post('/profile/two-factor/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile.two-factor.confirm');
    Route::post('/profile/two-factor/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
    Route::post('/profile/cafe', [ProfileController::class, 'storeCafe'])->name('profile.cafe.store');
    Route::delete('/profile/cafe/{tenant}', [ProfileController::class, 'destroyCafe'])->name('profile.cafe.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/staff/invitation/{token}', [StaffInvitationController::class, 'show'])->name('staff.invitation.show');
    Route::post('/staff/invitation/{token}/accept', [StaffInvitationController::class, 'accept'])->name('staff.invitation.accept');
    Route::post('/staff/invitation/{token}/decline', [StaffInvitationController::class, 'decline'])->name('staff.invitation.decline');
});

require __DIR__.'/auth.php';
