<section class="px-5 py-14 sm:py-20">
    <div class="mx-auto max-w-5xl">
        <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Privacy') }}</span>
        <h1 class="mt-6 font-serif text-5xl leading-[0.98] tracking-[-0.04em] text-[#181714] sm:text-6xl">{{ __('Privacy Policy') }}</h1>
        <p class="mt-5 max-w-3xl text-base leading-8 text-[#6d685f]">{{ __('We are committed to safeguarding your personal information.') }}</p>
        <div class="mt-8 rounded-[1.5rem] border border-[#ded7ca] bg-[#fffdf8] p-7 shadow-[0_28px_85px_-64px_rgba(24,23,20,.55)] md:p-9">
            <div class="prose prose-slate max-w-none prose-headings:font-serif prose-a:text-[#5f8dff]">
                {!! htmlspecialchars_decode(get_option('privacy_policy')) !!}
            </div>
        </div>
    </div>
</section>
