{{--
    SEO / social metadata partial.

    Validates Requirements 3.1–3.5: every field resolves with the priority
        seoSettings  →  generalSettings  →  static default
    and the partial never emits `content=""` — it always falls through to a
    non-empty string.

    All user-facing literals pass through `__()` so translators can localize.
    Variables are provided by the `layouts.frontend` View::composer in
    AppServiceProvider:  $seoSettings, $generalSettings.
--}}
@php
    $defaultTitle = config('app.name', 'AI Ad Studio');
    $defaultTagline = __('AI-powered image, video, and ad copy for every channel.');

    $siteName = $generalSettings?->site_name
        ?? $generalSettings?->name
        ?? $defaultTitle;

    $pageTitle = trim((string) ($seoSettings?->home_title ?? ''));
    if ($pageTitle === '') {
        $pageTitle = $siteName . ' — ' . $defaultTagline;
    }

    $pageDescription = trim((string) ($seoSettings?->home_description ?? ''));
    if ($pageDescription === '') {
        $pageDescription = $defaultTagline;
    }

    $pageKeywords = trim((string) ($seoSettings?->home_keywords ?? ''));
    $pageAuthor   = trim((string) ($seoSettings?->home_author ?? ''));

    $canonicalUrl = trim((string) ($seoSettings?->home_url ?? ''));
    if ($canonicalUrl === '') {
        $canonicalUrl = url('/');
    }

    // OG / Twitter image: prefer a general logo, then the favicon.
    $socialImage = $generalSettings?->og_image
        ?? $generalSettings?->logo_frontend
        ?? null;

    if (filled($socialImage)) {
        // Accept both absolute URLs and relative upload paths.
        $socialImageUrl = str_starts_with((string) $socialImage, 'http')
            ? $socialImage
            : asset($socialImage);
    } else {
        $socialImageUrl = asset('favicon.svg');
    }
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
@if ($pageKeywords !== '')
    <meta name="keywords" content="{{ $pageKeywords }}">
@endif
@if ($pageAuthor !== '')
    <meta name="author" content="{{ $pageAuthor }}">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ $socialImageUrl }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $pageTitle }}">
<meta property="og:locale" content="{{ str_replace('-', '_', str_replace('_', '-', app()->getLocale())) }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $socialImageUrl }}">
<meta name="twitter:image:alt" content="{{ $pageTitle }}">

<link rel="canonical" href="{{ $canonicalUrl }}">
