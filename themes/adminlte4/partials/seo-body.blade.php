@php
    $isPublicPage = $isPublicPage ?? true;
    $gtmId = \App\Support\SeoPolicy::tagManagerId();
    $yandexMetrikaId = \App\Support\SeoPolicy::yandexMetrikaId();
@endphp
@if(\App\Support\SeoPolicy::shouldInject($isPublicPage) && $gtmId !== '')
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
@if(\App\Support\SeoPolicy::shouldInject($isPublicPage) && $yandexMetrikaId !== '')
    <noscript><div><img src="https://mc.yandex.ru/watch/{{ $yandexMetrikaId }}" style="position:absolute; left:-9999px;" alt=""></div></noscript>
@endif
