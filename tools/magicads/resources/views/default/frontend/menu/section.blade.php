{{-- Navigation — refined transparent bar, animated underlines, ⌘K hint. --}}
@php
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

    $registrationEnabled = class_exists(\Laravel\Fortify\Features::class)
        && \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration());

    $siteName = $generalSettings?->site_name ?? config('app.name', 'AI Ad Studio');

    // Anchor links should point at the localized home URL so users on /de keep their locale
    // when clicking "Features", "Pricing", etc.
    $home = LaravelLocalization::localizeUrl('/');

    // Logo served from the general_settings table. Falls back to the inline
    // logomark + wordmark when no logo has been uploaded in the admin.
    $logoSrc = $generalSettings?->logo_frontend ?: null;
    $logoUrl = filled($logoSrc)
        ? (str_starts_with((string) $logoSrc, 'http') ? $logoSrc : asset($logoSrc))
        : null;

    $navLinks = [
        ['href' => $home . '#features',     'label' => __('Features')],
        ['href' => $home . '#showcase',     'label' => __('Showcase')],
        ['href' => $home . '#how-it-works', 'label' => __('How it works')],
        ['href' => $home . '#pricing',      'label' => __('Pricing')],
        ['href' => $home . '#faq',          'label' => __('FAQ')],
    ];

    // Pricing is part of the SaaS billing feature — drop the nav link when the
    // magicads-saas extension/feature is inactive.
    if (! \App\Services\HelperService::extensionSaaS()) {
        $navLinks = array_values(array_filter($navLinks, fn ($l) => ! str_ends_with($l['href'], '#pricing')));
    }

    // Locale switcher — driven by LaravelLocalization but gated by
    // `general_settings.languages` so only the locales an admin enabled in the
    // language manager are listed (same rule as the footer). Pure
    // <details>/<summary> + anchor links so it works without JS.
    $supportedLocales = LaravelLocalization::getSupportedLocales();
    $currentLocale    = LaravelLocalization::getCurrentLocale();

    $enabledLocaleCodes = collect(explode(',', (string) ($generalSettings?->languages ?? '')))
        ->map(fn ($code) => trim($code))
        ->filter()
        ->all();
    // Always keep the active locale visible even if it isn't in the enabled
    // set, so the user is never stranded.
    if (! in_array($currentLocale, $enabledLocaleCodes, true)) {
        $enabledLocaleCodes[] = $currentLocale;
    }
    $localeOptions = collect($supportedLocales)->only($enabledLocaleCodes)->all();
@endphp

<nav
    aria-label="{{ __('Primary') }}"
    data-landing-nav
    data-nav-scroll-watch
    class="fixed inset-x-0 top-0 z-50"
>
    <div class="l-navbar">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            {{-- Logo with gradient ring on hover --}}
            <a href="{{ LaravelLocalization::localizeUrl('/') }}" class="group inline-flex items-center gap-2.5 text-white">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-9 w-9 rounded-full object-contain" width="36" height="36">
                @else
                    <span class="l-logomark relative inline-flex h-9 w-9 items-center justify-center rounded-full bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-black transition-transform group-hover:scale-110" aria-hidden="true">
                            <path fill="currentColor" d="M12 2 3 7v10l9 5 9-5V7zm0 2.3 6.8 3.8L12 11.9 5.2 8.1zM5 9.6l6 3.3v7L5 16.6zm14 0v7l-6 3.3v-7z"/>
                        </svg>
                    </span>
                @endif
                <span class="text-[16px] font-semibold tracking-[-0.01em]">{{ $siteName }}</span>
            </a>

            {{-- Center links with animated underline --}}
            <ul role="list" class="hidden items-center gap-7 text-[13.5px] font-medium text-white/75 lg:flex">
                @foreach ($navLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="l-navlink inline-flex items-center gap-1 transition-colors hover:text-white">
                            {{ $link['label'] }}
                            @if ($link['href'] === '#pricing')
                                <span class="l-mono rounded-sm bg-emerald-400/15 px-1 py-[1px] text-[8px] font-bold uppercase tracking-wider text-emerald-300">{{ __('New') }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="flex items-center gap-2">

                {{-- Locale switcher — Flux language icon trigger, opens a dropdown of enabled locales. --}}
                @if (count($localeOptions) > 1)
                    <details class="group relative hidden sm:inline-block">
                        <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-full border border-white/15 px-3 py-2 text-[13px] font-medium text-white/75 transition-colors hover:bg-white/10 hover:text-white">
                            <flux:icon.languages class="h-4 w-4" />
                            <span>{{ $supportedLocales[$currentLocale]['native'] ?? strtoupper(str_replace('_', '-', $currentLocale)) }}</span>
                            <svg class="h-2.5 w-2.5 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8l4 4 4-4"/>
                            </svg>
                        </summary>
                        <ul role="list"
                            class="absolute right-0 top-full z-50 mt-2 max-h-72 w-48 overflow-auto rounded-xl border border-white/10 bg-black/90 p-1 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.6)] backdrop-blur-lg">
                            @foreach ($localeOptions as $localeCode => $properties)
                                <li>
                                    <a rel="alternate"
                                       hreflang="{{ $localeCode }}"
                                       href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                       @class([
                                           'flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-[12px] transition-colors',
                                           'bg-white text-black' => $localeCode === $currentLocale,
                                           'text-white/70 hover:bg-white/5 hover:text-white' => $localeCode !== $currentLocale,
                                       ])>
                                        <span>{{ $properties['native'] ?? $properties['name'] ?? $localeCode }}</span>
                                        <span class="l-mono text-[10px] opacity-60">{{ strtoupper(str_replace('_', '-', $localeCode)) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endif

                @auth
                    <a href="{{ route('user.dashboard') }}"
                       class="group inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-[13px] font-semibold text-black transition-transform hover:scale-[1.03]">
                        <span>{{ __('Dashboard') }}</span>
                        <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden rounded-full px-4 py-2 text-[13px] font-medium text-white/75 transition-colors hover:text-white sm:inline-flex">
                        {{ __('Sign in') }}
                    </a>
                    @if ($registrationEnabled)
                        <a href="{{ route('register') }}"
                           class="group inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-[13px] font-semibold text-black transition-transform hover:scale-[1.03]">
                            <span>{{ __('Get started') }}</span>
                            <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                            </svg>
                        </a>
                    @endif
                @endauth

                <button type="button"
                        data-mobile-nav-toggle
                        aria-controls="mobile-nav-panel"
                        aria-expanded="false"
                        aria-label="{{ __('Open menu') }}"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white transition-colors hover:bg-white/10 lg:hidden">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-nav-panel" hidden class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-white/10 bg-black/90 p-3 backdrop-blur-lg">
            <ul role="list" class="flex flex-col gap-0.5">
                @foreach ($navLinks as $link)
                    <li>
                        <a href="{{ $link['href'] }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-white/75 transition-colors hover:bg-white/5 hover:text-white">
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
                @guest
                    <li class="mt-1 border-t border-white/10 pt-2">
                        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-white/75 hover:bg-white/5 hover:text-white">
                            {{ __('Sign in') }}
                        </a>
                    </li>
                @endguest
            </ul>

            @if (count($localeOptions) > 1)
                <div class="mt-2 border-t border-white/10 pt-2">
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm font-medium text-white/75 transition-colors hover:bg-white/5 hover:text-white">
                            <span class="inline-flex items-center gap-1.5">
                                <flux:icon.languages class="h-4 w-4" />
                                {{ $supportedLocales[$currentLocale]['native'] ?? strtoupper(str_replace('_', '-', $currentLocale)) }}
                            </span>
                            <svg class="h-2.5 w-2.5 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8l4 4 4-4"/>
                            </svg>
                        </summary>
                        <ul role="list" class="mt-1 flex max-h-56 flex-col gap-0.5 overflow-auto">
                            @foreach ($localeOptions as $localeCode => $properties)
                                <li>
                                    <a rel="alternate"
                                       hreflang="{{ $localeCode }}"
                                       href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                       @class([
                                           'flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm transition-colors',
                                           'bg-white text-black' => $localeCode === $currentLocale,
                                           'text-white/75 hover:bg-white/5 hover:text-white' => $localeCode !== $currentLocale,
                                       ])>
                                        <span>{{ $properties['native'] ?? $properties['name'] ?? $localeCode }}</span>
                                        <span class="l-mono text-[10px] opacity-60">{{ strtoupper(str_replace('_', '-', $localeCode)) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </div>
            @endif
        </div>
    </div>
</nav>
