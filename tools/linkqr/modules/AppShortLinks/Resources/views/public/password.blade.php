<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Protected link') }}</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { display: grid; min-height: 100vh; margin: 0; place-items: center; background: #f8fafc; color: #0f172a; }
        .card { width: min(25rem, calc(100vw - 2rem)); border: 1px solid #dbe5f0; border-radius: 1rem; background: #fff; padding: 1.25rem; box-shadow: 0 24px 70px -54px rgba(15,23,42,.65); }
        h1 { margin: 0 0 .5rem; font-size: 1.25rem; }
        p { margin: 0 0 1rem; color: #52647a; font-size: .925rem; line-height: 1.6; }
        input { width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: .75rem; padding: .75rem .85rem; font: inherit; }
        button { width: 100%; margin-top: .75rem; border: 0; border-radius: .75rem; background: #2563eb; color: #fff; padding: .8rem 1rem; font-weight: 700; cursor: pointer; }
        .error { margin-top: .65rem; color: #dc2626; font-size: .85rem; }
    </style>
</head>
<body>
    <main class="card">
        <h1>{{ __('Protected link') }}</h1>
        <p>{{ __('Enter the password to continue.') }}</p>
        <form method="POST" action="{{ route('short-links.public.unlock', ['code' => $shortLink->short_code]) }}">
            @csrf
            <input type="password" name="password" autocomplete="current-password" autofocus>
            <button type="submit">{{ __('Continue') }}</button>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </form>
    </main>
</body>
</html>
