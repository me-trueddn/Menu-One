@extends('theme::layouts.auth')

@section('content')
<div class="locale-row">
    @include('theme::partials.auth.locale-switcher')
</div>

<div class="brand-name">
    <a href="{{ route('home') }}"><img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\SiteConfig::name() }}" style="height: {{ \App\Support\Branding::logoHeight() }}px;"></a>
</div>

<h1 class="login-title">{{ __('menu.two_factor_challenge_title') }}</h1>
<p class="login-subtitle">{{ __('menu.two_factor_challenge_hint') }}</p>

@if ($errors->any())
    <div class="alert alert-danger alert-login">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('two-factor.verify') }}">
    @csrf

    <div class="mb-4">
        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
               class="form-control text-center @error('code') is-invalid @enderror"
               placeholder="000000" autocomplete="one-time-code" required autofocus>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="d-flex gap-3 flex-wrap">
        <button type="submit" class="btn-login">{{ __('menu.verify') }}</button>
        <a href="{{ route('login') }}" class="btn-register">{{ __('menu.back_to_login') }}</a>
    </div>
</form>
@endsection
