<?php

namespace App\Services;

use App\Exceptions\PlatformStaffRegistrationBlockedException;
use App\Models\User;
use App\Support\OAuthPolicy;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Spatie\Permission\Models\Role;

class SocialAuthService
{
    public static function applyConfig(): void
    {
        Config::set('services.google', [
            'client_id' => OAuthPolicy::clientId('google'),
            'client_secret' => OAuthPolicy::clientSecret('google'),
            'redirect' => OAuthPolicy::redirectUrl('google'),
        ]);

        Config::set('services.azure', [
            'client_id' => OAuthPolicy::clientId('microsoft'),
            'client_secret' => OAuthPolicy::clientSecret('microsoft'),
            'redirect' => OAuthPolicy::redirectUrl('microsoft'),
            'tenant' => 'common',
        ]);
    }

    public static function findOrCreateCustomer(string $provider, SocialiteUser $socialUser): User
    {
        $providerId = (string) $socialUser->getId();

        $user = User::query()
            ->where('oauth_provider', $provider)
            ->where('oauth_provider_id', $providerId)
            ->first();

        if ($user) {
            static::ensureOAuthEmailVerified($user);

            return $user;
        }

        $email = Str::lower((string) $socialUser->getEmail());

        if ($email !== '') {
            $existing = User::query()
                ->whereNull('tenant_id')
                ->where('email', $email)
                ->first();

            if ($existing) {
                if ($existing->isPlatformStaffMember()) {
                    throw new PlatformStaffRegistrationBlockedException;
                }

                $existing->update([
                    'oauth_provider' => $provider,
                    'oauth_provider_id' => $providerId,
                ]);

                CafeStaffService::ensureCustomerRole($existing);
                static::ensureOAuthEmailVerified($existing);

                return $existing;
            }
        }

        $user = User::create([
            'tenant_id' => null,
            'name' => $socialUser->getName() ?: ($socialUser->getNickname() ?: 'User'),
            'email' => $email !== '' ? $email : $provider.'_'.$providerId.'@oauth.local',
            'phone' => null,
            'password' => Hash::make(Str::password(32)),
            'password_changed_at' => now(),
            'is_active' => true,
            'email_verified_at' => now(),
            'oauth_provider' => $provider,
            'oauth_provider_id' => $providerId,
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']));

        return $user;
    }

    public static function ensureOAuthEmailVerified(User $user): void
    {
        if (! $user->registeredViaOAuth()) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->forceFill(['email_verified_at' => now()])->save();
    }
}
