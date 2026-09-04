<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), config('app.rtl_locales', ['ar', 'he', 'fa', 'ur'])) ? 'rtl' : 'ltr' }}"
    class="dark"
>
    <head>
        @include('partials.head')
        @include('partials.auth-seo-meta')
    </head>
    @php
        $authBannerSettings = \App\Models\GeneralSetting::first();
        $authBannerLogo = $authBannerSettings?->logo_frontend_collapsed
            ? \Illuminate\Support\Facades\URL::asset($authBannerSettings->logo_frontend_collapsed)
            : null;
    @endphp
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid min-h-dvh flex-col items-center justify-center px-8 py-10 sm:px-0 lg:h-dvh lg:max-w-none lg:grid-cols-2 lg:px-0 lg:py-0">
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        @if($authBannerLogo)
                            <span class="flex h-9 w-9 items-center justify-center rounded-md">
                                <img src="{{ $authBannerLogo }}" alt="{{ config('app.name', 'Laravel') }}" class="h-9 w-9 object-contain" />
                            </span>
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-md">
                                <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                            </span>
                        @endif

                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
            <div class="hidden h-full p-3 lg:flex lg:p-4">
                <div
                    class="relative flex h-full w-full flex-col items-center justify-center overflow-hidden rounded-2xl border border-white/10 px-10 py-12 text-center text-white shadow-2xl"
                    style="background-color: #000000; background-image: radial-gradient(50% 50% at 100% 0%, rgba(129,140,248,0.30) 0%, rgba(79,70,229,0.16) 35%, rgba(0,0,0,0) 70%);"
                >
                    <!-- Soft glow centered on the top-right corner (only a portion bleeds in) -->
                    <div class="pointer-events-none absolute top-0 right-0 h-80 w-80 -translate-y-1/2 translate-x-1/2 rounded-full opacity-50 blur-[90px]" style="background-color: #4F46E5;"></div>

                    <!-- Brand -->
                    <a href="{{ route('home') }}" class="relative z-20 flex items-center justify-center text-lg font-semibold" wire:navigate>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/15 backdrop-blur">
                            @if($authBannerLogo)
                                <img src="{{ $authBannerLogo }}" alt="{{ config('app.name', 'Laravel') }}" class="h-6 w-6 object-contain" />
                            @else
                                <x-app-logo-icon class="h-6 fill-current text-white" />
                            @endif
                        </span>
                        <span class="ms-3">{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <!-- Headline -->
                    <div class="relative z-20 mt-12 flex flex-col items-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-medium uppercase tracking-wide text-white/90 ring-1 ring-white/15 backdrop-blur">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            {{ __('AI-powered ad creation') }}
                        </span>

                        <h2 class="mt-7 max-w-md text-3xl font-bold leading-tight tracking-tight text-white">
                            {{ __('Create scroll-stopping ads in minutes, not days.') }}
                        </h2>

                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-white/60">
                            {{ __('Generate high-converting images, videos and copy from a single brief — all on brand, all in one studio.') }}
                        </p>
                    </div>

                    <!-- Feature cards -->
                    <div class="relative z-20 mt-12 grid w-full max-w-md grid-cols-2 gap-3">
                        @php
                            $authFeatures = [
                                ['title' => __('AI Image Studio'),  'desc' => __('Prompts to polished creatives.')],
                                ['title' => __('AI Video Studio'),  'desc' => __('Motion ads for every feed.')],
                                ['title' => __('Ad Copy Studio'),   'desc' => __('Headlines that convert.')],
                                ['title' => __('Brand Kits'),       'desc' => __('Consistent voice & colors.')],
                            ];
                        @endphp

                        @foreach ($authFeatures as $feature)
                            <div class="flex flex-col items-center gap-2.5 rounded-xl border border-white/10 bg-white/[0.03] p-4 text-center backdrop-blur transition hover:border-white/20 hover:bg-white/[0.06]">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 ring-white/10" style="background-color: rgba(245, 158, 11, 0.14);">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16.5 5.5L8.25 13.75L4.5 10" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-white">{{ $feature['title'] }}</p>
                                    <p class="mt-0.5 text-xs leading-snug text-white/50">{{ $feature['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
