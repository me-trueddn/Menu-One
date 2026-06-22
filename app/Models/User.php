<?php

namespace App\Models;

use App\Services\UserPublicIdGenerator;
use App\Support\PlatformModules;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'tenant_id',
        'public_id',
        'name',
        'email',
        'phone',
        'oauth_provider',
        'oauth_provider_id',
        'password',
        'password_changed_at',
        'email_verified_at',
        'is_active',
        'is_super_admin',
        'two_factor_enabled',
        'unlicensed_cafe_deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'unlicensed_cafe_deleted_at' => 'datetime',
        ];
    }

    public function hasPremiumLicensedCafe(): bool
    {
        return $this->linkedTenants()->contains(fn (Tenant $tenant) => $tenant->isPremiumLicensed());
    }

    public function accountSubscriptionLabel(): string
    {
        return $this->hasPremiumLicensedCafe()
            ? __('menu.account_type_premium')
            : __('menu.account_type_free');
    }

    public function accountSubscriptionBadgeClass(): string
    {
        return $this->hasPremiumLicensedCafe() ? 'text-bg-warning' : 'text-bg-info';
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->public_id)) {
                $user->public_id = UserPublicIdGenerator::generate();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function assignedTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')->withTimestamps();
    }

    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_user_id');
    }

    public function canAccessTenant(string $tenantId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->tenant_id === $tenantId) {
            return true;
        }

        if ($this->ownedTenants()->whereKey($tenantId)->exists()) {
            return true;
        }

        return $this->assignedTenants()->whereKey($tenantId)->exists();
    }

    /** @return Collection<int, Tenant> */
    public function linkedTenants(): Collection
    {
        $tenants = ($this->relationLoaded('assignedTenants')
            ? $this->assignedTenants
            : $this->assignedTenants()->get())->keyBy('id');

        if ($this->tenant_id) {
            $primary = $this->relationLoaded('tenant') ? $this->tenant : $this->tenant()->first();
            if ($primary) {
                $tenants->put($primary->id, $primary);
            }
        }

        $owned = $this->relationLoaded('ownedTenants')
            ? $this->ownedTenants
            : $this->ownedTenants()->get();

        foreach ($owned as $tenant) {
            $tenants->put($tenant->id, $tenant);
        }

        return $tenants->sortBy('name')->values();
    }

    public function ownsTenant(Tenant|string $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this->ownedTenants()->whereKey($tenantId)->exists();
    }

    public function isLinkedToTenant(Tenant|string $tenant): bool
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        if ($this->tenant_id === $tenantId) {
            return true;
        }

        if ($this->ownedTenants()->whereKey($tenantId)->exists()) {
            return true;
        }

        return $this->assignedTenants()->whereKey($tenantId)->exists();
    }

    public function unlinkTenant(Tenant $tenant): void
    {
        $tenantId = $tenant->id;

        $this->assignedTenants()->detach($tenantId);

        if ($this->tenant_id === $tenantId) {
            $this->update(['tenant_id' => null]);
        }

        if ($tenant->owner_user_id === $this->id) {
            $tenant->update(['owner_user_id' => null]);
        }

        $this->refresh();

        if (
            $this->tenant_id === null
            && ! $this->assignedTenants()->exists()
            && ! $this->ownedTenants()->exists()
            && $this->hasRole('cafe_admin')
        ) {
            $this->removeRole('cafe_admin');
        }
    }

    public function managesCafePanel(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->tenant_id !== null) {
            return $this->hasAnyRole(['cafe_admin', 'waiter', 'kitchen', 'cashier']);
        }

        return $this->assignedTenants()->exists() && (
            $this->isCustomer() || $this->canAccessPlatformPanel()
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loginToken(): HasOne
    {
        return $this->hasOne(UserLoginToken::class);
    }

    public function scopePlatformStaff(Builder $query): Builder
    {
        return $query
            ->whereNull('tenant_id')
            ->where(function (Builder $builder) {
                $builder
                    ->where('is_super_admin', true)
                    ->orWhere(function (Builder $staff) {
                        $staff
                            ->whereHas('roles')
                            ->whereDoesntHave('roles', fn (Builder $roleQuery) => $roleQuery->where('name', 'user'));
                    });
            });
    }

    public function isPlatformStaffMember(): bool
    {
        if ($this->tenant_id !== null) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isCustomer()) {
            return false;
        }

        return $this->roles()->exists();
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->role('user');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('user');
    }

    public function isPlatformAdmin(): bool
    {
        return $this->tenant_id === null && $this->hasRole('platform_admin');
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function roleLabel(): string
    {
        if ($this->is_super_admin) {
            return __('menu.super_admin');
        }

        return $this->getRoleNames()->first() ?? __('menu.no_role');
    }

    public function canPlatformModule(string $module, string $action = 'view'): bool
    {
        return PlatformModules::userCan($this, $module, $action);
    }

    public function canAccessPlatformPanel(): bool
    {
        return PlatformModules::userCanAnyModule($this);
    }

    public function defaultRoute(): string
    {
        if ($this->isSuperAdmin() || $this->hasRole('platform_admin') || $this->canAccessPlatformPanel()) {
            return PlatformModules::firstAccessibleRoute($this) ?? 'profile.edit';
        }

        if ($this->hasRole('cafe_admin')) {
            return 'admin.dashboard';
        }

        if ($this->hasRole('waiter')) {
            return 'waiter.tables.index';
        }

        if ($this->hasRole('cashier')) {
            return 'cashier.tables.index';
        }

        if ($this->hasRole('kitchen')) {
            return 'kitchen.index';
        }

        if ($this->isCustomer()) {
            return 'profile.edit';
        }

        return 'login';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
