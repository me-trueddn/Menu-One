<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ \App\Support\Branding::faviconUrl() }}">
    @vite(['resources/css/themes/adminlte4.css', 'resources/js/themes/adminlte4.js'])
    @include('theme::partials.branding-styles')
    @stack('styles')
</head>
<body class="bg-body-tertiary">
@php($currentUser = auth()->user())
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body border-bottom">
        <div class="container-fluid">
            <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center py-0">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ \App\Support\SiteConfig::name() }}" class="navbar-brand-logo"
                     style="height: {{ \App\Support\Branding::sidebarLogoHeight() }}px; width: auto; max-width: 12rem; object-fit: contain;">
            </a>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                @if(!empty($canSwitchTenants) && $canSwitchTenants)
                    <li class="nav-item">@include('theme::partials.tenant-switcher')</li>
                @endif
                <li class="nav-item">@include('theme::partials.locale-switcher')</li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ $currentUser->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item active" href="{{ route('profile.edit') }}">{{ __('menu.profile') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('menu.logout') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <main class="app-main">
        <div class="app-content py-4">
            <div class="container-fluid" style="max-width: 960px;">
                @include('theme::partials.alerts')
                @yield('content')
            </div>
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
