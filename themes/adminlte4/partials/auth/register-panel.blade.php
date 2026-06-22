@if(\App\Support\CaptchaPolicy::registrationEnabled())
<div class="register-overlay" id="registerOverlay" aria-hidden="true" hidden>
    <div class="register-panel" id="registerPanel" role="dialog" aria-modal="true" aria-labelledby="registerTitle">
        <button type="button" class="register-close" id="registerClose" aria-label="{{ __('menu.close') }}">
            <i class="fa fa-times"></i>
        </button>

        <div class="text-center mb-3">
            <a href="{{ route('home') }}" class="text-decoration-none d-inline-block">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\SiteConfig::name() }}"
                     style="height: {{ \App\Support\Branding::registerLogoHeight() }}px; width: auto; max-width: 100%; object-fit: contain;">
            </a>
        </div>
        <h2 class="login-title" id="registerTitle">{{ __('menu.register_welcome') }}</h2>
        <p class="login-subtitle">{{ __('menu.register_subtitle') }}</p>

        @if ($errors->any())
            <div class="alert alert-danger alert-login">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            <input type="hidden" name="captcha_check" value="1">

            <div class="mb-3">
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="{{ __('menu.full_name') }}" required autocomplete="name">
            </div>

            <div class="mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="{{ __('menu.email') }}" required autocomplete="email">
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password" id="registerPassword"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="{{ __('menu.password') }}" required autocomplete="new-password">
                    <button class="btn-eye" type="button" data-toggle-target="registerPassword" title="{{ __('menu.toggle_password') }}">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="registerPasswordConfirm"
                           class="form-control" placeholder="{{ __('menu.password_confirm') }}" required autocomplete="new-password">
                    <button class="btn-eye" type="button" data-toggle-target="registerPasswordConfirm" title="{{ __('menu.toggle_password') }}">
                        <i class="fa fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="mb-2">
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" placeholder="{{ __('menu.phone') }}" required autocomplete="tel">
                <div class="form-text register-phone-hint">{{ __('menu.register_phone_hint') }}</div>
            </div>

            @include('theme::partials.auth.captcha', ['context' => \App\Support\CaptchaPolicy::CONTEXT_REGISTER])

            <button type="submit" class="btn-login w-100 mt-3">{{ __('menu.register') }}</button>
        </form>

        @include('theme::partials.auth.oauth-buttons', ['intent' => 'register'])
    </div>
</div>
@endif
