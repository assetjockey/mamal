{{-- FAQ — open panels flip to ink, indigo chevron. --}}
@php
    // Admin-managed FAQs (injected by the `welcome` view composer). When none
    // are configured yet we fall back to a curated default set so the section
    // never renders empty.
    $faqs = (isset($faqs) && count($faqs))
        ? collect($faqs)->map(fn ($faq) => [
            'question' => $faq['question'] ?? $faq->question ?? '',
            'answer' => $faq['answer'] ?? $faq->answer ?? '',
        ])->all()
        : [
        ['question' => __('Do I need design experience to use AI Ad Studio?'),            'answer' => __('No. The studio handles layout, typography, and platform sizing for you. Give it a short brief and your brand assets and it produces finished ads you can publish immediately.')],
        ['question' => __('Which platforms are supported?'),                              'answer' => __('We ship with more than twenty canvas presets across Instagram, Facebook, TikTok, YouTube, LinkedIn, and Google Display — covering every major aspect ratio for modern paid campaigns.')],
        ['question' => __('Can I use my own brand assets?'),                              'answer' => __('Yes. Upload your logo, pick your fonts and palette once in a brand kit, and every generation respects those rules automatically so your output stays on brand without manual cleanup.')],
        ['question' => __('Do you support video ads?'),                                   'answer' => __('The Video Studio produces short-form video ads with captions, looped B-roll, and platform-ready aspect ratios for Reels, Stories, TikTok, and YouTube Shorts.')],
        ['question' => __("What happens to generations I don't use?"),                    'answer' => __('Every render is saved to your asset gallery with tags and campaign folders. You can search, favorite, and remix any previous generation without regenerating from scratch.')],
        ['question' => __('Can I try it before paying?'),                                 'answer' => __("Start for free and generate a handful of ads with every studio to see the output quality. Upgrade to a paid plan when you're ready to scale across channels.")],
    ];
@endphp

<section id="faq" class="relative overflow-hidden py-24 sm:py-32">
    {{-- Abstract decorative lines behind the FAQ --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        {{-- Large sweeping curves — flowing left-to-right --}}
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 1200 800" preserveAspectRatio="none" fill="none">
            <path d="M-100,200 C 200,50 500,350 700,180 S 1100,400 1350,250" stroke="#4F46E5" stroke-width="1.5" opacity="0.25"/>
            <path d="M-50,350 C 250,200 450,500 750,320 S 1050,550 1300,380" stroke="#4F46E5" stroke-width="1" opacity="0.18"/>
            <path d="M-100,550 C 300,400 600,650 900,480 S 1150,700 1400,520" stroke="#000000" stroke-width="1" opacity="0.12"/>
        </svg>

        {{-- Diagonal accent line — top-right corner --}}
        <div class="absolute -right-20 top-10 h-[1.5px] w-[45%] origin-right -rotate-[18deg]"
             style="background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.35) 40%, rgba(79, 70, 229, 0.5) 70%, transparent);"></div>

        {{-- Diagonal accent line — bottom-left --}}
        <div class="absolute -left-10 bottom-20 h-[1.5px] w-[35%] origin-left rotate-[12deg]"
             style="background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.15) 30%, rgba(79, 70, 229, 0.3) 60%, transparent);"></div>

        {{-- Concentric arcs — right side --}}
        <svg class="absolute -right-32 top-1/2 h-[500px] w-[500px] -translate-y-1/2" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="80" stroke="#4F46E5" stroke-width="1" opacity="0.18"/>
            <circle cx="200" cy="200" r="130" stroke="#4F46E5" stroke-width="0.8" opacity="0.14"/>
            <circle cx="200" cy="200" r="180" stroke="#4F46E5" stroke-width="0.6" opacity="0.10"/>
        </svg>

        {{-- Small dot cluster — left side --}}
        <svg class="absolute left-8 top-1/3 h-32 w-32" viewBox="0 0 80 80" fill="#4F46E5" opacity="0.3">
            <circle cx="10" cy="10" r="2"/><circle cx="30" cy="10" r="2"/><circle cx="50" cy="10" r="2"/><circle cx="70" cy="10" r="2"/>
            <circle cx="10" cy="30" r="2"/><circle cx="30" cy="30" r="2"/><circle cx="50" cy="30" r="2"/><circle cx="70" cy="30" r="2"/>
            <circle cx="10" cy="50" r="2"/><circle cx="30" cy="50" r="2"/><circle cx="50" cy="50" r="2"/><circle cx="70" cy="50" r="2"/>
            <circle cx="10" cy="70" r="2"/><circle cx="30" cy="70" r="2"/><circle cx="50" cy="70" r="2"/><circle cx="70" cy="70" r="2"/>
        </svg>
    </div>
    <div class="relative z-10 mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                FAQ
            </span>
            <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                {{ __('Frequently') }}
                <span class="l-accent">{{ __('asked questions.') }}</span>
            </h2>
        </div>

        <dl class="mt-12 space-y-3">
            @foreach ($faqs as $faq)
                <details class="l-faq group l-card overflow-hidden p-0 transition-all">
                    <summary class="flex cursor-pointer items-center justify-between gap-6 px-6 py-5 text-left text-[15px] font-semibold">
                        <dt class="l-faq-q flex-1 text-black">{{ $faq['question'] }}</dt>
                        <span class="l-faq-ic inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border border-[var(--l-border)] text-black/70 transition-all group-open:rotate-180">
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 8l5 5 5-5"/>
                            </svg>
                        </span>
                    </summary>
                    <dd class="l-faq-a px-6 pb-6 text-[14px] leading-relaxed text-black/65">
                        {{ $faq['answer'] }}
                    </dd>
                </details>
            @endforeach
        </dl>

        <div class="mt-10 text-center">
            <p class="text-[13px] text-black/55">{{ __("Can't find what you're looking for?") }}</p>
            <a href="{{ route('contact') }}" class="l-btn-indigo mt-4 !px-6 !py-3">
                {{ __("Let's Talk With Us") }}
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                </svg>
            </a>
        </div>
    </div>
</section>
