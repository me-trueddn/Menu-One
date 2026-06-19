<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomVerifyEmailController extends Controller
{
    public function __construct(private EmailVerificationService $verification) {}

    public function show(Request $request): View
    {
        return view('theme::pages.auth.verify-email', [
            'email' => $request->user()?->email,
        ]);
    }

    public function verify(string $token): RedirectResponse
    {
        $user = $this->verification->verify($token);

        if (! $user) {
            return redirect()->route('login')->with('error', __('menu.verification_link_invalid'));
        }

        return redirect()->route('login')->with('success', __('menu.email_verified_success'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        $this->verification->issueAndSend($user);

        return back()->with('success', __('menu.verification_link_sent'));
    }
}
