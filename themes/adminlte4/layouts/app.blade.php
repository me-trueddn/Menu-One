<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="login-url" content="{{ route('login') }}">
    @endauth
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ \App\Support\Branding::faviconUrl() }}">
    @vite(['resources/css/themes/adminlte4.css', 'resources/js/themes/adminlte4.js'])
    @stack('styles')
    @auth
    @include('theme::partials.branding-styles')
    @endauth
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
@php($currentUser = auth()->user())
@include('theme::partials.impersonation-banner')
@include('theme::partials.support-banner')
<div class="app-wrapper">
    @include('theme::partials.navbar')
    @include('theme::partials.sidebar')

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="mb-0">@yield('page-title', config('app.name'))</h3>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-end d-flex gap-2 flex-wrap">
                            @yield('page-actions')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                @include('theme::partials.alerts')
                @yield('content')
            </div>
        </div>
    </main>

    @include('theme::partials.footer')
</div>
@stack('scripts')
</body>
</html>
