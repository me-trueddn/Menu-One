<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantStaffInvitation extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'invited_by_user_id',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'declined_at',
        'revoked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status() === 'pending';
    }

    public function status(): string
    {
        if ($this->accepted_at !== null) {
            return 'accepted';
        }

        if ($this->revoked_by_user_id !== null) {
            return 'revoked';
        }

        if ($this->declined_at !== null) {
            return 'declined';
        }

        if ($this->expires_at->isPast()) {
            return 'expired';
        }

        return 'pending';
    }

    public function statusLabel(): string
    {
        return __('menu.staff_invitation_status_'.$this->status());
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status()) {
            'pending' => 'text-bg-warning',
            'accepted' => 'text-bg-success',
            'declined' => 'text-bg-secondary',
            'revoked' => 'text-bg-dark',
            'expired' => 'text-bg-light text-dark border',
            default => 'text-bg-secondary',
        };
    }

    /** @param  Builder<static>  $query */
    public function scopeRecent(Builder $query, int $days = 90): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
