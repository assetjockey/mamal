<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include(theme_view('partials.head', 'guest'))
        @livewireStyles
    </head>
    @php
        $brandOptions = app(\Modules\AdminSettings\Support\OptionStore::class);
        $authDarkLogoPath = (string) ($brandOptions->get('website_logo_brand_dark')
            ?: $brandOptions->get('website_logo_dark')
            ?: 'public/img/logo-brand-dark.png');
        $authLightLogoPath = (string) ($brandOptions->get('website_logo_brand_light')
            ?: $brandOptions->get('website_logo_light')
            ?: 'public/img/logo-brand-light.png');
        $resolvedAuthDarkLogo = url($authDarkLogoPath);
        $resolvedAuthLightLogo = url($authLightLogoPath);
        $fallbackAuthDarkLogo = url('public/img/logo-brand-dark.png');
        $fallbackAuthLightLogo = url('public/img/logo-brand-light.png');
    @endphp
    <body class="min-h-screen antialiased" style="background-color: var(--theme-body-bg); color: var(--theme-header-text-color); font-family: var(--theme-font-sans);">
        <div class="relative min-h-screen overflow-hidden px-4 py-6 sm:px-6 lg:px-8">
            <div class="linkqr-shell relative grid min-h-[calc(100vh-3rem)] gap-6 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <section class="hidden lg:block">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3" wire:navigate>
                        <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="h-11 w-auto object-contain dark:hidden" onerror="this.onerror=null;this.src='{{ $fallbackAuthDarkLogo }}';">
                        <img src="{{ $resolvedAuthLightLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="hidden h-11 w-auto object-contain dark:block" onerror="this.onerror=null;this.src='{{ $fallbackAuthLightLogo }}';">
                    </a>

                    <div class="mt-12 max-w-xl">
                        <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                            <i class="fa-light fa-qrcode"></i>
                            {{ __('LinkQR workspace') }}
                        </span>
                        <h1 class="mt-6 text-5xl font-extrabold leading-[0.98] tracking-[-0.07em] text-slate-950">
                            {{ __('Manage every Bio link and QR campaign from one account.') }}
                        </h1>
                        <p class="mt-5 text-base leading-8 text-slate-600">
                            {{ __('Create branded Bio pages, dynamic QR codes, short links, custom domains, UTM presets, and analytics alerts without leaving the portal.') }}
                        </p>
                    </div>

                    <div class="mt-10 grid max-w-xl gap-4">
                        @foreach ([['fa-light fa-link', __('Bio pages'), __('Launch mobile-first link hubs.')], ['fa-light fa-route', __('Dynamic redirects'), __('Route scans by device, country, or campaign time.')], ['fa-light fa-chart-line', __('Analytics'), __('Read scan, click, CTA, and health signals.')]] as $item)
                            <div class="linkqr-card linkqr-hover-lift rounded-[1.1rem] p-4">
                                <div class="flex gap-4">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.9rem] bg-blue-50 text-blue-700">
                                        <i class="{{ $item[0] }}"></i>
                                    </span>
                                    <div>
                                        <p class="font-extrabold text-slate-950">{{ $item[1] }}</p>
                                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $item[2] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="flex min-h-[calc(100vh-3rem)] items-center justify-center lg:min-h-0">
                    <div class="w-full max-w-[36rem]">
                        <div class="mb-6 flex items-center justify-between gap-4 lg:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center" wire:navigate>
                                <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="h-10 w-auto object-contain dark:hidden" onerror="this.onerror=null;this.src='{{ $fallbackAuthDarkLogo }}';">
                                <img src="{{ $resolvedAuthLightLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="hidden h-10 w-auto object-contain dark:block" onerror="this.onerror=null;this.src='{{ $fallbackAuthLightLogo }}';">
                            </a>
                            <div class="flex items-center gap-2">
                                @include(theme_view('partials.appearance-toggle', 'guest'))
                                @include(theme_view('partials.auth-language-switcher', 'guest'))
                            </div>
                        </div>

                        <div class="linkqr-card relative overflow-hidden rounded-[2rem] p-6 shadow-[0_34px_90px_-60px_rgba(15,23,42,0.42)] sm:p-8 lg:p-10">
                            <div class="mb-6 hidden items-center justify-between gap-4 lg:flex">
                                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-bold text-slate-500 transition hover:text-blue-700" wire:navigate>
                                    <i class="fa-light fa-arrow-left mr-2"></i>
                                    {{ __('Back to home') }}
                                </a>
                                <div class="flex items-center gap-2">
                                    @include(theme_view('partials.appearance-toggle', 'guest'))
                                    @include(theme_view('partials.auth-language-switcher', 'guest'))
                                </div>
                            </div>
                            {{ $slot }}
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @include(theme_view('partials.embed-code-body', 'guest'))
        @livewireScripts
    </body>
</html>
