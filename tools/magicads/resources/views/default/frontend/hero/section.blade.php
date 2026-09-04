{{-- Hero — polished dark with subtle aurora, film grain, sparklines, entrance stagger. --}}
@php
    $registrationEnabled = class_exists(\Laravel\Fortify\Features::class)
        && \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration());

    $primaryCtaHref = auth()->check()
        ? route('user.dashboard')
        : ($registrationEnabled ? route('register') : route('login'));

    $primaryCtaLabel = auth()->check() ? __('Open dashboard') : __('Start free');

    // Reusable sparkline path generator — returns an SVG polyline
    $sparkPoints = function (array $values, int $width = 120, int $height = 28) {
        if (empty($values)) return '';
        $max = max($values); $min = min($values);
        $range = max(1, $max - $min);
        $step = $width / max(1, count($values) - 1);
        $points = [];
        foreach ($values as $i => $v) {
            $x = $i * $step;
            $y = $height - (($v - $min) / $range) * $height;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
        return implode(' ', $points);
    };

    $spark1 = $sparkPoints([3, 5, 4, 7, 6, 9, 8, 11, 13]);
    $spark2 = $sparkPoints([8, 6, 7, 5, 6, 8, 10, 9, 12]);
    $spark3 = $sparkPoints([2, 4, 3, 6, 8, 7, 10, 12, 14]);
@endphp

<section class="relative isolate flex min-h-svh flex-col overflow-hidden bg-black text-white">
    {{-- Deep black base with barely-there lift in the upper-middle --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10"
         style="background: radial-gradient(ellipse 80% 50% at 50% 25%, rgba(22, 22, 26, 0.6), transparent 75%);"></div>

    {{-- Indigo aurora — bottom-left, brand primary, the dominant glow --}}
    <div aria-hidden="true" class="l-aurora-soft -z-10"
         style="left: -12%; bottom: -22%; width: 640px; height: 640px;
                background: radial-gradient(circle, rgba(79, 70, 229, 0.28), transparent 70%);"></div>

    {{-- Indigo halo behind the headline — lifts the upper-left without a grid --}}
    <div aria-hidden="true" class="l-aurora-soft -z-10"
         style="left: 6%; top: 14%; width: 540px; height: 540px; animation-delay: -3s;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.12), transparent 70%);"></div>

    {{-- Amber whisper — top-right, brand-warm, breaks the all-cool tone --}}
    <div aria-hidden="true" class="l-aurora-soft -z-10"
         style="right: -10%; top: -10%; width: 420px; height: 420px; animation-delay: -6s;
                background: radial-gradient(circle, rgba(245, 158, 11, 0.14), transparent 70%);"></div>

    {{-- Film grain --}}
    <div aria-hidden="true" class="l-grain"></div>

    {{-- Abstract flow lines — sweeping bezier curves, soft rings, data-point sparks --}}
    <svg aria-hidden="true"
         class="pointer-events-none absolute inset-0 -z-10 h-full w-full"
         viewBox="0 0 1440 900" preserveAspectRatio="none" fill="none">
        <defs>
            {{-- Brand sweep: indigo → soft indigo → amber whisper (no banned purple→pink) --}}
            <linearGradient id="hero-flow-primary" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%"   stop-color="#4F46E5" stop-opacity="0"/>
                <stop offset="30%"  stop-color="#4F46E5" stop-opacity="0.45"/>
                <stop offset="65%"  stop-color="#818CF8" stop-opacity="0.32"/>
                <stop offset="100%" stop-color="#F59E0B" stop-opacity="0"/>
            </linearGradient>
            {{-- Warm counter-arc: amber → white whisper, very low opacity --}}
            <linearGradient id="hero-flow-warm" x1="0%" y1="100%" x2="100%" y2="0%">
                <stop offset="0%"   stop-color="#F59E0B" stop-opacity="0"/>
                <stop offset="45%"  stop-color="#F59E0B" stop-opacity="0.20"/>
                <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
            </linearGradient>
            {{-- Quiet diagonal whisper --}}
            <linearGradient id="hero-flow-whisper" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%"   stop-color="#FFFFFF" stop-opacity="0"/>
                <stop offset="50%"  stop-color="#FFFFFF" stop-opacity="0.12"/>
                <stop offset="100%" stop-color="#818CF8" stop-opacity="0"/>
            </linearGradient>
        </defs>

        {{-- Counter-arc — rises from bottom-left into upper-right, warm tint --}}
        <path d="M -80 760 C 320 720, 600 540, 920 600 S 1380 720, 1620 540"
              stroke="url(#hero-flow-warm)" stroke-width="1.5" stroke-linecap="round"/>

        {{-- Animated dashed whisper — diagonal, slow drift, gives life --}}
        <path class="l-flow-dash"
              d="M 160 880 C 480 600, 760 480, 1280 80"
              stroke="url(#hero-flow-whisper)" stroke-width="1" stroke-linecap="round"/>

        {{-- Data-point sparks along the flow lines --}}
        <circle cx="920"  cy="600" r="5"   fill="#818CF8" opacity="0.28"/>
        <circle cx="920"  cy="600" r="2"   fill="#818CF8"/>
        <circle cx="600"  cy="540" r="1.8" fill="#FFFFFF" opacity="0.55"/>
    </svg>

    {{-- Architectural accent lines — three deliberate horizontal strokes, kept for structure --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        {{-- Top hairline fading from left --}}
        <div class="absolute top-[18%] left-0 right-0 h-px"
             style="background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.12) 30%, rgba(79,70,229,0.22) 55%, transparent 90%);"></div>
        {{-- Bottom hairline fading from right --}}
        <div class="absolute top-[68%] left-0 right-0 h-px"
             style="background: linear-gradient(90deg, transparent 10%, rgba(245,158,11,0.14) 35%, rgba(255,255,255,0.10) 60%, transparent 100%);"></div>
        {{-- Single vertical accent — sits between columns, fades at ends --}}
        <div class="absolute top-0 bottom-0 left-[50%] w-px hidden lg:block"
             style="background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,0.06) 25%, rgba(255,255,255,0.06) 75%, transparent 100%);"></div>
    </div>

    <div class="relative mx-auto grid w-full flex-1 max-w-7xl grid-cols-1 items-center gap-12 px-4 pb-24 pt-28 sm:px-6 sm:pt-32 lg:grid-cols-[1fr_1.1fr] lg:gap-16 lg:px-8 lg:pb-28 lg:pt-36">

        {{-- LEFT: Copy column --}}
        <div class="relative">
            {{-- Eyebrow chip --}}
            <div class="l-fade-up l-fade-up-1 mb-7 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] py-1 pl-1.5 pr-3 text-[11px] text-white/80 backdrop-blur">
                <span class="l-shine inline-flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-[0.08em] text-black">
                    <span class="l-livedot !bg-[#4F46E5]"></span>
                    {{ __('Live') }}
                </span>
                <span class="font-medium">{{ __('Video Studio') }}</span>
                <svg class="h-3 w-3 text-white/40" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path d="m6 4 4 4-4 4"/>
                </svg>
            </div>

            <h1 class="l-fade-up l-fade-up-2 l-display text-[44px] font-extrabold leading-[1.02] tracking-[-0.035em] text-white sm:text-[64px] lg:text-[76px]">
                {{ __('Accelerate sales') }}<br>
                {{ __('with') }}
                <span class="relative inline-block">
                    <span style="color: #4F46E5;">{{ __('AI') }}</span>
                    {{-- Subtle indigo underline swipe --}}
                    <svg aria-hidden="true" class="pointer-events-none absolute -bottom-1 left-0 w-full" viewBox="0 0 80 12" preserveAspectRatio="none">
                        <path d="M2 8 Q 20 2, 40 6 T 78 5" fill="none" stroke="#4F46E5" stroke-width="2.5" stroke-linecap="round" opacity="0.7"/>
                    </svg>
                </span>
                {{ __('ad generator') }}
            </h1>

            <p class="l-fade-up l-fade-up-3 mt-6 max-w-lg text-[15px] leading-relaxed text-white/60 sm:text-base">
                {{ __('Generate on-brand image, video, and ad copy for every channel. One brief, every platform size, ready in under thirty seconds.') }}
            </p>

            <div class="l-fade-up l-fade-up-4 mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ $primaryCtaHref }}"
                   class="group inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-black transition-all hover:scale-[1.03] hover:shadow-[0_20px_40px_-10px_rgba(255,255,255,0.3)]">
                    <span>{{ $primaryCtaLabel }}</span>
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                    </svg>
                </a>
                <a href="#showcase"
                   class="group inline-flex items-center gap-2 rounded-full border border-white/20 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:border-white/40 hover:bg-white/5">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-white/30 transition-transform group-hover:scale-110">
                        <svg class="h-2.5 w-2.5 translate-x-[1px]" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                            <path d="M3 1.5v9l8-4.5z"/>
                        </svg>
                    </span>
                    <span>{{ __('Watch 90s tour') }}</span>
                </a>
            </div>

            {{-- Avatar cluster + trust row --}}
            <div class="l-fade-up l-fade-up-5 mt-12 flex flex-wrap items-center gap-10">
                {{-- Avatar cluster --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2.5">
                        <img src="{{ asset('assets/frontend/avatars/9.webp') }}" alt="" loading="lazy" class="h-9 w-9 rounded-full border-2 border-[#050505] object-cover">
                        <img src="{{ asset('assets/frontend/avatars/14.webp') }}" alt="" loading="lazy" class="h-9 w-9 rounded-full border-2 border-[#050505] object-cover">
                        <img src="{{ asset('assets/frontend/avatars/24.webp') }}" alt="" loading="lazy" class="h-9 w-9 rounded-full border-2 border-[#050505] object-cover">
                        <img src="{{ asset('assets/frontend/avatars/36.webp') }}" alt="" loading="lazy" class="h-9 w-9 rounded-full border-2 border-[#050505] object-cover">
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1" aria-hidden="true">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8l-5.2 2.8 1-5.8L1.5 7.7l5.9-.9z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="mt-0.5 text-[11px] text-white/55">
                            <span class="font-semibold text-white">4.9</span> · {{ __('from 1,247 reviews') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT: Asymmetric ad tile masonry with hover lift + entrance stagger --}}
        <div class="relative l-fade-up l-fade-up-3">
            <div class="grid grid-cols-3 gap-4 sm:gap-5">

                {{-- Column 1 --}}
                <div class="flex flex-col gap-4 sm:gap-5">
                    {{-- Analytics card w/ sparkline --}}
                    <div class="l-tile rounded-xl bg-[#141414] p-3 ring-1 ring-white/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="l-livedot"></span>
                                <span class="l-mono text-[9px] text-white/60">IG campaign</span>
                            </div>
                            <span class="l-mono text-[9px] font-semibold text-emerald-400">+42%</span>
                        </div>
                        <svg viewBox="0 0 120 28" class="mt-2 w-full" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="sp1" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#4F46E5" stop-opacity="0.4"/>
                                    <stop offset="100%" stop-color="#4F46E5" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <polyline points="{{ $spark1 }}" fill="none" stroke="#4F46E5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <polygon points="0,28 {{ $spark1 }} 120,28" fill="url(#sp1)"/>
                        </svg>
                        <div class="mt-1 grid grid-cols-3 gap-1.5">
                            <div><div class="l-mono text-[10px] font-bold text-white">6.61%</div><div class="l-mono text-[8px] text-white/45">CTR</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">2,047</div><div class="l-mono text-[8px] text-white/45">clicks</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">$2.3K</div><div class="l-mono text-[8px] text-white/45">spent</div></div>
                        </div>
                    </div>

                    {{-- First — image background --}}
                    <figure class="l-tile relative overflow-hidden rounded-2xl"
                            style="aspect-ratio: 3/4;
                                   background-image: url('{{ asset('assets/frontend/21.avif') }}');
                                   background-size: cover;
                                   background-position: center;"></figure>

                    <div class="flex items-center justify-center gap-2 text-[13px] text-white/75">
                        <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M6 2 11 9H1z"/></svg>
                        {{ __('Likes in Instagram:') }} <span class="font-bold text-white">101K</span>
                    </div>

                    {{-- Second - image background --}}
                    <figure class="l-tile relative overflow-hidden rounded-2xl"
                            style="aspect-ratio: 3/4;
                                   background-image: url('{{ asset('assets/frontend/2.avif') }}');
                                   background-size: cover;
                                   background-position: center;"></figure>
                </div>

                {{-- Column 2 --}}
                <div class="flex flex-col gap-4 sm:gap-5 pt-10">
                    {{-- Third — image background --}}
                    <figure class="l-tile relative overflow-hidden rounded-2xl"
                            style="aspect-ratio: 3/4;
                                   background-image: url('{{ asset('assets/frontend/5.avif') }}');
                                   background-size: cover;
                                   background-position: center;">
                        {{-- Hovering 25% OFF discount badge --}}
                        <div class="l-float absolute left-4 top-1/2 -translate-y-1/2">
                            <div class="l-shine flex h-16 w-16 -rotate-12 items-center justify-center rounded-full text-center text-white shadow-lg"
                                 style="background: linear-gradient(135deg, #FBBF24, #F59E0B);">
                                <div>
                                    <div class="text-[8px] font-bold leading-none">UP TO</div>
                                    <div class="l-display text-xl font-extrabold leading-none">25%</div>
                                    <div class="text-[8px] font-bold leading-none">OFF</div>
                                </div>
                            </div>
                        </div>
                    </figure>

                    {{-- Fourth — image background --}}
                    <figure class="l-tile relative overflow-hidden rounded-2xl"
                            style="aspect-ratio: 3/4;
                                   background-image: url('{{ asset('assets/frontend/30.avif') }}');
                                   background-size: cover;
                                   background-position: center;"></figure>

                    <div class="flex items-center justify-center gap-2 text-[13px] text-white/75">
                        <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M6 2 11 9H1z"/></svg>
                        {{ __('Sales Growth:') }} <span class="font-bold text-emerald-400">+97% ↑</span>
                    </div>

                    {{-- Facebook analytics card w/ sparkline --}}
                    <div class="l-tile rounded-xl bg-[#141414] p-3 ring-1 ring-white/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="l-livedot"></span>
                                <span class="l-mono text-[9px] text-white/60">FB campaign</span>
                            </div>
                            <span class="l-mono text-[9px] font-semibold text-emerald-400">+28%</span>
                        </div>
                        <svg viewBox="0 0 120 28" class="mt-2 w-full" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="sp2" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#10B981" stop-opacity="0.4"/>
                                    <stop offset="100%" stop-color="#10B981" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <polyline points="{{ $spark2 }}" fill="none" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <polygon points="0,28 {{ $spark2 }} 120,28" fill="url(#sp2)"/>
                        </svg>
                        <div class="mt-1 grid grid-cols-3 gap-1.5">
                            <div><div class="l-mono text-[10px] font-bold text-white">6.89%</div><div class="l-mono text-[8px] text-white/45">CTR</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">832</div><div class="l-mono text-[8px] text-white/45">clicks</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">$647</div><div class="l-mono text-[8px] text-white/45">spent</div></div>
                        </div>
                    </div>
                </div>

                {{-- Column 3 --}}
                <div class="flex flex-col gap-4 sm:gap-5">
                    {{-- YouTube analytics --}}
                    <div class="l-tile rounded-xl bg-[#141414] p-3 ring-1 ring-white/5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="l-livedot"></span>
                                <span class="l-mono text-[9px] text-white/60">YT campaign</span>
                            </div>
                            <span class="l-mono text-[9px] font-semibold text-emerald-400">+56%</span>
                        </div>
                        <svg viewBox="0 0 120 28" class="mt-2 w-full" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="sp3" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#F59E0B" stop-opacity="0.35"/>
                                    <stop offset="100%" stop-color="#F59E0B" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <polyline points="{{ $spark3 }}" fill="none" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <polygon points="0,28 {{ $spark3 }} 120,28" fill="url(#sp3)"/>
                        </svg>
                        <div class="mt-1 grid grid-cols-3 gap-1.5">
                            <div><div class="l-mono text-[10px] font-bold text-white">8.91%</div><div class="l-mono text-[8px] text-white/45">CTR</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">3,053</div><div class="l-mono text-[8px] text-white/45">views</div></div>
                            <div><div class="l-mono text-[10px] font-bold text-white">$2.1K</div><div class="l-mono text-[8px] text-white/45">spent</div></div>
                        </div>
                    </div>

                    {{-- Fifth — video with play / pause buttons --}}
                    <figure class="l-tile group relative overflow-hidden rounded-2xl bg-black" style="aspect-ratio: 3/4;">
                        <video class="absolute inset-0 h-full w-full object-cover"
                               playsinline
                               preload="metadata"
                               onplay="this.closest('figure').querySelector('[data-play-overlay]').style.display='none'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='flex'"
                               onpause="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'"
                               onended="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'">
                            <source src="{{ asset('assets/frontend/video/2.mp4') }}" type="video/mp4">
                        </video>
                        {{-- Play overlay (shown while paused) --}}
                        <button type="button" data-play-overlay
                                onclick="this.closest('figure').querySelector('video').play()"
                                class="absolute inset-0 z-10 flex items-center justify-center bg-black/20 transition-colors hover:bg-black/10"
                                aria-label="{{ __('Play video') }}">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur-sm transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5 translate-x-[1px] text-black" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                    <path d="M3 1.5v9l8-4.5z"/>
                                </svg>
                            </span>
                        </button>
                        {{-- Pause overlay (only while playing, reveals on hover) --}}
                        <button type="button" data-pause-overlay style="display:none"
                                onclick="this.closest('figure').querySelector('video').pause()"
                                class="absolute inset-0 z-10 items-center justify-center bg-black/0 opacity-0 transition-all hover:bg-black/20 group-hover:opacity-100"
                                aria-label="{{ __('Pause video') }}">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur-sm transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5 text-black" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                    <rect x="3" y="2" width="2.5" height="8" rx="0.5"/>
                                    <rect x="6.5" y="2" width="2.5" height="8" rx="0.5"/>
                                </svg>
                            </span>
                        </button>
                    </figure>

                    {{-- Struggling with your… tile — video with play / pause buttons --}}
                    <figure class="l-tile group relative overflow-hidden rounded-2xl bg-black" style="aspect-ratio: 3/4;">
                        <video class="absolute inset-0 h-full w-full object-cover"
                               playsinline
                               preload="metadata"
                               onplay="this.closest('figure').querySelector('[data-play-overlay]').style.display='none'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='flex'"
                               onpause="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'"
                               onended="this.closest('figure').querySelector('[data-play-overlay]').style.display='flex'; this.closest('figure').querySelector('[data-pause-overlay]').style.display='none'">
                            <source src="{{ asset('assets/frontend/video/21.mp4') }}" type="video/mp4">
                        </video>
                        {{-- Play overlay (shown while paused) --}}
                        <button type="button" data-play-overlay
                                onclick="this.closest('figure').querySelector('video').play()"
                                class="absolute inset-0 z-10 flex items-center justify-center bg-black/20 transition-colors hover:bg-black/10"
                                aria-label="{{ __('Play video') }}">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur-sm transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5 translate-x-[1px] text-black" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                    <path d="M3 1.5v9l8-4.5z"/>
                                </svg>
                            </span>
                        </button>
                        {{-- Pause overlay (only while playing, reveals on hover) --}}
                        <button type="button" data-pause-overlay style="display:none"
                                onclick="this.closest('figure').querySelector('video').pause()"
                                class="absolute inset-0 z-10 items-center justify-center bg-black/0 opacity-0 transition-all hover:bg-black/20 group-hover:opacity-100"
                                aria-label="{{ __('Pause video') }}">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow-lg backdrop-blur-sm transition-transform group-hover:scale-110">
                                <svg class="h-5 w-5 text-black" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                    <rect x="3" y="2" width="2.5" height="8" rx="0.5"/>
                                    <rect x="6.5" y="2" width="2.5" height="8" rx="0.5"/>
                                </svg>
                            </span>
                        </button>
                    </figure>

                    <div class="flex items-center justify-center gap-2 text-[13px] text-white/75">
                        <svg class="h-3 w-3 text-emerald-400" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M6 2 11 9H1z"/></svg>
                        {{ __('Sales Growth:') }} <span class="font-bold text-emerald-400">+83% ↑</span>
                    </div>
                </div>
            </div>

            {{-- Floating "Generating now" pill bottom-left of tile grid --}}
            <div class="absolute -left-2 -bottom-2 hidden items-center gap-2 rounded-full border border-white/10 bg-[#0D0D0D]/90 px-3 py-2 backdrop-blur md:inline-flex">
                <span class="l-livedot !bg-[#4F46E5]"></span>
                <span class="l-mono text-[10px] font-semibold text-white">{{ __('Generating') }}</span>
                <span class="l-mono text-[10px] text-white/55">· 12 / 12</span>
            </div>
        </div>
    </div>

    {{-- Soft fade to black at the very bottom of the hero so the grid + tiles dissolve into shadow --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-40 -z-10"
         style="background: linear-gradient(180deg, transparent, #000000 60%);"></div>

    {{-- SVG curve bottom — shallow concave dip, no apex poking into content --}}
    <div aria-hidden="true" class="absolute inset-x-0 bottom-0 leading-[0]">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="block h-10 w-full sm:h-12 lg:h-14">
            <path d="M0,0 Q 720,90 1440,0 L1440,60 L0,60 Z" fill="#FFFFFF"/>
        </svg>
    </div>
</section>

{{-- Logo / trust band on white --}}
<section class="relative bg-white py-14">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <p class="text-center text-[15px] font-semibold text-black sm:text-base">
            {{ __('Trusted by 1,200+ teams to ship their ad pipeline') }}
        </p>
        <p class="mt-1 text-center text-[12px] text-black/50">
            {{ __('From fast-growing DTC brands to enterprise marketing orgs.') }}
        </p>
        <div class="l-marquee-mask mt-8 overflow-hidden">
            <div class="l-marquee">
                @foreach (array_merge(
                    ['stripe', 'afterpay', 'hopin', 'splunk', 'attentive', 'vercel', 'notion', 'linear'],
                    ['stripe', 'afterpay', 'hopin', 'splunk', 'attentive', 'vercel', 'notion', 'linear']
                ) as $logo)
                    <span class="whitespace-nowrap text-[22px] font-semibold tracking-tight text-black/30 transition-colors hover:text-black">
                        {{ $logo }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</section>
