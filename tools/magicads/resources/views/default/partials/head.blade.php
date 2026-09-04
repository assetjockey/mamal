<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

{{-- Login & registration pages emit their own SEO <title>/meta via
     partials.auth-seo-meta (driven by the admin SEO Manager). Everywhere
     else falls back to the generic title. --}}
@unless (request()->routeIs('login') || request()->routeIs('register'))
    <title>{{ $title ?? config('app.name') }}</title>
@endunless

@include('partials.favicon')

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

{{-- FontAwesome 6 (self-hosted under public/assets/icons/fontawesome).
     The glyph mappings + base `.fa` styling live in fontawesome.min.css; each
     weight file adds its own @font-face + weight rule. Load the core first,
     then the styles actually used across the dashboard (solid, regular, brands).
     Without the core file the per-weight CSS renders nothing. --}}
<link rel="stylesheet" href="{{ asset('assets/icons/fontawesome/css/fontawesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/icons/fontawesome/css/solid.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/icons/fontawesome/css/regular.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/icons/fontawesome/css/brands.min.css') }}">

@filamentStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
