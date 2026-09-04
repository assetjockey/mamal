<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Opening link') }}</title>
    @foreach (($pixelSnippets ?? []) as $pixelSnippet)
        {!! $pixelSnippet !!}
    @endforeach
    <script>
        window.setTimeout(function () {
            window.location.replace(@json($destination));
        }, 320);
    </script>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        body {
            display: grid;
            min-height: 100vh;
            margin: 0;
            place-items: center;
            background: #f8fafc;
            color: #0f172a;
        }
        .card {
            width: min(24rem, calc(100vw - 2rem));
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            background: #fff;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 24px 70px -54px rgba(15,23,42,.65);
        }
        .spinner {
            width: 2rem;
            height: 2rem;
            margin: 0 auto 1rem;
            border: 3px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 999px;
            animation: spin .75s linear infinite;
        }
        p { margin: 0; color: #52647a; font-size: .925rem; line-height: 1.6; }
        a { color: #2563eb; font-weight: 700; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <main class="card">
        <div class="spinner" aria-hidden="true"></div>
        <p>{{ __('Opening the link...') }}</p>
        <p><a href="{{ $destination }}">{{ __('Continue now') }}</a></p>
    </main>
</body>
</html>
