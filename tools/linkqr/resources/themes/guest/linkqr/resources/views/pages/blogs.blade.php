@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="linkqr-shell linkqr-section pt-10">
        <div class="grid gap-8 xl:grid-cols-[0.72fr_1.28fr] xl:items-start">
            <aside class="xl:sticky xl:top-28">
                <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <i class="fa-light fa-newspaper"></i>
                    {{ __('Blog') }}
                </span>
                <h1 class="mt-6 text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">
                    {{ __('Playbooks for QR, Bio, and campaign growth.') }}
                </h1>
                <p class="mt-5 max-w-xl text-base leading-8 text-slate-600">
                    {{ __('Read practical notes on dynamic QR routing, link attribution, branded domains, client workspaces, and analytics operations.') }}
                </p>

                <form method="GET" action="{{ route('guest.blogs') }}" class="linkqr-card mt-7 rounded-[1.25rem] p-4">
                    <label for="blog-search" class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Find a post') }}</label>
                    <div class="mt-3 flex gap-2">
                        <input id="blog-search" name="q" value="{{ $filters['q'] }}" placeholder="{{ __('Search articles...') }}" class="h-12 min-w-0 flex-1 rounded-[var(--theme-input-radius)] border bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">
                        <button type="submit" class="linkqr-button-primary inline-flex h-12 items-center justify-center rounded-[var(--theme-button-radius)] px-5 text-sm font-bold">
                            <i class="fa-light fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </aside>

            <div>
                @if ($featuredPost)
                    <article class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.7rem]">
                        <div class="grid lg:grid-cols-[1.08fr_0.92fr]">
                            <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="block min-h-[22rem]">
                                @if ($featuredPost->thumbnailUrl())
                                    <span class="linkqr-image-frame block h-full min-h-[22rem]">
                                        <img src="{{ $featuredPost->thumbnailUrl() }}" alt="{{ $featuredPost->titleForLocale() }}" loading="lazy" onerror="this.remove();">
                                    </span>
                                @else
                                    @include(theme_view('partials.visual-card', 'guest'), [
                                        'type' => 'analytics',
                                        'icon' => 'fa-light fa-newspaper',
                                        'label' => __('Featured article'),
                                    ])
                                @endif
                            </a>
                            <div class="flex flex-col justify-between p-6 sm:p-8">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $featuredPost->publishedAtFormatted('M d, Y') ?: $featuredPost->createdAtFormatted('M d, Y') }}</p>
                                    <h2 class="mt-4 text-3xl font-extrabold leading-tight tracking-[-0.05em] text-slate-950">
                                        <a href="{{ route('guest.blog-show', $featuredPost->slug) }}">{{ $featuredPost->titleForLocale() }}</a>
                                    </h2>
                                    <p class="mt-4 text-sm leading-8 text-slate-600">{{ $featuredPost->contentPreview(240) }}</p>
                                </div>
                                <a href="{{ route('guest.blog-show', $featuredPost->slug) }}" class="linkqr-button-secondary mt-6 inline-flex w-max items-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                                    {{ __('Read article') }}
                                    <i class="fa-light fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endif
            </div>
        </div>

        <div class="mt-12 flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Latest') }}</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.05em] text-slate-950">{{ __('Recent articles and operating notes') }}</h2>
            </div>
            <span class="hidden rounded-full border bg-white px-4 py-2 text-sm font-bold text-slate-500 md:inline-flex" style="border-color: rgba(var(--theme-border-color-rgb),0.8);">{{ $blogs->total() }} {{ __('posts') }}</span>
        </div>

        <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($blogs as $blog)
                <article class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.35rem]">
                    <a href="{{ route('guest.blog-show', $blog->slug) }}" class="block h-52">
                        @if ($blog->thumbnailUrl())
                            <span class="linkqr-image-frame block h-full">
                                <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" loading="lazy" onerror="this.remove();">
                            </span>
                        @else
                            @include(theme_view('partials.visual-card', 'guest'), [
                                'type' => 'rules',
                                'icon' => 'fa-light fa-newspaper',
                                'label' => $blog->category?->nameForLocale() ?: __('Article'),
                            ])
                        @endif
                    </a>
                    <div class="p-6">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $blog->category?->nameForLocale() ?: __('Article') }}</p>
                        <h3 class="mt-3 text-xl font-extrabold tracking-[-0.035em] text-slate-950">
                            <a href="{{ route('guest.blog-show', $blog->slug) }}">{{ $blog->titleForLocale() }}</a>
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $blog->contentPreview(150) }}</p>
                    </div>
                </article>
            @empty
                <div class="linkqr-card rounded-[1.5rem] border-dashed px-6 py-14 text-center text-sm text-slate-500 xl:col-span-3">{{ __('No blog posts found.') }}</div>
            @endforelse
        </div>

        @if ($blogs->hasPages())
            <div class="mt-8">{{ $blogs->links() }}</div>
        @endif
    </section>
@endcomponent
