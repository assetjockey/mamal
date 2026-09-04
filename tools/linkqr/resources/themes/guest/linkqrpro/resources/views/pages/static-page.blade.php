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

    <section class="px-5 py-14 sm:py-20">
        <div class="mx-auto max-w-5xl">
            <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ $eyebrow }}</span>
            <div class="mt-6 grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-end">
                <div>
                    <h1 class="font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ $pageTitle }}</h1>
                </div>
                <p class="text-base leading-8 text-[#6d685f]">
                    {{ $pageType === 'social'
                        ? __('Public destinations connected to this workspace.')
                        : __('Clear public information for customers, buyers, and operators reviewing the platform.') }}
                </p>
            </div>

            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($pageTabs as $tab)
                    <a href="{{ route($tab['route']) }}" class="{{ $routeName === $tab['route'] ? 'bg-[#181714] text-white' : 'border-[#d8d3c7] bg-white/70 text-[#6d685f] hover:text-[#181714]' }} inline-flex min-h-10 items-center rounded-full border px-4 text-sm font-bold transition">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="mt-8 overflow-hidden rounded-[1.5rem] border border-[#ded7ca] bg-[#fffdf8] shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)]">
                <div class="border-b border-[#e5dfd2] bg-white/60 px-7 py-5 md:px-9">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-[#8a867d]">{{ __('Page content') }}</p>
                </div>
                <div class="px-7 py-7 md:px-9 md:py-8">
                    @if ($pageType === 'social')
                        @if (trim($pageContent) !== '')
                            <div class="mb-8 max-w-3xl whitespace-pre-line text-base leading-8 text-[#6d685f]">{{ $pageContent }}</div>
                        @endif

                        @if ($socialLinks !== [])
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($socialLinks as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-[1.2rem] border border-[#ded7ca] bg-white p-5 transition hover:-translate-y-0.5">
                                        <div class="flex items-center gap-4">
                                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#cfeefd] text-[#181714]">
                                                <i class="{{ $link['icon'] }} text-xl"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-bold text-[#181714]">{{ $link['label'] }}</p>
                                                <p class="mt-1 truncate text-sm text-[#6d685f]">{{ $link['url'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[1.25rem] border border-dashed border-[#ded7ca] px-6 py-12 text-center text-sm text-[#6d685f]">{{ __('No social links have been configured yet.') }}</div>
                        @endif
                    @elseif (trim($pageContent) === '')
                        <div class="rounded-[1.25rem] border border-dashed border-[#ded7ca] px-6 py-12 text-center text-sm text-[#6d685f]">{{ __('This page has not been configured yet.') }}</div>
                    @elseif ($hasHtml)
                        <div class="guest-static-content prose prose-slate max-w-none prose-headings:font-serif prose-a:text-[#5f8dff]">
                            {!! $pageContent !!}
                        </div>
                    @else
                        <div class="guest-static-content whitespace-pre-line text-sm leading-8 text-[#6d685f]">
                            {{ $pageContent }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endcomponent
