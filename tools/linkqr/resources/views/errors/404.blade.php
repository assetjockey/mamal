@php
    $statusCode = 404;
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $settingsTitle = trim((string) $options->get('website_title', ''));
    $siteTitle = $settingsTitle !== '' && ! str_contains(strtolower($settingsTitle), 'stackposts') ? $settingsTitle : config('app.name', 'SmartBio');
    $logoPath = trim((string) ($options->get('website_logo_brand_dark') ?: $options->get('website_logo_dark') ?: $options->get('website_logo') ?: ''));
    $homeUrl = auth()->check() ? url('/portal/dashboard') : url('/');
    $homeLabel = auth()->check() ? __('Go to dashboard') : __('Go home');
    $basePath = trim(parse_url(url('/'), PHP_URL_PATH) ?: '', '/');
    $requestedPath = trim(request()->getPathInfo(), '/');
    $requestedPath = $basePath !== '' && ! str_starts_with($requestedPath, $basePath.'/')
        ? trim($basePath.'/'.$requestedPath, '/')
        : $requestedPath;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Page not found') }} - {{ $siteTitle }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --ink: #181714;
            --muted: #6f6a60;
            --paper: #fbfaf6;
            --line: #ddd7cb;
            --blue: #5f8dff;
            --yellow: #ffc52d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 16% 12%, rgba(95, 141, 255, .11), transparent 28%),
                radial-gradient(circle at 84% 78%, rgba(255, 197, 45, .18), transparent 30%),
                linear-gradient(rgba(24, 23, 20, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(24, 23, 20, .035) 1px, transparent 1px),
                var(--paper);
            background-size: auto, auto, 36px 36px, 36px 36px, auto;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            display: grid;
            min-height: 100vh;
            grid-template-rows: auto 1fr auto;
            padding: 24px;
        }

        .topbar,
        .footer {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .brand img {
            max-width: 154px;
            height: 36px;
            object-fit: contain;
        }

        .pill {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, .72);
            padding: 0 16px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 16px 40px -34px rgba(24, 23, 20, .55);
        }

        .wrap {
            display: grid;
            place-items: center;
            width: min(1080px, 100%);
            margin: 0 auto;
            padding: 56px 0;
        }

        .card {
            display: grid;
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: rgba(255, 253, 248, .82);
            box-shadow: 0 36px 100px -74px rgba(24, 23, 20, .58);
            backdrop-filter: blur(18px);
        }

        @media (min-width: 880px) {
            .card {
                grid-template-columns: .92fr 1.08fr;
                min-height: 520px;
            }
        }

        .copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(32px, 6vw, 72px);
        }

        .eyebrow {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(95, 141, 255, .35);
            border-radius: 999px;
            background: rgba(95, 141, 255, .09);
            padding: 8px 12px;
            color: #315fcf;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        h1 {
            margin: 24px 0 0;
            max-width: 560px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(46px, 7vw, 94px);
            font-weight: 500;
            letter-spacing: -.055em;
            line-height: .92;
        }

        .body {
            max-width: 540px;
            margin: 22px 0 0;
            color: var(--muted);
            font-size: 16px;
            font-weight: 500;
            line-height: 1.75;
        }

        .path {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            margin-top: 20px;
            border: 1px dashed #c9c2b5;
            border-radius: 12px;
            background: rgba(255, 255, 255, .62);
            padding: 10px 12px;
            color: #8a8175;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 800;
        }

        .button.primary {
            background: var(--ink);
            color: white;
        }

        .button.secondary {
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, .72);
            color: var(--ink);
        }

        .art {
            position: relative;
            min-height: 390px;
            overflow: hidden;
            background:
                repeating-linear-gradient(0deg, rgba(50, 109, 133, .18) 0 1px, transparent 1px 31px),
                repeating-linear-gradient(90deg, rgba(50, 109, 133, .08) 0 1px, transparent 1px 72px),
                #d9f1fb;
        }

        .art-inner {
            position: absolute;
            inset: 10%;
            display: grid;
            place-items: center;
        }

        .note {
            position: absolute;
            border: 3px solid var(--blue);
            background: #fffdf8;
            box-shadow: 0 20px 0 rgba(95, 141, 255, .18);
        }

        .note.one {
            inset: 14% 12% auto auto;
            width: min(360px, 78%);
            height: 250px;
        }

        .note.two {
            inset: auto auto 14% 10%;
            width: 220px;
            height: 190px;
            background: var(--blue);
        }

        .window {
            position: absolute;
            top: 22%;
            left: 22%;
            width: min(390px, 72%);
            border: 1px solid #e3ddcf;
            border-radius: 14px;
            background: white;
            padding: 16px;
            box-shadow: 0 24px 60px -46px rgba(24, 23, 20, .55);
        }

        .bar {
            height: 14px;
            border-radius: 999px;
            background: #dcebd7;
        }

        .row {
            margin-top: 12px;
            border: 1px solid #eee7da;
            border-radius: 10px;
            background: #fffdf8;
            padding: 12px;
        }

        .row strong {
            display: block;
            font-size: 13px;
        }

        .row span {
            display: block;
            margin-top: 5px;
            color: #8a8175;
            font-size: 12px;
        }

        .doodle {
            position: absolute;
            color: rgba(24, 23, 20, .34);
        }

        .doodle.top {
            top: 34px;
            left: 48px;
            width: 120px;
        }

        .doodle.bottom {
            right: 48px;
            bottom: 32px;
            width: 140px;
        }

        .footer {
            display: flex;
            justify-content: center;
            color: #9a9287;
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .page {
                padding: 16px;
            }

            .pill {
                display: none;
            }

            .copy {
                padding: 30px 22px;
            }

            .art {
                min-height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <a href="{{ url('/') }}" class="brand">
                @if ($logoPath !== '')
                    <img src="{{ url($logoPath) }}" alt="{{ $siteTitle }}">
                @else
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7.5 21 3l-5.2 17.5-4.2-7.4L3 7.5Z"/>
                        <path d="m11.6 13.1 4.8-5.2"/>
                    </svg>
                    <span>{{ $siteTitle }}</span>
                @endif
            </a>
            <span class="pill">{{ __('404') }} / {{ __('Not found') }}</span>
        </header>

        <main class="wrap">
            <section class="card" aria-labelledby="page-title">
                <div class="copy">
                    <span class="eyebrow">{{ __('Page not found') }}</span>
                    <h1 id="page-title">{{ __("This link doesn't exist.") }}</h1>
                    <p class="body">
                        {{ __('The page may have been moved, deleted, or the URL may be misspelled. You can return to a safe place and keep working.') }}
                    </p>

                    @if ($requestedPath !== '')
                        <span class="path">/{{ $requestedPath }}</span>
                    @endif

                    <div class="actions">
                        <a href="{{ $homeUrl }}" class="button primary">{{ $homeLabel }}</a>
                        <a href="{{ url('/') }}" class="button secondary">{{ __('Open homepage') }}</a>
                    </div>
                </div>

                <div class="art" aria-hidden="true">
                    <svg class="doodle top" viewBox="0 0 120 90" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 68c24-42 52-50 82-24 16 14 12 34-5 36-19 2-31-22-13-35 10-7 23-8 38-2"/>
                        <path d="m82 8 26 9-25 13M106 17H58"/>
                    </svg>
                    <svg class="doodle bottom" viewBox="0 0 150 95" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">
                        <path d="M18 72c28-33 61-36 99-8M48 48l18-31 23 35M59 31l19 1M108 28c7-8 15-8 24 0"/>
                    </svg>
                    <div class="art-inner">
                        <div class="note two"></div>
                        <div class="note one"></div>
                        <div class="window">
                            <div class="bar"></div>
                            <div class="row">
                                <strong>{{ __('We checked this address') }}</strong>
                                <span>{{ __('No matching page was found.') }}</span>
                            </div>
                            <div class="row">
                                <strong>{{ __('Try the dashboard or homepage') }}</strong>
                                <span>{{ __('Your links and QR tools are still available.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <span>&copy; {{ date('Y') }} {{ $siteTitle }}</span>
        </footer>
    </div>
</body>
</html>
