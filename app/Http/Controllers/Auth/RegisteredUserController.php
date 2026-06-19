<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\UserLoginTokenService;
use App\Support\CaptchaPolicy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function __construct(private UserLoginTokenService $loginTokens) {}

    public function create(): RedirectResponse|View
    {
        if (! CaptchaPolicy::registrationEnabled()) {
            return redirect()->route('login')->with('error', __('menu.registration_disabled'));
        }

        return redirect()->route('login', ['register' => 1]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'tenant_id' => null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'password_changed_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();
        $token = $this->loginTokens->issue($user, $request);
        $request->session()->put('user_access_token', $token);

        return redirect(route('dashboard', absolute: false));
    }
}
