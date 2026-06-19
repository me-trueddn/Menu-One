<a href="{{ route('home') }}" class="brand-link d-flex align-items-center gap-2 text-decoration-none">
    <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ config('app.name') }}" style="height: {{ $height ?? 28 }}px;">
</a>
