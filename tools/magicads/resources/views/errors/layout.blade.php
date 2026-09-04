{{--
    Shared layout for HTTP error pages (404 / 419 / 500 …).

    Deliberately self-contained:
      • No @vite, no View composers, no DB lookups, no theme layout.
      • Inline CSS only — so the page still renders cleanly during a 500 when
        the asset manifest, database, or app bootstrap may be unavailable.

    Brand palette (see .kiro/steering/brand-palette.md):
      primary  #4F46E5  ·  secondary #0F172A  ·  accent-text #D97706
      Code numerals use the "Full brand — TEXT" clip-text gradient.

    Child pages provide: @section('code'|'label'|'title'|'message'|'actions'|'hint')
--}}
@php
    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl  = in_array(app()->getLocale(), config('app.rtl_locales', ['ar', 'he', 'fa', 'ur']));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#FFFFFF">
    <title>@yield('title') — {{ config('app.name') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">

    <style>
        :root {
            --e-bg:        #FFFFFF;
            --e-bg-2:      #FAFAFA;
            --e-ink:       #0F172A;   /* secondary */
            --e-muted:     #475569;
            --e-hairline:  #EAEAEA;
            --e-border:    #DADADA;
            --e-primary:   #4F46E5;   /* primary */
            --e-primary-h: #6366F1;   /* primary-hover */
            --e-accent-tx: #D97706;   /* accent (text-safe) */
        }

        * { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            margin: 0;
            color: var(--e-ink);
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            /* White base with a soft, light brand wash in the corners */
            background-color: #FFFFFF;
            background-image:
                radial-gradient(ellipse 60% 50% at 100% 0%, rgba(79, 70, 229, 0.08), transparent 70%),
                radial-gradient(ellipse 60% 50% at 0% 100%, rgba(245, 158, 11, 0.06), transparent 70%);
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .err {
            position: relative;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 2.5rem 1.25rem;
        }

        .err__card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 640px;
            text-align: center;
        }

        .err__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .375rem .85rem;
            border-radius: 9999px;
            background: var(--e-bg);
            border: 1px solid var(--e-hairline);
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--e-muted);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .err__eyebrow .dot {
            width: .5rem; height: .5rem;
            border-radius: 9999px;
            background: var(--e-primary);
        }

        .err__code {
            margin: 1.25rem 0 .25rem;
            font-size: clamp(5.5rem, 22vw, 11rem);
            font-weight: 900;
            line-height: .88;
            letter-spacing: -0.05em;
            /* Full brand — TEXT recipe (#F59E0B swapped for #D97706 for legibility) */
            background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #D97706);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }

        .err__title {
            margin: .5rem 0 0;
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--e-ink);
        }

        .err__msg {
            margin: .9rem auto 0;
            max-width: 30rem;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--e-muted);
        }

        .err__actions {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            align-items: center;
            justify-content: center;
        }

        .err-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border-radius: 9999px;
            padding: .8rem 1.5rem;
            font-size: .95rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .22s ease;
        }
        .err-btn svg { width: 1.05rem; height: 1.05rem; }

        /* Black "Back to home" button — brand secondary #0F172A */
        .err-btn--dark {
            background: #0F172A;
            color: #FFFFFF;
            box-shadow: 0 12px 30px -10px rgba(15, 23, 42, 0.45);
        }
        .err-btn--dark:hover {
            background: #1E293B;
            transform: translateY(-1px);
            box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.55);
        }

        .err-btn--primary {
            background: var(--e-primary);
            color: #FFFFFF;                       /* white on #4F46E5 ≈ 6.1:1 ✓ */
            box-shadow: 0 12px 30px -8px rgba(79, 70, 229, 0.5);
        }
        .err-btn--primary:hover {
            background: var(--e-primary-h);
            transform: translateY(-1px);
            box-shadow: 0 18px 40px -10px rgba(79, 70, 229, 0.6);
        }

        .err-btn--outline {
            background: var(--e-bg);
            color: var(--e-ink);
            border-color: var(--e-border);
        }
        .err-btn--outline:hover {
            border-color: var(--e-ink);
            background: var(--e-bg-2);
        }

        .err-btn:focus-visible,
        a:focus-visible {
            outline: 2px solid var(--e-primary);
            outline-offset: 3px;
        }

        .err__hint {
            margin-top: 1.75rem;
            font-size: .85rem;
            color: var(--e-muted);
        }
        .err__hint a {
            color: var(--e-accent-tx);            /* text-safe amber on white ✓ */
            font-weight: 600;
            text-decoration: none;
        }
        .err__hint a:hover { text-decoration: underline; }

        /* Small status panel — used by pages where the work continues in the
           background (e.g. 504 during image/video generation). */
        .err__status {
            margin: 1.6rem auto 0;
            max-width: 26rem;
            display: flex;
            align-items: center;
            gap: .85rem;
            text-align: start;
            padding: .9rem 1.1rem;
            border-radius: .9rem;
            background: var(--e-bg);
            border: 1px solid var(--e-hairline);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .err__status .spinner {
            flex: none;
            width: 1.35rem;
            height: 1.35rem;
            border-radius: 9999px;
            border: 2.5px solid rgba(79, 70, 229, 0.18);
            border-top-color: var(--e-primary);
            animation: err-spin .8s linear infinite;
        }
        .err__status .txt {
            font-size: .9rem;
            line-height: 1.45;
            color: var(--e-ink);
        }
        .err__status .txt b { font-weight: 600; }
        .err__status .txt span {
            display: block;
            color: var(--e-muted);
            font-size: .82rem;
        }

        @keyframes err-spin { to { transform: rotate(360deg); } }

        @media (prefers-reduced-motion: reduce) {
            .err__status .spinner { animation: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .err-btn { transition: none; }
            .err-btn--dark:hover,
            .err-btn--primary:hover { transform: none; }
        }
    </style>
</head>
<body>
    <main class="err" role="main">
        <div class="err__card">
            <span class="err__eyebrow"><span class="dot" aria-hidden="true"></span>@yield('label')</span>

            <div class="err__code" aria-hidden="true">@yield('code')</div>

            <h1 class="err__title">@yield('title')</h1>

            <p class="err__msg">@yield('message')</p>

            @hasSection('extra')
                @yield('extra')
            @endif

            <div class="err__actions">
                @yield('actions')
            </div>

            @hasSection('hint')
                <p class="err__hint">@yield('hint')</p>
            @endif
        </div>
    </main>
</body>
</html>
