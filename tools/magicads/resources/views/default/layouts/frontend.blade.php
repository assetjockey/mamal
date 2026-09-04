{{--
    Master layout — white / black / indigo editorial.
    Scoped under `.landing` so no styles leak into the authenticated dashboard.
--}}
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), config('app.rtl_locales', ['ar', 'he', 'fa', 'ur'])) ? 'rtl' : 'ltr' }}"
    class="scroll-smooth"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#FFFFFF">

    {{-- Performance hints --}}
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="x-dns-prefetch-control" content="on">

    @include('partials.favicon')

    {{--
        Font loading strategy:
        - dns-prefetch as a fallback for older browsers that don't honor preconnect
        - preconnect opens the TCP/TLS handshake to fonts.bunny.net before the CSS request
        - &display=swap tells the browser to render fallback text immediately, then swap
          to Inter when the woff2 arrives — eliminates FOIT for the H1 LCP text
        - rel="preload" as="style" elevates the font CSS to highest priority
        - rel="preload" as="font" warms the three Inter weights actually used by the
          landing page (400 body, 700 headings, 800 hero display) so the H1 paints in
          its final font without a flash of fallback
    --}}
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link
        rel="preload"
        as="style"
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:400,500&display=swap"
    >
    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:400,500&display=swap"
        rel="stylesheet"
    >
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="https://fonts.bunny.net/inter/files/inter-latin-400-normal.woff2">
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="https://fonts.bunny.net/inter/files/inter-latin-700-normal.woff2">
    <link rel="preload" as="font" type="font/woff2" crossorigin
          href="https://fonts.bunny.net/inter/files/inter-latin-800-normal.woff2">

    @filamentStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.seo-meta')
    @include('partials.structured-data')
    @include('partials.adsense-head')
    @include('partials.google-analytics')

    {{--
        Speculation Rules (Chrome 121+) — prerender the registration page when the
        user hovers any link to it. The browser ignores this script in unsupported
        engines, so it's safe to ship unconditionally. `eagerness: moderate`
        triggers on hover/touchstart, not on every visible link, so we don't waste
        bandwidth or render budget.
    --}}
    <script type="speculationrules">
    {
        "prerender": [
            {
                "where": {
                    "and": [
                        { "href_matches": "/register" },
                        { "not": { "href_matches": "/admin/*" } }
                    ]
                },
                "eagerness": "moderate"
            }
        ],
        "prefetch": [
            {
                "where": { "href_matches": "/login" },
                "eagerness": "moderate"
            }
        ]
    }
    </script>

    @yield('metadata')
    @yield('css')

    <style>
        html, body { background: #FFFFFF; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .landing h1, .landing h2, .landing .l-display {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        /* Emphasis word style — indigo accent, same Inter family, tighter tracking */
        .landing .l-accent {
            color: var(--l-indigo);
            font-weight: 800;
        }
        .landing .l-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        }
    </style>
</head>
<body class="landing min-h-screen antialiased @yield('body_class')">
    <a
        href="#main-content"
        class="sr-only focus-visible:not-sr-only focus-visible:fixed focus-visible:top-3 focus-visible:left-3 focus-visible:z-[60] focus-visible:rounded-lg focus-visible:bg-black focus-visible:px-4 focus-visible:py-2 focus-visible:text-white focus-visible:shadow-lg"
    >
        {{ __('Skip to main content') }}
    </a>

    <header role="banner">
        @yield('menu')
    </header>

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer role="contentinfo">
        @yield('footer')
    </footer>

    @includeWhen(($cookieSettings?->enable_cookies ?? false), 'frontend.cookie-banner.section')

    @yield('js')
</body>
</html>
