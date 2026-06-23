<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('seo_title', \App\Support\SeoPolicy::metaTitle(config('app.name').' - '.__('menu.login')))</title>
    <link rel="icon" href="{{ \App\Support\Branding::faviconUrl() }}">
    @include('theme::partials.seo-head', ['isPublicPage' => true])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @if(\App\Support\CaptchaPolicy::configured())
        @if(\App\Support\CaptchaPolicy::provider() === 'google')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @elseif(\App\Support\CaptchaPolicy::provider() === 'turnstile')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endif
    @endif
    @vite(['resources/css/themes/login.css', 'resources/js/themes/login.js'])
</head>
<body>
@include('theme::partials.seo-body', ['isPublicPage' => true])

<div class="login-wrapper">
    @include('theme::partials.auth.login-slider')

    <div class="login-right">
        @yield('content')
    </div>
</div>

@if(\App\Support\CaptchaPolicy::registrationEnabled())
    @include('theme::partials.auth.register-panel')
@endif

@stack('scripts')
</body>
</html>
