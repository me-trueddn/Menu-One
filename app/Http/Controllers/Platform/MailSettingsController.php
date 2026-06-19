<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\TestMailConfiguration;
use App\Models\Setting;
use App\Services\MailConfigService;
use App\Support\MailExceptionFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'mail_mailer' => Setting::get('mail_mailer', env('MAIL_MAILER', 'log')),
            'mail_host' => Setting::get('mail_host', env('MAIL_HOST', '')),
            'mail_port' => Setting::get('mail_port', env('MAIL_PORT', '587')),
            'mail_username' => Setting::get('mail_username', env('MAIL_USERNAME', '')),
            'mail_encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', '')),
            'mail_from_name' => Setting::get('mail_from_name', env('MAIL_FROM_NAME', '')),
            'mail_timeout_seconds' => Setting::get('mail_timeout_seconds', '15'),
            'has_password' => (bool) Setting::get('mail_password'),
        ];

        return view('theme::pages.platform.settings.mail', [
            'settings' => $settings,
            'mailProviders' => config('mail_providers', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log,sendmail'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_timeout_seconds' => ['required', 'integer', 'min:5', 'max:120'],
        ]);

        $pairs = [
            'mail_mailer' => $validated['mail_mailer'],
            'mail_host' => $validated['mail_host'] ?? '',
            'mail_port' => (string) ($validated['mail_port'] ?? 587),
            'mail_username' => $validated['mail_username'] ?? '',
            'mail_encryption' => $validated['mail_encryption'] === 'none' ? '' : ($validated['mail_encryption'] ?? ''),
            'mail_from_address' => $validated['mail_from_address'] ?? '',
            'mail_from_name' => $validated['mail_from_name'] ?? '',
            'mail_timeout_seconds' => (string) $validated['mail_timeout_seconds'],
        ];

        if (! empty($validated['mail_password'])) {
            $pairs['mail_password'] = Crypt::encryptString($validated['mail_password']);
        }

        Setting::setMany($pairs, 'mail');

        return redirect()
            ->route('platform.settings.mail')
            ->with('success', __('menu.messages.updated'));
    }

    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
            'mail_mailer' => ['required', 'in:smtp,log,sendmail'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            MailConfigService::runWithTimeout(function () use ($validated) {
                Mail::to($validated['test_email'])->send(
                    new TestMailConfiguration($this->authUser())
                );
            }, $validated);
        } catch (\RuntimeException $exception) {
            report($exception);

            return redirect()
                ->route('platform.settings.mail')
                ->withInput()
                ->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('platform.settings.mail')
                ->withInput()
                ->with('error', MailExceptionFormatter::toUserMessage($exception));
        }

        return redirect()
            ->route('platform.settings.mail')
            ->with('success', __('menu.mail_test_sent', ['email' => $validated['test_email']]));
    }
}
