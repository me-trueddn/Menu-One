<?php

namespace App\Services;

use App\Mail\StaffInvitationMail;
use App\Models\Tenant;
use App\Models\TenantStaffInvitation;
use App\Models\User;
use App\Support\EmailTemplatePolicy;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffInvitationService
{
    public function __construct(private CafeStaffService $staff) {}

    public function invite(Tenant $tenant, User $user, string $role, User $invitedBy): TenantStaffInvitation
    {
        $this->staff->assertCanAttach($tenant, $user);

        if ($user->tenant_id === $tenant->id && $user->hasAnyRole(CafeStaffService::assignableRoles())) {
            throw ValidationException::withMessages([
                'email' => __('menu.staff_already_member'),
            ]);
        }

        TenantStaffInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where('expires_at', '>', now())
            ->update(['declined_at' => now()]);

        $invitation = TenantStaffInvitation::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'invited_by_user_id' => $invitedBy->id,
            'role' => $role,
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(EmailTemplatePolicy::staffInvitationExpiresMinutes()),
        ]);

        MailConfigService::runWithTimeout(function () use ($user, $invitation) {
            Mail::to($user->email)->send(new StaffInvitationMail($invitation));
        });

        return $invitation;
    }

    public function findPending(string $token): ?TenantStaffInvitation
    {
        $invitation = TenantStaffInvitation::query()
            ->with(['tenant', 'user', 'invitedBy'])
            ->where('token', $token)
            ->first();

        return $invitation?->isPending() ? $invitation : null;
    }

    public function accept(TenantStaffInvitation $invitation, User $actor): User
    {
        abort_unless($invitation->isPending(), 410);
        abort_unless($actor->id === $invitation->user_id, 403);

        $this->staff->attach($invitation->tenant, $invitation->user, $invitation->role);

        $invitation->update(['accepted_at' => now()]);

        return $invitation->user->fresh();
    }

    public function decline(TenantStaffInvitation $invitation, User $actor): void
    {
        abort_unless($invitation->isPending(), 410);
        abort_unless($actor->id === $invitation->user_id, 403);

        $invitation->update(['declined_at' => now()]);
    }

    public function revoke(TenantStaffInvitation $invitation, User $actor): void
    {
        abort_unless($invitation->isPending(), 404);

        $invitation->update([
            'declined_at' => now(),
            'revoked_by_user_id' => $actor->id,
        ]);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, TenantStaffInvitation> */
    public function listForTenant(Tenant $tenant, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return TenantStaffInvitation::query()
            ->with(['user', 'invitedBy', 'revokedBy'])
            ->where('tenant_id', $tenant->id)
            ->recent()
            ->latest()
            ->limit($limit)
            ->get();
    }
}
