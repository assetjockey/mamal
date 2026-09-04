@php($displayBaseUrl = preg_replace('#^https?://#i', '', rtrim(url('/'), '/')))
<section class="px-5 py-20">
    <div class="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
        <div>
            <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('404') }}</span>
            <h1 class="mt-6 font-serif text-6xl leading-[0.92] tracking-[-0.04em] text-[#181714] sm:text-7xl">{{ __('This page is not on the map.') }}</h1>
            <p class="mt-6 text-base leading-8 text-[#6d685f]">{{ __('The link may have moved, expired, or never existed. Head back home and claim a clean profile URL instead.') }}</p>
            <a href="{{ route('home') }}" class="mt-8 inline-flex min-h-12 items-center rounded-xl bg-[#181714] px-5 text-sm font-bold text-white">{{ __('Go Back to Homepage') }}</a>
        </div>
        <div class="rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)]">
            <div class="rounded-[1.35rem] bg-[#d8f2fb] bg-[linear-gradient(#b8e1ef_1px,transparent_1px),linear-gradient(90deg,#b8e1ef_1px,transparent_1px)] bg-[length:28px_28px] p-6">
                <div class="rounded-[1.15rem] border-4 border-[#5f8dff] bg-[#fffdf8] p-6">
                    <p class="font-mono text-sm font-bold text-[#8a867d]">{{ $displayBaseUrl }}/missing-page</p>
                    <p class="mt-16 font-serif text-5xl leading-none text-[#181714]">{{ __('Not found') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
