{{--
    Blog carousel — horizontal scroll-snap rail of the latest published posts.
    Hides itself when there are no posts yet so a fresh install renders cleanly.
    Visuals match the default theme: light surface, indigo chip + accent,
    JetBrains Mono for metadata, Inter 800 for the H2 LCP-style heading.
--}}
@php
    /** @var \Illuminate\Support\Collection $latestBlogPosts */
    $latestBlogPosts = $latestBlogPosts ?? collect();
@endphp

@if ($latestBlogPosts->isNotEmpty())
<section id="blog" class="relative overflow-hidden bg-white py-24 sm:py-32">
    {{-- Decorative left/right gradient gutters so the rail feels masked rather than truncated --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 left-0 z-10 hidden w-24 sm:block"
         style="background: linear-gradient(90deg, #FFFFFF, transparent);"></div>
    <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 z-10 hidden w-24 sm:block"
         style="background: linear-gradient(270deg, #FFFFFF, transparent);"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header row --}}
        <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
            <div>
                <span class="l-chip l-chip--indigo">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                    {{ __('From the blog') }}
                </span>
                <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                    {{ __('Field notes from') }}
                    <span class="l-accent">{{ __('teams shipping ads with AI.') }}</span>
                </h2>
                <p class="mt-4 max-w-xl text-[15px] text-black/60">
                    {{ __('Playbooks, benchmarks, and short reads on AI ad generation, brand-safe creative, and getting found by AI search engines.') }}
                </p>
            </div>

            {{-- Carousel controls + view-all link --}}
            <div class="flex items-center gap-3">
                <button type="button"
                        data-blog-carousel-prev
                        aria-controls="blog-carousel-rail"
                        aria-label="{{ __('Previous posts') }}"
                        class="group inline-flex h-11 w-11 items-center justify-center rounded-full border border-black/10 bg-white text-black/70 transition-all hover:-translate-x-0.5 hover:border-[#4F46E5] hover:text-[#4F46E5]">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 4 6 10l6 6"/>
                    </svg>
                </button>
                <button type="button"
                        data-blog-carousel-next
                        aria-controls="blog-carousel-rail"
                        aria-label="{{ __('Next posts') }}"
                        class="group inline-flex h-11 w-11 items-center justify-center rounded-full border border-black/10 bg-white text-black/70 transition-all hover:translate-x-0.5 hover:border-[#4F46E5] hover:text-[#4F46E5]">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M8 4l6 6-6 6"/>
                    </svg>
                </button>
                <a href="{{ route('blog.index') }}"
                   class="hidden shrink-0 items-center gap-2 whitespace-nowrap rounded-full bg-black px-5 py-3 text-sm font-semibold text-white transition-all hover:bg-[#4F46E5] sm:inline-flex">
                    <span>{{ __('View all') }}</span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Horizontal scroll rail. Native scroll-snap on the container,
             snap-start on each card. Negative margin + padding gives bleed
             at the viewport edges without breaking max-width. --}}
        <div
            id="blog-carousel-rail"
            data-blog-carousel-rail
            tabindex="0"
            role="region"
            aria-label="{{ __('Blog posts carousel') }}"
            class="mt-12 -mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4 pl-4 pr-4 sm:-mx-6 sm:pl-6 sm:pr-6 lg:-mx-8 lg:pl-8 lg:pr-8"
            style="scrollbar-width: none; -ms-overflow-style: none;"
        >
            <style>
                #blog-carousel-rail::-webkit-scrollbar { display: none; }
            </style>

            @foreach ($latestBlogPosts as $post)
                @php
                    /** @var \App\Models\BlogPost $post */
                    $cover = $post->featured_image_url
                        ?? 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="#0F172A"/></svg>');
                    $tags = is_array($post->tags) ? array_slice($post->tags, 0, 2) : [];
                @endphp
                <article class="group relative w-[300px] flex-shrink-0 snap-start overflow-hidden rounded-2xl border border-black/[0.08] bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-[#4F46E5]/40 hover:shadow-[0_20px_40px_-15px_rgba(79,70,229,0.25)] sm:w-[360px]">
                    {{-- Cover --}}
                    <a href="{{ $post->url }}"
                       class="block relative aspect-[16/10] overflow-hidden">
                        <img src="{{ $cover }}"
                             alt="{{ $post->featured_image_alt ?: $post->title }}"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                             loading="lazy"
                             decoding="async"
                             width="600"
                             height="375">
                        @if ($post->is_featured)
                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-[#4F46E5] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-[0_4px_12px_-2px_rgba(79,70,229,0.6)]">
                                <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2 9 9H2l5.5 4.5L5 21l7-5 7 5-2.5-7.5L22 9h-7z"/>
                                </svg>
                                {{ __('Featured') }}
                            </span>
                        @endif
                    </a>

                    {{-- Body --}}
                    <div class="flex flex-col gap-3 p-5">
                        {{-- Meta row --}}
                        <div class="flex items-center gap-2 text-[11px] text-black/50">
                            @if ($post->category)
                                <a href="{{ route('blog.index', ['category' => $post->category]) }}"
                                   class="l-mono inline-flex items-center rounded-full bg-[#4F46E5]/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#4F46E5] transition-colors hover:bg-[#4F46E5] hover:text-white">
                                    {{ $post->category }}
                                </a>
                                <span aria-hidden="true" class="h-1 w-1 rounded-full bg-black/15"></span>
                            @endif
                            <time datetime="{{ optional($post->published_at)->toIso8601String() }}" class="l-mono">
                                {{ optional($post->published_at)->format('M j, Y') }}
                            </time>
                            <span aria-hidden="true" class="h-1 w-1 rounded-full bg-black/15"></span>
                            <span class="l-mono">{{ $post->reading_time_minutes }} {{ __('min') }}</span>
                        </div>

                        {{-- Title --}}
                        <h3 class="text-[17px] font-bold leading-snug tracking-tight text-black">
                            <a href="{{ $post->url }}"
                               class="transition-colors before:absolute before:inset-0 before:content-[''] hover:text-[#4F46E5]">
                                {{ $post->title }}
                            </a>
                        </h3>

                        {{-- Excerpt --}}
                        @if (filled($post->excerpt))
                            <p class="line-clamp-3 text-[13.5px] leading-relaxed text-black/65">
                                {{ $post->excerpt }}
                            </p>
                        @endif

                        {{-- Tags --}}
                        @if (! empty($tags))
                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                @foreach ($tags as $tag)
                                    <span class="l-mono inline-flex items-center rounded-full border border-black/10 bg-white px-2 py-0.5 text-[10px] font-medium text-black/60">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Read more --}}
                        <div class="mt-2 flex items-center justify-between border-t border-black/[0.06] pt-3 text-[12px]">
                            <span class="l-mono text-black/45">{{ $post->author_name }}</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-[#4F46E5]">
                                {{ __('Read') }}
                                <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach

            {{-- Sentinel CTA card at the end of the rail --}}
            <a href="{{ route('blog.index') }}"
               class="group relative flex w-[260px] flex-shrink-0 snap-start flex-col items-start justify-between overflow-hidden rounded-2xl border border-dashed border-black/15 bg-[var(--l-bg-2,#FAFAFA)] p-6 transition-all hover:-translate-y-1 hover:border-[#4F46E5] hover:bg-white sm:w-[300px]">
                <div>
                    <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Read more') }}</span>
                    <p class="mt-3 text-[18px] font-bold leading-snug tracking-tight text-black">
                        {{ __('Browse the full archive') }}
                    </p>
                    <p class="mt-2 text-[13px] text-black/55">
                        {{ __('Filter by category, tag, or search the catalog.') }}
                    </p>
                </div>
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-black text-white transition-transform group-hover:translate-x-1 group-hover:bg-[#4F46E5]">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                    </svg>
                </span>
            </a>
        </div>

        {{-- Mobile view-all (header version is hidden on small screens) --}}
        <div class="mt-8 flex justify-center sm:hidden">
            <a href="{{ route('blog.index') }}"
               class="inline-flex items-center gap-2 rounded-full bg-black px-5 py-3 text-sm font-semibold text-white">
                {{ __('View all posts') }}
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Carousel scroll handlers — native scrollBy, no JS framework needed --}}
    <script>
        (function () {
            var rail = document.getElementById('blog-carousel-rail');
            if (!rail) return;
            var prev = document.querySelector('[data-blog-carousel-prev]');
            var next = document.querySelector('[data-blog-carousel-next]');
            // Step roughly equals one card width + gap
            function step() {
                var card = rail.querySelector('article, a[href]');
                if (!card) return rail.clientWidth * 0.8;
                var gap = 20; // tailwind gap-5
                return card.getBoundingClientRect().width + gap;
            }
            prev && prev.addEventListener('click', function () { rail.scrollBy({ left: -step(), behavior: 'smooth' }); });
            next && next.addEventListener('click', function () { rail.scrollBy({ left:  step(), behavior: 'smooth' }); });
            // Keyboard support when the rail is focused
            rail.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') { e.preventDefault(); rail.scrollBy({ left:  step(), behavior: 'smooth' }); }
                if (e.key === 'ArrowLeft')  { e.preventDefault(); rail.scrollBy({ left: -step(), behavior: 'smooth' }); }
            });
        })();
    </script>
</section>
@endif
