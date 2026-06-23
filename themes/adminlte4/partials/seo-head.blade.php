@php
    $isPublicPage = $isPublicPage ?? true;
    $pageTitle = $pageTitle ?? null;
    $pageDescription = $pageDescription ?? null;
    $inject = \App\Support\SeoPolicy::shouldInject($isPublicPage);
    $metaTitle = $pageTitle ?: \App\Support\SeoPolicy::metaTitle();
    $metaDescription = $pageDescription ?: \App\Support\SeoPolicy::metaDescription();
    $canonical = \App\Support\SeoPolicy::canonicalUrl();
    $ogImage = \App\Support\SeoPolicy::ogImageUrl();
    $gtmId = \App\Support\SeoPolicy::tagManagerId();
    $gaId = \App\Support\SeoPolicy::analyticsId();
    $adsId = \App\Support\SeoPolicy::adsId();
    $gscVerification = \App\Support\SeoPolicy::searchConsoleVerification();
    $yandexVerification = \App\Support\SeoPolicy::yandexWebmasterVerification();
    $yandexMetrikaId = \App\Support\SeoPolicy::yandexMetrikaId();
    $schema = \App\Support\SeoPolicy::organizationSchema();
@endphp
@if($inject)
    @if($gscVerification !== '')
        <meta name="google-site-verification" content="{{ $gscVerification }}">
    @endif

    @if($yandexVerification !== '')
        <meta name="yandex-verification" content="{{ $yandexVerification }}">
    @endif

    @if(! $isPublicPage)
        <meta name="robots" content="noindex, nofollow">
    @elseif(! \App\Support\SeoPolicy::indexPublic())
        <meta name="robots" content="noindex, nofollow">
    @endif

    @if($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if(\App\Support\SeoPolicy::metaKeywords() !== '')
        <meta name="keywords" content="{{ \App\Support\SeoPolicy::metaKeywords() }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @if($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif

    @if($gtmId !== '')
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    @elseif($gaId !== '' || $adsId !== '')
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId !== '' ? $gaId : $adsId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            @if($gaId !== '')
            gtag('config', @json($gaId));
            @endif
            @if($adsId !== '')
            gtag('config', @json($adsId));
            @endif
        </script>
    @endif

    @if($yandexMetrikaId !== '')
        <script>
            (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
            (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
            ym({{ $yandexMetrikaId }}, "init", {
                clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:true
            });
        </script>
    @endif
@elseif(! $isPublicPage)
    <meta name="robots" content="noindex, nofollow">
@endif
