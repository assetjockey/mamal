{{--
    Public blog index — grid of published posts with category, tag, and
    free-text search filters. Filters are URL-driven so every state is
    crawlable and shareable.

    SEO:
    - Override <title> and <meta description> via @section('metadata')
    - Inline JSON-LD: BreadcrumbList + Blog with the visible posts as
      ItemList entries (helps AI engines build a topical map of the site)

    Layout: extends the same `layouts.frontend` master used by welcome,
    so the menu / footer / cookie banner / SEO meta partials all carry
    over without duplication.
--}}
@php
    $pageTitle = __('Blog — AI ad generation, brand-safe creative, and AI search visibility');
    $pageDescription = __('Field notes from teams shipping ads with AI. Playbooks on brand kits, canvas presets, ad copy, and generative engine optimization (GEO).');
    $canonical = url()->current();

    $hasFilters = filled($activeCategory) || filled($activeTag) || filled($searchTerm);

    // ItemList of currently visible posts for AI search engine extraction
    $itemList = $posts->map(function ($p, $i) {
        return [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => $p->url,
            'name' => $p->title,
        ];
    })->all();

    $blogSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => __('Home'), 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => __('Blog'), 'item' => route('blog.index')],
                ],
            ],
            [
                '@type' => 'Blog',
                'name'  => __('AI Ad Studio Blog'),
                'url'   => route('blog.index'),
                'description' => $pageDescription,
                'blogPost' => $itemList,
            ],
        ],
    ];
@endphp

@extends('layouts.frontend')

@section('body_class', 'landing--on-light')

@section('metadata')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph override for the blog index --}}
    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ $canonical }}">
    <meta property="og:title"       content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">

    {{-- Don't index empty filtered results — but always index the canonical /blog --}}
    @if ($hasFilters && $posts->total() === 0)
        <meta name="robots" content="noindex,follow">
    @endif

    <script type="application/ld+json">
    {!! json_encode($blogSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('menu')
    @include('frontend.menu.section')
@endsection

@section('content')
<section class="relative bg-white pt-32 pb-16 sm:pt-40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-[12px] text-black/55">
            <ol class="flex items-center gap-1.5">
                <li><a href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::localizeUrl('/') }}" class="hover:text-[#4F46E5]">{{ __('Home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li class="font-medium text-black">{{ __('Blog') }}</li>
            </ol>
        </nav>

        {{-- Title --}}
        <header class="mt-6">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('Blog') }}
            </span>
            <h1 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl lg:text-6xl">
                {{ __('Field notes from teams shipping ads with') }}
                <span class="l-accent">{{ __('AI.') }}</span>
            </h1>
            <p class="mt-4 max-w-2xl text-[15px] text-black/60 sm:text-base">
                {{ __('Playbooks, benchmarks, and short reads on AI ad generation, brand-safe creative, canvas presets, ad copy, and getting found by AI search engines.') }}
            </p>
        </header>

        @include('frontend.ads.slot', ['placement' => 'blog_top'])

        {{-- Search + filter bar --}}
        <form method="GET" action="{{ route('blog.index') }}" class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="blog-search" class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Search') }}</label>
                <div class="relative mt-2">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-black/40" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                        <circle cx="9" cy="9" r="6"/><path d="m14 14 4 4"/>
                    </svg>
                    <input id="blog-search"
                           name="q"
                           type="search"
                           value="{{ $searchTerm }}"
                           placeholder="{{ __('Search posts…') }}"
                           class="w-full rounded-full border border-black/10 bg-white py-3 pl-10 pr-4 text-[14px] text-black placeholder-black/40 transition-colors focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">
                    {{-- Preserve active filters across submits --}}
                    @if (filled($activeCategory))
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif
                    @if (filled($activeTag))
                        <input type="hidden" name="tag" value="{{ $activeTag }}">
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#4F46E5]">
                    {{ __('Apply') }}
                </button>
                @if ($hasFilters)
                    <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-white px-5 py-3 text-sm font-medium text-black/70 transition-colors hover:border-[#4F46E5] hover:text-[#4F46E5]">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>

        {{-- Active-filter chips --}}
        @if ($hasFilters)
            <div class="mt-5 flex flex-wrap items-center gap-2 text-[12px]">
                <span class="text-black/45">{{ __('Filtered by:') }}</span>
                @if (filled($activeCategory))
                    <a href="{{ route('blog.index', array_filter(['tag' => $activeTag, 'q' => $searchTerm])) }}"
                       class="inline-flex items-center gap-1.5 rounded-full bg-[#4F46E5] px-3 py-1 font-semibold text-white">
                        {{ $activeCategory }}
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15"/></svg>
                    </a>
                @endif
                @if (filled($activeTag))
                    <a href="{{ route('blog.index', array_filter(['category' => $activeCategory, 'q' => $searchTerm])) }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-black/10 bg-white px-3 py-1 font-medium text-black/70">
                        #{{ $activeTag }}
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15"/></svg>
                    </a>
                @endif
                @if (filled($searchTerm))
                    <a href="{{ route('blog.index', array_filter(['category' => $activeCategory, 'tag' => $activeTag])) }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-black/10 bg-white px-3 py-1 font-medium text-black/70">
                        “{{ $searchTerm }}”
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15"/></svg>
                    </a>
                @endif
            </div>
        @endif

        {{-- Categories rail --}}
        @if ($categories->isNotEmpty())
            <div class="mt-10 border-t border-black/[0.06] pt-6">
                <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Categories') }}</span>
                <ul role="list" class="mt-3 flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('blog.index', array_filter(['tag' => $activeTag, 'q' => $searchTerm])) }}"
                           @class([
                               'inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[12.5px] font-semibold transition-colors',
                               'bg-black text-white' => blank($activeCategory),
                               'border border-black/10 bg-white text-black/70 hover:border-[#4F46E5] hover:text-[#4F46E5]' => filled($activeCategory),
                           ])>
                            {{ __('All') }}
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('blog.index', array_filter(['category' => $cat->category, 'tag' => $activeTag, 'q' => $searchTerm])) }}"
                               @class([
                                   'inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[12.5px] font-semibold transition-colors',
                                   'bg-[#4F46E5] text-white' => $activeCategory === $cat->category,
                                   'border border-black/10 bg-white text-black/70 hover:border-[#4F46E5] hover:text-[#4F46E5]' => $activeCategory !== $cat->category,
                               ])>
                                {{ $cat->category }}
                                <span @class([
                                    'l-mono rounded-full px-1.5 py-0.5 text-[9px] font-bold',
                                    'bg-white/20 text-white' => $activeCategory === $cat->category,
                                    'bg-black/[0.04] text-black/55' => $activeCategory !== $cat->category,
                                ])>{{ $cat->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>

{{-- Posts grid --}}
<section class="bg-white pb-24 sm:pb-32">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if ($posts->isEmpty())
            {{-- Empty state --}}
            <div class="mx-auto max-w-md py-16 text-center">
                <div class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-full bg-black/[0.04]">
                    <svg class="h-6 w-6 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </div>
                <h2 class="mt-5 text-xl font-bold text-black">{{ __('No posts match your filters.') }}</h2>
                <p class="mt-2 text-[14px] text-black/60">{{ __('Try a different category or search term, or browse all published posts.') }}</p>
                <a href="{{ route('blog.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-black px-5 py-3 text-sm font-semibold text-white hover:bg-[#4F46E5]">
                    {{ __('Browse all posts') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @php
                        $cover = $post->featured_image_url
                            ?? 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="#0F172A"/></svg>');
                        $tags = is_array($post->tags) ? array_slice($post->tags, 0, 3) : [];
                    @endphp
                    <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-black/[0.08] bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-[#4F46E5]/40 hover:shadow-[0_20px_40px_-15px_rgba(79,70,229,0.25)]">
                        <a href="{{ $post->url }}" class="block relative aspect-[16/10] overflow-hidden">
                            <img src="{{ $cover }}"
                                 alt="{{ $post->featured_image_alt ?: $post->title }}"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                                 loading="lazy"
                                 decoding="async"
                                 width="600"
                                 height="375">
                            @if ($post->is_featured)
                                <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-[#4F46E5] px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">
                                    <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12 2 9 9H2l5.5 4.5L5 21l7-5 7 5-2.5-7.5L22 9h-7z"/>
                                    </svg>
                                    {{ __('Featured') }}
                                </span>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col gap-3 p-5">
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-black/50">
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

                            <h2 class="text-[18px] font-bold leading-snug tracking-tight text-black">
                                <a href="{{ $post->url }}"
                                   class="transition-colors before:absolute before:inset-0 before:content-[''] hover:text-[#4F46E5]">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            @if (filled($post->excerpt))
                                <p class="line-clamp-3 text-[13.5px] leading-relaxed text-black/65">
                                    {{ $post->excerpt }}
                                </p>
                            @endif

                            @if (! empty($tags))
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    @foreach ($tags as $tag)
                                        <span class="l-mono inline-flex items-center rounded-full border border-black/10 bg-white px-2 py-0.5 text-[10px] font-medium text-black/60">
                                            #{{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-auto flex items-center justify-between border-t border-black/[0.06] pt-3 text-[12px]">
                                <span class="l-mono text-black/45">{{ $post->author_name }}</span>
                                <span class="inline-flex items-center gap-1 font-semibold text-[#4F46E5]">
                                    {{ __('Read article') }}
                                    <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex flex-col items-center gap-3">
                <p class="l-mono text-[11px] uppercase tracking-[0.2em] text-black/45">
                    {{ __('Showing :from–:to of :total', ['from' => $posts->firstItem(), 'to' => $posts->lastItem(), 'total' => $posts->total()]) }}
                </p>
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @endif

        {{-- Popular tags rail at the bottom for topical SEO --}}
        @if ($popularTags->isNotEmpty())
            <div class="mt-16 border-t border-black/[0.06] pt-8">
                <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Popular topics') }}</span>
                <ul role="list" class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach ($popularTags as $tag)
                        <li>
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                               @class([
                                   'l-mono inline-flex items-center rounded-full px-3 py-1 text-[11px] font-medium transition-colors',
                                   'bg-[#4F46E5] text-white' => $activeTag === $tag,
                                   'border border-black/10 bg-white text-black/70 hover:border-[#4F46E5] hover:text-[#4F46E5]' => $activeTag !== $tag,
                               ])>
                                #{{ $tag }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection
