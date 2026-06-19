<div class="locale-pills">
    @foreach(config('locale.available', []) as $code)
        <a href="{{ route('locale.switch', $code) }}"
           class="locale-pill {{ app()->getLocale() === $code ? 'active' : '' }}"
           title="{{ config('locale.names.'.$code, $code) }}">
            {{ strtoupper($code) }}
        </a>
    @endforeach
</div>
