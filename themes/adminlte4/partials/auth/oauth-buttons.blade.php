@if(count($providers = \App\Support\OAuthPolicy::enabledProviders()) > 0)
    <div class="oauth-divider">{{ __('menu.or_continue_with') }}</div>
    <div class="oauth-buttons">
        @if(in_array('google', $providers, true) && ($intent !== 'register' || \App\Support\OAuthPolicy::allowRegister()) && ($intent !== 'login' || \App\Support\OAuthPolicy::allowLogin()))
            <a href="{{ route('oauth.redirect', ['provider' => 'google', 'intent' => $intent ?? 'login']) }}" class="oauth-btn oauth-btn-google">
                <i class="fab fa-google"></i> Google
            </a>
        @endif
        @if(in_array('microsoft', $providers, true) && ($intent !== 'register' || \App\Support\OAuthPolicy::allowRegister()) && ($intent !== 'login' || \App\Support\OAuthPolicy::allowLogin()))
            <a href="{{ route('oauth.redirect', ['provider' => 'microsoft', 'intent' => $intent ?? 'login']) }}" class="oauth-btn oauth-btn-microsoft">
                <i class="fab fa-microsoft"></i> Microsoft
            </a>
        @endif
    </div>
@endif
