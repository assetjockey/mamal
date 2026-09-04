@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <article class="px-5 py-14 sm:py-20">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('guest.blogs') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#d8d3c7] bg-white/70 px-4 text-sm font-bold text-[#6d685f] transition hover:text-[#181714]">
                <i class="fa-light fa-arrow-left mr-2"></i>
                {{ __('Back to blog') }}
            </a>

            <header class="mt-8">
                <div class="flex flex-wrap items-center gap-3 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">
                    @if ($blog->category)
                        <span class="rounded-full bg-[#cef8dc] px-3 py-1 text-[#181714]">{{ $blog->category->nameForLocale() }}</span>
                    @endif
                    <span>{{ $blog->publishedAtFormatted('M d, Y') ?: $blog->createdAtFormatted('M d, Y') }}</span>
                </div>
                <h1 class="mt-5 max-w-4xl font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">
                    {{ $blog->titleForLocale() }}
                </h1>
                <p class="mt-6 max-w-3xl text-base leading-8 text-[#6d685f]">{{ $blog->contentPreview(220) }}</p>
            </header>

            <div class="mt-10 overflow-hidden rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)]">
                <div class="h-[24rem] bg-[#cfeefd]">
                    @if ($blog->thumbnailUrl())
                        <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" loading="lazy" onerror="this.remove();" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center p-8">
                            <div class="w-full max-w-md rotate-[-1deg] rounded-xl border-4 border-[#5f8dff] bg-[#fffdf8] p-8">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#8a867d]">{{ $blog->category?->nameForLocale() ?: __('Article') }}</p>
                                <p class="mt-20 font-serif text-5xl leading-none text-[#181714]">{{ __('Field note') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
                <div class="rounded-[1.5rem] border border-[#ded7ca] bg-[#fffdf8] p-6 shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)] sm:p-8">
                    <div class="prose prose-slate max-w-none prose-headings:font-serif prose-headings:tracking-[-0.03em] prose-a:text-[#5f8dff]">
                        {!! $blog->normalizedContentForLocale() !!}
                    </div>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-28">
                    <div class="rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-5">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Next step') }}</p>
                        <h2 class="mt-3 font-serif text-2xl leading-tight text-[#181714]">{{ __('Turn readers into measurable visits.') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-[#6d685f]">{{ __('Use one profile URL with branded short links, QR sharing, clean links, and city-level reporting.') }}</p>
                        <a href="{{ route('guest.pricing') }}" class="mt-5 inline-flex w-full min-h-11 items-center justify-center rounded-xl bg-[#181714] px-5 text-sm font-bold text-white">{{ __('View pricing') }}</a>
                    </div>
                    <div class="rounded-[1.25rem] border border-[#ded7ca] bg-white/70 p-5">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Explore') }}</p>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('guest.faqs') }}" class="rounded-xl bg-[#f4f1ea] px-4 py-3 text-sm font-bold text-[#57534b] hover:text-[#181714]">{{ __('FAQs') }}</a>
                            <a href="{{ route('guest.contact') }}" class="rounded-xl bg-[#f4f1ea] px-4 py-3 text-sm font-bold text-[#57534b] hover:text-[#181714]">{{ __('Contact') }}</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </article>

    @if ($relatedBlogs->isNotEmpty())
        <section class="px-5 pb-16">
            <div class="mx-auto max-w-5xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#5f8dff]">{{ __('Related') }}</p>
                <h2 class="mt-3 font-serif text-4xl leading-tight text-[#181714]">{{ __('More operating notes') }}</h2>
                <div class="mt-7 grid gap-5 md:grid-cols-3">
                    @foreach ($relatedBlogs as $related)
                        <article class="rounded-[1.2rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_18px_55px_-48px_rgba(24,23,20,.42)]">
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ $related->category?->nameForLocale() ?: __('Article') }}</p>
                            <h3 class="mt-3 text-lg font-bold leading-snug text-[#181714]">
                                <a href="{{ route('guest.blog-show', $related->slug) }}">{{ $related->titleForLocale() }}</a>
                            </h3>
                            <p class="mt-3 text-sm leading-7 text-[#6d685f]">{{ $related->contentPreview(120) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endcomponent
