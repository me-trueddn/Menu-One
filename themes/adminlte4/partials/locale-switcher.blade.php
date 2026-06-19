<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-globe"></i> {{ config('locale.names.'.app()->getLocale(), app()->getLocale()) }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach(config('locale.available', []) as $code)
            <li>
                <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                   href="{{ route('locale.switch', $code) }}">
                    {{ config('locale.names.'.$code, $code) }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
