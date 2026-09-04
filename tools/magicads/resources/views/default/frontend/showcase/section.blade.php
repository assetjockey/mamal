{{-- Showcase — tabbed gallery, ad cards with floating overlay chips. --}}
@php
    $copyCards = [
        ['platform' => __('Instagram'),     'headline' => __('Drop the template, ship the campaign.'),              'body' => __('Brand-safe captions in your voice. Rendered in every feed size.')],
        ['platform' => __('LinkedIn'),      'headline' => __("Modern teams don't wait for the design queue."),      'body' => __('Professional tone, hook-first headlines, 150-char safe.')],
        ['platform' => __('Google Search'), 'headline' => __('Show up for every high-intent query.'),               'body' => __('Responsive headline groups tuned to your keyword themes.')],
    ];

    // Image tiles — reference-style bento with floating overlay chips
    $imageTiles = [
        [
            'ratio' => '3/4',
            'label' => __('Square feed'),
            'span'  => 'sm:col-span-3',
            'img'   => 'assets/frontend/13.avif',
            'chips' => [
                ['pos' => 'top-right', 'variant' => 'white', 'text' => '1:1 · 1080²', 'mono' => true],
            ],
            'hero_chip' => ['type' => 'discount', 'text' => 'UP TO 20% OFF'],
            'caption'   => __('Hamilton Precision Watch'),
            'meta'      => __('Likes: 101K'),
        ],
        [
            'ratio' => '3/4',
            'label' => __('Landscape'),
            'span'  => 'sm:col-span-3',
            'img'   => 'assets/frontend/14.avif',
            'chips' => [
                ['pos' => 'top-left', 'variant' => 'ink', 'text' => 'Why wait to glow?', 'emoji' => '✨'],
                ['pos' => 'top-right', 'variant' => 'white', 'text' => '1.91:1', 'mono' => true],
            ],
            'hero_chip' => ['type' => 'discount', 'text' => 'NEW DROP'],
            'caption'   => __('Exfoliating creme · CPG'),
            'meta'      => __('Likes: 43K'),
        ],
        [
            'ratio' => '3/4',
            'label' => __('Reel'),
            'span'  => 'sm:col-span-3',
            'img'   => 'assets/frontend/15.avif',
            'chips' => [
                ['pos' => 'top-left', 'variant' => 'indigo', 'text' => 'Visit Sweden'],
                ['pos' => 'top-right', 'variant' => 'white', 'text' => '9:16', 'mono' => true],
            ],
            'caption'   => __('Travel brand · long-form'),
            'meta'      => __('Sales growth: +83%'),
        ],
        [
            'ratio' => '3/4',
            'label' => __('Story'),
            'span'  => 'sm:col-span-3',
            'img'   => 'assets/frontend/16.avif',
            'chips' => [
                ['pos' => 'top-left', 'variant' => 'white', 'text' => 'Curious about…', 'mono' => false],
                ['pos' => 'top-right', 'variant' => 'indigo', 'text' => 'A/B'],
            ],
            'caption'   => __('Handmade goods · DTC'),
            'meta'      => __('Sales growth: +97%'),
        ],
    ];

    // Video tiles — same pattern, but taller 9:16
    $videoTiles = [
        [
            'label' => __('Reels preview'),
            'duration' => '00:15',
            'video' => 'assets/frontend/video/6.mp4',
            'chips' => [['pos' => 'top-left', 'variant' => 'white', 'text' => 'Meta · Reel', 'mono' => false]],
            'caption' => __('Fashion drop · 15s'),
        ],
        [
            'label' => __('TikTok preview'),
            'duration' => '00:10',
            'video' => 'assets/frontend/video/7.mp4',
            'chips' => [['pos' => 'top-left', 'variant' => 'ink', 'text' => 'TikTok · In-Feed']],
            'caption' => __('App install · 10s'),
        ],
        [
            'label' => __('Shorts preview'),
            'duration' => '00:30',
            'video' => 'assets/frontend/video/8.mp4',
            'chips' => [['pos' => 'top-left', 'variant' => 'indigo', 'text' => 'YouTube Shorts']],
            'caption' => __('SaaS announce · 30s'),
        ],
        [
            'label' => __('Story preview'),
            'duration' => '00:08',
            'video' => 'assets/frontend/video/9.mp4',
            'chips' => [['pos' => 'top-left', 'variant' => 'white', 'text' => 'IG · Story'],
                        ['pos' => 'top-right', 'variant' => 'indigo', 'text' => 'NEW']],
            'caption' => __('Product teaser · 8s'),
        ],
    ];
@endphp

<section id="showcase" class="relative overflow-hidden py-24 sm:py-32">
    {{-- Subtle light guide lines — a few thin hairlines, nothing busy. --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        {{-- Horizontal hairlines, fading at the edges --}}
        <div class="absolute inset-x-0 top-[22%] h-px"
             style="background: linear-gradient(90deg, transparent, rgba(79,70,229,0.12) 35%, rgba(15,23,42,0.06) 60%, transparent);"></div>
        <div class="absolute inset-x-0 top-[58%] h-px"
             style="background: linear-gradient(90deg, transparent, rgba(15,23,42,0.05) 30%, rgba(79,70,229,0.1) 65%, transparent);"></div>
        <div class="absolute inset-x-0 bottom-[14%] h-px"
             style="background: linear-gradient(90deg, transparent 10%, rgba(79,70,229,0.08) 50%, transparent 90%);"></div>

        {{-- Two faint vertical hairlines --}}
        <div class="absolute inset-y-0 left-1/3 w-px hidden lg:block"
             style="background: linear-gradient(180deg, transparent, rgba(15,23,42,0.05) 25%, rgba(15,23,42,0.05) 75%, transparent);"></div>
        <div class="absolute inset-y-0 right-1/4 w-px hidden lg:block"
             style="background: linear-gradient(180deg, transparent, rgba(79,70,229,0.08) 30%, rgba(79,70,229,0.08) 70%, transparent);"></div>
    </div>

    <div aria-hidden="true" class="l-dots absolute inset-x-0 top-0 h-64 opacity-50"
         style="mask-image: linear-gradient(180deg, black, transparent);
                -webkit-mask-image: linear-gradient(180deg, black, transparent);"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('Showcase') }}
            </span>
            <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                {{ __('See what') }}
                <span class="l-accent">{{ __('shipped') }}</span>
                {{ __('yesterday.') }}
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-[15px] text-black/60">
                {{ __('Real output from real customers. Pick a studio.') }}
            </p>
        </div>

        <div class="mt-10 flex justify-center">
            <div role="tablist" aria-label="{{ __('Studio showcase') }}"
                 class="inline-flex items-center gap-1 rounded-full border border-[var(--l-border)] bg-white p-1 text-sm">
                <button type="button" role="tab" id="showcase-tab-video"
                        aria-selected="true" aria-controls="showcase-panel-video" tabindex="0"
                        class="l-tab rounded-full px-5 py-2 font-medium text-black/65 transition-colors hover:text-black">
                    {{ __('Video Ads') }}
                </button>
                <button type="button" role="tab" id="showcase-tab-image"
                        aria-selected="false" aria-controls="showcase-panel-image" tabindex="-1"
                        class="l-tab rounded-full px-5 py-2 font-medium text-black/65 transition-colors hover:text-black">
                    {{ __('Image Ads') }}
                </button>
                <button type="button" role="tab" id="showcase-tab-copy"
                        aria-selected="false" aria-controls="showcase-panel-copy" tabindex="-1"
                        class="l-tab rounded-full px-5 py-2 font-medium text-black/65 transition-colors hover:text-black">
                    {{ __('Ad Copy') }}
                </button>
            </div>
        </div>

        {{-- VIDEO --}}
        <div id="showcase-panel-video" role="tabpanel" aria-labelledby="showcase-tab-video" tabindex="0" class="mt-12">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($videoTiles as $v)
                    <figure class="group relative overflow-hidden rounded-2xl ring-1 ring-black/5 shadow-[0_10px_40px_-12px_rgba(0,0,0,0.2)] transition-transform hover:-translate-y-1">
                        <div class="relative overflow-hidden bg-black" style="aspect-ratio: 9/16;">
                            <video class="absolute inset-0 h-full w-full object-cover"
                                   playsinline
                                   loop
                                   preload="metadata"
                                   aria-label="{{ $v['label'] }}"
                                   onplay="this.parentElement.querySelectorAll('[data-video-overlay]').forEach(el => el.style.display='none'); this.parentElement.querySelector('[data-pause-overlay]').style.display='flex'"
                                   onpause="this.parentElement.querySelectorAll('[data-video-overlay]').forEach(el => el.style.display=''); this.parentElement.querySelector('[data-pause-overlay]').style.display='none'"
                                   onended="this.parentElement.querySelectorAll('[data-video-overlay]').forEach(el => el.style.display=''); this.parentElement.querySelector('[data-pause-overlay]').style.display='none'">
                                <source src="{{ asset($v['video']) }}" type="video/mp4">
                            </video>

                            {{-- Chips --}}
                            @foreach ($v['chips'] as $chip)
                                @php
                                    $posClass = match($chip['pos']) {
                                        'top-left'     => 'top-3 left-3',
                                        'top-right'    => 'top-3 right-3',
                                        default        => 'top-3 left-3',
                                    };
                                    $variantClass = match($chip['variant']) {
                                        'indigo' => 'l-overlay-chip--indigo',
                                        'ink'    => 'l-overlay-chip--ink',
                                        default  => '',
                                    };
                                @endphp
                                <span class="l-overlay-chip {{ $variantClass }} absolute {{ $posClass }} z-20 !text-[10px]">
                                    {{ $chip['text'] }}
                                </span>
                            @endforeach

                            {{-- Play button --}}
                            <button type="button" data-video-overlay
                                    onclick="this.parentElement.querySelector('video').play()"
                                    class="absolute inset-0 z-10 flex items-center justify-center bg-black/15 transition-colors hover:bg-black/5"
                                    aria-label="{{ __('Play video') }}">
                                <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/95 text-black shadow-lg transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5 translate-x-[2px]" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                        <path d="M3 1.5v9l8-4.5z"/>
                                    </svg>
                                </span>
                            </button>

                            {{-- Pause button (only while playing, reveals on hover) --}}
                            <button type="button" data-pause-overlay style="display:none"
                                    onclick="this.parentElement.querySelector('video').pause()"
                                    class="absolute inset-0 z-10 items-center justify-center bg-black/0 opacity-0 transition-all hover:bg-black/15 group-hover:opacity-100"
                                    aria-label="{{ __('Pause video') }}">
                                <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-white/95 text-black shadow-lg transition-transform group-hover:scale-110">
                                    <svg class="h-5 w-5" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                        <rect x="3" y="2" width="2.5" height="8" rx="0.5"/>
                                        <rect x="6.5" y="2" width="2.5" height="8" rx="0.5"/>
                                    </svg>
                                </span>
                            </button>

                            {{-- Duration --}}
                            <div data-video-overlay class="pointer-events-none absolute inset-x-3 bottom-3 z-20">
                                <div class="flex items-center justify-end text-[9px] font-medium text-white/85">
                                    <span class="l-mono rounded bg-black/40 px-1.5 py-0.5 backdrop-blur">{{ $v['duration'] }}</span>
                                </div>
                            </div>
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>

        {{-- IMAGE — bento-style grid with floating overlay chips --}}
        <div id="showcase-panel-image" role="tabpanel" aria-labelledby="showcase-tab-image" tabindex="0" hidden aria-hidden="true" class="mt-12">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-12">
                @foreach ($imageTiles as $tile)
                    <figure class="{{ $tile['span'] }} group relative overflow-hidden rounded-2xl ring-1 ring-black/5 shadow-[0_10px_40px_-12px_rgba(0,0,0,0.2)] transition-transform hover:-translate-y-1">
                        <div class="relative overflow-hidden bg-cover bg-center" style="aspect-ratio: {{ $tile['ratio'] }}; background-image: url('{{ asset($tile['img']) }}');"></div>
                    </figure>
                @endforeach
            </div>
        </div>

        {{-- COPY --}}
        <div id="showcase-panel-copy" role="tabpanel" aria-labelledby="showcase-tab-copy" tabindex="0" hidden aria-hidden="true" class="mt-12">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($copyCards as $card)
                    <article class="l-card relative overflow-hidden p-6">
                        <div aria-hidden="true" class="absolute right-0 top-0 h-20 w-20 -translate-y-6 translate-x-6 rounded-full opacity-40"
                             style="background: radial-gradient(circle, rgba(79,70,229,0.25), transparent 70%);"></div>
                        <div class="relative flex items-center justify-between">
                            <span class="l-chip">
                                <span class="h-1 w-1 rounded-full bg-[#4F46E5]"></span>
                                {{ $card['platform'] }}
                            </span>
                            <span class="l-chip l-chip--indigo !px-2 !py-0.5 !text-[9px]">A/B</span>
                        </div>
                        <h3 class="relative mt-4 text-[17px] font-bold leading-snug text-black">{{ $card['headline'] }}</h3>
                        <p class="relative mt-2 text-[13px] text-black/65">{{ $card['body'] }}</p>
                        <div class="relative mt-5 flex items-center gap-2 border-t border-[var(--l-hairline)] pt-4 text-[11px] text-black/55">
                            <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="6" cy="6" r="4.5"/><path d="M6 3v3l2 1.5"/></svg>
                            <span class="l-mono">0.82s</span>
                            <span class="text-black/25">·</span>
                            <span>{{ __('150 chars') }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
