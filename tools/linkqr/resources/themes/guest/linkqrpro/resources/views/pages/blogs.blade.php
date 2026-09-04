@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="px-5 py-14 sm:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-10 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
                <aside class="lg:sticky lg:top-28">
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Blog') }}</span>
                    <h1 class="mt-6 font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ __('Practical notes for useful public profiles.') }}</h1>
                    <p class="mt-6 max-w-xl text-base leading-8 text-[#6d685f]">{{ __('Learn how to claim clean usernames, publish Bio pages, create branded short links, share QR campaigns, and read click signals without making the workspace heavy.') }}</p>

                    <form method="GET" action="{{ route('guest.blogs') }}" class="mt-8 rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-3 shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)]">
                        <label for="blog-search" class="sr-only">{{ __('Find a post') }}</label>
                        <div class="flex gap-2">
                            <input id="blog-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search articles...') }}" class="min-h-12 min-w-0 flex-1 rounded-xl border-0 bg-[#f4f1ea] px-4 text-sm font-semibold text-[#181714] outline-none placeholder:text-[#aaa39a] focus:ring-2 focus:ring-[#5f8dff]/30">
                            <button type="submit" class="inline-flex min-h-12 items-center rounded-xl bg-[#181714] px-5 text-sm font-bold text-white">
                                <i class="fa-light fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 grid grid-cols-3 gap-3 text-center">
                        @foreach ([[__('Guides'), '12'], [__('Reports'), '18'], [__('Use cases'), '7']] as $stat)
                            <div class="rounded-2xl border border-[#ded7ca] bg-white/60 px-3 py-4">
                                <p class="font-serif text-3xl text-[#181714]">{{ $stat[1] }}</p>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#8a867d]">{{ $stat[0] }}</p>
                            </div>
                        @endforeach
                    </div>
                </aside>

                <div class="space-y-8">
                    @if ($featuredPost)
                        <article class="overflow-hidden rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)]">
                            <div class="grid lg:grid-cols-[1.05fr_0.95fr]">
                                <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="block min-h-[20rem] bg-[#cfeefd]">
                                    @if ($featuredPost->thumbnailUrl())
                                        <img src="{{ $featuredPost->thumbnailUrl() }}" alt="{{ $featuredPost->titleForLocale() }}" loading="lazy" onerror="this.remove();" class="h-full min-h-[20rem] w-full object-cover">
                                    @else
                                        <div class="flex h-full min-h-[20rem] items-center justify-center p-8">
                                            <div class="w-full max-w-sm rotate-[-2deg] rounded-xl border-4 border-[#5f8dff] bg-[#fffdf8] p-6">
                                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ __('Featured article') }}</p>
                                                <p class="mt-16 font-serif text-4xl leading-tight text-[#181714]">{{ __('Launch note') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </a>
                                <div class="flex flex-col justify-between p-6 sm:p-8">
                                    <div>
                                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ $featuredPost->publishedAtFormatted('M d, Y') ?: $featuredPost->createdAtFormatted('M d, Y') }}</p>
                                        <h2 class="mt-4 font-serif text-4xl leading-tight text-[#181714]">
                                            <a href="{{ route('guest.blog-show', $featuredPost->slug) }}">{{ $featuredPost->titleForLocale() }}</a>
                                        </h2>
                                        <p class="mt-4 text-sm leading-7 text-[#6d685f]">{{ $featuredPost->contentPreview(240) }}</p>
                                    </div>
                                    <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="mt-6 inline-flex w-max min-h-11 items-center rounded-xl border border-[#d8d3c7] bg-white px-5 text-sm font-bold text-[#181714]">
                                        {{ __('Read article') }}
                                        <i class="fa-light fa-arrow-right ml-2"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endif

                    <div>
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#5f8dff]">{{ __('Latest') }}</p>
                                <h2 class="mt-3 font-serif text-4xl leading-tight text-[#181714]">{{ __('Recent articles') }}</h2>
                            </div>
                            <span class="hidden rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-sm font-bold text-[#6d685f] md:inline-flex">{{ $blogs->total() }} {{ __('posts') }}</span>
                        </div>

                        <div class="mt-6 grid gap-5 md:grid-cols-2">
                            @forelse ($blogs as $blog)
                                <article class="overflow-hidden rounded-[1.35rem] border border-[#ded7ca] bg-[#fffdf8] shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)] transition hover:-translate-y-0.5">
                                    <a href="{{ route('guest.blog-show', $blog->slug) }}" class="block h-48 bg-[#f4f1ea]">
                                        @if ($blog->thumbnailUrl())
                                            <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" loading="lazy" onerror="this.remove();" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center p-6">
                                                <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-[#cef8dc] text-2xl text-[#181714]"><i class="fa-light fa-newspaper"></i></span>
                                            </div>
                                        @endif
                                    </a>
                                    <div class="p-6">
                                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ $blog->category?->nameForLocale() ?: __('Article') }}</p>
                                        <h3 class="mt-3 text-xl font-bold leading-snug text-[#181714]">
                                            <a href="{{ route('guest.blog-show', $blog->slug) }}">{{ $blog->titleForLocale() }}</a>
                                        </h3>
                                        <p class="mt-3 text-sm leading-7 text-[#6d685f]">{{ $blog->contentPreview(150) }}</p>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-[1.5rem] border border-dashed border-[#ded7ca] bg-[#fffdf8] px-6 py-14 text-center text-sm text-[#6d685f] md:col-span-2">{{ __('No blog posts found.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    @if ($blogs->hasPages())
                        <div>{{ $blogs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endcomponent
