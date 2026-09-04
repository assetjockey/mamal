<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
@php
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $gaEnabled = (string) $options->get('google_analytics_status', '0') === '1';
    $gaMeasurementId = trim((string) $options->get('google_analytics_measurement_id', ''));
    $gaTrackGuest = (string) $options->get('google_analytics_track_guest', '1') === '1';
    $siteFavicon = url((string) $options->get('website_favicon', 'public/img/favicon.png'));
    $siteTitle = trim((string) $options->get('website_title', ''));
    $siteTitle = $siteTitle !== '' ? $siteTitle : 'LinkQR Pro';
    $cardRadius = theme_setting('card_radius', 'guest', 18);
    $inputRadius = theme_setting('input_radius', 'guest', 14);
    $buttonRadius = theme_setting('button_radius', 'guest', 14);
    $pageMaxWidth = theme_setting('page_max_width', 'guest', '86rem');
    $sectionSpacing = theme_setting('section_spacing', 'guest', '5rem');
@endphp

<title>{{ filled($title ?? null) ? $title.' - '.$siteTitle : $siteTitle }}</title>

@if ($gaEnabled && $gaTrackGuest && $gaMeasurementId !== '')
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaMeasurementId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', @js($gaMeasurementId));
    </script>
@endif

@include(theme_view('partials.embed-code-head', 'guest'))

<link rel="icon" href="{{ $siteFavicon }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $siteFavicon }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|instrument-sans:400,500,600,700|plus-jakarta-sans:400,500,600,700,800|manrope:400,500,600,700,800|outfit:400,500,600,700,800" rel="stylesheet" />
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/fontawesome.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/brands.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/light.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/regular.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/fontawesome/css/solid.css') }}">
<link rel="stylesheet" href="{{ theme_shared_asset('plugins/flags/flag-icon.css') }}">

<style>
    :root,
    html[data-theme-resolved='light'],
    html[data-theme-resolved='dark'] {
        --theme-accent: {{ theme_setting('accent_color', 'guest', '#2454E8') }};
        --theme-accent-rgb: {{ theme_accent_rgb('guest') }};
        --theme-body-bg: {{ theme_setting('body_bg_color', 'guest', '#F7FAFC') }};
        --theme-body-bg-rgb: {{ theme_color_rgb('body_bg_color', 'guest', '#F7FAFC') }};
        --theme-surface-bg: {{ theme_setting('surface_bg_color', 'guest', '#FFFFFF') }};
        --theme-surface-bg-rgb: {{ theme_color_rgb('surface_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-bg: {{ theme_setting('header_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-bg-rgb: {{ theme_color_rgb('header_bg_color', 'guest', '#FFFFFF') }};
        --theme-header-text-color: {{ theme_setting('header_text_color', 'guest', '#0F172A') }};
        --theme-link-color: {{ theme_setting('link_color', 'guest', '#2454E8') }};
        --theme-link-hover-color: {{ theme_setting('link_hover_color', 'guest', '#0F766E') }};
        --theme-border-color: {{ theme_setting('border_color', 'guest', '#DCE6F3') }};
        --theme-border-color-rgb: {{ theme_color_rgb('border_color', 'guest', '#DCE6F3') }};
        --theme-muted-text-color: {{ theme_setting('muted_text_color', 'guest', '#64748B') }};
        --theme-success-color: {{ theme_setting('success_color', 'guest', '#059669') }};
        --theme-warning-color: {{ theme_setting('warning_color', 'guest', '#D97706') }};
        --theme-danger-color: {{ theme_setting('danger_color', 'guest', '#DC2626') }};
        --theme-card-radius: {{ is_numeric($cardRadius) ? $cardRadius.'px' : $cardRadius }};
        --theme-input-radius: {{ is_numeric($inputRadius) ? $inputRadius.'px' : $inputRadius }};
        --theme-button-radius: {{ is_numeric($buttonRadius) ? $buttonRadius.'px' : $buttonRadius }};
        --theme-page-max-width: {{ $pageMaxWidth }};
        --theme-section-spacing: {{ $sectionSpacing }};
        --theme-font-sans: {!! theme_font_stack('guest') !!};
    }

    html {
        background: #f7fafc;
        color: #0f172a;
        max-width: 100%;
        overflow-x: clip;
    }

    body {
        max-width: 100%;
        overflow-x: clip;
        background:
            linear-gradient(rgba(36, 84, 232, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36, 84, 232, 0.035) 1px, transparent 1px),
            linear-gradient(180deg, #f8fbff 0%, #f7fafc 42%, #eef5fb 100%);
        background-size: 36px 36px, 36px 36px, auto;
    }

    .linkqr-shell {
        width: min(calc(100% - 1.5rem), var(--theme-page-max-width));
        margin-left: auto;
        margin-right: auto;
    }

    @media (min-width: 1024px) {
        .linkqr-shell {
            width: min(calc(100% - 3rem), var(--theme-page-max-width));
        }
    }

    .linkqr-section {
        padding-top: var(--theme-section-spacing);
        padding-bottom: var(--theme-section-spacing);
    }

    .linkqr-hero-stage {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.88);
        background:
            linear-gradient(135deg, rgba(36,84,232,0.11) 0%, rgba(255,255,255,0.88) 34%, rgba(20,184,166,0.12) 68%, rgba(245,158,11,0.14) 100%),
            linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,251,255,0.94));
        box-shadow: 0 38px 110px -78px rgba(15,23,42,0.5);
    }

    .linkqr-hero-stage::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(rgba(36,84,232,0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.05) 1px, transparent 1px);
        background-size: 28px 28px;
        mask-image: linear-gradient(180deg, black, transparent 86%);
        animation: linkqr-grid-drift 22s linear infinite;
    }

    .linkqr-hero-stage::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        z-index: -1;
        height: 34%;
        background: linear-gradient(90deg, rgba(36,84,232,0.12), rgba(20,184,166,0.12), rgba(245,158,11,0.10));
        clip-path: polygon(0 34%, 100% 0, 100% 100%, 0 100%);
        opacity: 0.8;
    }

    .linkqr-hero-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.84);
        background: rgba(255,255,255,0.82);
        box-shadow: 0 18px 48px -38px rgba(15,23,42,0.35);
        animation: linkqr-hero-breathe 5.8s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .linkqr-hero-stat::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--hero-accent, #2454E8);
    }

    .linkqr-hero-chip {
        border: 1px solid color-mix(in srgb, var(--hero-accent, #2454E8) 24%, rgba(var(--theme-border-color-rgb),0.78));
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--hero-accent, #2454E8) 12%, white), rgba(255,255,255,0.86));
        box-shadow: 0 14px 38px -32px rgba(15,23,42,0.32);
        animation: linkqr-hero-chip-drift 6.4s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .linkqr-hero-dashboard {
        animation: linkqr-hero-float 7.2s ease-in-out infinite;
        transform-origin: center;
    }

    .linkqr-hero-float-card {
        animation: linkqr-hero-float 5.8s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .linkqr-hero-qr-dot {
        animation: linkqr-qr-pulse 2.7s ease-in-out infinite;
        animation-delay: var(--hero-delay, 0ms);
    }

    .linkqr-case-tile {
        position: relative;
        min-height: 18rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.82);
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--case-accent, #2454E8) 18%, #ffffff), rgba(255,255,255,0.74)),
            linear-gradient(180deg, #f8fbff, #eef6ff);
        box-shadow: 0 30px 86px -58px rgba(15,23,42,0.45);
    }

    .linkqr-case-tile::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(rgba(36,84,232,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.07) 1px, transparent 1px);
        background-size: 28px 28px;
        animation: linkqr-grid-drift 20s linear infinite;
    }

    .linkqr-case-tile::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        z-index: -1;
        height: 58%;
        background: linear-gradient(180deg, transparent 0%, rgba(15,23,42,0.18) 24%, rgba(15,23,42,0.68) 100%);
    }

    .linkqr-visual-copy {
        text-shadow: 0 3px 18px rgba(15, 23, 42, 0.62), 0 1px 2px rgba(15, 23, 42, 0.72);
    }

    .linkqr-case-window {
        border: 1px solid rgba(255,255,255,0.74);
        background: rgba(255,255,255,0.82);
        box-shadow: 0 22px 52px -38px rgba(15,23,42,0.45);
        backdrop-filter: blur(16px);
        animation: linkqr-hero-float 6.8s ease-in-out infinite;
    }

    .linkqr-case-metric {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.74);
        background: rgba(255,255,255,0.9);
    }

    .linkqr-marquee {
        overflow: hidden;
        mask-image: linear-gradient(90deg, transparent, black 12%, black 88%, transparent);
    }

    .linkqr-marquee-track {
        display: flex;
        width: max-content;
        gap: 1rem;
        animation: linkqr-marquee 30s linear infinite;
    }

    .linkqr-marquee-track.is-reverse {
        animation-direction: reverse;
        animation-duration: 36s;
    }

    .linkqr-marquee-card {
        min-width: 15rem;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.76);
        background: rgba(255,255,255,0.86);
        box-shadow: 0 20px 52px -42px rgba(15,23,42,0.36);
        backdrop-filter: blur(16px);
    }

    .linkqr-workflow-section {
        margin-top: clamp(-2.5rem, -3vw, -1rem);
    }

    .linkqr-card {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.78);
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 24px 70px -48px rgba(15, 23, 42, 0.24);
        backdrop-filter: blur(18px);
    }

    .linkqr-soft {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.72);
        background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(248,250,252,0.72));
    }

    .linkqr-premium {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .linkqr-premium::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(135deg, rgba(36,84,232,0.10), transparent 32%),
            linear-gradient(315deg, rgba(20,184,166,0.10), transparent 34%),
            linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.82));
    }

    .linkqr-hover-lift {
        transition:
            transform 260ms cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 260ms cubic-bezier(0.22, 1, 0.36, 1),
            border-color 260ms ease;
    }

    .linkqr-hover-lift:hover {
        transform: translateY(-6px);
        border-color: rgba(var(--theme-accent-rgb), 0.34);
        box-shadow: 0 34px 90px -56px rgba(15, 23, 42, 0.34);
    }

    .linkqr-pricing-grid > :not([hidden]) + :not([hidden]) {
        box-shadow: inset 1px 0 0 rgba(var(--theme-border-color-rgb), 0.42);
    }

    .linkqr-operation-row {
        background: rgba(255, 255, 255, 0.72);
        box-shadow: 0 18px 42px -38px rgba(15, 23, 42, 0.28);
    }

    .linkqr-operation-row:hover {
        background: rgba(248, 250, 252, 0.92);
        transform: translateX(4px);
        border-color: rgba(var(--theme-accent-rgb), 0.32) !important;
    }

    .linkqr-image-frame {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 20% 18%, rgba(36,84,232,0.18), transparent 28%),
            radial-gradient(circle at 78% 28%, rgba(20,184,166,0.16), transparent 30%),
            linear-gradient(135deg, #eaf2ff, #dcefff 48%, #eef7f8);
        isolation: isolate;
    }

    .linkqr-image-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        color: transparent;
        transform: scale(1.01);
        transition: transform 700ms cubic-bezier(0.22, 1, 0.36, 1), filter 700ms ease;
    }

    .linkqr-image-frame::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            linear-gradient(180deg, rgba(15,23,42,0.02), rgba(15,23,42,0.46)),
            linear-gradient(90deg, rgba(36,84,232,0.16), transparent 42%);
        pointer-events: none;
    }

    .linkqr-hover-lift:hover .linkqr-image-frame img {
        transform: scale(1.07);
        filter: saturate(1.08) contrast(1.03);
    }

    .linkqr-glass-badge {
        border: 1px solid rgba(255, 255, 255, 0.42);
        background: rgba(255, 255, 255, 0.86);
        box-shadow: 0 14px 30px -22px rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(14px);
    }

    .linkqr-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        background:
            linear-gradient(135deg, rgba(36,84,232,0.10), rgba(20,184,166,0.10)),
            linear-gradient(180deg, #f8fbff, #eaf2fb);
    }

    .linkqr-visual-bio,
    .linkqr-visual-retail {
        background:
            linear-gradient(135deg, rgba(36,84,232,0.18), rgba(20,184,166,0.16)),
            linear-gradient(180deg, #f8fbff, #dff4f1);
    }

    .linkqr-visual-qr,
    .linkqr-visual-rules {
        background:
            linear-gradient(135deg, rgba(124,58,237,0.16), rgba(36,84,232,0.18)),
            linear-gradient(180deg, #f8fbff, #e8edff);
    }

    .linkqr-visual-analytics,
    .linkqr-visual-alerts {
        background:
            linear-gradient(135deg, rgba(245,158,11,0.18), rgba(36,84,232,0.16)),
            linear-gradient(180deg, #fffaf0, #eaf2ff);
    }

    .linkqr-visual-domain {
        background:
            linear-gradient(135deg, rgba(14,165,233,0.18), rgba(5,150,105,0.16)),
            linear-gradient(180deg, #f0f9ff, #def7ec);
    }

    .linkqr-visual-utm {
        background:
            linear-gradient(135deg, rgba(217,119,6,0.18), rgba(236,72,153,0.14)),
            linear-gradient(180deg, #fff7ed, #fdf2f8);
    }

    .linkqr-visual-team,
    .linkqr-visual-workspace {
        background:
            linear-gradient(135deg, rgba(124,58,237,0.16), rgba(20,184,166,0.14)),
            linear-gradient(180deg, #f5f3ff, #ecfeff);
    }

    .linkqr-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(180deg, transparent 32%, rgba(15,23,42,0.14) 48%, rgba(15,23,42,0.72) 100%);
        pointer-events: none;
    }

    .linkqr-visual-grid {
        position: absolute;
        inset: 0;
        opacity: 0.66;
        background:
            linear-gradient(rgba(36,84,232,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(36,84,232,0.07) 1px, transparent 1px);
        background-size: 26px 26px;
        animation: linkqr-grid-drift 18s linear infinite;
    }

    .linkqr-phone,
    .linkqr-chart-card,
    .linkqr-route-map,
    .linkqr-dashboard-stack {
        position: absolute;
        inset: 1.25rem;
        z-index: 2;
    }

    .linkqr-phone {
        width: 11rem;
        max-width: 48%;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.9);
        border-radius: 1.4rem;
        background: rgba(255,255,255,0.86);
        padding: 1rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
        animation: linkqr-float 5.6s ease-in-out infinite;
    }

    .linkqr-phone > span {
        display: block;
        margin: 0 auto 0.9rem;
        width: 2.25rem;
        height: 0.3rem;
        border-radius: 999px;
        background: #dbe6f3;
    }

    .linkqr-avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #2454e8, #14b8a6);
    }

    .linkqr-line,
    .linkqr-button-line {
        height: 0.55rem;
        border-radius: 999px;
        background: #dce6f3;
    }

    .linkqr-line {
        margin-top: 0.6rem;
    }

    .linkqr-button-line {
        background: linear-gradient(90deg, rgba(36,84,232,0.18), rgba(20,184,166,0.20));
    }

    .linkqr-floating-qr,
    .linkqr-route-qr {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        grid-template-rows: repeat(7, minmax(0, 1fr));
        gap: 0.18rem;
        border-radius: 1rem;
        background: #fff;
        padding: 0.9rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.5);
    }

    .linkqr-floating-qr {
        position: absolute;
        right: 1.2rem;
        top: 2.4rem;
        width: 8.5rem;
        height: 8.5rem;
        animation: linkqr-float 6.4s ease-in-out infinite reverse;
    }

    .linkqr-floating-qr span,
    .linkqr-route-qr span {
        border-radius: 0.16rem;
        background: transparent;
    }

    .linkqr-floating-qr span.is-on,
    .linkqr-route-qr span.is-on {
        background: #0f172a;
        animation: linkqr-qr-pulse 2.8s ease-in-out infinite;
    }

    .linkqr-chart-card {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.84);
        border-radius: 1.25rem;
        background: rgba(255,255,255,0.9);
        padding: 1.25rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.45);
    }

    .linkqr-visual-bar {
        flex: 1;
        border-radius: 999px 999px 0.4rem 0.4rem;
        background: linear-gradient(180deg, #2454e8, #14b8a6);
        transform-origin: bottom;
        animation: linkqr-bar-rise 3.2s ease-in-out infinite;
    }

    .linkqr-visual-stroke,
    .linkqr-route-path {
        stroke: #2454e8;
        stroke-width: 4;
        stroke-linecap: round;
        stroke-dasharray: 18 12;
        animation: linkqr-dash 2.8s linear infinite;
    }

    .linkqr-route-qr {
        position: absolute;
        left: 1.2rem;
        top: 50%;
        width: 8.25rem;
        height: 8.25rem;
        transform: translateY(-50%);
        z-index: 3;
    }

    .linkqr-route-node {
        position: absolute;
        z-index: 4;
        transform: translate(-50%, -50%);
        border: 1px solid rgba(var(--theme-border-color-rgb),0.92);
        border-radius: 999px;
        background: rgba(255,255,255,0.92);
        padding: 0.55rem 0.75rem;
        color: #0f172a;
        font-size: 0.72rem;
        font-weight: 800;
        box-shadow: 0 16px 36px -26px rgba(15,23,42,0.5);
    }

    .linkqr-mini-window {
        position: absolute;
        inset: 1.25rem;
        border: 1px solid rgba(var(--theme-border-color-rgb),0.86);
        border-radius: 1.25rem;
        background: rgba(255,255,255,0.92);
        padding: 1rem;
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
        animation: linkqr-float 6s ease-in-out infinite;
    }

    .linkqr-domain-visual,
    .linkqr-utm-visual,
    .linkqr-team-visual {
        position: absolute;
        inset: 1.25rem;
        z-index: 2;
    }

    .linkqr-domain-card,
    .linkqr-utm-card,
    .linkqr-team-card {
        border: 1px solid rgba(var(--theme-border-color-rgb),0.86);
        border-radius: 1.15rem;
        background: rgba(255,255,255,0.9);
        box-shadow: 0 24px 60px -42px rgba(15,23,42,0.44);
    }

    .linkqr-domain-card {
        position: absolute;
        left: 0.6rem;
        right: 1rem;
        top: 2.6rem;
        padding: 1rem;
        animation: linkqr-float 5.8s ease-in-out infinite;
    }

    .linkqr-domain-node {
        position: absolute;
        right: 1.2rem;
        bottom: 1.4rem;
        border-radius: 999px;
        background: #0f766e;
        color: white;
        padding: 0.65rem 0.85rem;
        font-size: 0.72rem;
        font-weight: 800;
        box-shadow: 0 18px 40px -26px rgba(15,23,42,0.55);
    }

    .linkqr-utm-card {
        position: absolute;
        left: 0.75rem;
        top: 1.35rem;
        width: 72%;
        padding: 1rem;
        animation: linkqr-float 6.2s ease-in-out infinite;
    }

    .linkqr-utm-pill {
        display: inline-flex;
        margin: 0.2rem;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(245,158,11,0.18), rgba(236,72,153,0.18));
        padding: 0.48rem 0.65rem;
        color: #7c2d12;
        font-size: 0.68rem;
        font-weight: 800;
    }

    .linkqr-team-card {
        position: absolute;
        inset: 1rem 0.9rem auto 0.9rem;
        padding: 1rem;
        animation: linkqr-float 5.9s ease-in-out infinite;
    }

    .linkqr-member-row {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border-radius: 0.85rem;
        background: rgba(248,250,252,0.9);
        padding: 0.55rem;
    }

    .linkqr-member-avatar {
        width: 1.8rem;
        height: 1.8rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #7c3aed, #14b8a6);
    }

    .linkqr-sheen {
        position: relative;
        overflow: hidden;
    }

    .linkqr-sheen::after {
        content: "";
        position: absolute;
        inset: -120% auto -120% -40%;
        width: 32%;
        transform: rotate(18deg) translateX(-240%);
        background: linear-gradient(180deg, transparent, rgba(255,255,255,0.28), transparent);
        opacity: 0;
        pointer-events: none;
    }

    .linkqr-sheen:hover::after {
        opacity: 1;
        animation: linkqr-sheen 1.1s ease;
    }

    .linkqr-flow-line {
        position: relative;
    }

    .linkqr-flow-line::before {
        content: "";
        position: absolute;
        left: 1.35rem;
        top: 3.4rem;
        bottom: 1.2rem;
        width: 1px;
        background: linear-gradient(180deg, rgba(36,84,232,0.32), rgba(20,184,166,0.18));
    }

    .linkqr-data-bars span {
        display: block;
        height: 0.65rem;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(36,84,232,0.92), rgba(20,184,166,0.88));
        transform-origin: left center;
        animation: linkqr-bar 3.8s ease-in-out infinite;
    }

    .linkqr-button-primary {
        background: linear-gradient(135deg, #2454e8, #14b8a6);
        color: #ffffff;
        box-shadow: 0 18px 38px -24px rgba(36, 84, 232, 0.72);
    }

    .linkqr-auth-primary {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(var(--theme-accent-rgb), 0.58);
        background:
            linear-gradient(135deg, #2563eb 0%, #0ea5e9 45%, #14b8a6 100%);
        color: #ffffff;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.2),
            0 22px 44px -28px rgba(36, 84, 232, 0.72);
    }

    .linkqr-auth-primary::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.22), transparent);
        transform: translateX(-120%);
        transition: transform 520ms ease;
    }

    .linkqr-auth-primary:hover {
        transform: translateY(-1px);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.24),
            0 26px 58px -30px rgba(20, 184, 166, 0.72);
    }

    .linkqr-auth-primary:hover::after {
        transform: translateX(120%);
    }

    .linkqr-auth-primary > span {
        position: relative;
        z-index: 1;
    }

    .linkqr-auth-social {
        border-color: rgba(var(--theme-border-color-rgb), 0.9);
        background: rgba(255, 255, 255, 0.78);
        color: #0f172a;
        box-shadow: 0 14px 34px -30px rgba(15, 23, 42, 0.32);
    }

    .linkqr-auth-social:hover {
        transform: translateY(-1px);
        border-color: rgba(var(--theme-accent-rgb), 0.34);
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 20px 46px -34px rgba(15, 23, 42, 0.38);
    }

    .linkqr-button-secondary {
        border: 1px solid rgba(var(--theme-border-color-rgb), 0.86);
        background: rgba(255,255,255,0.82);
        color: #0f172a;
        box-shadow: 0 14px 28px -24px rgba(15, 23, 42, 0.28);
    }

    .linkqr-gradient-text {
        background: linear-gradient(90deg, #2454e8 0%, #0f766e 52%, #f59e0b 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .linkqr-reveal {
        --linkqr-reveal-delay: 0ms;
        opacity: 0;
        transform: translate3d(0, 46px, 0) scale(0.975);
        filter: blur(12px);
        transition:
            opacity 860ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 860ms cubic-bezier(0.22, 1, 0.36, 1),
            filter 860ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--linkqr-reveal-delay);
        will-change: transform, opacity, filter;
    }

    .linkqr-reveal.is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
        filter: blur(0);
    }

    @keyframes linkqr-sheen {
        0% { transform: rotate(18deg) translateX(-240%); }
        100% { transform: rotate(18deg) translateX(520%); }
    }

    @keyframes linkqr-bar {
        0%, 100% { transform: scaleX(0.72); opacity: 0.72; }
        50% { transform: scaleX(1); opacity: 1; }
    }

    @keyframes linkqr-grid-drift {
        0% { background-position: 0 0, 0 0; }
        100% { background-position: 52px 52px, 52px 52px; }
    }

    @keyframes linkqr-float {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, -10px, 0); }
    }

    @keyframes linkqr-hero-float {
        0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
        50% { transform: translate3d(0, -8px, 0) rotate(0.18deg); }
    }

    @keyframes linkqr-hero-breathe {
        0%, 100% { transform: translate3d(0, 0, 0); box-shadow: 0 18px 48px -38px rgba(15,23,42,0.35); }
        50% { transform: translate3d(0, -3px, 0); box-shadow: 0 24px 58px -40px rgba(15,23,42,0.42); }
    }

    @keyframes linkqr-hero-chip-drift {
        0%, 100% { transform: translate3d(0, 0, 0); }
        50% { transform: translate3d(0, -4px, 0); }
    }

    @keyframes linkqr-qr-pulse {
        0%, 100% { opacity: 0.72; transform: scale(0.96); }
        50% { opacity: 1; transform: scale(1); }
    }

    @keyframes linkqr-bar-rise {
        0%, 100% { transform: scaleY(0.72); opacity: 0.74; }
        50% { transform: scaleY(1); opacity: 1; }
    }

    @keyframes linkqr-dash {
        to { stroke-dashoffset: -60; }
    }

    @keyframes linkqr-marquee {
        from { transform: translate3d(0, 0, 0); }
        to { transform: translate3d(-50%, 0, 0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .linkqr-reveal,
        .linkqr-hover-lift,
        .linkqr-data-bars span,
        .linkqr-sheen::after,
        .linkqr-visual-grid,
        .linkqr-phone,
        .linkqr-floating-qr,
        .linkqr-mini-window,
        .linkqr-visual-bar,
        .linkqr-visual-stroke,
        .linkqr-route-path,
        .linkqr-hero-stage::before,
        .linkqr-hero-stat,
        .linkqr-hero-chip,
        .linkqr-hero-dashboard,
        .linkqr-hero-float-card,
        .linkqr-hero-qr-dot,
        .linkqr-marquee-track {
            animation: none !important;
            transition: none !important;
            transform: none !important;
            filter: none !important;
            opacity: 1 !important;
        }
    }

    ::selection {
        background: rgba(var(--theme-accent-rgb), 0.22);
        color: #0f172a;
    }

    html[data-theme-resolved='dark'] {
        --theme-body-bg: #07111F;
        --theme-body-bg-rgb: 7, 17, 31;
        --theme-surface-bg: #0B1526;
        --theme-surface-bg-rgb: 11, 21, 38;
        --theme-header-bg: #07111F;
        --theme-header-bg-rgb: 7, 17, 31;
        --theme-header-text-color: #E8EEF7;
        --theme-border-color: #25364D;
        --theme-border-color-rgb: 37, 54, 77;
        --theme-muted-text-color: #94A3B8;
        background: #07111f;
        color: #e8eef7;
    }

    html[data-theme-resolved='dark'] body {
        background:
            linear-gradient(rgba(96, 165, 250, 0.055) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96, 165, 250, 0.055) 1px, transparent 1px),
            radial-gradient(circle at 20% 8%, rgba(37, 99, 235, 0.22), transparent 28%),
            radial-gradient(circle at 82% 22%, rgba(20, 184, 166, 0.18), transparent 26%),
            linear-gradient(180deg, #07111f 0%, #0b1526 48%, #07111f 100%);
        background-size: 36px 36px, 36px 36px, auto, auto, auto;
    }

    html[data-theme-resolved='dark'] .linkqr-card,
    html[data-theme-resolved='dark'] .linkqr-soft,
    html[data-theme-resolved='dark'] .linkqr-hero-stage,
    html[data-theme-resolved='dark'] .linkqr-case-tile,
    html[data-theme-resolved='dark'] .linkqr-marquee-card,
    html[data-theme-resolved='dark'] footer,
    html[data-theme-resolved='dark'] header > div {
        border-color: rgba(96, 165, 250, 0.22) !important;
        background: rgba(11, 21, 38, 0.82) !important;
        box-shadow: 0 34px 100px -72px rgba(0, 0, 0, 0.8);
    }

    html[data-theme-resolved='dark'] .linkqr-hero-stage::before,
    html[data-theme-resolved='dark'] .linkqr-case-tile::before {
        background:
            linear-gradient(rgba(96,165,250,0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(96,165,250,0.07) 1px, transparent 1px);
    }

    html[data-theme-resolved='dark'] .linkqr-main-hero {
        overflow: hidden;
        border-color: transparent !important;
        background:
            radial-gradient(circle at 16% 22%, rgba(37, 99, 235, 0.18), transparent 34%),
            radial-gradient(circle at 76% 42%, rgba(20, 184, 166, 0.14), transparent 32%) !important;
        box-shadow: none !important;
    }

    html[data-theme-resolved='dark'] .linkqr-main-hero::before {
        inset: -1.5rem;
        opacity: 0.55;
        mask-image: radial-gradient(circle at center, black 0%, transparent 76%);
    }

    html[data-theme-resolved='dark'] .linkqr-main-hero::after {
        opacity: 0.28;
        height: 26%;
        filter: blur(12px);
    }

    html[data-theme-resolved='dark'] .bg-white,
    html[data-theme-resolved='dark'] .bg-white\/70,
    html[data-theme-resolved='dark'] .bg-white\/72,
    html[data-theme-resolved='dark'] .bg-white\/78,
    html[data-theme-resolved='dark'] .bg-white\/82,
    html[data-theme-resolved='dark'] .bg-white\/86,
    html[data-theme-resolved='dark'] .bg-white\/88,
    html[data-theme-resolved='dark'] .bg-white\/92,
    html[data-theme-resolved='dark'] .bg-white\/95 {
        background-color: rgba(15, 23, 42, 0.82) !important;
    }

    html[data-theme-resolved='dark'] .bg-slate-50,
    html[data-theme-resolved='dark'] .bg-slate-50\/80,
    html[data-theme-resolved='dark'] .bg-slate-50\/90,
    html[data-theme-resolved='dark'] .bg-slate-100 {
        background-color: rgba(30, 41, 59, 0.84) !important;
    }

    html[data-theme-resolved='dark'] .text-slate-950,
    html[data-theme-resolved='dark'] .text-slate-900,
    html[data-theme-resolved='dark'] .text-slate-800 {
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] .text-slate-700,
    html[data-theme-resolved='dark'] .text-slate-600 {
        color: #cbd5e1 !important;
    }

    html[data-theme-resolved='dark'] .text-slate-500,
    html[data-theme-resolved='dark'] .text-slate-400 {
        color: #94a3b8 !important;
    }

    html[data-theme-resolved='dark'] .border,
    html[data-theme-resolved='dark'] .border-t,
    html[data-theme-resolved='dark'] .border-b {
        border-color: rgba(96, 165, 250, 0.22) !important;
    }

    html[data-theme-resolved='dark'] [class*="divide-"] > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-slate-200 > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-blueGray-200 > :not([hidden]) ~ :not([hidden]),
    html[data-theme-resolved='dark'] .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: rgba(96, 165, 250, 0.14) !important;
        border-left-color: rgba(96, 165, 250, 0.14) !important;
        border-top-color: rgba(96, 165, 250, 0.14) !important;
        border-right-color: rgba(96, 165, 250, 0.14) !important;
        border-bottom-color: rgba(96, 165, 250, 0.14) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-pricing-grid > :not([hidden]) + :not([hidden]) {
        box-shadow: inset 1px 0 0 rgba(96, 165, 250, 0.08) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-pricing-card.is-featured {
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(20, 184, 166, 0.08) 48%, rgba(15, 23, 42, 0.9)),
            rgba(15, 23, 42, 0.9) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-operation-row {
        background:
            linear-gradient(135deg, rgba(15, 23, 42, 0.94), rgba(11, 21, 38, 0.88)),
            rgba(15, 23, 42, 0.9) !important;
        border-color: rgba(96, 165, 250, 0.2) !important;
        box-shadow: 0 20px 64px -54px rgba(0, 0, 0, 0.9);
    }

    html[data-theme-resolved='dark'] .linkqr-operation-row:hover {
        background:
            linear-gradient(135deg, rgba(37, 99, 235, 0.14), rgba(15, 23, 42, 0.94) 46%, rgba(20, 184, 166, 0.08)),
            rgba(15, 23, 42, 0.94) !important;
        border-color: rgba(96, 165, 250, 0.34) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-button-secondary {
        background: rgba(15, 23, 42, 0.7);
        color: #e8eef7;
        border-color: rgba(96, 165, 250, 0.26);
    }

    html[data-theme-resolved='dark'] .linkqr-auth-primary {
        border-color: rgba(34, 211, 238, 0.28);
        background:
            linear-gradient(135deg, #2563eb 0%, #0f8fc7 48%, #10b7a7 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.16),
            0 22px 58px -32px rgba(20, 184, 166, 0.82),
            0 0 0 1px rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .linkqr-auth-primary:hover {
        filter: saturate(1.06) brightness(1.04);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.18),
            0 28px 70px -34px rgba(37, 99, 235, 0.88),
            0 0 0 1px rgba(34, 211, 238, 0.16);
    }

    html[data-theme-resolved='dark'] .linkqr-auth-social {
        border-color: rgba(96, 165, 250, 0.22);
        background:
            linear-gradient(180deg, rgba(15, 23, 42, 0.82), rgba(11, 21, 38, 0.72));
        color: #e8eef7;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.035);
    }

    html[data-theme-resolved='dark'] .linkqr-auth-social:hover {
        border-color: rgba(34, 211, 238, 0.32);
        background:
            linear-gradient(180deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.78));
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.055),
            0 18px 46px -36px rgba(20, 184, 166, 0.6);
    }

    html[data-theme-resolved='dark'] header a:not(.linkqr-nav-link):hover,
    html[data-theme-resolved='dark'] header button:hover {
        background-color: rgba(30, 41, 59, 0.78) !important;
        color: #f8fafc !important;
    }

    html[data-theme-resolved='dark'] header .linkqr-nav-link:hover {
        background: transparent !important;
        color: #f8fafc !important;
        text-shadow: 0 0 22px rgba(96, 165, 250, 0.36);
    }

    html[data-theme-resolved='dark'] header .linkqr-button-primary:hover {
        background: linear-gradient(135deg, #2563eb, #14b8a6) !important;
        color: #fff !important;
    }

    html[data-theme-resolved='dark'] .linkqr-glass-badge,
    html[data-theme-resolved='dark'] .linkqr-case-metric,
    html[data-theme-resolved='dark'] .linkqr-case-window {
        background: rgba(15, 23, 42, 0.78);
        border-color: rgba(96, 165, 250, 0.24);
    }

    html[data-theme-resolved='dark'] .linkqr-premium::before {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.18), transparent 36%),
            linear-gradient(315deg, rgba(20,184,166,0.16), transparent 38%),
            linear-gradient(180deg, rgba(15,23,42,0.94), rgba(11,21,38,0.86));
    }

    html[data-theme-resolved='dark'] .linkqr-hero-stat,
    html[data-theme-resolved='dark'] .linkqr-hero-chip {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.28) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-visual,
    html[data-theme-resolved='dark'] .linkqr-visual-bio,
    html[data-theme-resolved='dark'] .linkqr-visual-retail,
    html[data-theme-resolved='dark'] .linkqr-visual-qr,
    html[data-theme-resolved='dark'] .linkqr-visual-rules,
    html[data-theme-resolved='dark'] .linkqr-visual-analytics,
    html[data-theme-resolved='dark'] .linkqr-visual-alerts,
    html[data-theme-resolved='dark'] .linkqr-visual-domain,
    html[data-theme-resolved='dark'] .linkqr-visual-utm,
    html[data-theme-resolved='dark'] .linkqr-visual-team,
    html[data-theme-resolved='dark'] .linkqr-visual-workspace {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.28), rgba(20,184,166,0.18)),
            linear-gradient(180deg, #101d32, #0b1526) !important;
    }

    html[data-theme-resolved='dark'] .linkqr-phone,
    html[data-theme-resolved='dark'] .linkqr-chart-card,
    html[data-theme-resolved='dark'] .linkqr-mini-window,
    html[data-theme-resolved='dark'] .linkqr-domain-card,
    html[data-theme-resolved='dark'] .linkqr-utm-card,
    html[data-theme-resolved='dark'] .linkqr-team-card {
        background: rgba(15, 23, 42, 0.78) !important;
        border-color: rgba(96, 165, 250, 0.26) !important;
        box-shadow: 0 24px 70px -46px rgba(0, 0, 0, 0.82);
    }

    html[data-theme-resolved='dark'] .linkqr-floating-qr,
    html[data-theme-resolved='dark'] .linkqr-route-qr {
        background: rgba(15, 23, 42, 0.88) !important;
        border: 1px solid rgba(96, 165, 250, 0.28);
        box-shadow: 0 24px 70px -44px rgba(0, 0, 0, 0.86);
    }

    html[data-theme-resolved='dark'] .linkqr-floating-qr span.is-on,
    html[data-theme-resolved='dark'] .linkqr-route-qr span.is-on {
        background: #e2e8f0;
    }

    html[data-theme-resolved='dark'] .linkqr-floating-qr span:not(.is-on),
    html[data-theme-resolved='dark'] .linkqr-route-qr span:not(.is-on) {
        background: rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .linkqr-visual::after {
        background: linear-gradient(180deg, transparent 32%, rgba(2,6,23,0.16) 48%, rgba(2,6,23,0.76));
    }

    html[data-theme-resolved='dark'] .linkqr-line {
        background: rgba(148, 163, 184, 0.34);
    }

    html[data-theme-resolved='dark'] .linkqr-button-line {
        background: linear-gradient(90deg, rgba(37,99,235,0.42), rgba(20,184,166,0.46));
    }

    html[data-theme-resolved='dark'] .linkqr-avatar,
    html[data-theme-resolved='dark'] .linkqr-member-avatar {
        box-shadow: 0 0 0 8px rgba(96, 165, 250, 0.08);
    }

    html[data-theme-resolved='dark'] .linkqr-utm-pill {
        background: rgba(245, 158, 11, 0.14);
        color: #fed7aa;
    }

    html[data-theme-resolved='dark'] .linkqr-member-row {
        background: rgba(30, 41, 59, 0.7);
    }

    html[data-theme-resolved='dark'] .linkqr-image-frame {
        background:
            linear-gradient(135deg, rgba(37,99,235,0.22), rgba(20,184,166,0.14)),
            #101d32;
    }

    html[data-theme-resolved='dark'] .linkqr-image-frame::after {
        background:
            linear-gradient(180deg, rgba(2,6,23,0.02), rgba(2,6,23,0.62)),
            linear-gradient(90deg, rgba(37,99,235,0.18), transparent 42%);
    }

    html[data-theme-resolved='dark'] article[class*="from-blue-50"],
    html[data-theme-resolved='dark'] div[class*="from-blue-50"],
    html[data-theme-resolved='dark'] [class*="to-teal-50"] {
        background: linear-gradient(180deg, rgba(30,41,59,0.96), rgba(15,23,42,0.9)) !important;
    }

    html[data-theme-resolved='dark'] .bg-blue-50,
    html[data-theme-resolved='dark'] .bg-emerald-50,
    html[data-theme-resolved='dark'] .bg-amber-50,
    html[data-theme-resolved='dark'] .bg-violet-50 {
        background-color: rgba(30, 41, 59, 0.9) !important;
    }

    html[data-theme-resolved='dark'] ::selection {
        background: rgba(96, 165, 250, 0.32);
        color: #f8fafc;
    }
</style>

@if (filled(theme_setting('custom_css', 'guest')))
    <style>{!! theme_setting('custom_css', 'guest') !!}</style>
@endif

<script>
    (() => {
        const storageKey = 'linkqr-theme-mode';
        const defaultStorageKey = `${storageKey}:default`;
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const supportsDark = @js((string) theme_setting('supports_dark_mode', 'guest', '1')) !== '0';
        const allowToggle = @js((string) theme_setting('allow_user_appearance_toggle', 'guest', '1')) !== '0';
        const defaultMode = @js(theme_setting('default_appearance', 'guest', theme_setting('appearance', 'guest', 'dark')));
        const normalize = (mode) => {
            const allowedModes = supportsDark ? ['light', 'dark', 'system'] : ['light'];
            const fallback = supportsDark ? (defaultMode || 'dark') : 'light';

            return allowedModes.includes(mode) ? mode : fallback;
        };
        const resolve = (mode) => {
            const normalized = normalize(mode);

            if (!supportsDark) {
                return 'light';
            }

            return normalized === 'dark' || (normalized === 'system' && media.matches) ? 'dark' : 'light';
        };
        const storedValue = (key) => {
            try {
                return localStorage.getItem(key);
            } catch (error) {
                return null;
            }
        };
        const rememberMode = (mode) => {
            try {
                localStorage.setItem(storageKey, mode);
                localStorage.setItem(defaultStorageKey, configuredMode);
            } catch (error) {}
        };
        const apply = (mode, notify = true) => {
            const nextMode = normalize(mode);
            const resolved = resolve(nextMode);
            document.documentElement.dataset.themeMode = nextMode;
            document.documentElement.dataset.themeResolved = resolved;
            document.documentElement.classList.toggle('dark', resolved === 'dark');

            const state = { mode: nextMode, resolved };

            if (notify) {
                window.dispatchEvent(new CustomEvent('theme-mode-changed', { detail: state }));
            }

            return state;
        };
        const configuredMode = normalize(defaultMode || 'dark');
        const initialMode = () => {
            if (!allowToggle) {
                return configuredMode;
            }

            if (storedValue(defaultStorageKey) !== configuredMode) {
                return configuredMode;
            }

            return normalize(storedValue(storageKey) || configuredMode);
        };
        window.themeMode = {
            getMode: () => document.documentElement.dataset.themeMode || initialMode(),
            getResolved: () => document.documentElement.dataset.themeResolved || resolve(document.documentElement.dataset.themeMode || initialMode()),
            supportsDark: () => supportsDark,
            allowToggle: () => allowToggle && supportsDark,
            setMode: (mode) => {
                const nextMode = normalize(mode);
                if (allowToggle && supportsDark) {
                    rememberMode(nextMode);
                }
                return apply(nextMode);
            },
            toggle: () => {
                const nextMode = (document.documentElement.dataset.themeResolved || resolve(initialMode())) === 'dark' ? 'light' : 'dark';
                if (allowToggle && supportsDark) {
                    rememberMode(nextMode);
                }
                return apply(nextMode);
            },
            apply,
        };
        apply(initialMode(), false);

        media.addEventListener?.('change', () => {
            if (window.themeMode.getMode() !== 'system') {
                return;
            }

            apply('system');
        });
    })();
</script>
<script>
    (() => {
        const initReveal = () => {
            const nodes = document.querySelectorAll('[data-reveal]');

            if (!nodes.length) {
                return;
            }

            if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                nodes.forEach((node) => node.classList.add('linkqr-reveal', 'is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.14 });

            nodes.forEach((node, index) => {
                node.classList.add('linkqr-reveal');
                node.style.setProperty('--linkqr-reveal-delay', `${Math.min((index % 6) * 70, 280)}ms`);
                observer.observe(node);
            });
        };

        document.addEventListener('DOMContentLoaded', initReveal);
        document.addEventListener('livewire:navigated', initReveal);
    })();
</script>
@if (filled(theme_setting('custom_js', 'guest')))
    <script>{!! theme_setting('custom_js', 'guest') !!}</script>
@endif
{!! theme_vite('guest', ['assets/js/app.js', 'resources/themes/shared/js/highcharts.js', 'resources/themes/shared/js/image-editor.js']) !!}
