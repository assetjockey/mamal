{{--
    SEO / social metadata for the auth pages (login & registration).

    Reads the admin-managed SEO Manager values for whichever auth page is
    being rendered and emits <title> + meta tags. Falls back to the site name
    and a sensible default when a field is empty, so the head is never blank.

    Pulls the row directly (these layouts have no `layouts.frontend` composer).
    Guarded against fresh installs where the table may not exist yet.
--}}
@php
    $seo = (\Illuminate\Support\Facades\Schema::hasTable('seo_settings'))
        ? \App\Models\SeoSetting::query()->first()
        : null;

    $general = (\Illuminate\Support\Facades\Schema::hasTable('general_settings'))
        ? \App\Models\GeneralSetting::query()->first()
        : null;

    $siteName = $general?->site_name ?? $general?->name ?? config('app.name', 'AI Ad Studio');

    $isRegister = request()->routeIs('register');

    if ($isRegister) {
        $metaTitle       = trim((string) ($seo?->register_title ?? ''));
        $metaDescription = trim((string) ($seo?->register_description ?? ''));
        $metaKeywords    = trim((string) ($seo?->register_keywords ?? ''));
        $metaAuthor      = trim((string) ($seo?->register_author ?? ''));
        $metaCanonical   = trim((string) ($seo?->register_url ?? ''));
        $fallbackTitle   = $siteName . ' — ' . __('Create your account');
    } else {
        $metaTitle       = trim((string) ($seo?->login_title ?? ''));
        $metaDescription = trim((string) ($seo?->login_description ?? ''));
        $metaKeywords    = trim((string) ($seo?->login_keywords ?? ''));
        $metaAuthor      = trim((string) ($seo?->login_author ?? ''));
        $metaCanonical   = trim((string) ($seo?->login_url ?? ''));
        $fallbackTitle   = $siteName . ' — ' . __('Sign in');
    }

    if ($metaTitle === '')       { $metaTitle = $fallbackTitle; }
    if ($metaCanonical === '')   { $metaCanonical = url()->current(); }
@endphp

<title>{{ $metaTitle }}</title>
@if ($metaDescription !== '')
    <meta name="description" content="{{ $metaDescription }}">
@endif
@if ($metaKeywords !== '')
    <meta name="keywords" content="{{ $metaKeywords }}">
@endif
@if ($metaAuthor !== '')
    <meta name="author" content="{{ $metaAuthor }}">
@endif

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $metaCanonical }}">
<meta property="og:title" content="{{ $metaTitle }}">
@if ($metaDescription !== '')
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endif
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $metaTitle }}">

<link rel="canonical" href="{{ $metaCanonical }}">
