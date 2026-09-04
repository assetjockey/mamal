@php
    $countPostBlog = Home::countPostBlog();
    $recentPlogs = Home::getRecentBlogs();
    $categories = Home::getBlogCategories();
    $tags = Home::getBlogTags();
    $blogDetail = Home::getBlogDetail();
@endphp

@section('pagetitle', $blogDetail->title)

<section class="px-5 py-14 sm:py-20">
    <div class="mx-auto max-w-6xl">
        <a href="{{ route('blogs') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#d8d3c7] bg-white/70 px-4 text-sm font-bold text-[#6d685f] transition hover:text-[#181714]">
            <i class="fa-light fa-arrow-left mr-2"></i>{{ __('Back to blog') }}
        </a>

        <header class="mt-8 max-w-4xl">
            <div class="flex flex-wrap items-center gap-2">
                @if($blogDetail->category)
                    <span class="rounded-full bg-[#cef8dc] px-3 py-1 text-xs font-extrabold uppercase tracking-[0.14em] text-[#181714]">{{ $blogDetail->category->name }}</span>
                @endif
                @foreach($blogDetail->tags as $tag)
                    <span class="rounded-full border border-[#d8d3c7] bg-white/70 px-3 py-1 text-xs font-bold text-[#6d685f]">{{ $tag->name }}</span>
                @endforeach
            </div>
            <h1 class="mt-5 font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ $blogDetail->title }}</h1>
            <p class="mt-5 text-sm font-semibold text-[#8a867d]">{{ __("Created at: ") }} {{ $blogDetail->created ? \Carbon\Carbon::createFromTimestamp($blogDetail->created)->format('d M, Y') : '' }}</p>
        </header>

        <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
            <article class="rounded-[1.5rem] border border-[#ded7ca] bg-[#fffdf8] p-6 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] md:p-9">
                @if($blogDetail->thumbnail)
                    <img src="{{ Media::url($blogDetail->thumbnail) }}" alt="{{ $blogDetail->title }}" class="mb-8 h-auto w-full rounded-[1.25rem] object-cover">
                @endif
                <div class="prose prose-slate max-w-none prose-headings:font-serif prose-a:text-[#5f8dff]">
                    {!! htmlspecialchars_decode($blogDetail->content) !!}
                </div>
            </article>

            <aside class="space-y-4 lg:sticky lg:top-28">
                <div class="rounded-[1.25rem] border border-[#ded7ca] bg-[#fffdf8] p-5">
                    <h2 class="font-serif text-2xl text-[#181714]">{{ __('Categories') }}</h2>
                    <div class="mt-4 grid gap-2">
                        <a href="{{ route('blogs') }}" class="rounded-xl bg-[#f4f1ea] px-4 py-3 text-sm font-bold text-[#57534b]">{{ __("All Categories") }} ({{ $countPostBlog }})</a>
                        @foreach($categories as $cat)
                            <a href="{{ url('blogs/'.$cat->slug) }}" class="rounded-xl bg-[#f4f1ea] px-4 py-3 text-sm font-bold text-[#57534b]">{{ $cat->name }} ({{ $cat->articles_count }})</a>
                        @endforeach
                    </div>
                </div>

                @if($tags->isNotEmpty())
                    <div class="rounded-[1.25rem] border border-[#ded7ca] bg-white/70 p-5">
                        <h2 class="font-serif text-2xl text-[#181714]">{{ __('Popular Tags') }}</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <a href="{{ url('blogs/tag/'.$tag->slug) }}" class="rounded-full border border-[#d8d3c7] bg-white px-3 py-1 text-xs font-bold text-[#6d685f]">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($recentPlogs->isNotEmpty())
                    <div class="rounded-[1.25rem] border border-[#ded7ca] bg-white/70 p-5">
                        <h2 class="font-serif text-2xl text-[#181714]">{{ __('Recent Posts') }}</h2>
                        <div class="mt-4 grid gap-3">
                            @foreach($recentPlogs as $blog)
                                <a href="{{ route('blog.detail', $blog->slug) }}" class="text-sm font-bold leading-6 text-[#57534b] hover:text-[#181714]">{{ $blog->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
