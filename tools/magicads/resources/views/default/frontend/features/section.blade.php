{{-- Features — asymmetric bento with content-rich cards. --}}
<section id="features" class="relative py-24 sm:py-32">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-[1fr_auto] md:items-end">
            <div>
                <span class="l-chip l-chip--indigo">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                    {{ __('Capabilities') }}
                </span>
                <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl lg:text-6xl">
                    {{ __('One studio.') }}
                    <span class="l-accent">{{ __('Every') }}</span>
                    {{ __('ad format.') }}
                </h2>
            </div>
            <p class="max-w-sm text-[15px] text-black/60 md:text-right">
                {{ __('From a one-sentence brief to platform-ready creative — images, video, and copy, locked to your brand.') }}
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-5 md:grid-cols-12 md:auto-rows-[minmax(0,1fr)]">

            {{-- 1. IMAGE STUDIO — content-rich hero card --}}
            <article class="l-card relative overflow-hidden md:col-span-7 md:row-span-2 min-h-[420px] p-7 sm:p-8">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-black/45">01 / image</span>
                        <h3 class="mt-3 text-2xl font-bold leading-tight tracking-tight text-black sm:text-[26px]">
                            {{ __('Photoreal ads, every canvas.') }}
                        </h3>
                        <p class="mt-2 max-w-md text-[13px] leading-relaxed text-black/60">
                            {{ __('One brief → twelve placements. Your brand kit locks logos, fonts, and palette automatically.') }}
                        </p>
                    </div>
                    <span class="l-chip l-chip--indigo shrink-0">
                        <span class="l-pulse inline-block h-1 w-1 rounded-full bg-[#4F46E5]"></span>
                        {{ __('Generating') }}
                    </span>
                </div>

                {{-- Workflow: compact brief panel + dense output grid --}}
                <div class="mt-5 grid grid-cols-12 gap-3">
                    {{-- Left: brief + kit (compact) --}}
                    <div class="col-span-4 space-y-2">
                        <div class="rounded-lg border border-[var(--l-hairline)] bg-[var(--l-bg-2)] p-3">
                            <div class="l-mono text-[9px] uppercase tracking-[0.15em] text-black/45">{{ __('Prompt') }}</div>
                            <p class="mt-1 text-[11px] leading-snug text-black">
                                <span class="font-semibold">{{ __('Autumn drop.') }}</span>
                                <span class="text-black/60">{{ __(' Playful, amber & plum.') }}</span>
                            </p>
                        </div>
                        <div class="rounded-lg border border-[var(--l-hairline)] bg-[var(--l-bg-2)] p-3">
                            <div class="l-mono text-[9px] uppercase tracking-[0.15em] text-black/45">{{ __('Brand kit') }}</div>
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <span class="h-4 w-4 rounded-full border border-white bg-[#4F46E5]"></span>
                                <span class="h-4 w-4 rounded-full border border-white bg-black"></span>
                                <span class="h-4 w-4 rounded-full border border-white bg-white ring-1 ring-black/10"></span>
                                <span class="l-mono ml-1 text-[9px] text-black/55">Inter · 700</span>
                            </div>
                        </div>
                        <div class="rounded-lg border border-[var(--l-hairline)] bg-[var(--l-bg-2)] p-3">
                            <div class="l-mono text-[9px] uppercase tracking-[0.15em] text-black/45">{{ __('Queue') }}</div>
                            <div class="mt-1.5 space-y-1">
                                @foreach (['IG Feed','Reel','LinkedIn','TikTok','FB Story','YT Thumb'] as $item)
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-2 w-2 text-emerald-500" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="m2 5 2 2 4-4"/></svg>
                                        <span class="l-mono flex-1 truncate text-[9px] text-black/70">{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Right: dense output tile grid (8 cols) --}}
                    <div class="col-span-8 grid grid-cols-4 gap-1.5">
                        {{-- Row 1: IG Feed wide + TikTok square --}}
                        <div class="relative col-span-2 aspect-[4/3] overflow-hidden rounded-lg"
                             style="background: radial-gradient(circle at 30% 30%, rgba(79,70,229,0.6), transparent 60%), #0A0A0A;">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/5.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/15 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">IG Feed</span>
                            <div class="absolute inset-x-2 bottom-2">
                                <div class="text-[9px] font-bold leading-tight text-white">{{ __('Autumn drop') }}</div>
                                <div class="mt-0.5 h-0.5 w-8 rounded-full bg-[#4F46E5]"></div>
                            </div>
                        </div>
                        <div class="relative aspect-square overflow-hidden rounded-lg"
                             style="background: linear-gradient(135deg, #0A0A0A 0%, #1F1F1F 60%, #4F46E5 100%);">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/6.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/15 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">TikTok</span>
                            <div class="absolute inset-x-2 bottom-2 text-[8px] font-bold text-white/80">{{ __('Swipe →') }}</div>
                        </div>
                        <div class="relative aspect-square overflow-hidden rounded-lg bg-[#4F46E5]">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/7.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/20 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">Story</span>
                        </div>

                        {{-- Row 2: Reel tall + LinkedIn wide + YT --}}
                        <div class="relative row-span-2 overflow-hidden rounded-lg bg-[#4F46E5]"
                             style="background: linear-gradient(180deg, #4F46E5 0%, #1E1B4B 100%);">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/8.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/20 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">Reel</span>
                            <div class="absolute inset-x-1.5 bottom-1.5">
                                <div class="text-[9px] font-bold text-white">{{ __('New drop.') }}</div>
                                <div class="mt-0.5 h-0.5 w-full rounded-full bg-white/20">
                                    <div class="h-full w-2/3 rounded-full bg-white"></div>
                                </div>
                            </div>
                        </div>
                        <div class="relative col-span-2 aspect-[1.91/1] overflow-hidden rounded-lg border border-[var(--l-hairline)] bg-white">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/9.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-black/80 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">LinkedIn</span>
                            <div class="absolute inset-x-2 bottom-2 flex items-center justify-between">
                                <div class="text-[9px] font-bold text-white drop-shadow">{{ __('Meet the team.') }}</div>
                                <div class="h-3 w-8 rounded-sm bg-black"></div>
                            </div>
                        </div>
                        <div class="relative aspect-video overflow-hidden rounded-lg"
                             style="background: linear-gradient(160deg, #1A1A2E 0%, #0A0A0A 50%, rgba(79,70,229,0.4) 100%);">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/10.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/15 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">YT</span>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                                    <svg class="h-2 w-2 translate-x-[1px] text-white" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M3 1.5v9l8-4.5z"/></svg>
                                </span>
                            </div>
                        </div>

                        {{-- Row 3: FB wide + Google Display + extra square --}}
                        <div class="relative col-span-2 aspect-[1.91/1] overflow-hidden rounded-lg"
                             style="background: linear-gradient(110deg, #0A0A0A 0%, #1F1F1F 50%, #4F46E5 100%);">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/11.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-white/15 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">FB</span>
                            <div class="absolute inset-x-2 bottom-2 text-[8px] font-bold text-white/80">{{ __('Shop now →') }}</div>
                        </div>
                        <div class="relative aspect-[4/3] overflow-hidden rounded-lg border border-[var(--l-hairline)] bg-[var(--l-bg-2)]">
                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/frontend/12.avif') }}');"></div>
                            <span class="absolute left-1.5 top-1.5 l-mono rounded bg-black/80 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">GDN</span>
                            <div class="absolute inset-x-2 bottom-2 space-y-0.5">
                                <div class="h-0.5 w-3/4 rounded-full bg-white/70"></div>
                                <div class="h-0.5 w-1/2 rounded-full bg-white/40"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer stat strip --}}
                <div class="mt-6 flex items-center justify-between border-t border-[var(--l-hairline)] pt-4 text-[11px] text-black/55">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="l-pulse inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            <span class="l-mono">12 / 12 {{ __('rendered') }}</span>
                        </span>
                        <span class="l-mono">00:23 {{ __('elapsed') }}</span>
                    </div>
                    <span class="l-mono">{{ __('Gemini · 2.5 Pro') }}</span>
                </div>
            </article>

            {{-- 2. VIDEO STUDIO --}}
            <article class="l-card l-card--ink relative overflow-hidden md:col-span-5 min-h-[320px] p-7">
                <div aria-hidden="true" class="l-grid absolute inset-0 opacity-[0.06]"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-white/50">02 / video</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-0.5 text-[9px] font-semibold text-white/80">
                            <span class="l-livedot !bg-emerald-400 !h-1 !w-1"></span>
                            {{ __('Rendering') }}
                        </span>
                    </div>
                    <h3 class="mt-3 text-xl font-bold tracking-tight text-white">
                        {{ __('Short-form video, captions included.') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-relaxed text-white/65">
                        {{ __('Reels, Shorts, TikTok In-Feed — aspect ratios handled for you.') }}
                    </p>

                    {{-- Video placeholder grid --}}
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        @foreach ([
                            ['ratio' => '9/16', 'label' => 'Reel',   'src' => 'assets/frontend/video/3.mp4'],
                            ['ratio' => '9/16', 'label' => 'TikTok', 'src' => 'assets/frontend/video/4.mp4'],
                            ['ratio' => '9/16', 'label' => 'Short',  'src' => 'assets/frontend/video/5.mp4'],
                        ] as $vid)
                            <figure class="group relative overflow-hidden rounded-lg bg-black" style="aspect-ratio: {{ $vid['ratio'] }};">
                                <video class="absolute inset-0 h-full w-full object-cover"
                                       playsinline
                                       preload="metadata"
                                       onplay="this.closest('figure').querySelector('[data-play-overlay]').style.display='none'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='flex'"
                                       onpause="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'"
                                       onended="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'">
                                    <source src="{{ asset($vid['src']) }}" type="video/mp4">
                                </video>
                                {{-- Play overlay (shown while paused) --}}
                                <button type="button" data-play-overlay
                                        onclick="this.closest('figure').querySelector('video').play()"
                                        class="absolute inset-0 z-10 flex items-center justify-center bg-black/20 transition-colors hover:bg-black/10"
                                        aria-label="{{ __('Play video') }}">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition-transform group-hover:scale-110">
                                        <svg class="h-3 w-3 translate-x-[1px] text-white" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                            <path d="M3 1.5v9l8-4.5z"/>
                                        </svg>
                                    </span>
                                </button>
                                {{-- Pause overlay (only while playing, reveals on hover) --}}
                                <button type="button" data-pause-overlay style="display:none"
                                        onclick="this.closest('figure').querySelector('video').pause()"
                                        class="absolute inset-0 z-10 items-center justify-center bg-black/0 opacity-0 transition-all hover:bg-black/20 group-hover:opacity-100"
                                        aria-label="{{ __('Pause video') }}">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm transition-transform group-hover:scale-110">
                                        <svg class="h-3 w-3 text-white" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                            <rect x="3" y="2" width="2.5" height="8" rx="0.5"/>
                                            <rect x="6.5" y="2" width="2.5" height="8" rx="0.5"/>
                                        </svg>
                                    </span>
                                </button>
                                {{-- Label --}}
                                <span class="pointer-events-none absolute left-1.5 top-1.5 z-20 l-mono rounded bg-white/15 px-1 py-0.5 text-[7px] font-bold uppercase tracking-widest text-white backdrop-blur">{{ $vid['label'] }}</span>
                            </figure>
                        @endforeach
                    </div>

                    {{-- Duration + format chips --}}
                    <div class="mt-3 flex items-center gap-2 text-[10px] text-white/55">
                        <span class="l-mono rounded-full border border-white/15 px-2 py-0.5">15s</span>
                        <span class="l-mono rounded-full border border-white/15 px-2 py-0.5">9:16</span>
                        <span class="l-mono rounded-full border border-white/15 px-2 py-0.5">Auto-caption</span>
                        <span class="l-mono rounded-full border border-white/15 px-2 py-0.5">B-roll</span>
                    </div>
                </div>
            </article>

            {{-- 3. AD COPY --}}
            <article class="l-card relative overflow-hidden md:col-span-5 min-h-[320px] p-7">
                <div aria-hidden="true" class="l-dots absolute -right-4 -bottom-4 h-40 w-40 opacity-60"></div>
                <div class="relative">
                    <div class="flex items-center justify-between">
                        <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-black/45">03 / copy</span>
                        <span class="l-chip l-chip--indigo !px-2 !py-0 !text-[9px]">A/B</span>
                    </div>
                    <h3 class="mt-3 text-xl font-bold tracking-tight text-black">
                        {{ __('Headlines, in your voice.') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-relaxed text-black/60">
                        {{ __('Three variants per platform, tuned to your audience and tone.') }}
                    </p>

                    {{-- Headline variants --}}
                    <div class="mt-4 space-y-1.5">
                        @foreach ([
                            ['text' => __('Ship faster. Sleep better.'),              'platform' => 'LinkedIn', 'score' => '9.2'],
                            ['text' => __('Modern ops, without the overhead.'),       'platform' => 'Meta',     'score' => '8.7'],
                            ['text' => __('Your brief. Twelve ads. Thirty seconds.'), 'platform' => 'Google',   'score' => '9.0'],
                            ['text' => __('Ditch the template. Ship the campaign.'),  'platform' => 'TikTok',   'score' => '8.4'],
                        ] as $variant)
                            <div class="flex items-center justify-between gap-2 rounded-lg border border-[var(--l-hairline)] bg-[var(--l-bg-2)] px-3 py-2">
                                <span class="truncate text-[12px] text-black">{{ $variant['text'] }}</span>
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <span class="l-mono rounded bg-black/5 px-1 py-0.5 text-[9px] text-black/55">{{ $variant['platform'] }}</span>
                                    <span class="l-mono text-[9px] font-semibold text-emerald-600">{{ $variant['score'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tone + length chips --}}
                    <div class="mt-4 flex flex-wrap items-center gap-1.5">
                        @foreach (['Professional', 'Playful', 'Direct', '< 150 chars', 'Hook-first'] as $tag)
                            <span class="inline-flex items-center rounded-full border border-[var(--l-hairline)] bg-white px-2 py-0.5 text-[9px] font-medium text-black/65">
                                {{ __($tag) }}
                            </span>
                        @endforeach
                    </div>

                    {{-- Performance row --}}
                    <div class="mt-4 flex items-center gap-4 border-t border-[var(--l-hairline)] pt-3 text-[10px] text-black/55">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3 w-3 text-[#4F46E5]" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="6" cy="6" r="4.5"/><path d="M6 3v3l2 1.5"/></svg>
                            <span class="l-mono">0.82s {{ __('avg') }}</span>
                        </span>
                        <span class="l-mono">4 {{ __('variants') }}</span>
                        <span class="l-mono">3 {{ __('platforms') }}</span>
                    </div>
                </div>
            </article>

            {{-- 4. BRAND KITS --}}
            <article class="l-card relative overflow-hidden md:col-span-4 min-h-[280px] p-7">
                <div class="relative flex h-full flex-col justify-between">
                    <div>
                        <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-black/45">04 / kits</span>
                        <h3 class="mt-3 text-xl font-bold tracking-tight text-black">
                            {{ __('Your brand, baked in.') }}
                        </h3>
                        <p class="mt-2 text-[13px] leading-relaxed text-black/60">
                            {{ __('Upload once. Every render stays on brand — no manual cleanup.') }}
                        </p>
                    </div>

                    {{-- Mini brand kit panel mock --}}
                    <div class="mt-5 rounded-xl border border-[var(--l-hairline)] bg-[var(--l-bg-2)] p-4">
                        {{-- Header --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-black">
                                    <span class="text-[10px] font-bold text-white">Ab</span>
                                </div>
                                <div>
                                    <div class="text-[12px] font-semibold text-black">{{ __('Acme Corp') }}</div>
                                    <div class="l-mono text-[9px] text-black/45">{{ __('Brand Kit · v2') }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-semibold text-emerald-700">
                                <svg class="h-2.5 w-2.5" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 5h4M5 3v4"/></svg>
                                {{ __('Locked') }}
                            </span>
                        </div>

                        {{-- Logo placeholder --}}
                        <div class="mt-3 flex h-12 items-center justify-center rounded-lg border border-dashed border-[var(--l-border)] bg-white">
                            <span class="text-[10px] font-medium text-black/40">{{ __('Your logo here') }}</span>
                        </div>

                        {{-- Palette --}}
                        <div class="mt-3">
                            <div class="l-mono text-[9px] uppercase tracking-[0.15em] text-black/45">{{ __('Palette') }}</div>
                            <div class="mt-1.5 flex items-center gap-1.5">
                                <span class="h-6 w-6 rounded-md border border-white bg-[#4F46E5] shadow-sm" title="#4F46E5"></span>
                                <span class="h-6 w-6 rounded-md border border-white bg-black shadow-sm" title="#000000"></span>
                                <span class="h-6 w-6 rounded-md border border-white bg-white shadow-sm ring-1 ring-black/10" title="#FFFFFF"></span>
                                <span class="h-6 w-6 rounded-md border border-white bg-[#F59E0B] shadow-sm" title="#F59E0B"></span>
                                <span class="h-6 w-6 rounded-md border border-white bg-[#10B981] shadow-sm" title="#10B981"></span>
                                <span class="ml-auto l-mono text-[9px] text-black/40">5 {{ __('colors') }}</span>
                            </div>
                        </div>

                        {{-- Typography --}}
                        <div class="mt-3">
                            <div class="l-mono text-[9px] uppercase tracking-[0.15em] text-black/45">{{ __('Typography') }}</div>
                            <div class="mt-1.5 flex items-center justify-between">
                                <div class="flex items-baseline gap-3">
                                    <span class="text-[18px] font-bold tracking-tight text-black">Aa</span>
                                    <span class="text-[14px] font-medium text-black/70">Bb</span>
                                    <span class="text-[11px] text-black/50">Cc</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] font-semibold text-black">Inter</div>
                                    <div class="l-mono text-[9px] text-black/45">400 · 600 · 700</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- 5. CANVAS PRESETS --}}
            <article class="l-card relative overflow-hidden md:col-span-4 min-h-[280px] p-7">
                <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-black/45">05 / presets</span>
                <h3 class="mt-3 text-xl font-bold tracking-tight text-black">
                    {{ __('20+ canvases, one pass.') }}
                </h3>
                <p class="mt-2 text-[13px] leading-relaxed text-black/60">
                    {{ __('Render once, export every platform-ready size.') }}
                </p>
                <div class="mt-5 grid grid-cols-5 gap-1.5">
                    @foreach ([
                        ['platform' => 'Instagram', 'ratio' => '1/1',  'dim' => '1080×1080'],
                        ['platform' => 'TikTok',    'ratio' => '9/16', 'dim' => '1080×1920'],
                        ['platform' => 'Facebook',  'ratio' => '1/1',  'dim' => '1200×1200'],
                        ['platform' => 'YouTube',   'ratio' => '16/9', 'dim' => '1280×720'],
                        ['platform' => 'Pinterest', 'ratio' => '1/1',  'dim' => '1000×1000'],
                        ['platform' => 'IG Story',  'ratio' => '4/5',  'dim' => '1080×1350'],
                        ['platform' => 'LinkedIn',  'ratio' => '1/1',  'dim' => '1200×1200'],
                        ['platform' => 'Reels',     'ratio' => '9/16', 'dim' => '1080×1920'],
                        ['platform' => 'X / Twitter','ratio' => '1/1', 'dim' => '1080×1080'],
                        ['platform' => 'Shorts',    'ratio' => '16/9', 'dim' => '1280×720'],
                    ] as $preset)
                        @php [$w,$h] = explode('/', $preset['ratio']); @endphp
                        <div class="group relative flex flex-col items-center justify-center gap-0.5 overflow-hidden rounded-sm border border-[var(--l-hairline)] p-1 text-center transition-colors hover:border-[#4F46E5]"
                             style="aspect-ratio: {{ $w }}/{{ $h }}; background: var(--l-bg-2);">
                            <span class="l-mono text-[7px] font-bold leading-tight text-black/70 group-hover:text-[#4F46E5]">{{ $preset['platform'] }}</span>
                            <span class="l-mono text-[6px] leading-tight text-black/40 group-hover:text-[#4F46E5]">{{ $preset['dim'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="l-display text-3xl font-extrabold tracking-tight text-black">24</span>
                    <span class="text-[12px] text-black/55">{{ __('presets, included') }}</span>
                </div>
            </article>

            {{-- 6. ASSET GALLERY --}}
            <article class="l-card l-card--ink relative overflow-hidden md:col-span-4 min-h-[280px] flex flex-col">
                <div class="p-7 pb-0">
                    <div class="flex items-center justify-between">
                        <span class="l-mono text-[11px] uppercase tracking-[0.2em] text-white/50">06 / gallery</span>
                        <span class="l-mono text-[11px] text-white/50">2,418</span>
                    </div>
                    <h3 class="mt-3 text-xl font-bold tracking-tight text-white">
                        {{ __('Every generation, searchable.') }}
                    </h3>
                    <p class="mt-2 text-[13px] leading-relaxed text-white/60">
                        {{ __('Tags, favorites, campaign folders. Remix winners fast.') }}
                    </p>
                </div>

                {{-- Two-row scrolling image grid that fills the rest of the card --}}
                <div class="mt-4 flex-1 overflow-hidden pb-7">
                    {{-- Row 1 --}}
                    <div class="l-marquee-mask overflow-hidden">
                        <div class="l-marquee !gap-2" style="animation-duration: 28s;">
                            @foreach ([
                                ['w' => 'w-28', 'img' => '1.avif'],
                                ['w' => 'w-20', 'img' => '2.avif'],
                                ['w' => 'w-24', 'img' => '3.avif'],
                                ['w' => 'w-20', 'img' => '4.avif'],
                                ['w' => 'w-32', 'img' => '5.avif'],
                                ['w' => 'w-20', 'img' => '6.avif'],
                                ['w' => 'w-28', 'img' => '7.avif'],
                                ['w' => 'w-24', 'img' => '8.avif'],
                                ['w' => 'w-28', 'img' => '9.avif'],
                                ['w' => 'w-20', 'img' => '10.avif'],
                                ['w' => 'w-24', 'img' => '11.avif'],
                                ['w' => 'w-20', 'img' => '12.avif'],
                                ['w' => 'w-32', 'img' => '13.avif'],
                                ['w' => 'w-20', 'img' => '14.avif'],
                                ['w' => 'w-28', 'img' => '15.avif'],
                                ['w' => 'w-24', 'img' => '16.avif'],
                            ] as $tile)
                                <div class="{{ $tile['w'] }} h-32 shrink-0 rounded-lg bg-cover bg-center ring-1 ring-white/10"
                                     style="background-image: url('{{ asset('assets/frontend/'.$tile['img']) }}');"></div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Row 2 (reverse direction) --}}
                    <div class="l-marquee-mask mt-2 overflow-hidden">
                        <div class="l-marquee l-marquee--reverse !gap-2" style="animation-duration: 34s;">
                            @foreach ([
                                ['w' => 'w-24', 'img' => '17.avif'],
                                ['w' => 'w-32', 'img' => '18.avif'],
                                ['w' => 'w-20', 'img' => '19.avif'],
                                ['w' => 'w-28', 'img' => '20.avif'],
                                ['w' => 'w-20', 'img' => '21.avif'],
                                ['w' => 'w-24', 'img' => '22.avif'],
                                ['w' => 'w-28', 'img' => '23.avif'],
                                ['w' => 'w-20', 'img' => '24.avif'],
                                ['w' => 'w-24', 'img' => '26.avif'],
                                ['w' => 'w-32', 'img' => '28.avif'],
                                ['w' => 'w-20', 'img' => '29.avif'],
                                ['w' => 'w-28', 'img' => '30.avif'],
                                ['w' => 'w-20', 'img' => '31.avif'],
                                ['w' => 'w-24', 'img' => '33.avif'],
                                ['w' => 'w-28', 'img' => '25.jpg'],
                                ['w' => 'w-20', 'img' => '27.jpg'],
                            ] as $tile)
                                <div class="{{ $tile['w'] }} h-32 shrink-0 rounded-lg bg-cover bg-center ring-1 ring-white/10"
                                     style="background-image: url('{{ asset('assets/frontend/'.$tile['img']) }}');"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>
