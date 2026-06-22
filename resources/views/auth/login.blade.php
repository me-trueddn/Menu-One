@extends('theme::layouts.auth')

@section('content')
<div class="locale-row">
    @include('theme::partials.auth.locale-switcher')
</div>

<div class="brand-name">
    <a href="{{ route('home') }}"><img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\SiteConfig::name() }}" style="height: {{ \App\Support\Branding::logoHeight() }}px;"></a>
</div>

<h1 class="login-title">{{ __('menu.login_welcome') }}</h1>
<p class="login-subtitle">
    <a href="{{ \App\Support\SiteConfig::mainSiteUrl() }}" target="_blank" rel="noopener">{{ parse_url(\App\Support\SiteConfig::mainSiteUrl(), PHP_URL_HOST) }}</a>
    {{ __('menu.login_portal') }}
</p>

@if (session('status'))
    <div class="alert alert-success alert-login">{{ session('status') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-login">{{ session('error') }}</div>
@endif

@if ($errors->any() && ! $errors->has('name') && ! $errors->has('phone'))
    <div class="alert alert-danger alert-login">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="hidden" name="captcha_check" value="1">

    <div class="mb-3">
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="{{ __('menu.email') }}" autocomplete="username" required autofocus>
    </div>

    <div class="mb-2">
        <div class="input-group">
            <input id="password" type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="{{ __('menu.password') }}" autocomplete="current-password" required>
            <button class="btn-eye" type="button" id="togglePwd" title="{{ __('menu.toggle_password') }}">
                <i class="fa fa-eye-slash" id="eyeIcon"></i>
            </button>
        </div>
    </div>

    @error('captcha')
        <div class="text-danger small mb-2">{{ $message }}</div>
    @enderror

    @include('theme::partials.auth.captcha', ['context' => \App\Support\CaptchaPolicy::CONTEXT_LOGIN])

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check mb-0">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember" style="font-size:13px;">{{ __('menu.remember_me') }}</label>
        </div>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="forgot-link">{{ __('menu.forgot_password') }}</a>
        @endif
    </div>

    <div class="d-flex gap-3 flex-wrap">
        <button type="submit" class="btn-login">{{ __('menu.login') }}</button>
        @if (Route::has('register') && \App\Support\CaptchaPolicy::registrationEnabled())
            <button type="button" class="btn-register" id="openRegisterPanel">{{ __('menu.register_free') }}</button>
        @endif
    </div>
</form>

@include('theme::partials.auth.oauth-buttons', ['intent' => 'login'])

<hr class="divider">

<p class="contact-title">{{ __('menu.contact_title') }}</p>
@if($phone = \App\Support\SiteConfig::contactPhone())
    <div class="contact-item"><i class="fa fa-phone"></i><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></div>
@endif
@if($email = \App\Support\SiteConfig::supportEmail())
    <div class="contact-item"><i class="fa fa-envelope"></i><a href="mailto:{{ $email }}">{{ $email }}</a></div>
@endif
<div class="contact-item">
    <i class="fa fa-globe"></i>
    <a href="{{ \App\Support\SiteConfig::mainSiteUrl() }}" target="_blank" rel="noopener">{{ parse_url(\App\Support\SiteConfig::mainSiteUrl(), PHP_URL_HOST) }}</a>
</div>
@endsection

@push('scripts')
@if ($errors->any() && ($errors->has('name') || $errors->has('phone')))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('registerOverlay');
    if (!overlay) return;
    overlay.hidden = false;
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('register-open');
});
</script>
@elseif(request()->boolean('register'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('registerOverlay');
    if (!overlay) return;
    overlay.hidden = false;
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('register-open');
});
</script>
@endif
@endpush
