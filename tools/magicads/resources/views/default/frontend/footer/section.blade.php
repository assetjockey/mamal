{{-- Footer — white minimalist, dashed dividers, indigo hover accents. --}}
@php
    // Defensive defaults: this footer is shared across themes/pages, so never
    // assume the view composer ran (e.g. stale caches, non-frontend layouts).
    $frontendSettings = $frontendSettings ?? null;
    $socialMedia = $socialMedia ?? null;

    $siteName = $generalSettings?->site_name ?? config('app.name', 'AI Ad Studio');

    // Logo served from the general_settings table. Falls back to the inline
    // logomark + wordmark when no logo has been uploaded in the admin.
    $logoSrc = $generalSettings?->logo_frontend ?: null;
    $logoUrl = filled($logoSrc)
        ? (str_starts_with((string) $logoSrc, 'http') ? $logoSrc : asset($logoSrc))
        : null;

    $social = [
        ['url' => $frontendSettings?->linkedin  ?: ($socialMedia?->linkedin_url  ?? null), 'label' => __('LinkedIn'),  'path' => 'M4.98 3.5A2.5 2.5 0 1 1 0 3.5a2.5 2.5 0 0 1 4.98 0zM.25 8h4.5V22H.25V8zm7.75 0h4.3v1.9h.1c.6-1.1 2.1-2.2 4.3-2.2 4.6 0 5.4 3 5.4 7V22h-4.5v-6c0-1.4 0-3.3-2-3.3s-2.4 1.6-2.4 3.2V22H8V8z'],
        ['url' => $frontendSettings?->twitter   ?: ($socialMedia?->twitter_url   ?? null), 'label' => __('Twitter'),   'path' => 'M18.9 4H22l-7.2 8.2L23 20h-6.6l-5.2-6.2L5.3 20H2.2l7.7-8.8L1 4h6.8l4.7 5.6L18.9 4z'],
        ['url' => $frontendSettings?->facebook  ?: null,                                    'label' => __('Facebook'),  'path' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
        ['url' => $frontendSettings?->youtube   ?: ($socialMedia?->youtube_url   ?? null), 'label' => __('YouTube'),   'path' => 'M23 7.2s-.2-1.6-.9-2.3c-.8-.9-1.8-.9-2.2-1-3.1-.2-7.9-.2-7.9-.2s-4.8 0-7.9.2c-.4.1-1.4.1-2.2 1C1.2 5.6 1 7.2 1 7.2S.8 9 .8 10.8v1.7C.8 14.2 1 16 1 16s.2 1.6.9 2.3c.8.9 1.9.9 2.4 1 1.8.1 7.7.2 7.7.2s4.8 0 7.9-.2c.4-.1 1.4-.1 2.2-1 .7-.7.9-2.3.9-2.3s.2-1.8.2-3.5v-1.7c0-1.8-.2-3.6-.2-3.6zM9.7 14.4v-6l6 3-6 3z'],
        ['url' => $frontendSettings?->instagram ?: ($socialMedia?->instagram_url ?? null), 'label' => __('Instagram'), 'path' => 'M12 2.2c3.2 0 3.6 0 4.8.1 1.2.1 1.8.2 2.3.4.5.2 1 .5 1.4.9.4.4.7.9.9 1.4.2.5.4 1.1.4 2.3.1 1.2.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 1.2-.2 1.8-.4 2.3-.2.5-.5 1-.9 1.4-.4.4-.9.7-1.4.9-.5.2-1.1.4-2.3.4-1.2.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-1.2-.1-1.8-.2-2.3-.4-.5-.2-1-.5-1.4-.9-.4-.4-.7-.9-.9-1.4-.2-.5-.4-1.1-.4-2.3C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8c.1-1.2.2-1.8.4-2.3.2-.5.5-1 .9-1.4.4-.4.9-.7 1.4-.9.5-.2 1.1-.4 2.3-.4 1.2-.1 1.6-.1 4.7-.1zm0 1.8c-3.1 0-3.5 0-4.7.1-1.1.1-1.7.2-2.1.4-.5.2-.9.4-1.2.8-.3.3-.6.7-.8 1.2-.1.4-.3 1-.4 2.1-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c.1 1.1.2 1.7.4 2.1.2.5.4.9.8 1.2.3.3.7.6 1.2.8.4.1 1 .3 2.1.4 1.2.1 1.6.1 4.7.1s3.5 0 4.7-.1c1.1-.1 1.7-.2 2.1-.4.5-.2.9-.4 1.2-.8.3-.3.6-.7.8-1.2.1-.4.3-1 .4-2.1.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c-.1-1.1-.2-1.7-.4-2.1-.2-.5-.4-.9-.8-1.2-.3-.3-.7-.6-1.2-.8-.4-.1-1-.3-2.1-.4-1.2-.1-1.6-.1-4.7-.1zM12 6.9a5.1 5.1 0 1 1 0 10.2 5.1 5.1 0 0 1 0-10.2zm0 1.8a3.3 3.3 0 1 0 0 6.6 3.3 3.3 0 0 0 0-6.6zm5.3-2a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z'],
        ['url' => $frontendSettings?->tiktok    ?: null,                                    'label' => __('TikTok'),    'path' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
    ];

    $productLinks = [
        ['label' => __('Features'),   'href' => '#features'],
        ['label' => __('Showcase'),   'href' => '#showcase'],
        ['label' => __('Pricing'),    'href' => '#pricing'],
        ['label' => __('FAQ'),        'href' => '#faq'],
    ];

    // Pricing is part of the SaaS billing feature — drop the footer link when
    // the magicads-saas extension/feature is inactive.
    if (! \App\Services\HelperService::extensionSaaS()) {
        $productLinks = array_values(array_filter($productLinks, fn ($l) => ! str_ends_with($l['href'], '#pricing')));
    }

    $legalLinks = [];
    if (filled($generalSettings?->contact_email ?? null)) {
        $legalLinks[] = ['label' => __('Contact'), 'href' => 'mailto:' . $generalSettings->contact_email];
    }
    $legalLinks[] = ['label' => __('Privacy policy'),  'href' => route('privacy')];
    $legalLinks[] = ['label' => __('Terms of service'),'href' => route('terms')];
@endphp

<section class="bg-white pt-8">
    <div class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
        <div class="l-dash mb-12"></div>

        <div class="grid grid-cols-1 gap-10 md:grid-cols-4">
            <div class="md:col-span-2">
                <a href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::localizeUrl('/') }}" class="inline-flex items-center gap-2.5 text-black">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-8 w-8 rounded-lg object-contain" width="32" height="32">
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-black">
                            <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#4F46E5]" aria-hidden="true">
                                <path fill="currentColor" d="M12 2 3 7v10l9 5 9-5V7zm0 2.3 6.8 3.8L12 11.9 5.2 8.1zM5 9.6l6 3.3v7L5 16.6zm14 0v7l-6 3.3v-7z"/>
                            </svg>
                        </span>
                    @endif
                    <span class="text-[15px] font-semibold tracking-tight">{{ $siteName }}</span>
                </a>
                @if (filled($generalSettings?->contact_email ?? null))
                    <a href="mailto:{{ $generalSettings->contact_email }}"
                       class="mt-5 inline-flex items-center gap-2 text-[15px] font-medium text-black/75 transition-colors hover:text-[#4F46E5]">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[var(--l-bg-2)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>
                            </svg>
                        </span>
                        <span>{{ $generalSettings->contact_email }}</span>
                    </a>
                @endif
                <p class="mt-6 max-w-sm text-[13px] text-black/55">
                    {{ __('Built for modern marketers — craft on-brand image, video, and copy in minutes.') }}
                </p>
                @if (collect($social)->contains(fn ($s) => filled($s['url'])))
                    <ul role="list" class="mt-6 flex items-center gap-2">
                        @foreach ($social as $s)
                            @if (filled($s['url']))
                                <li>
                                    <a href="{{ $s['url'] }}" aria-label="{{ $s['label'] }}" rel="noopener noreferrer" target="_blank"
                                       class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[var(--l-border)] bg-white text-black/70 transition-colors hover:border-[#4F46E5] hover:bg-[#4F46E5] hover:text-white">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="{{ $s['path'] }}"/>
                                        </svg>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <h3 class="l-mono text-[11px] font-semibold uppercase tracking-[0.2em] text-black/50">{{ __('Product') }}</h3>
                <ul role="list" class="mt-4 space-y-2.5 text-sm">
                    @foreach ($productLinks as $link)
                        <li><a href="{{ $link['href'] }}" class="text-black/75 transition-colors hover:text-[#4F46E5]">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="l-mono text-[11px] font-semibold uppercase tracking-[0.2em] text-black/50">{{ __('Legal') }}</h3>
                <ul role="list" class="mt-4 space-y-2.5 text-sm">
                    @foreach ($legalLinks as $link)
                        <li><a href="{{ $link['href'] }}" class="text-black/75 transition-colors hover:text-[#4F46E5]">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="l-dash my-10"></div>

        @php
            // Language switcher — driven by LaravelLocalization but gated by
            // `general_settings.languages` so only the locales an admin enabled
            // in the language manager are listed (same rule as the dashboard
            // user menu). Pure <details>/<summary> + anchor links so it works
            // without JS (the frontend layout has no Alpine). Opens upward.
            $supportedLocales = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getSupportedLocales();
            $currentLocale    = \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getCurrentLocale();

            $enabledLocaleCodes = collect(explode(',', (string) ($generalSettings?->languages ?? '')))
                ->map(fn ($code) => trim($code))
                ->filter()
                ->all();
            // Always keep the active locale visible even if it isn't in the
            // enabled set, so the user is never stranded.
            if (! in_array($currentLocale, $enabledLocaleCodes, true)) {
                $enabledLocaleCodes[] = $currentLocale;
            }
            $localeOptions = collect($supportedLocales)->only($enabledLocaleCodes)->all();
        @endphp

        <div class="flex flex-col items-center justify-between gap-3 text-[12px] text-black/50 sm:flex-row">
            <span>&copy; {{ now()->year }} {{ $siteName }}. {{ __('All rights reserved.') }}</span>

            @if (count($localeOptions) > 1)
                <details class="group relative inline-block">
                    <summary class="l-mono inline-flex cursor-pointer list-none items-center gap-1.5 rounded-full border border-[var(--l-border)] bg-white px-3 py-1.5 text-[11px] text-black/65 transition-colors hover:border-[#4F46E5] hover:text-black">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a10 10 0 0 1 0 14M10 3a10 10 0 0 0 0 14"/>
                        </svg>
                        <span>{{ $supportedLocales[$currentLocale]['native'] ?? strtoupper(str_replace('_', '-', $currentLocale)) }}</span>
                        <svg class="h-2.5 w-2.5 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 8l4 4 4-4"/>
                        </svg>
                    </summary>
                    <ul role="list"
                        class="absolute bottom-full right-0 z-50 mb-2 max-h-72 w-48 overflow-auto rounded-xl border border-[var(--l-border)] bg-white p-1 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.25)]">
                        @foreach ($localeOptions as $localeCode => $properties)
                            <li>
                                <a rel="alternate"
                                   hreflang="{{ $localeCode }}"
                                   href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                   @class([
                                       'flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-[12px] transition-colors',
                                       'bg-[#4F46E5] text-white' => $localeCode === $currentLocale,
                                       'text-black/70 hover:bg-[var(--l-bg-2)] hover:text-black' => $localeCode !== $currentLocale,
                                   ])>
                                    <span>{{ $properties['native'] ?? $properties['name'] ?? $localeCode }}</span>
                                    <span class="l-mono text-[10px] opacity-60">{{ strtoupper(str_replace('_', '-', $localeCode)) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    </div>
</section>
