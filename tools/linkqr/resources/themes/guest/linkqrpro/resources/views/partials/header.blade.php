@php
    $navItems = [
        ['label' => __('Home'), 'href' => url(''), 'active' => request()->is('/')],
        ['label' => __('Features'), 'href' => url('').'/#features', 'active' => false],
        ['label' => __('Pricing'), 'href' => url('pricing'), 'active' => request()->is('pricing*')],
        ['label' => __('FAQs'), 'href' => url('faqs'), 'active' => request()->is('faqs*')],
        ['label' => __('Blog'), 'href' => url('blogs'), 'active' => request()->is('blogs*')],
        ['label' => __('Contact'), 'href' => url('contact'), 'active' => request()->is('contact*')],
    ];
    $languages = available_languages();
    $activeLanguage = $languages->firstWhere('code', app()->getLocale()) ?? $languages->first();
@endphp

<header x-data="{ mobileNavOpen: false }" class="relative z-50">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5">
        <a href="{{ url('') }}" class="inline-flex items-center">
            <img class="h-7 max-w-[9rem] object-contain" src="{{ url(get_option('website_logo_brand_dark', 'public/img/logo-brand-dark.png')) }}" alt="">
        </a>

        <nav class="hidden items-center gap-7 text-[13px] font-semibold lg:flex">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" class="{{ $item['active'] ? 'text-[#181714]' : 'text-[#57534b]' }} transition hover:opacity-60">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
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

            @if(Auth::check())
                <a href="{{ route('portal.dashboard') }}" class="rounded-lg border border-[#d8d3c7] bg-white px-4 py-2 text-xs font-semibold text-[#181714] transition hover:bg-[#f4f0e7]">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-xs font-semibold text-[#57534b] transition hover:text-[#181714]">{{ __('Login') }}</a>
                @if(get_option("auth_signup_page_status", 1))
                    <a href="{{ url('/register') }}" class="rounded-lg border border-[#d8d3c7] bg-white px-4 py-2 text-xs font-semibold text-[#181714] transition hover:bg-[#f4f0e7]">{{ __("Start free") }}</a>
                @endif
            @endif
        </div>

        <button type="button" x-on:click="mobileNavOpen = ! mobileNavOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#d8d3c7] bg-white text-[#181714] lg:hidden" aria-label="{{ __('Open menu') }}">
            <i class="fa-light fa-bars"></i>
        </button>
    </div>

    <div x-cloak x-show="mobileNavOpen" x-transition class="mx-5 rounded-xl border border-[#d8d3c7] bg-[#fbfaf6] p-3 shadow-xl lg:hidden">
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
            @if(Auth::check())
                <a href="{{ route('portal.dashboard') }}" class="mt-2 rounded-lg bg-[#181714] px-3 py-2 text-center text-sm font-bold text-white">{{ __('Dashboard') }}</a>
            @else
                <a href="{{ route('login') }}" class="mt-2 rounded-lg border border-[#d8d3c7] bg-white px-3 py-2 text-center text-sm font-bold text-[#181714]">{{ __('Login') }}</a>
                @if(get_option("auth_signup_page_status", 1))
                    <a href="{{ url('/register') }}" class="mt-2 rounded-lg bg-[#181714] px-3 py-2 text-center text-sm font-bold text-white">{{ __("Start free") }}</a>
                @endif
            @endif
        </nav>
    </div>
</header>
