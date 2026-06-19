@extends('theme::layouts.auth')

@section('content')
<div class="login-form-container">
    <div class="mb-4 text-center">
        <a href="{{ route('home') }}"><img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ config('app.name') }}" style="height: 42px;"></a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">{{ __('menu.verify_email_title') }}</h4>
            <p class="text-muted">{{ __('menu.verify_email_hint', ['email' => $email ?? '']) }}</p>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-primary">{{ __('menu.resend_verification') }}</button>
            </form>

            <a href="{{ route('login') }}" class="btn btn-link mt-3">{{ __('menu.back_to_login') }}</a>
        </div>
    </div>
</div>
@endsection
