<?php

namespace App\Http\Controllers;

use App\Models\TenantStaffInvitation;
use App\Services\StaffInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffInvitationController extends Controller
{
    public function __construct(private StaffInvitationService $invitations) {}

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $invitation = $this->invitations->findPending($token);

        if (! $invitation) {
            return view('theme::pages.staff.invitation-status', [
                'status' => 'invalid',
            ]);
        }

        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('staff.invitation.show', ['token' => $token]));
        }

        if ($user->id !== $invitation->user_id) {
            return view('theme::pages.staff.invitation-status', [
                'status' => 'wrong_account',
                'invitation' => $invitation,
            ]);
        }

        return view('theme::pages.staff.invitation', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = TenantStaffInvitation::query()
            ->with(['tenant', 'user'])
            ->where('token', $token)
            ->firstOrFail();

        $this->invitations->accept($invitation, $this->authUser());

        return redirect()
            ->route('dashboard')
            ->with('success', __('menu.staff_invitation_accepted', ['cafe' => $invitation->tenant->name]));
    }

    public function decline(Request $request, string $token): RedirectResponse
    {
        $invitation = TenantStaffInvitation::query()
            ->with(['tenant'])
            ->where('token', $token)
            ->firstOrFail();

        $this->invitations->decline($invitation, $this->authUser());

        return redirect()
            ->route('profile.edit')
            ->with('success', __('menu.staff_invitation_declined'));
    }
}
