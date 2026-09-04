@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    @php
        $hasHtml = str_contains($pageContent, '<') && str_contains($pageContent, '>');
        $pageType = $pageType ?? 'html';
        $socialLinks = $socialLinks ?? [];
        $eyebrow = $pageType === 'social'
            ? __('Social directory')
            : (str_contains(strtolower($pageTitle), 'privacy')
                ? __('Privacy')
                : (str_contains(strtolower($pageTitle), 'term') ? __('Legal') : __('Information')));
        $pageTabs = [
            ['label' => __('Privacy Policy'), 'route' => 'guest.privacy-policy'],
            ['label' => __('Terms of Use'), 'route' => 'guest.terms-of-use'],
            ['label' => __('Social Pages'), 'route' => 'guest.social-pages'],
        ];
    @endphp

    <section class="linkqr-shell linkqr-section pt-10">
        <div class="mx-auto max-w-5xl">
            <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                <i class="fa-light fa-file-lines"></i>
                {{ $eyebrow }}
            </span>
            <h1 class="mt-6 max-w-4xl text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">{{ $pageTitle }}</h1>
            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
                {{ $pageType === 'social'
                    ? __('Public destinations connected to the LinkQR brand.')
                    : __('Clear public information for customers, buyers, and operators reviewing the platform.') }}
            </p>

            <div class="mt-8 flex flex-wrap gap-2 rounded-[1.25rem] border bg-white p-2 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                @foreach ($pageTabs as $tab)
                    <a href="{{ route($tab['route']) }}" class="{{ $routeName === $tab['route'] ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-700' }} rounded-full px-4 py-2 text-sm font-bold transition">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="linkqr-card mt-6 overflow-hidden rounded-[1.6rem]">
                <div class="border-b px-7 py-5 md:px-9" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Page content') }}</p>
                </div>
                <div class="px-7 py-7 md:px-9 md:py-8">
                    @if ($pageType === 'social')
                        @if (trim($pageContent) !== '')
                            <div class="mb-8 max-w-3xl text-base leading-8 text-slate-600 whitespace-pre-line">{{ $pageContent }}</div>
                        @endif

                        @if ($socialLinks !== [])
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($socialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="linkqr-card linkqr-hover-lift rounded-[1.2rem] p-5">
                                        <div class="flex items-center gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[0.9rem] bg-blue-50 text-blue-700">
                                                <i class="{{ $link['icon'] }} text-xl"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-950">{{ $link['label'] }}</p>
                                                <p class="mt-1 truncate text-sm text-slate-500">{{ $link['url'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[1.25rem] border border-dashed px-6 py-12 text-center text-sm text-slate-500" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">{{ __('No social links have been configured yet.') }}</div>
                        @endif
                    @elseif (trim($pageContent) === '')
                        <div class="rounded-[1.25rem] border border-dashed px-6 py-12 text-center text-sm text-slate-500" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">{{ __('This page has not been configured yet.') }}</div>
                    @elseif ($hasHtml)
                        <div class="guest-static-content prose prose-slate max-w-none prose-a:text-blue-700">
                            {!! $pageContent !!}
                        </div>
                    @else
                        <div class="guest-static-content whitespace-pre-line text-sm leading-8 text-slate-600">
                            {{ $pageContent }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endcomponent
