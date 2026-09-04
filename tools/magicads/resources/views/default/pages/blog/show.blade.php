{{--
    Blog post detail — the canonical content page.

    This is where AI search engines extract passages and where Google indexes
    keyword-rich body copy. Every signal is on the page:

    - <h1> matches the post title (one per page)
    - <article> wraps the post body with itemscope/itemtype as a fallback
    - JSON-LD: BreadcrumbList + BlogPosting (with author, dates, image)
    - OG / Twitter overrides specific to this post
    - Canonical URL points to this page (post.canonical_url override allowed)
    - Related posts surface internal links → topical authority
    - Comments markup is semantic (article > footer) so AI can attribute

    Layout: extends `layouts.frontend` so menu / footer / cookie banner /
    base SEO meta partials carry over.
--}}
@php
    /** @var \App\Models\BlogPost $post */

    $pageTitle = $post->resolved_meta_title;
    $pageDescription = $post->resolved_meta_description;
    $canonical = filled($post->canonical_url) ? $post->canonical_url : route('blog.show', ['slug' => $post->slug]);
    $cover = $post->featured_image_url
        ?? 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="#0F172A"/></svg>');
    $keywords = $post->keywords_array;
    $tags = is_array($post->tags) ? $post->tags : [];

    // BlogPosting + Breadcrumb schema. Uses the page composer's
    // $generalSettings if available (publisher/Organization), otherwise
    // falls back to app config.
    $publisherName = $generalSettings?->site_name ?? config('app.name', 'AI Ad Studio');
    $publisherLogo = $generalSettings?->logo_frontend
        ?? $generalSettings?->logo
        ?? null;
    $publisherLogoUrl = filled($publisherLogo)
        ? (str_starts_with((string) $publisherLogo, 'http') ? $publisherLogo : asset($publisherLogo))
        : asset('favicon.svg');

    $articleSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => __('Home'), 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => __('Blog'), 'item' => route('blog.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $canonical],
                ],
            ],
            array_filter([
                '@type'         => 'BlogPosting',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
                'headline'      => $post->title,
                'description'   => $pageDescription,
                'image'         => $cover,
                'datePublished' => $post->published_date_for_schema,
                'dateModified'  => $post->updated_date_for_schema,
                'wordCount'     => str_word_count(strip_tags((string) $post->content)),
                'articleSection'=> $post->category,
                'keywords'      => ! empty($keywords) ? implode(', ', $keywords) : null,
                'author' => [
                    '@type' => 'Person',
                    'name'  => $post->author_name,
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name'  => $publisherName,
                    'logo'  => ['@type' => 'ImageObject', 'url' => $publisherLogoUrl],
                ],
                'url' => $canonical,
            ]),
        ],
    ];
@endphp

@extends('layouts.frontend')

@section('body_class', 'landing--on-light')

@section('metadata')
    <title>{{ $pageTitle }} — {{ $publisherName }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if (! empty($keywords))
        <meta name="keywords" content="{{ implode(', ', $keywords) }}">
    @endif
    <meta name="author" content="{{ $post->author_name }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph as Article --}}
    <meta property="og:type"        content="article">
    <meta property="og:url"         content="{{ $canonical }}">
    <meta property="og:title"       content="{{ $post->title }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image"       content="{{ $cover }}">
    <meta property="og:image:alt"   content="{{ $post->featured_image_alt ?: $post->title }}">
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @endif
    @if ($post->updated_at)
        <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    @endif
    @if ($post->category)
        <meta property="article:section" content="{{ $post->category }}">
    @endif
    @foreach ($tags as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach

    <meta name="twitter:card"  content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->title }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $cover }}">

    <script type="application/ld+json">
    {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <style>
        .blog-prose { color: #1F2937; font-size: 16.5px; line-height: 1.75; }
        .blog-prose .lead { font-size: 19px; color: #0F172A; line-height: 1.6; margin-bottom: 1.5em; }
        .blog-prose h2 { font-size: 28px; font-weight: 800; letter-spacing: -0.025em; color: #000; margin: 2.2em 0 0.6em; line-height: 1.15; }
        .blog-prose h3 { font-size: 21px; font-weight: 700; color: #000; margin: 1.8em 0 0.5em; line-height: 1.2; }
        .blog-prose p  { margin: 0 0 1em; }
        .blog-prose ul, .blog-prose ol { margin: 0 0 1.2em; padding-left: 1.4em; }
        .blog-prose ul { list-style: disc; }
        .blog-prose ol { list-style: decimal; }
        .blog-prose li { margin-bottom: 0.4em; }
        .blog-prose a  { color: #4F46E5; text-decoration: underline; text-decoration-thickness: 1.5px; text-underline-offset: 3px; }
        .blog-prose a:hover { color: #312E81; }
        .blog-prose strong { color: #000; font-weight: 700; }
        .blog-prose em { color: #0F172A; }
        .blog-prose code { background: rgba(79,70,229,0.08); color: #4338CA; padding: 0.15em 0.45em; border-radius: 4px; font-size: 0.92em; }
        .blog-prose blockquote { border-left: 3px solid #4F46E5; padding: 0.4em 0 0.4em 1.2em; color: #374151; font-style: italic; margin: 1.5em 0; }
        .blog-prose hr { border: 0; border-top: 1px solid rgba(0,0,0,0.08); margin: 2em 0; }
        .blog-prose img { border-radius: 12px; margin: 1.4em 0; }
        /* Reading progress bar */
        #reading-progress { position: fixed; top: 0; left: 0; height: 3px; width: 0; background: linear-gradient(90deg, #4F46E5, #F59E0B); z-index: 60; transition: width 80ms linear; }
    </style>
@endsection

@section('menu')
    @include('frontend.menu.section')
@endsection

@section('content')
<div id="reading-progress" aria-hidden="true"></div>

{{-- Hero / header --}}
<section class="relative bg-white pt-28 pb-10 sm:pt-36">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-[12px] text-black/55">
            <ol class="flex items-center gap-1.5">
                <li><a href="{{ \Mcamara\LaravelLocalization\Facades\LaravelLocalization::localizeUrl('/') }}" class="hover:text-[#4F46E5]">{{ __('Home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('blog.index') }}" class="hover:text-[#4F46E5]">{{ __('Blog') }}</a></li>
                @if ($post->category)
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('blog.index', ['category' => $post->category]) }}" class="hover:text-[#4F46E5]">{{ $post->category }}</a></li>
                @endif
            </ol>
        </nav>

        <header class="mt-6">
            <div class="flex flex-wrap items-center gap-2 text-[11px] text-black/55">
                @if ($post->category)
                    <a href="{{ route('blog.index', ['category' => $post->category]) }}"
                       class="l-mono inline-flex items-center rounded-full bg-[#4F46E5]/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#4F46E5] hover:bg-[#4F46E5] hover:text-white">
                        {{ $post->category }}
                    </a>
                @endif
                <time datetime="{{ optional($post->published_at)->toIso8601String() }}" class="l-mono">
                    {{ optional($post->published_at)->format('F j, Y') }}
                </time>
                <span aria-hidden="true" class="h-1 w-1 rounded-full bg-black/15"></span>
                <span class="l-mono">{{ $post->reading_time_minutes }} {{ __('min read') }}</span>
                @if ($post->view_count > 0)
                    <span aria-hidden="true" class="h-1 w-1 rounded-full bg-black/15"></span>
                    <span class="l-mono">{{ number_format($post->view_count) }} {{ __('views') }}</span>
                @endif
            </div>

            <h1 class="l-display mt-4 text-4xl font-extrabold leading-[1.05] tracking-[-0.025em] text-black sm:text-5xl lg:text-[56px]">
                {{ $post->title }}
            </h1>

            @if (filled($post->excerpt))
                <p class="mt-5 text-[18px] leading-relaxed text-black/65">{{ $post->excerpt }}</p>
            @endif

            {{-- Author + share --}}
            <div class="mt-7 flex items-center justify-between gap-3 border-y border-black/[0.06] py-4">
                <div class="flex items-center gap-3">
                    <span aria-hidden="true"
                          class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                          style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                        {{ \Illuminate\Support\Str::of($post->author_name)->substr(0, 1)->upper() }}
                    </span>
                    <div>
                        <div class="text-[13.5px] font-semibold text-black">{{ $post->author_name }}</div>
                        @if ($post->author_role)
                            <div class="text-[11px] text-black/50">{{ $post->author_role }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode($canonical) }}"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="{{ __('Share on Twitter') }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-black/10 text-black/60 transition-all hover:-translate-y-0.5 hover:border-[#4F46E5] hover:text-[#4F46E5]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 4H22l-7.2 8.2L23 20h-6.6l-5.2-6.2L5.3 20H2.2l7.7-8.8L1 4h6.8l4.7 5.6L18.9 4z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($canonical) }}"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="{{ __('Share on LinkedIn') }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-black/10 text-black/60 transition-all hover:-translate-y-0.5 hover:border-[#4F46E5] hover:text-[#4F46E5]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5A2.5 2.5 0 1 1 0 3.5a2.5 2.5 0 0 1 4.98 0zM.25 8h4.5V22H.25V8zm7.75 0h4.3v1.9h.1c.6-1.1 2.1-2.2 4.3-2.2 4.6 0 5.4 3 5.4 7V22h-4.5v-6c0-1.4 0-3.3-2-3.3s-2.4 1.6-2.4 3.2V22H8V8z"/></svg>
                    </a>
                    <button type="button" data-copy-link="{{ $canonical }}"
                            aria-label="{{ __('Copy link') }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-black/10 text-black/60 transition-all hover:-translate-y-0.5 hover:border-[#4F46E5] hover:text-[#4F46E5]">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 7H5a3 3 0 0 0 0 6h4M11 13h4a3 3 0 0 0 0-6h-4M7 10h6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>
    </div>

    {{-- Featured image — full-bleed with rounded corners on desktop --}}
    <div class="mt-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <figure class="overflow-hidden rounded-2xl border border-black/[0.06] shadow-sm">
                <img src="{{ $cover }}"
                     alt="{{ $post->featured_image_alt ?: $post->title }}"
                     class="h-auto w-full object-cover"
                     fetchpriority="high"
                     decoding="async"
                     width="1200"
                     height="630">
            </figure>
        </div>
    </div>
</section>

{{-- Article body --}}
<section class="bg-white pb-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <article id="post-content" class="blog-prose"
                 itemscope itemtype="https://schema.org/BlogPosting">
            <meta itemprop="headline" content="{{ $post->title }}">
            <meta itemprop="datePublished" content="{{ $post->published_date_for_schema }}">
            <meta itemprop="dateModified"  content="{{ $post->updated_date_for_schema }}">
            <div itemprop="articleBody">
                {!! $post->content !!}
            </div>
        </article>

        @include('frontend.ads.slot', ['placement' => 'blog_article'])

        {{-- Tag rail --}}
        @if (! empty($tags))
            <div class="mt-12 border-t border-black/[0.06] pt-6">
                <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Tagged') }}</span>
                <ul role="list" class="mt-3 flex flex-wrap items-center gap-2">
                    @foreach ($tags as $tag)
                        <li>
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                               rel="tag"
                               class="l-mono inline-flex items-center rounded-full border border-black/10 bg-white px-3 py-1 text-[11px] font-medium text-black/70 transition-colors hover:border-[#4F46E5] hover:text-[#4F46E5]">
                                #{{ $tag }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- CTA back to product --}}
        <div class="mt-12 rounded-2xl border border-black/[0.08] bg-[var(--l-bg-2,#FAFAFA)] p-7 sm:p-9">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('Try it') }}
            </span>
            <h3 class="l-display mt-3 text-2xl font-extrabold tracking-tight text-black sm:text-3xl">
                {{ __('Ship your next campaign before the week is out.') }}
            </h3>
            <p class="mt-2 max-w-xl text-[14px] text-black/60">
                {{ __('One brief, every platform size, ready in under thirty seconds. Start free — no credit card.') }}
            </p>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a href="{{ url('/#pricing') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#4F46E5]">
                    {{ __('See pricing') }}
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                    </svg>
                </a>
                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-white px-5 py-3 text-sm font-medium text-black/70 transition-colors hover:border-[#4F46E5] hover:text-[#4F46E5]">
                    {{ __('Back to blog') }}
                </a>
            </div>
        </div>

        @include('frontend.ads.slot', ['placement' => 'blog_bottom'])
    </div>
</section>

{{-- Related posts --}}
@if ($relatedPosts->isNotEmpty())
    <section class="bg-white pb-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="border-t border-black/[0.06] pt-12">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Read next') }}</span>
                        <h2 class="l-display mt-2 text-2xl font-extrabold tracking-tight text-black sm:text-3xl">
                            {{ __('More from the blog') }}
                        </h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="hidden text-[13px] font-semibold text-[#4F46E5] hover:underline sm:inline-flex">
                        {{ __('View all posts →') }}
                    </a>
                </div>
                <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ($relatedPosts as $rp)
                        @php
                            $rpCover = $rp->featured_image_url
                                ?? 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="#0F172A"/></svg>');
                        @endphp
                        <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-black/[0.08] bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-[#4F46E5]/40 hover:shadow-[0_20px_40px_-15px_rgba(79,70,229,0.25)]">
                            <a href="{{ $rp->url }}" class="block relative aspect-[16/10] overflow-hidden">
                                <img src="{{ $rpCover }}"
                                     alt="{{ $rp->featured_image_alt ?: $rp->title }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
                                     loading="lazy"
                                     decoding="async"
                                     width="600"
                                     height="375">
                            </a>
                            <div class="flex flex-1 flex-col gap-2 p-5">
                                <div class="flex items-center gap-2 text-[11px] text-black/50">
                                    @if ($rp->category)
                                        <span class="l-mono rounded-full bg-[#4F46E5]/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#4F46E5]">{{ $rp->category }}</span>
                                        <span aria-hidden="true" class="h-1 w-1 rounded-full bg-black/15"></span>
                                    @endif
                                    <span class="l-mono">{{ $rp->reading_time_minutes }} {{ __('min') }}</span>
                                </div>
                                <h3 class="text-[16px] font-bold leading-snug tracking-tight text-black">
                                    <a href="{{ $rp->url }}" class="transition-colors before:absolute before:inset-0 before:content-[''] hover:text-[#4F46E5]">
                                        {{ $rp->title }}
                                    </a>
                                </h3>
                                @if (filled($rp->excerpt))
                                    <p class="line-clamp-2 text-[13px] leading-relaxed text-black/65">{{ $rp->excerpt }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

{{-- Comments section --}}
<section id="comments" class="bg-[var(--l-bg-2,#FAFAFA)] py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <header class="flex items-end justify-between gap-4">
            <div>
                <span class="l-mono text-[10px] uppercase tracking-[0.2em] text-black/45">{{ __('Discussion') }}</span>
                <h2 class="l-display mt-2 text-2xl font-extrabold tracking-tight text-black sm:text-3xl">
                    {{ __('Comments') }}
                    <span class="text-black/40">({{ $comments->count() + $comments->sum(fn ($c) => $c->replies->count()) }})</span>
                </h2>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('comment_success'))
            <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13.5px] text-emerald-800">
                {{ session('comment_success') }}
            </div>
        @endif
        @if (session('comment_error'))
            <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13.5px] text-rose-800">
                {{ session('comment_error') }}
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13.5px] text-rose-800">
                <p class="font-semibold">{{ __('Please fix the following:') }}</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Comment form --}}
        <form method="POST" action="{{ route('blog.comments.store', ['slug' => $post->slug]) }}"
              class="mt-6 rounded-2xl border border-black/[0.08] bg-white p-6">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="l-mono text-[10px] uppercase tracking-[0.15em] text-black/45">{{ __('Name') }} *</span>
                    <input type="text" name="name" required maxlength="120"
                           value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border border-black/10 bg-white px-3 py-2.5 text-[14px] focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">
                </label>
                <label class="block">
                    <span class="l-mono text-[10px] uppercase tracking-[0.15em] text-black/45">{{ __('Email') }} *</span>
                    <input type="email" name="email" required maxlength="200"
                           value="{{ old('email') }}"
                           class="mt-1 w-full rounded-lg border border-black/10 bg-white px-3 py-2.5 text-[14px] focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">
                    <span class="mt-1 block text-[10px] text-black/40">{{ __('Not published — used for Gravatar avatar.') }}</span>
                </label>
            </div>
            <label class="mt-4 block">
                <span class="l-mono text-[10px] uppercase tracking-[0.15em] text-black/45">{{ __('Website') }}</span>
                <input type="url" name="website" maxlength="255"
                       value="{{ old('website') }}"
                       placeholder="https://"
                       class="mt-1 w-full rounded-lg border border-black/10 bg-white px-3 py-2.5 text-[14px] focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">
            </label>
            <label class="mt-4 block">
                <span class="l-mono text-[10px] uppercase tracking-[0.15em] text-black/45">{{ __('Comment') }} *</span>
                <textarea name="content" required minlength="3" maxlength="4000" rows="4"
                          class="mt-1 w-full rounded-lg border border-black/10 bg-white px-3 py-2.5 text-[14px] focus:border-[#4F46E5] focus:outline-none focus:ring-2 focus:ring-[#4F46E5]/20">{{ old('content') }}</textarea>
            </label>

            {{-- Honeypot — bots fill this; real users never see it --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;height:0;width:0;overflow:hidden;">
                <label>{{ __('Leave this empty') }}<input type="text" name="website_url_2" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3">
                <p class="text-[11px] text-black/50">
                    {{ __('Comments are moderated before publishing.') }}
                </p>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-full bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#4F46E5]">
                    {{ __('Post comment') }}
                </button>
            </div>
        </form>

        {{-- Comments list --}}
        @if ($comments->isNotEmpty())
            <ul role="list" class="mt-10 space-y-6">
                @foreach ($comments as $comment)
                    <li>
                        <article class="rounded-2xl border border-black/[0.08] bg-white p-5">
                            <header class="flex items-start gap-3">
                                <img src="{{ $comment->avatar_url }}"
                                     alt="{{ $comment->name }}"
                                     loading="lazy"
                                     decoding="async"
                                     width="40" height="40"
                                     class="h-10 w-10 rounded-full bg-black/[0.05]">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                        @if (filled($comment->website))
                                            <a href="{{ $comment->website }}" rel="nofollow ugc noopener" target="_blank"
                                               class="text-[13.5px] font-semibold text-black hover:text-[#4F46E5]">{{ $comment->name }}</a>
                                        @else
                                            <span class="text-[13.5px] font-semibold text-black">{{ $comment->name }}</span>
                                        @endif
                                        <time datetime="{{ $comment->created_at->toIso8601String() }}" class="l-mono text-[11px] text-black/45">
                                            {{ $comment->created_at->diffForHumans() }}
                                        </time>
                                    </div>
                                </div>
                            </header>
                            <p class="mt-3 whitespace-pre-line text-[14px] leading-relaxed text-black/80">{{ $comment->content }}</p>

                            {{-- Replies --}}
                            @if ($comment->replies->isNotEmpty())
                                <ul role="list" class="mt-5 space-y-4 border-l-2 border-[#4F46E5]/15 pl-5">
                                    @foreach ($comment->replies as $reply)
                                        <li>
                                            <article>
                                                <header class="flex items-center gap-2">
                                                    <img src="{{ $reply->avatar_url }}"
                                                         alt="{{ $reply->name }}"
                                                         loading="lazy"
                                                         decoding="async"
                                                         width="28" height="28"
                                                         class="h-7 w-7 rounded-full bg-black/[0.05]">
                                                    <span class="text-[12.5px] font-semibold text-black">{{ $reply->name }}</span>
                                                    <time datetime="{{ $reply->created_at->toIso8601String() }}" class="l-mono text-[10px] text-black/45">
                                                        {{ $reply->created_at->diffForHumans() }}
                                                    </time>
                                                </header>
                                                <p class="mt-2 whitespace-pre-line text-[13px] leading-relaxed text-black/75">{{ $reply->content }}</p>
                                            </article>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-10 rounded-2xl border border-dashed border-black/10 bg-white px-5 py-8 text-center text-[13.5px] text-black/55">
                {{ __('Be the first to leave a comment.') }}
            </p>
        @endif
    </div>
</section>

{{-- Reading progress + copy-link interactions --}}
<script>
    (function () {
        // Reading progress bar
        var bar = document.getElementById('reading-progress');
        var article = document.getElementById('post-content');
        if (bar && article) {
            var update = function () {
                var rect = article.getBoundingClientRect();
                var total = rect.height - window.innerHeight + rect.top;
                var scrolled = -rect.top;
                var pct = Math.max(0, Math.min(100, (scrolled / Math.max(1, total)) * 100));
                bar.style.width = pct + '%';
            };
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        }
        // Copy link button
        document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-copy-link');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).catch(function () {});
                }
                var orig = btn.getAttribute('aria-label');
                btn.setAttribute('aria-label', 'Link copied');
                btn.style.color = '#4F46E5';
                setTimeout(function () {
                    btn.setAttribute('aria-label', orig);
                    btn.style.color = '';
                }, 1500);
            });
        });
    })();
</script>
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection
