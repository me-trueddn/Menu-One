@extends('theme::layouts.auth')

@section('content')
<div class="locale-row">
    @include('theme::partials.auth.locale-switcher')
</div>

<div class="brand-name">{{ strtoupper(\App\Support\SiteConfig::name()) }}</div>

<h1 class="login-title">{{ __('menu.forgot_password') }}</h1>
<p class="login-subtitle">{{ __('menu.forgot_password_hint') }}</p>

@if (session('status'))
    <div class="alert alert-success alert-login">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-login">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <input type="hidden" name="captcha_check" value="1">

    <div class="mb-3">
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="{{ __('menu.email') }}" required autofocus>
    </div>

    @include('theme::partials.auth.captcha', ['context' => \App\Support\CaptchaPolicy::CONTEXT_PASSWORD_RESET])

    <div class="d-flex gap-3 flex-wrap mt-3">
        <button type="submit" class="btn-login">{{ __('menu.send_reset_link') }}</button>
        <a href="{{ route('login') }}" class="btn-register">{{ __('menu.back_to_login') }}</a>
    </div>
</form>
@endsection
