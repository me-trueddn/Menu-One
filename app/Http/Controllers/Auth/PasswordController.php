<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordLifecycleService;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(private PasswordLifecycleService $passwordLifecycle) {}

    public function update(Request $request): RedirectResponse
    {
        $this->passwordLifecycle->assertCanChange($request->user());

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', SecurityPolicy::passwordRules(), 'confirmed'],
        ]);

        $user = $request->user();
        $this->passwordLifecycle->assertNotInHistory($user, $validated['password']);

        $oldHash = $user->getRawOriginal('password');
        $user->update(['password' => $validated['password']]);
        $this->passwordLifecycle->recordChange($user->fresh(), $oldHash);

        return redirect()
            ->route('profile.edit', ['tab' => 'security'])
            ->with('success', __('menu.password_updated'));
    }
}
