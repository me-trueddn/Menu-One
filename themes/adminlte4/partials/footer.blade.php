<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Menu-One v{{ config('app.version', '1.0.0') }}
        @if((int) config('app.build', 0) > 0)
            · Build {{ config('app.build') }}
        @endif
    </div>
    <strong>Menu-One</strong> Cafe Adisyon Sistemi
</footer>
