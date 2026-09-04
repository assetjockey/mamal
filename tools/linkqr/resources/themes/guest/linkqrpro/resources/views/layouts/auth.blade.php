<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include(theme_view('partials.head', 'guest'))
        @livewireStyles
        <style>
            .lq-auth-preview {
                animation: lq-auth-rise .75s cubic-bezier(.16, 1, .3, 1) both;
            }

            .lq-auth-preview-shell {
                animation: lq-auth-float 7s ease-in-out infinite;
            }

            .lq-auth-preview-panel {
                position: relative;
                overflow: hidden;
            }

            .lq-auth-preview-panel::before {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(115deg, transparent 0%, transparent 42%, rgba(255, 197, 45, .16) 47%, transparent 54%, transparent 100%);
                transform: translateX(-55%);
                animation: lq-auth-sheen 5.5s ease-in-out infinite;
                pointer-events: none;
            }

            .lq-auth-qr {
                animation: lq-auth-pop .6s cubic-bezier(.16, 1, .3, 1) both, lq-auth-pulse 3.8s ease-in-out infinite;
            }

            .lq-auth-row {
                animation: lq-auth-rise .55s cubic-bezier(.16, 1, .3, 1) both;
                animation-delay: var(--lq-delay);
                transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
            }

            .lq-auth-row:hover {
                border-color: rgba(95, 141, 255, .5);
                box-shadow: 0 16px 35px -28px rgba(24, 23, 20, .55);
                transform: translateX(6px);
            }

            .lq-auth-row i:last-child {
                transition: transform .25s ease, color .25s ease;
            }

            .lq-auth-row:hover i:last-child {
                color: #5f8dff;
                transform: translate(2px, -2px);
            }

            .lq-auth-metric {
                animation: lq-auth-pop .5s cubic-bezier(.16, 1, .3, 1) both;
                animation-delay: var(--lq-delay);
            }

            @keyframes lq-auth-rise {
                from { opacity: 0; transform: translateY(18px) scale(.985); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }

            @keyframes lq-auth-float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-8px); }
            }

            @keyframes lq-auth-pop {
                from { opacity: 0; transform: scale(.92); }
                to { opacity: 1; transform: scale(1); }
            }

            @keyframes lq-auth-pulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(24, 23, 20, .18); }
                50% { box-shadow: 0 0 0 10px rgba(24, 23, 20, 0); }
            }

            @keyframes lq-auth-sheen {
                0%, 45% { transform: translateX(-60%); }
                70%, 100% { transform: translateX(65%); }
            }

            @media (prefers-reduced-motion: reduce) {
                .lq-auth-preview,
                .lq-auth-preview-shell,
                .lq-auth-preview-panel,
                .lq-auth-qr,
                .lq-auth-row,
                .lq-auth-metric {
                    animation: none !important;
                    transition: none !important;
                }
            }
        </style>
    </head>
    @php
        $brandOptions = app(\Modules\AdminSettings\Support\OptionStore::class);
        $displayBaseUrl = preg_replace('#^https?://#i', '', rtrim(url('/'), '/'));
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
    <body class="min-h-screen antialiased" style="background:#fbfaf6;color:#181714;font-family:Inter,ui-sans-serif,system-ui,sans-serif;">
        <div class="min-h-screen bg-[linear-gradient(#edf3fb_1px,transparent_1px),linear-gradient(90deg,#edf3fb_1px,transparent_1px)] bg-[length:34px_34px]">
            <main class="mx-auto grid min-h-screen max-w-7xl gap-8 px-5 py-6 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:px-8">
                <section class="hidden lg:block">
                    <a href="{{ route('home') }}" class="inline-flex items-center">
                        <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="h-10 w-auto object-contain" onerror="this.onerror=null;this.src='{{ $fallbackAuthDarkLogo }}';">
                    </a>

                    <div class="mt-14 max-w-xl">
                        <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('LinkQR workspace') }}</span>
                        <h1 class="mt-6 font-serif text-6xl leading-[0.92] tracking-[-0.04em] text-[#181714]">{{ __('Claim a clean name. Build one useful profile.') }}</h1>
                        <p class="mt-6 text-base leading-8 text-[#6d685f]">{{ __('Sign in or create an account to continue the username-first flow, publish Bio pages, create short links, attach QR campaigns, and read scan/click analytics.') }}</p>
                    </div>

                    @php
                        $authPreviewRows = [
                            ['icon' => 'fa-brands fa-instagram', 'label' => __('Add social links'), 'meta' => __('Profile blocks')],
                            ['icon' => 'fa-light fa-link-simple', 'label' => __('Create short links'), 'meta' => __('Branded campaigns')],
                            ['icon' => 'fa-light fa-qrcode', 'label' => __('Share QR codes'), 'meta' => __('Offline scans')],
                            ['icon' => 'fa-light fa-chart-line-up', 'label' => __('Track cities and clicks'), 'meta' => __('Live reports')],
                        ];
                        $authPreviewMetrics = [
                            ['value' => '3.8K', 'label' => __('Clicks')],
                            ['value' => '18', 'label' => __('Cities')],
                            ['value' => '42%', 'label' => __('CTR')],
                        ];
                    @endphp
                    <div class="lq-auth-preview relative mt-10 max-w-xl">
                        <span class="absolute -left-4 top-10 h-20 w-14 -rotate-6 rounded-lg bg-[#ffc52d]/80"></span>
                        <span class="absolute -right-3 bottom-9 h-24 w-14 rotate-6 rounded-lg bg-[#cdeeff]/90"></span>
                        <div class="lq-auth-preview-shell relative rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_30px_95px_-62px_rgba(24,23,20,.62)]">
                            <div class="rounded-[1.35rem] bg-[#d8f2fb] bg-[linear-gradient(#b8e1ef_1px,transparent_1px),linear-gradient(90deg,#b8e1ef_1px,transparent_1px)] bg-[length:28px_28px] p-5">
                                <div class="lq-auth-preview-panel rounded-[1.15rem] border-[3px] border-[#5f8dff] bg-[#fffdf8] p-5 shadow-[0_20px_55px_-42px_rgba(24,23,20,.6)]">
                                    <div class="relative z-10 flex items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ __('Public profile') }}</p>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-[#cef8dc] px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#176336]">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-[#24b15b]"></span>
                                                    {{ __('Live') }}
                                                </span>
                                            </div>
                                            <p class="mt-1 font-mono text-sm font-bold text-[#181714]">{{ $displayBaseUrl }}/yourname</p>
                                        </div>
                                        <span class="lq-auth-qr inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#181714] text-white">
                                            <i class="fa-light fa-qrcode text-lg"></i>
                                        </span>
                                    </div>

                                    <div class="relative z-10 mt-6 grid gap-3">
                                        @foreach ($authPreviewRows as $index => $row)
                                            <div class="lq-auth-row flex items-center justify-between gap-3 rounded-xl border border-[#e5dfd2] bg-white/90 px-4 py-3 text-sm" style="--lq-delay: {{ 0.12 + ($index * 0.08) }}s;">
                                                <span class="flex min-w-0 items-center gap-3">
                                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#eef7ff] text-[#181714]">
                                                        <i class="{{ $row['icon'] }}"></i>
                                                    </span>
                                                    <span class="min-w-0">
                                                        <span class="block font-extrabold text-[#181714]">{{ $row['label'] }}</span>
                                                        <span class="block text-xs font-semibold text-[#8a867d]">{{ $row['meta'] }}</span>
                                                    </span>
                                                </span>
                                                <i class="fa-light fa-arrow-up-right text-[#8a867d]"></i>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="relative z-10 mt-5 grid grid-cols-3 gap-3">
                                        @foreach ($authPreviewMetrics as $index => $metric)
                                            <div class="lq-auth-metric rounded-xl border border-[#eadfca] bg-[#fff6dd] px-3 py-3 text-center" style="--lq-delay: {{ 0.42 + ($index * 0.08) }}s;">
                                                <p class="text-lg font-black leading-none text-[#181714]">{{ $metric['value'] }}</p>
                                                <p class="mt-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ $metric['label'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex min-h-[calc(100vh-3rem)] items-center justify-center lg:min-h-0">
                    <div class="w-full max-w-[38rem]">
                        <div class="mb-6 flex items-center justify-between gap-4 lg:hidden">
                            <a href="{{ route('home') }}" class="inline-flex items-center">
                                <img src="{{ $resolvedAuthDarkLogo }}" alt="{{ config('app.name', 'LinkQR Pro') }}" class="h-9 w-auto object-contain" onerror="this.onerror=null;this.src='{{ $fallbackAuthDarkLogo }}';">
                            </a>
                            <div class="flex items-center gap-2">
                                @include(theme_view('partials.appearance-toggle', 'guest'))
                                @include(theme_view('partials.auth-language-switcher', 'guest'))
                            </div>
                        </div>

                        <div class="rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-6 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] sm:p-8 lg:p-10">
                            <div class="mb-7 hidden items-center justify-between gap-4 lg:flex">
                                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-bold text-[#6d685f] transition hover:text-[#181714]">
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
            </main>
        </div>
        @include(theme_view('partials.embed-code-body', 'guest'))
        @livewireScripts
    </body>
</html>
