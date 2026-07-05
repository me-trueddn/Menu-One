<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $tenant?->name ?? config('app.name'))</title>
    <link rel="icon" href="{{ $logoUrl ?? \App\Support\Branding::faviconUrl() }}">
    @vite(['resources/css/themes/adminlte4.css'])
    @stack('styles')
</head>
<body class="digital-menu-body bg-body">
@yield('content')
@stack('scripts')
</body>
</html>
