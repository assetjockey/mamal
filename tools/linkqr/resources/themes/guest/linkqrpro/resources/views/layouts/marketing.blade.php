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
    $siteLogo = trim((string) ($options->get('website_logo_brand_dark') ?: $options->get('website_logo_dark') ?: $options->get('website_logo') ?: ''));
    $signupEnabled = (string) $options->get('auth_signup_page_status', '1') === '1';
    $navItems = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home') || request()->is('/')],
        ['label' => __('Features'), 'href' => route('home').'#features', 'active' => false],
        ['label' => __('Pricing'), 'href' => route('guest.pricing'), 'active' => request()->routeIs('guest.pricing') || request()->is('pricing*')],
        ['label' => __('FAQs'), 'href' => route('guest.faqs'), 'active' => request()->routeIs('guest.faqs') || request()->is('faqs*')],
        ['label' => __('Blog'), 'href' => route('guest.blogs'), 'active' => request()->routeIs('guest.blogs') || request()->routeIs('guest.blog-show') || request()->is('blogs*')],
        ['label' => __('Contact'), 'href' => route('guest.contact'), 'active' => request()->routeIs('guest.contact') || request()->is('contact*')],
    ];
    $activeLanguage = $languages->firstWhere('code', app()->getLocale()) ?? $languages->first();
@endphp
<body class="min-h-screen bg-[#fbfaf6] text-[#181714] antialiased" style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background:#fbfaf6; color:#181714;">
    <div class="relative min-h-screen overflow-hidden">
        <header x-data="{ open: false }" class="relative z-40">
            <div class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between px-5">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm font-semibold tracking-tight text-[#181714]">
                    @if ($siteLogo !== '')
                        <img src="{{ url($siteLogo) }}" alt="{{ $siteTitle }}" class="h-7 w-auto max-w-[9rem] object-contain">
                    @else
                        <span class="inline-flex h-5 w-5 items-center justify-center">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round">
                                <path d="M3 7.5 21 3l-5.2 17.5-4.2-7.4L3 7.5Z"/>
                                <path d="m11.6 13.1 4.8-5.2"/>
                            </svg>
                        </span>
                        <span>{{ $siteTitle }}</span>
                    @endif
                </a>

                <nav class="hidden items-center gap-7 text-[13px] font-semibold text-[#181714] md:flex">
                    @foreach ($navItems as $item)
                        <a href="{{ $item['href'] }}" class="{{ $item['active'] ? 'text-[#181714]' : 'text-[#57534b]' }} transition hover:opacity-60">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    <button
                        type="button"
                        x-data="{ mode: window.themeMode?.getMode?.() || 'light' }"
                        x-on:click="
                            const next = mode === 'dark' ? 'light' : 'dark';
                            window.themeMode?.setMode?.(next);
                            mode = next;
                        "
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[#d8d3c7] bg-white text-[#57534b] transition hover:bg-[#f4f0e7] hover:text-[#181714]"
                        aria-label="{{ __('Toggle appearance') }}"
                    >
                        <i class="fa-light fa-sun-bright text-base"></i>
                    </button>

                    @if ($languages->isNotEmpty())
                        <div x-data="{ languageOpen: false }" class="relative">
                            <button type="button" x-on:click="languageOpen = ! languageOpen" x-on:click.outside="languageOpen = false" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-[#d8d3c7] bg-white px-3 text-[#57534b] transition hover:bg-[#f4f0e7] hover:text-[#181714]" aria-label="{{ __('Change language') }}">
                                <span class="{{ language_flag_class($activeLanguage ?? app()->getLocale()) }} rounded-sm text-[18px]"></span>
                                <i class="fa-light fa-chevron-down text-[10px]"></i>
                            </button>
                            <div x-cloak x-show="languageOpen" x-transition.origin.top.right class="absolute right-0 top-full z-40 mt-3 w-52 rounded-xl border border-[#d8d3c7] bg-[#fffdf8] p-2 shadow-xl">
                                @foreach ($languages as $language)
                                    @php $isActiveLanguage = app()->getLocale() === $language->code; @endphp
                                    <a href="{{ route('language.switch', $language->code) }}" class="{{ $isActiveLanguage ? 'bg-[#181714] text-white' : 'text-[#57534b] hover:bg-[#f4f0e7] hover:text-[#181714]' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-bold">
                                        <span class="{{ language_flag_class($language) }} rounded-sm text-[18px]"></span>
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
                        <a href="{{ route('portal.dashboard') }}" class="rounded-lg border border-[#d8d3c7] bg-white px-4 py-2 text-xs font-semibold text-[#181714] transition hover:bg-[#f4f0e7]">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-xs font-semibold text-[#57534b] transition hover:text-[#181714]">{{ __('Login') }}</a>
                        @if ($signupEnabled)
                            <a href="{{ url('/register') }}" class="rounded-lg border border-[#d8d3c7] bg-white px-4 py-2 text-xs font-semibold text-[#181714] transition hover:bg-[#f4f0e7]">{{ __('Start free') }}</a>
                        @endif
                    @endauth
                </div>

                <button type="button" x-on:click="open = ! open" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#d8d3c7] bg-white text-[#181714] md:hidden" aria-label="{{ __('Open menu') }}">
                    <i class="fa-light fa-bars"></i>
                </button>
            </div>

            <div x-cloak x-show="open" x-transition class="mx-5 rounded-xl border border-[#d8d3c7] bg-[#fbfaf6] p-3 shadow-xl md:hidden">
                <nav class="grid gap-1">
                    <div class="mb-2 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            x-data="{ mode: window.themeMode?.getMode?.() || 'light' }"
                            x-on:click="
                                const next = mode === 'dark' ? 'light' : 'dark';
                                window.themeMode?.setMode?.(next);
                                mode = next;
                            "
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-[#d8d3c7] bg-white text-[#57534b]"
                            aria-label="{{ __('Toggle appearance') }}"
                        >
                            <i class="fa-light fa-sun-bright"></i>
                        </button>
                        @if ($languages->isNotEmpty())
                            <a href="{{ route('language.switch', $activeLanguage?->code ?? app()->getLocale()) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#d8d3c7] bg-white text-[#57534b]">
                                <span class="{{ language_flag_class($activeLanguage ?? app()->getLocale()) }} rounded-sm text-[18px]"></span>
                            </a>
                        @endif
                    </div>
                    @foreach ($navItems as $item)
                        <a href="{{ $item['href'] }}" class="{{ $item['active'] ? 'text-[#181714]' : 'text-[#8a867d]' }} rounded-lg px-3 py-2 text-sm font-bold hover:bg-[#f4f1ea] hover:text-[#181714]">{{ $item['label'] }}</a>
                    @endforeach
                    @auth
                        <a href="{{ route('portal.dashboard') }}" class="mt-2 rounded-lg bg-[#181714] px-3 py-2 text-center text-sm font-bold text-white">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="mt-2 rounded-lg border border-[#d8d3c7] bg-white px-3 py-2 text-center text-sm font-bold text-[#181714]">{{ __('Login') }}</a>
                        @if ($signupEnabled)
                            <a href="{{ url('/register') }}" class="mt-2 rounded-lg bg-[#181714] px-3 py-2 text-center text-sm font-bold text-white">{{ __('Start free') }}</a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>

        <footer class="relative mt-16 border-t border-[#e8dfd1] px-5 pb-10 pt-10">
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 opacity-20">
                <svg viewBox="0 0 1200 160" class="h-full w-full" fill="none" stroke="#181714" stroke-width="1.25" stroke-linecap="round">
                    <path d="M42 116c28-30 55-17 38 11-10 17-44 17-51 0-8-18 14-36 34-40M244 132l27-53 30 51M264 96l27 2M520 130c30-10 49-4 54 9M640 118c24 18 52 18 78 0M903 122c25-42 60-43 73-16 13 25-16 47-40 29-22-15-16-52 12-60M1092 128l24-39 29 43M1113 103l25 2"/>
                </svg>
            </div>

            <div class="relative mx-auto grid max-w-6xl gap-10 lg:grid-cols-[1fr_1.4fr_0.8fr] lg:items-start">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center">
                        @if ($siteLogo !== '')
                            <img src="{{ url($siteLogo) }}" alt="{{ $siteTitle }}" class="h-8 w-auto max-w-[10rem] object-contain">
                        @else
                            <span class="font-serif text-2xl text-[#181714]">{{ $siteTitle }}</span>
                        @endif
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-7 text-[#6d685f]">{{ __('One workspace for Bio pages, short links, QR campaigns, and click reporting.') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6 text-sm sm:grid-cols-3">
                    <div class="grid gap-3">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Product') }}</p>
                        <a href="{{ route('home') }}#features" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Features') }}</a>
                        <a href="{{ route('guest.pricing') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Pricing') }}</a>
                        <a href="{{ route('guest.faqs') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('FAQs') }}</a>
                    </div>
                    <div class="grid gap-3">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Resources') }}</p>
                        <a href="{{ route('guest.blogs') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Blog') }}</a>
                        <a href="{{ route('guest.contact') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Contact') }}</a>
                        <a href="{{ route('guest.social-pages') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Social Pages') }}</a>
                    </div>
                    <div class="grid gap-3">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Legal') }}</p>
                        <a href="{{ route('guest.privacy-policy') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Privacy Policy') }}</a>
                        <a href="{{ route('guest.terms-of-use') }}" class="font-bold text-[#57534b] hover:text-[#181714]">{{ __('Terms of Use') }}</a>
                    </div>
                </div>

                <div class="rounded-[1.2rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_20px_60px_-54px_rgba(24,23,20,.45)]">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Start') }}</p>
                    <p class="mt-2 text-sm leading-6 text-[#6d685f]">{{ __('Claim a username and publish your first useful profile.') }}</p>
                    @if ($signupEnabled)
                        <a href="{{ url('/register') }}" class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#181714] px-4 text-sm font-bold text-white">{{ __('Start free') }}</a>
                    @endif
                </div>
            </div>

            <div class="relative mx-auto mt-10 flex max-w-6xl flex-col gap-3 border-t border-[#e8dfd1] pt-5 text-xs font-medium text-[#8a867d] sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ __('All rights reserved.') }}</p>
                <p>{{ __('Bio pages') }} · {{ __('Short links') }} · {{ __('QR analytics') }}</p>
            </div>
        </footer>
    </div>
    @include(theme_view('partials.gdpr-consent', 'guest'))
    @include(theme_view('partials.embed-code-body', 'guest'))
</body>
</html>
