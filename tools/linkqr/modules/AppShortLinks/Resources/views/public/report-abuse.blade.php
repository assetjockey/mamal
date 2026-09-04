<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Report abuse') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 px-4 py-10 text-slate-950">
    <main class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600">!</span>
            <div>
                <h1 class="text-xl font-semibold">{{ __('Report abusive short link') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $shortLink->shortUrl() }}</p>
            </div>
        </div>

        @if (session('reported'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ __('Thanks. Your report has been submitted for review.') }}
            </div>
        @else
            <form method="POST" action="{{ route('short-links.public.report.store', ['code' => $shortLink->short_code]) }}" class="mt-6 space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm font-semibold">{{ __('Reason') }}</span>
                    <select name="reason" class="mt-2 h-11 w-full rounded-xl border border-slate-300 px-3 text-sm">
                        <option value="phishing">{{ __('Phishing') }}</option>
                        <option value="malware">{{ __('Malware') }}</option>
                        <option value="spam">{{ __('Spam') }}</option>
                        <option value="impersonation">{{ __('Impersonation') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                    @error('reason') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">{{ __('Details') }}</span>
                    <textarea name="details" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">{{ old('details') }}</textarea>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">{{ __('Email') }}</span>
                    <input type="email" name="reporter_email" value="{{ old('reporter_email') }}" class="mt-2 h-11 w-full rounded-xl border border-slate-300 px-3 text-sm">
                </label>
                <button class="h-11 rounded-xl bg-black px-5 text-sm font-semibold text-white">{{ __('Submit report') }}</button>
            </form>
        @endif
    </main>
</body>
</html>
