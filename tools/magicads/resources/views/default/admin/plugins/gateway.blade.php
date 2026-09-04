<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Secure Checkout') }} · {{ config('app.name') }}</title>
    @if (!empty($_SERVER['HTTPS']))
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
    @endif
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #ffffff;
            color: #0F172A;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #09090b; color: #fafafa; }
            .card { background: #101013 !important; border-color: rgba(255,255,255,0.08) !important; }
            .spinner { border-color: rgba(255,255,255,0.12) !important; border-top-color: #818CF8 !important; }
            h1 { color: #fafafa !important; }
            p { color: #a1a1aa !important; }
            .secured { color: #52525b !important; }
            .back { color: #818CF8 !important; }
        }
        .card {
            width: 100%;
            max-width: 400px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 20px;
            padding: 2.75rem 2rem;
            box-shadow: 0 30px 60px -25px rgba(15,23,42,0.35);
        }
        .badge {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Brand gradient — recipe 1 (full brand UI). */
            background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);
            color: #ffffff;
        }
        .badge svg { width: 30px; height: 30px; }
        .spinner {
            width: 40px;
            height: 40px;
            margin: 1.75rem auto 1.25rem;
            border-radius: 50%;
            border: 3px solid #e4e4e7;
            border-top-color: #4F46E5;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 1.1rem; font-weight: 700; color: #0F172A; margin: 0 0 0.5rem; }
        p { font-size: 0.85rem; line-height: 1.5; color: #71717a; margin: 0; }
        .error {
            display: none;
            margin-top: 1.25rem;
            font-size: 0.8rem;
            color: #e11d48;
            background: rgba(225,29,72,0.06);
            border: 1px solid rgba(225,29,72,0.18);
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }
        .back {
            display: none;
            margin-top: 1.25rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4F46E5;
            text-decoration: none;
        }
        .back:hover { text-decoration: underline; }
        .secured { margin-top: 1.75rem; font-size: 0.68rem; letter-spacing: 0.08em; text-transform: uppercase; color: #a1a1aa; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>

        <div id="loading-state">
            <div class="spinner"></div>
            <h1>{{ __('Initiating secure payment') }}</h1>
            <p>{{ __('Please do not close this tab. You are being redirected to our secure payment provider…') }}</p>
        </div>

        <div id="error-state" class="error"></div>
        <a id="back-link" class="back" href="{{ route('admin.plugins') }}">{{ __('Return to Plugins') }}</a>

        <div class="secured">{{ __('Secured by Stripe') }}</div>
    </div>

    <script>
        (function () {
            const processUrl = @json(route('admin.plugins.payments.process'));
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function showError(message) {
                document.getElementById('loading-state').style.display = 'none';
                const err = document.getElementById('error-state');
                err.textContent = message || @json(__('Something went wrong while starting the payment.'));
                err.style.display = 'block';
                document.getElementById('back-link').style.display = 'inline-block';
            }

            fetch(processUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.status || !data.url) {
                    showError(data.message);
                    return;
                }
                window.location.href = data.url;
            })
            .catch(() => showError());
        })();
    </script>
</body>
</html>
