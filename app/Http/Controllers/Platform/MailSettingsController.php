<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Mail\TestMailConfiguration;
use App\Models\Setting;
use App\Services\MailConfigService;
use App\Support\MailExceptionFormatter;
use App\Support\SettingPersistence;
use App\Support\SettingsDefaults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    /** @var list<string> */
    private const OPTIONAL_MAIL_KEYS = [
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_from_address',
        'mail_from_name',
    ];

    public function edit(): View
    {
        SettingsDefaults::ensureMailSettingsIfUnset();

        $mail = Setting::mergedGroup('mail', SettingsDefaults::mailDefaults());

        $settings = [
            'mail_mailer' => Setting::getFilled('mail_mailer', $mail['mail_mailer'] ?? 'log'),
            'mail_host' => Setting::getFilled('mail_host', $mail['mail_host'] ?? ''),
            'mail_port' => Setting::getFilled('mail_port', $mail['mail_port'] ?? '587'),
            'mail_username' => Setting::getFilled('mail_username', $mail['mail_username'] ?? ''),
            'mail_encryption' => Setting::getFilled('mail_encryption', $mail['mail_encryption'] ?? 'tls'),
            'mail_from_address' => Setting::getFilled('mail_from_address', $mail['mail_from_address'] ?? ''),
            'mail_from_name' => Setting::getFilled('mail_from_name', $mail['mail_from_name'] ?? ''),
            'mail_timeout_seconds' => Setting::getFilled('mail_timeout_seconds', $mail['mail_timeout_seconds'] ?? '15'),
            'has_password' => (bool) Setting::get('mail_password'),
        ];

        return view('theme::pages.platform.settings.mail', [
            'settings' => $settings,
            'mailProviders' => config('mail_providers', []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'mail_host' => $request->input('mail_host') ?: null,
            'mail_port' => $request->input('mail_port') ?: null,
            'mail_username' => $request->input('mail_username') ?: null,
            'mail_from_address' => $request->input('mail_from_address') ?: null,
            'mail_from_name' => $request->input('mail_from_name') ?: null,
            'mail_password' => $request->input('mail_password') ?: null,
        ]);

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
            'mail_timeout_seconds' => (string) $validated['mail_timeout_seconds'],
        ];

        foreach (self::OPTIONAL_MAIL_KEYS as $key) {
            $incoming = SettingPersistence::incomingOrSkip($key, $validated[$key] ?? null);

            if ($incoming !== null) {
                $pairs[$key] = is_int($incoming) ? (string) $incoming : $incoming;
            }
        }

        if (SettingPersistence::isPresent($validated['mail_encryption'] ?? null)) {
            $pairs['mail_encryption'] = $validated['mail_encryption'] === 'none'
                ? ''
                : $validated['mail_encryption'];
        }

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
