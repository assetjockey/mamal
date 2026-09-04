<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ current_locale_direction() }}">
<head>
    @include(theme_view('partials.head', 'guest'), ['title' => $pageTitle ?? null])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
@php
    $languages = available_languages();
    $options = app(\Modules\AdminSettings\Support\OptionStore::class);
    $settingsTitle = trim((string) $options->get('website_title', ''));
    $siteTitle = $settingsTitle !== '' && ! str_contains(strtolower($settingsTitle), 'stackposts') ? $settingsTitle : 'LinkQR Pro';
    $siteLogoDark = trim((string) ($options->get('website_logo_brand_dark')
        ?: $options->get('website_logo_dark')
        ?: $options->get('website_logo')
        ?: 'public/img/logo-brand-dark.png'));
    $siteLogoLight = trim((string) ($options->get('website_logo_brand_light')
        ?: $options->get('website_logo_light')
        ?: $options->get('website_logo')
        ?: 'public/img/logo-brand-light.png'));
    $siteDescription = trim((string) $options->get('website_description', ''));
    $siteDescription = $siteDescription !== '' ? $siteDescription : __('AI Bio Links, Dynamic QR Codes, branded Short Links, and campaign analytics for brands and agencies.');
    $signupEnabled = (string) $options->get('auth_signup_page_status', '1') === '1';
    $contactEmail = trim((string) $options->get('contact_email', ''));
    $navItems = [
        ['key' => 'home', 'label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['key' => 'features', 'label' => __('Features'), 'href' => route('home').'#features', 'active' => false],
        ['key' => 'pricing', 'label' => __('Pricing'), 'href' => route('guest.pricing'), 'active' => request()->routeIs('guest.pricing')],
        ['key' => 'faqs', 'label' => __('FAQs'), 'href' => route('guest.faqs'), 'active' => request()->routeIs('guest.faqs')],
        ['key' => 'blog', 'label' => __('Blog'), 'href' => route('guest.blogs'), 'active' => request()->routeIs('guest.blogs') || request()->routeIs('guest.blog*')],
        ['key' => 'contact', 'label' => __('Contact'), 'href' => route('guest.contact'), 'active' => request()->routeIs('guest.contact')],
    ];
    $footerLegal = [
        ['label' => __('Privacy Policy'), 'href' => route('guest.privacy-policy')],
        ['label' => __('Terms of Use'), 'href' => route('guest.terms-of-use')],
    ];
@endphp
<body class="min-h-screen antialiased" style="font-family: var(--theme-font-sans); color: var(--theme-header-text-color);">
    <div class="relative isolate min-h-screen">
        <header
            x-data="{
                open: false,
                scrolled: false,
                mode: window.themeMode?.getMode?.() || 'dark',
                resolved: window.themeMode?.getResolved?.() || 'dark',
                syncTheme(state = null) {
                    this.mode = state?.mode || window.themeMode?.getMode?.() || 'dark';
                    this.resolved = state?.resolved || window.themeMode?.getResolved?.() || 'dark';
                },
                toggleTheme() {
                    this.syncTheme(window.themeMode?.toggle?.());
                }
            }"
            x-init="
                scrolled = window.scrollY > 12;
                syncTheme();
                window.addEventListener('scroll', () => { scrolled = window.scrollY > 12 }, { passive: true });
                window.addEventListener('theme-mode-changed', (event) => syncTheme(event.detail));
            "
            class="sticky top-0 z-50"
        >
            <div
                class="border-b bg-white/82 backdrop-blur-2xl transition"
                style="border-color: rgba(var(--theme-border-color-rgb),0.78);"
                x-bind:class="scrolled ? 'bg-white/94 shadow-[0_18px_58px_-48px_rgba(15,23,42,0.42)]' : ''"
            >
                <div class="linkqr-shell">
                    <div class="flex min-h-[4.75rem] items-center justify-between gap-5">
                        <a href="{{ route('home') }}" class="group flex min-w-0 items-center no-theme-link">
                            <span class="relative inline-flex h-12 shrink-0 items-center justify-center overflow-hidden">
                                @if ($siteLogoDark !== '' || $siteLogoLight !== '')
                                    <img
                                        src="{{ url($siteLogoDark) }}"
                                        x-bind:src="resolved === 'dark' ? @js(url($siteLogoLight)) : @js(url($siteLogoDark))"
                                        alt="{{ $siteTitle }}"
                                        class="h-10 w-auto max-w-[13rem] object-contain"
                                    >
                                @else
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.95rem] text-white shadow-[0_18px_34px_-24px_rgba(36,84,232,0.9)]" style="background: linear-gradient(135deg,#2454e8,#14b8a6);">
                                        <i class="fa-light fa-qrcode text-lg"></i>
                                    </span>
                                @endif
                            </span>
                        </a>

                        <nav class="hidden items-center gap-8 lg:flex">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" class="linkqr-nav-link group relative py-7 text-sm font-extrabold transition {{ $item['active'] ? 'text-slate-950' : 'text-slate-500 hover:text-slate-950' }}">
                                    {{ $item['label'] }}
                                    <span class="absolute inset-x-0 bottom-0 h-[3px] origin-center rounded-full bg-gradient-to-r from-blue-600 to-teal-500 transition {{ $item['active'] ? 'scale-x-100 opacity-100' : 'scale-x-0 opacity-0 group-hover:scale-x-100 group-hover:opacity-100' }}"></span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="hidden items-center gap-2 lg:flex">
                            <button
                                type="button"
                                x-on:click="toggleTheme()"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem] border bg-white/88 text-slate-700 shadow-sm transition hover:bg-slate-50"
                                style="border-color: rgba(var(--theme-border-color-rgb),0.85);"
                                x-bind:aria-label="resolved === 'dark' ? @js(__('Switch to light mode')) : @js(__('Switch to dark mode'))"
                                x-bind:title="resolved === 'dark' ? @js(__('Switch to light mode')) : @js(__('Switch to dark mode'))"
                            >
                                <i class="fa-light text-sm" x-bind:class="resolved === 'dark' ? 'fa-sun-bright' : 'fa-moon-stars'"></i>
                            </button>

                            @if ($languages->isNotEmpty())
                                @php $activeLanguage = $languages->firstWhere('code', app()->getLocale()) ?? $languages->first(); @endphp
                                <div x-data="{ openLanguage: false }" class="relative">
                                    <button type="button" x-on:click="openLanguage = ! openLanguage" x-on:click.outside="openLanguage = false" class="inline-flex h-11 items-center gap-2 rounded-[0.9rem] border bg-white/88 px-3 text-sm font-bold text-slate-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.85);">
                                        <span class="{{ language_flag_class($activeLanguage ?? app()->getLocale()) }} rounded-sm text-[17px]"></span>
                                    </button>
                                    <div x-cloak x-show="openLanguage" x-transition.origin.top.right class="absolute right-0 z-30 mt-3 w-56 overflow-hidden rounded-[1rem] border bg-white p-2 shadow-xl" style="border-color: rgba(var(--theme-border-color-rgb),0.85);">
                                        @foreach ($languages as $language)
                                            @php $isActiveLanguage = app()->getLocale() === $language->code; @endphp
                                            <a href="{{ route('language.switch', $language->code) }}" class="flex items-center gap-3 rounded-[0.8rem] px-3 py-2.5 text-sm font-semibold transition {{ $isActiveLanguage ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                                <span class="{{ language_flag_class($language) }} rounded-sm text-[17px]"></span>
                                                <span class="flex-1">{{ $language->name ?? strtoupper((string) $language->code) }}</span>
                                                @if ($isActiveLanguage)
                                                    <i class="fa-light fa-check text-xs"></i>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @auth
                                <a href="{{ route('portal.dashboard') }}" class="linkqr-button-primary linkqr-sheen inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-bold">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-100 hover:text-slate-950">{{ __('Log in') }}</a>
                                @if ($signupEnabled)
                                    <a href="{{ route('register') }}" class="linkqr-button-primary linkqr-sheen inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-bold">{{ __('Start free') }}</a>
                                @endif
                            @endauth
                        </div>

                        <button type="button" x-on:click="open = ! open" class="inline-flex h-11 w-11 items-center justify-center rounded-[0.95rem] border bg-white text-slate-700 shadow-sm lg:hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.85);">
                            <i class="fa-light fa-bars"></i>
                        </button>
                    </div>

                    <div x-cloak x-show="open" x-transition class="border-t pb-4 pt-3 lg:hidden" style="border-color: rgba(var(--theme-border-color-rgb),0.75);">
                        <nav class="grid gap-1">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" class="rounded-[0.95rem] px-3 py-2.5 text-sm font-bold {{ $item['active'] ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">{{ $item['label'] }}</a>
                            @endforeach
                        </nav>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            <button
                                type="button"
                                x-on:click="toggleTheme()"
                                class="linkqr-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-semibold sm:col-span-2"
                            >
                                <i class="fa-light mr-2" x-bind:class="resolved === 'dark' ? 'fa-sun-bright' : 'fa-moon-stars'"></i>
                                <span x-text="resolved === 'dark' ? @js(__('Light mode')) : @js(__('Dark mode'))"></span>
                            </button>
                            @auth
                                <a href="{{ route('portal.dashboard') }}" class="linkqr-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-bold">{{ __('Dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="linkqr-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-semibold">{{ __('Log in') }}</a>
                                @if ($signupEnabled)
                                    <a href="{{ route('register') }}" class="linkqr-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-4 py-3 text-sm font-bold">{{ __('Start free') }}</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                    </div>
            </div>
        </header>

        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        <footer class="mt-12 border-t bg-white/86 backdrop-blur-xl" style="border-color: rgba(var(--theme-border-color-rgb),0.78);">
            <div class="linkqr-shell py-10">
                <div class="grid gap-10 lg:grid-cols-[1.25fr_0.8fr_0.8fr_1fr]">
                    <div>
                        <a href="{{ route('home') }}" class="inline-flex items-center no-theme-link">
                            <span class="inline-flex h-12 shrink-0 items-center justify-center overflow-hidden">
                                @if ($siteLogoDark !== '' || $siteLogoLight !== '')
                                    <img
                                        src="{{ url($siteLogoDark) }}"
                                        alt="{{ $siteTitle }}"
                                        class="h-10 w-auto max-w-[13rem] object-contain dark:hidden"
                                    >
                                    <img
                                        src="{{ url($siteLogoLight) }}"
                                        alt="{{ $siteTitle }}"
                                        class="hidden h-10 w-auto max-w-[13rem] object-contain dark:block"
                                    >
                                @else
                                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.95rem] text-white" style="background: linear-gradient(135deg,#2454e8,#14b8a6);">
                                        <i class="fa-light fa-qrcode"></i>
                                    </span>
                                @endif
                            </span>
                        </a>
                        <p class="mt-4 max-w-md text-sm leading-7 text-slate-500">{{ $siteDescription }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Product') }}</p>
                        <div class="mt-4 grid gap-3 text-sm font-bold text-slate-600">
                            <a href="{{ route('home') }}#features" class="hover:text-blue-700">{{ __('Features') }}</a>
                            <a href="{{ route('guest.pricing') }}" class="hover:text-blue-700">{{ __('Pricing') }}</a>
                            <a href="{{ route('guest.faqs') }}" class="hover:text-blue-700">{{ __('FAQs') }}</a>
                            <a href="{{ route('guest.blogs') }}" class="hover:text-blue-700">{{ __('Blog') }}</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Company') }}</p>
                        <div class="mt-4 grid gap-3 text-sm font-bold text-slate-600">
                            <a href="{{ route('guest.contact') }}" class="hover:text-blue-700">{{ __('Contact') }}</a>
                            @foreach ($footerLegal as $item)
                                <a href="{{ $item['href'] }}" class="hover:text-blue-700">{{ $item['label'] }}</a>
                            @endforeach
                            @if ($contactEmail !== '')
                                <a href="mailto:{{ $contactEmail }}" class="hover:text-blue-700">{{ $contactEmail }}</a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Built for') }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ([__('Bio links'), __('Dynamic QR'), __('Short links'), __('Campaign analytics')] as $badge)
                                <span class="rounded-full border bg-white px-3 py-1.5 text-[11px] font-bold text-slate-600 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">{{ $badge }}</span>
                            @endforeach
                        </div>
                        <a href="{{ $signupEnabled ? route('register') : route('guest.contact') }}" class="linkqr-button-primary linkqr-sheen mt-5 inline-flex h-11 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-bold">
                            {{ $signupEnabled ? __('Start free') : __('Contact us') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t" style="border-color: rgba(var(--theme-border-color-rgb),0.78);">
                <div class="linkqr-shell flex flex-col gap-3 py-4 text-xs font-bold text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ __('All rights reserved.') }}</p>
                    <p>{{ __('AI Bio Links, Dynamic QR Codes, Short Links, and analytics in one SaaS.') }}</p>
                </div>
            </div>
        </footer>
    </div>
    @include(theme_view('partials.gdpr-consent', 'guest'))
    @include(theme_view('partials.embed-code-body', 'guest'))
</body>
</html>
