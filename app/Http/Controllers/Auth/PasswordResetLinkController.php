<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\ValidCaptcha;
use App\Services\MailConfigService;
use App\Support\CaptchaPolicy;
use App\Support\MailExceptionFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'email' => ['required', 'email'],
        ];

        if (CaptchaPolicy::requiredFor(CaptchaPolicy::CONTEXT_PASSWORD_RESET)) {
            $rules['captcha_check'] = [new ValidCaptcha(CaptchaPolicy::CONTEXT_PASSWORD_RESET)];
        }

        $request->validate($rules);

        $status = null;

        try {
            MailConfigService::runWithTimeout(function () use ($request, &$status) {
                $status = Password::sendResetLink(
                    $request->only('email')
                );
            });
        } catch (\RuntimeException $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => [$exception->getMessage()],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'email' => [MailExceptionFormatter::toUserMessage($exception)],
            ]);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
