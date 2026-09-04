@php
    $user = auth()->user();
    $firstName = \Illuminate\Support\Str::before($user->name, ' ') ?: $user->name;
    $defaultBrand = $this->defaultBrand;
    $onboarding = $this->onboarding;
    $presetLibrary = $this->presetLibrary;

    // Brand palette (locked, see .kiro/steering/brand-palette.md)
    $brandPrimary    = '#4F46E5'; // db-brand-1  (indigo-600)
    $brandSecondary  = '#0F172A'; // db-brand-2  (slate-900)
    $brandAccent     = '#F59E0B'; // db-brand-3  (amber-500, UI only — never text)
    $brandAccentText = '#D97706'; // db-brand-3-text (amber-600, text-safe twin)

    // Sparkline path
    $series = collect($this->burnSeries);
    $maxVal = max(1, $series->max('credits'));
    $pathW = 280;
    $pathH = 60;
    $points = $series->map(function ($p, $i) use ($series, $maxVal, $pathW, $pathH) {
        $x = $series->count() > 1 ? round(($i / ($series->count() - 1)) * $pathW, 2) : 0;
        $y = round($pathH - (($p['credits'] / $maxVal) * ($pathH - 6)) - 3, 2);
        return [$x, $y, $p];
    });
@endphp

<div
    x-data="userDashboard()"
    @keydown.window.escape="paletteOpen = false"
    @keydown.window.ctrl.k.prevent="paletteOpen = !paletteOpen"
    @keydown.window.meta.k.prevent="paletteOpen = !paletteOpen"
    class="relative"
>
    {{-- ========================================================= --}}
    {{-- Design tokens & motion                                     --}}
    {{-- ========================================================= --}}
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --db-brand-1: {{ $brandPrimary }};
            --db-brand-2: {{ $brandSecondary }};
            --db-brand-3: {{ $brandAccent }};
            --db-brand-3-text: {{ $brandAccentText }};
        }

        @keyframes dbBlob1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(40px,-25px) scale(1.08)} }
        @keyframes dbBlob2 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-30px,20px) scale(0.94)} }
        @keyframes dbFadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        @keyframes dbShimmer { 0%{transform:translateX(-120%) skewX(-20deg)} 100%{transform:translateX(220%) skewX(-20deg)} }
        @keyframes dbPulse   { 0%,100%{opacity:0.55} 50%{opacity:1} }
        @keyframes dbSparkleDraw { from{stroke-dashoffset:600} to{stroke-dashoffset:0} }
        @keyframes dbProgress { 0%{background-position:0 0} 100%{background-position:40px 0} }

        .db-blob-1 { animation: dbBlob1 16s ease-in-out infinite; }
        .db-blob-2 { animation: dbBlob2 20s ease-in-out infinite; }
        .db-fade   { animation: dbFadeUp .55s cubic-bezier(.16,1,.3,1) both; }
        .db-d-1 { animation-delay: 60ms; }
        .db-d-2 { animation-delay: 140ms; }
        .db-d-3 { animation-delay: 220ms; }
        .db-d-4 { animation-delay: 320ms; }
        .db-d-5 { animation-delay: 420ms; }
        .db-shimmer::after {
            content:""; position:absolute; inset:0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.28), transparent);
            transform: translateX(-120%) skewX(-20deg);
            animation: dbShimmer 4s ease-in-out infinite;
        }
        .db-pulse { animation: dbPulse 1.8s ease-in-out infinite; }
        .db-spark-line { stroke-dasharray: 600; animation: dbSparkleDraw 1.4s cubic-bezier(.16,1,.3,1) both; }
        .db-progress-stripes {
            background-image: linear-gradient(45deg, rgba(255,255,255,.18) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.18) 50%, rgba(255,255,255,.18) 75%, transparent 75%);
            background-size: 40px 40px;
            animation: dbProgress 1.2s linear infinite;
        }

        /* Elevation system — only 3 levels (near-black dark theme) */
        .db-card       { @apply bg-white dark:bg-(--default-element-bg-color) border border-zinc-200/80 dark:border-white/6 rounded-2xl; }
        .db-card-soft  { @apply bg-white/60 dark:bg-white/[.025] border border-white/80 dark:border-white/6 rounded-2xl backdrop-blur-xl; }
        .db-lift       { @apply transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-500/5 hover:border-indigo-300/60 dark:hover:border-indigo-500/25; }

        .db-section-title { @apply text-[11px] font-bold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400; }

        @media (prefers-reduced-motion: reduce) {
            .db-blob-1, .db-blob-2, .db-fade, .db-shimmer::after, .db-pulse, .db-spark-line, .db-progress-stripes { animation: none !important; }
        }
    </style>

    {{-- ========================================================= --}}
    {{-- COMMAND PALETTE (⌘K)                                       --}}
    {{-- ========================================================= --}}
    <template x-teleport="body">
        <div
            x-show="paletteOpen"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-[70] flex items-start justify-center pt-[12vh] px-4 bg-zinc-950/50 backdrop-blur-sm"
            @click.self="paletteOpen = false"
        >
            <div
                x-show="paletteOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="w-full max-w-xl bg-white dark:bg-(--default-element-bg-color) border border-zinc-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden"
            >
                <div class="flex items-center gap-3 px-4 py-3.5 border-b border-zinc-200 dark:border-white/8">
                    <flux:icon.magnifying-glass class="size-4 text-zinc-400" />
                    <input
                        x-ref="paletteInput"
                        x-model="paletteQuery"
                        @keydown.arrow-down.prevent="paletteMove(1)"
                        @keydown.arrow-up.prevent="paletteMove(-1)"
                        @keydown.enter.prevent="paletteCommit()"
                        type="text"
                        placeholder="{{ __('Search presets, brands, actions…') }}"
                        class="flex-1 bg-transparent border-0 focus:ring-0 text-sm text-zinc-800 dark:text-zinc-100 placeholder:text-zinc-400 outline-none"
                    />
                    <kbd class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold text-zinc-500 bg-zinc-100 dark:bg-white/5 dark:text-zinc-400">ESC</kbd>
                </div>
                <div class="max-h-[55vh] overflow-y-auto py-2" x-ref="paletteList">
                    <template x-if="paletteFiltered().length === 0">
                        <div class="px-5 py-10 text-center text-xs text-zinc-400">{{ __('No results. Try a preset like "square", "story" or "leaderboard".') }}</div>
                    </template>
                    <template x-for="(item, i) in paletteFiltered()" :key="item.id">
                        <a
                            :href="item.href"
                            wire:navigate
                            @mouseenter="paletteIndex = i"
                            :class="paletteIndex === i ? 'bg-indigo-50 dark:bg-indigo-500/10' : ''"
                            class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition"
                        >
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="item.iconBg">
                                <span x-html="item.iconSvg"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[13px] font-semibold text-zinc-800 dark:text-zinc-100 truncate" x-text="item.label"></div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate" x-text="item.hint"></div>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md" :class="item.tagClass" x-text="item.tag"></span>
                        </a>
                    </template>
                </div>
                <div class="flex items-center justify-between px-4 py-2 border-t border-zinc-200 dark:border-white/5 bg-zinc-50/70 dark:bg-white/[.02] text-[10px] text-zinc-500 dark:text-zinc-400">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-white/10 border border-zinc-200 dark:border-white/10">↑↓</kbd>{{ __('navigate') }}</span>
                        <span class="inline-flex items-center gap-1"><kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-white/10 border border-zinc-200 dark:border-white/10">⏎</kbd>{{ __('open') }}</span>
                    </div>
                    <span>{{ __('Quick search') }}</span>
                </div>
            </div>
        </div>
    </template>


    {{-- ========================================================= --}}
    {{-- CREATE MENU — teleported, escapes all overflow/stacking     --}}
    {{-- ========================================================= --}}
    <template x-teleport="body">
        <div
            x-show="createOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-[0.98]"
            @click.outside="createOpen = false"
            @keydown.window.escape="createOpen = false"
            :style="createMenuStyle"
            class="fixed z-[100] min-w-[240px] rounded-xl bg-white dark:bg-(--default-element-bg-color) border border-zinc-200 dark:border-white/10 shadow-2xl shadow-indigo-500/10 p-1.5"
        >
            <a href="{{ route('user.studio.images') }}" wire:navigate class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/15 flex items-center justify-center"><flux:icon.image-plus class="size-4 text-indigo-600 dark:text-indigo-400" /></div>
                <div>
                    <div class="text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Image Ad') }}</div>
                    <div class="text-[11px] text-zinc-500">{{ $imageCost }} {{ __('credits') }} / {{ __('image') }}</div>
                </div>
            </a>
            <a href="{{ route('user.studio.videos') }}" wire:navigate class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                <div class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-500/15 flex items-center justify-center"><flux:icon.film class="size-4 text-violet-600 dark:text-violet-400" /></div>
                <div>
                    <div class="text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Video Ad') }}</div>
                    <div class="text-[11px] text-zinc-500">{{ $videoCost }} {{ __('credits') }} / {{ __('sec') }}</div>
                </div>
            </a>
            <a href="{{ route('user.copy.studio') }}" wire:navigate class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/15 flex items-center justify-center"><flux:icon.pencil class="size-4 text-emerald-600 dark:text-emerald-400" /></div>
                <div>
                    <div class="text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ __('Ad Copy') }}</div>
                    <div class="text-[11px] text-zinc-500">{{ $copyCost }} {{ __('credits') }} / 1k {{ __('words') }}</div>
                </div>
            </a>
            <div class="my-1 h-px bg-zinc-100 dark:bg-white/5"></div>
            <a href="{{ route('user.brands.create') }}" wire:navigate class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/15 flex items-center justify-center"><flux:icon.sparkles class="size-4 text-amber-600 dark:text-amber-400" /></div>
                <div>
                    <div class="text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ __('New Brand') }}</div>
                    <div class="text-[11px] text-zinc-500">{{ __('Free · 4 quick steps') }}</div>
                </div>
            </a>
        </div>
    </template>

    {{-- ========================================================= --}}
    {{-- HERO — compact, brand-tinted, single signature gradient    --}}
    {{-- ========================================================= --}}
    <section class="relative overflow-hidden rounded-2xl mb-15 border border-zinc-200/70 dark:border-white/6 bg-gradient-to-br from-white via-white to-zinc-50 dark:from-[#0b0b11] dark:via-[#070709] dark:to-[#000000]">

        {{-- brand-tinted glow (single indigo blob — amber is carried by the headline text, no need to double it in the background) --}}
        <div class="absolute -top-28 -left-24 w-[460px] h-[460px] rounded-full blur-[120px] db-blob-1 opacity-40 dark:opacity-30"
             style="background: radial-gradient(circle at 50% 50%, var(--db-brand-1), transparent 60%);"></div>

        {{-- subtle dot grid --}}
        <div class="absolute inset-0 opacity-[0.025] dark:opacity-[0.04]" style="background-image: radial-gradient(circle, currentColor 0.6px, transparent 0.6px); background-size: 28px 28px; color: var(--db-brand-1);"></div>

        <div class="relative px-6 sm:px-10 lg:px-14 pt-10 pb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                {{-- Left: greeting + meta --}}
                <div class="flex-1 min-w-0 db-fade">

                    <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-black tracking-tight leading-[1.05] mb-3">
                        <span class="text-zinc-900 dark:text-zinc-50">{{ __('Hey') }} {{ $firstName }},</span><br />
                        <span class="bg-clip-text text-transparent" style="background-image: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2), var(--db-brand-3-text));">
                            {{ __('what should we create today?') }}
                        </span>
                    </h1>

                    <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-lg mb-5 leading-relaxed">
                            {{ __('Instantly craft images, videos, and ad copy with AI. Pick a preset, hit generate, ship like a pro') }}
                    </p>

                    {{-- Primary + secondary CTAs --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button
                            x-ref="createTrigger"
                            @click="toggleCreateMenu()"
                            class="relative group inline-flex items-center gap-2.5 pl-4 pr-5 py-3 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-indigo-500/40 active:scale-[0.98] transition-all overflow-hidden db-shimmer"
                            style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));"
                        >
                            <flux:icon.sparkles class="size-4" />
                            {{ __('Create') }}
                            <flux:icon.chevron-down class="size-3.5 opacity-75 transition-transform" x-bind:class="createOpen && 'rotate-180'" />
                        </button>

                        <button
                            @click="paletteOpen = true; $nextTick(() => $refs.paletteInput?.focus())"
                            class="inline-flex items-center gap-2 pl-3 pr-2 py-3 rounded-xl text-[13px] font-medium text-zinc-600 dark:text-zinc-300 bg-white/80 dark:bg-white/5 border border-zinc-200/80 dark:border-white/10 hover:border-indigo-300 dark:hover:border-indigo-500/30 transition"
                        >
                            <flux:icon.magnifying-glass class="size-4 text-zinc-400" />
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Search or jump to…') }}</span>
                            <kbd class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-zinc-100 dark:bg-white/10 text-zinc-500 dark:text-zinc-400">⌘K</kbd>
                        </button>

                    </div>
                </div>

                {{-- Right: Credit Insights card --}}
                <div class="lg:w-[360px] shrink-0 db-fade db-d-2">
                    <div class="relative rounded-2xl p-5 bg-zinc-900 dark:bg-(--default-element-bg-color) text-zinc-100 border border-white/5 overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full blur-3xl opacity-40" style="background: radial-gradient(circle, var(--db-brand-1), transparent 60%);"></div>

                        <div class="relative flex items-start justify-between mb-1.5">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">{{ __('Credit Balance') }}</div>
                                <div class="flex items-end gap-1.5 mt-1">
                                    <span class="text-3xl font-black tabular-nums">{{ number_format($creditBalance) }}</span>
                                </div>
                            </div>
                            @if($burnTrend != 0.0)
                                @php
                                    $trendClass = $burnTrend > 0 ? 'text-rose-300' : 'text-emerald-300';
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-white/5 border border-white/10 {{ $trendClass }}">
                                    @if($burnTrend > 0)
                                        <flux:icon.arrow-trending-up class="size-3" /> +{{ $burnTrend }}%
                                    @else
                                        <flux:icon.arrow-trending-down class="size-3" /> {{ $burnTrend }}%
                                    @endif
                                </span>
                            @endif
                        </div>

                        {{-- Sparkline --}}
                        <div class="relative mt-3 -mx-1">
                            <svg viewBox="0 0 {{ $pathW }} {{ $pathH }}" class="w-full h-[60px]" preserveAspectRatio="none">
                                <defs>
                                    <linearGradient id="sparkFill" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="{{ $brandPrimary }}" stop-opacity="0.45"/>
                                        <stop offset="100%" stop-color="{{ $brandPrimary }}" stop-opacity="0"/>
                                    </linearGradient>
                                    <linearGradient id="sparkStroke" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="{{ $brandPrimary }}"/>
                                        <stop offset="100%" stop-color="{{ $brandAccent }}"/>
                                    </linearGradient>
                                </defs>
                                @php
                                    $linePath = '';
                                    $areaPath = '';
                                    foreach ($points as $i => $pt) {
                                        [$x, $y] = $pt;
                                        $cmd = $i === 0 ? 'M' : 'L';
                                        $linePath .= "{$cmd}{$x} {$y} ";
                                    }
                                    if ($points->count()) {
                                        $last = $points->last();
                                        $first = $points->first();
                                        $areaPath = $linePath . "L{$last[0]} {$pathH} L{$first[0]} {$pathH} Z";
                                    }
                                @endphp
                                <path d="{{ $areaPath }}" fill="url(#sparkFill)"></path>
                                <path d="{{ $linePath }}" fill="none" stroke="url(#sparkStroke)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="db-spark-line"></path>
                                @foreach($points as $i => $pt)
                                    @if($pt[2]['credits'] > 0)
                                        <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="1.8" fill="{{ $brandPrimary }}"></circle>
                                    @endif
                                @endforeach
                            </svg>
                            <div class="flex justify-between text-[9px] text-zinc-500 mt-1 px-0.5">
                                <span>{{ \Illuminate\Support\Carbon::now()->subDays(13)->format('M j') }}</span>
                                <span>{{ __('14-day burn') }}</span>
                                <span>{{ __('Today') }}</span>
                            </div>
                        </div>

                        <div class="relative mt-4 grid grid-cols-3 gap-3 pt-4 border-t border-white/5">
                            <div>
                                <div class="text-[9px] font-semibold uppercase tracking-widest text-zinc-500">{{ __('Last 7d') }}</div>
                                <div class="text-sm font-bold text-zinc-100 tabular-nums mt-0.5">{{ number_format($burnLast7) }}</div>
                            </div>
                            <div>
                                <div class="text-[9px] font-semibold uppercase tracking-widest text-zinc-500">{{ __('Daily avg') }}</div>
                                <div class="text-sm font-bold text-zinc-100 tabular-nums mt-0.5">{{ number_format($dailyAverage) }}</div>
                            </div>
                            <div>
                                <div class="text-[9px] font-semibold uppercase tracking-widest text-zinc-500">{{ __('Runway') }}</div>
                                <div class="text-sm font-bold text-zinc-100 tabular-nums mt-0.5">
                                    @if($projectedDaysLeft !== null)
                                        ~{{ $projectedDaysLeft }}d
                                    @else
                                        ∞
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stat chips --}}
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-2.5 db-fade db-d-3">
                @php
                    $chips = [
                        ['icon' => 'image-plus', 'label' => __('Images'),   'value' => $totalImages,  'tone' => 'indigo'],
                        ['icon' => 'film',       'label' => __('Videos'),   'value' => $totalVideos,  'tone' => 'violet'],
                        ['icon' => 'pencil',     'label' => __('Copies'),   'value' => $totalCopies,  'tone' => 'emerald'],
                        ['icon' => 'folder',     'label' => __('Total'),    'value' => $totalImages + $totalVideos + $totalCopies, 'tone' => 'amber'],
                    ];
                    $toneMap = [
                        'indigo'  => 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10',
                        'violet'  => 'text-violet-600 dark:text-violet-400 bg-violet-50 dark:bg-violet-500/10',
                        'emerald' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10',
                        'amber'   => 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10',
                    ];
                @endphp
                @foreach($chips as $chip)
                    <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl db-card-soft">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $toneMap[$chip['tone']] }}">
                            <flux:icon :name="$chip['icon']" class="size-4" />
                        </div>
                        <div>
                            <div class="text-lg font-black text-zinc-800 dark:text-zinc-100 leading-none tabular-nums">{{ number_format($chip['value']) }}</div>
                            <div class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500 mt-1">{{ $chip['label'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- ONBOARDING CHECKLIST (first-run only)                      --}}
    {{-- ========================================================= --}}
    @if($onboarding['visible'])
        <section class="mb-15 db-fade db-d-4" x-data="{ dismissed: false }" x-show="!dismissed">
            <div class="relative overflow-hidden rounded-2xl border border-zinc-200/70 dark:border-white/5 bg-white dark:bg-(--default-element-bg-color)">
                {{-- subtle indigo kiss from the top-left, fades to transparent — no amber on the body --}}
                <div class="pointer-events-none absolute inset-0 opacity-60"
                     style="background: linear-gradient(135deg, rgba(79,70,229,0.08), transparent 50%);"></div>
                <div class="relative p-5 sm:p-6">
                    <div class="flex items-start justify-between mb-4 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm shadow-indigo-500/25"
                                 style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                                <flux:icon.rocket-launch class="size-5 text-white" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-black tracking-tight">
                                        <span class="bg-clip-text text-transparent"
                                              style="background-image: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2), var(--db-brand-3-text));">
                                            {{ __('Get started in 4 quick steps') }}
                                        </span>
                                    </h2>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ __('Knock these out to unlock your creative flow.') }}</p>
                            </div>
                        </div>
                        <button @click="dismissed = true" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition" aria-label="{{ __('Dismiss') }}">
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    {{-- progress bar (brand gradient) --}}
                    <div class="relative h-1.5 rounded-full bg-zinc-200/70 dark:bg-white/5 overflow-hidden mb-5">
                        <div class="absolute inset-y-0 left-0 rounded-full transition-all duration-500"
                             style="width: {{ $onboarding['percent'] }}%; background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                        @foreach($onboarding['steps'] as $i => $step)
                            <a href="{{ $step['route'] }}" wire:navigate class="group relative flex items-start gap-3 p-3 rounded-xl bg-white/70 dark:bg-white/[.03] border border-white/80 dark:border-white/5 hover:border-indigo-300 dark:hover:border-indigo-500/30 hover:shadow-md transition">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-all {{ $step['done'] ? 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-white/5 text-zinc-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/15 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}">
                                    @if($step['done'])
                                        <flux:icon.check class="size-4" />
                                    @else
                                        <flux:icon :name="$step['icon']" class="size-4" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1 text-[11px] font-semibold text-zinc-400 dark:text-zinc-500 mb-0.5">{{ __('Step') }} {{ $i + 1 }}</div>
                                    <div class="text-[13px] font-semibold leading-tight {{ $step['done'] ? 'text-zinc-500 dark:text-zinc-500 line-through' : 'text-zinc-800 dark:text-zinc-100' }}">{{ $step['label'] }}</div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2">{{ $step['hint'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ========================================================= --}}
    {{-- IN-PROGRESS + RESUME LAST                                  --}}
    {{-- ========================================================= --}}
    @if($this->inProgress->count() > 0)
        <section class="mb-6 db-fade db-d-4" wire:poll.8s>
            <div class="flex items-center justify-between mb-3">
                <h2 class="db-section-title">{{ __('In progress') }}</h2>
                <span class="text-[11px] text-zinc-400">{{ __('Auto-refreshing') }}</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($this->inProgress as $job)
                    <div class="relative overflow-hidden rounded-2xl db-card p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $job->type === 'image' ? 'bg-indigo-500/15 text-indigo-500' : 'bg-violet-500/15 text-violet-500' }}">
                                <flux:icon :name="$job->type === 'image' ? 'image-plus' : 'film'" class="size-4" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[12px] font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ \Illuminate\Support\Str::limit($job->prompt, 32) }}</div>
                                <div class="text-[10px] text-zinc-500">{{ ucfirst($job->provider) }} · {{ $job->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="relative h-1.5 rounded-full bg-zinc-100 dark:bg-white/5 overflow-hidden">
                            <div class="absolute inset-0 rounded-full db-progress-stripes" style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ $job->status }}</span>
                            <span class="text-[10px] text-zinc-400 tabular-nums">{{ $job->width }}×{{ $job->height }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ========================================================= --}}
    {{-- BRAND STRIP                                                --}}
    {{-- ========================================================= --}}
    <section class="mb-15 db-fade db-d-4 border border-zinc-200 dark:border-white/8 rounded-2xl p-5 md:p-6">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <h2 class="db-section-title font-bold">{{ __('Your Brands') }}</h2>
            </div>
            <a href="{{ route('user.brands.index') }}" wire:navigate class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Manage all') }} →</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @forelse($this->brands as $brand)
                @php
                    $score    = $brand->completion_score;
                    $primary  = $brand->primary_color   ?: '#6366f1';
                    $secondary= $brand->secondary_color ?: '#8b5cf6';
                    $initial  = mb_strtoupper(mb_substr($brand->name, 0, 1));
                @endphp
                <a href="{{ route('user.brands.edit', $brand->id) }}" wire:navigate
                   class="group relative rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden transition-colors duration-300 hover:border-indigo-300 dark:hover:border-indigo-700/60">

                    {{-- Default badge --}}
                    @if($brand->is_default)
                        <div class="absolute top-3 left-3 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest border border-zinc-200 text-amber-700 dark:bg-amber-950/40 dark:border-amber-900/40 dark:text-amber-300">
                            <flux:icon.star class="size-2.5 fill-amber-400 text-amber-400" />
                            {{ __('Default') }}
                        </div>
                    @endif

                    {{-- Logo zone — centered circle logo --}}
                    <div class="relative flex items-center justify-center px-4 py-6">
                        <div class="relative w-[104px] h-[104px] rounded-full overflow-hidden border border-zinc-200 dark:border-white/8 flex-shrink-0">
                            @if($brand->logo_path)
                                <img src="{{ URL::asset($brand->logo_path) }}" alt="{{ $brand->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                <div class="hidden items-center justify-center w-full h-full text-2xl font-black text-white"
                                     style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                                    {{ $initial }}
                                </div>
                            @else
                                <div class="flex items-center justify-center w-full h-full text-2xl font-black text-white"
                                     style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Title + meta --}}
                    <div class="px-4 py-3 border-t border-zinc-100 dark:border-white/6 text-center">
                        <h3 class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $brand->name }}</h3>
                        <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mt-0.5 truncate">{{ $brand->industry ?: ($brand->tagline ?: __('No industry set')) }}</p>
                    </div>
                </a>
            @empty
                <a href="{{ route('user.brands.create') }}" wire:navigate class="sm:col-span-2 lg:col-span-4 rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 flex items-center gap-4 hover:border-indigo-300 dark:hover:border-indigo-700/60 transition group">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition"
                         style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                        <flux:icon.sparkles class="size-5 text-white" />
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Set up your first brand') }}</div>
                        <div class="text-xs text-zinc-500 mt-0.5">{{ __('Lock in colors, logo and voice — your ads instantly become on-brand.') }}</div>
                    </div>
                    <flux:icon.arrow-right class="size-4 text-zinc-400 group-hover:translate-x-1 transition" />
                </a>
            @endforelse

            @if($this->brands->count() > 0 && $this->brands->count() < 5)
                <a href="{{ route('user.brands.create') }}" wire:navigate class="rounded-2xl border border-dashed border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-4 flex items-center justify-center gap-2 hover:border-indigo-300 dark:hover:border-indigo-700/60 text-xs font-semibold text-zinc-500 hover:text-indigo-600 transition group">
                    <flux:icon.plus class="size-4 group-hover:scale-110 transition" />
                    {{ __('New brand') }}
                </a>
            @endif
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- RECENT CREATIVES + RECENT COPIES (two-column)              --}}
    {{-- ========================================================= --}}
    <section class="mb-15 grid grid-cols-1 lg:grid-cols-5 gap-7 db-fade db-d-5">

        {{-- Recent creatives (3 cols) --}}
        <div class="lg:col-span-3 border border-zinc-200 dark:border-white/8 rounded-2xl p-5 md:p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="db-section-title font-bold">{{ __('Recent Creatives') }}</h2>
                <a href="{{ route('user.studio.gallery') }}" wire:navigate class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('View all') }} →</a>
            </div>

            @if($this->recentAssets->count())
                <div class="flex-1 grid grid-cols-3 sm:grid-cols-4 gap-3">
                    @foreach($this->recentAssets as $asset)
                        <a href="{{ route('user.studio.gallery') }}?open={{ $asset->id }}" wire:navigate
                           class="group relative aspect-square rounded-xl overflow-hidden bg-zinc-100 dark:bg-white/5 ring-1 ring-zinc-200/60 dark:ring-white/10 hover:ring-indigo-400 dark:hover:ring-indigo-400 transition"
                           title="{{ \Illuminate\Support\Str::limit($asset->prompt, 120) }}">
                            @if($asset->type === 'image' && $asset->file_path)
                                <img src="{{ $asset->fileUrl() }}" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy" />
                            @elseif($asset->type === 'video' && $asset->file_path)
                                {{-- First frame as poster, like the gallery --}}
                                <video class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" muted playsinline preload="metadata">
                                    <source src="{{ $asset->fileUrl() }}#t=0.1" @if($asset->mime_type) type="{{ $asset->mime_type }}" @endif />
                                </video>
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full text-white backdrop-blur-sm ring-1 ring-white/25 shadow-lg transition-transform duration-200 group-hover:scale-110"
                                          style="background-color: #000000;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                    </span>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-zinc-100 dark:bg-(--default-element-light-bg-color)">
                                    <flux:icon.film class="size-6 text-zinc-400" />
                                </div>
                            @endif

                            {{-- Type badge --}}
                            <span class="absolute top-1.5 left-1.5 text-[9px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider {{ $asset->type === 'image' ? 'bg-indigo-50/90 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-amber-50/90 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' }}">{{ $asset->type }}</span>

                            {{-- Hover overlay with prompt + meta --}}
                            <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/75 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                                <p class="text-[10px] font-semibold text-white truncate">{{ \Illuminate\Support\Str::limit($asset->prompt, 40) }}</p>
                                <span class="text-[9px] text-white/70 tabular-nums">{{ $asset->width }}×{{ $asset->height }} · {{ $asset->created_at->diffForHumans(short: true) }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                {{-- Empty state: 3-step illustrated flow --}}
                <div class="flex-1 rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 text-center flex flex-col items-center justify-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm shadow-indigo-500/25 mb-3"
                         style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                        <flux:icon.sparkles class="size-6 text-white" />
                    </div>
                    <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('No creatives yet') }}</div>
                    <p class="text-xs text-zinc-500 mt-1 mb-4">{{ __('Three steps — prompt, style, generate. Under a minute, first time.') }}</p>
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('user.studio.images') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold  hover:bg-zinc-200 transition border border-zinc-200 dark:border-white/8" >
                            <flux:icon.image-plus class="size-3.5" /> {{ __('Start with image') }}
                        </a>
                        <a href="{{ route('user.studio.videos') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold dark:text-zinc-200 dark:bg-white/5 border border-zinc-200 dark:border-white/8 hover:bg-zinc-200 dark:hover:bg-white/10 transition">
                            <flux:icon.film class="size-3.5" /> {{ __('Or video') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Recent copies teaser (2 cols) --}}
        <div class="lg:col-span-2 border border-zinc-200 dark:border-white/8 rounded-2xl p-5 md:p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h2 class="db-section-title font-bold">{{ __('Latest Ad Copies') }}</h2>
                <a href="{{ route('user.copy.library') }}" wire:navigate class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('Library') }} →</a>
            </div>

            @if($this->recentCopies->count())
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 content-start">
                    @foreach($this->recentCopies as $copy)
                        @php
                            $tint = $copy->platformTint();
                            $tintMap = [
                                'blue'    => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                                'pink'    => 'bg-pink-500/10 text-pink-600 dark:text-pink-400',
                                'fuchsia' => 'bg-fuchsia-500/10 text-fuchsia-600 dark:text-fuchsia-400',
                                'zinc'    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-300',
                                'sky'     => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                                'red'     => 'bg-red-500/10 text-red-600 dark:text-red-400',
                                'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                'amber'   => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                'violet'  => 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
                                'indigo'  => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
                                'teal'    => 'bg-teal-500/10 text-teal-600 dark:text-teal-400',
                            ];
                            $badgeClass = $tintMap[$tint] ?? $tintMap['zinc'];
                            $copyTitle = $copy->title ?: \Illuminate\Support\Str::limit($copy->product_description, 50);
                        @endphp
                        <a href="{{ route('user.copy.library', ['focus' => $copy->id]) }}" wire:navigate
                           class="group relative flex flex-col gap-2 rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-3.5 hover:border-indigo-300 dark:hover:border-indigo-500/40 hover:shadow-md hover:shadow-indigo-500/5 transition-all duration-200"
                           title="{{ $copyTitle }}">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $badgeClass }}">
                                    @include('livewire.user.copy-studio.partials._platform-icon', [
                                        'slug'     => $copy->platform,
                                        'fallback' => $copy->platformIcon(),
                                        'class'    => 'size-4',
                                    ])
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-200 truncate">{{ $copy->platformLabel() }}</div>
                                    <div class="text-[10px] text-zinc-400 truncate">{{ $copy->created_at->diffForHumans(short: true) }}</div>
                                </div>
                                @if($copy->is_favorite)
                                    <flux:icon.star class="size-3.5 text-amber-500 shrink-0" />
                                @endif
                            </div>
                            <div class="text-[12px] font-semibold text-zinc-800 dark:text-zinc-100 line-clamp-2 leading-snug">{{ $copyTitle }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex-1 rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 text-center flex flex-col items-center justify-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm shadow-indigo-500/25 mb-3"
                         style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                        <flux:icon.pencil class="size-6 text-white" />
                    </div>
                    <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('No copies yet') }}</div>
                    <p class="text-xs text-zinc-500 mt-1 mb-4">{{ __('13 platforms, 8 frameworks, proven formulas — pick and generate in seconds.') }}</p>
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('user.copy.studio') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold hover:bg-zinc-200 border border-zinc-200 transition">
                            <flux:icon.sparkles class="size-3.5" /> {{ __('Open Copy Studio') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- PRESET LIBRARY — real presets, filterable, deep-linked     --}}
    {{-- ========================================================= --}}
    <section class="mb-6 db-fade db-d-5">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="db-section-title font-bold">{{ __('Preset Library') }}</h2>
                <p class="text-[13px] text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Pick an ad you like — we drop it into the studio as your reference image.') }}</p>
            </div>
        </div>

        @php $presetGallery = $this->presetGallery; @endphp

        {{-- Masonry: CSS columns keep items flowing one after another across responsive column counts --}}
        <div class="columns-2 sm:columns-3 md:columns-4 lg:columns-5 xl:columns-6 gap-3 [column-fill:_balance]">
            @foreach($presetGallery as $preset)
                <div class="break-inside-avoid mb-3">
                    <a href="{{ route('user.studio.images') }}?ref_preset={{ urlencode($preset['id']) }}"
                       wire:navigate
                       class="group relative block rounded-xl overflow-hidden border {{ $preset['url'] ? 'border-zinc-200 dark:border-white/8' : 'border-dashed border-zinc-200 dark:border-white/8' }} hover:border-indigo-300 dark:hover:border-indigo-500/30 hover:shadow-md hover:shadow-indigo-500/5 transition-all duration-200">
                        <div class="relative w-full" style="padding-bottom: {{ $preset['ratioPct'] }}%">
                            @if($preset['url'])
                                <img src="{{ $preset['url'] }}" alt="{{ __('Ad preset') }}" loading="lazy"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300" />
                            @else
                                {{-- Empty slot — transparent background, border only --}}
                                <div class="absolute inset-0 bg-transparent"></div>
                            @endif

                            {{-- Hover CTA overlay — white pill (black in dark mode) --}}
                            <div class="absolute inset-0 z-10 {{ $preset['url'] ? 'bg-slate-900/40 backdrop-blur-[1px]' : '' }} opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold shadow-md translate-y-1 group-hover:translate-y-0 transition-all duration-200 text-white"
                                     style="background-color: var(--db-brand-1, #4F46E5);"
                                     onmouseover="this.style.backgroundColor='#ffffff'; this.style.color='var(--db-brand-1, #4F46E5)';"
                                     onmouseout="this.style.backgroundColor='var(--db-brand-1, #4F46E5)'; this.style.color='#ffffff';">
                                    <flux:icon.plus class="size-3" />
                                    {{ __('Use as reference') }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>


    {{-- ========================================================= --}}
    {{-- COMMAND PALETTE DATA + BEHAVIOR                            --}}
    {{-- ========================================================= --}}
    @php
        // Build the palette index from real data
        $paletteItems = [];

        // Actions
        $paletteItems[] = ['id' => 'act-image',   'label' => __('Open Image Studio'),    'hint' => __('Create a new image ad'),    'href' => route('user.studio.images'),   'tag' => __('Action'), 'tagClass' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300', 'iconBg' => 'bg-indigo-100 dark:bg-indigo-500/15', 'icon' => 'image-plus'];
        $paletteItems[] = ['id' => 'act-video',   'label' => __('Open Video Studio'),    'hint' => __('Generate a video ad'),      'href' => route('user.studio.videos'),   'tag' => __('Action'), 'tagClass' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300', 'iconBg' => 'bg-violet-100 dark:bg-violet-500/15', 'icon' => 'film'];
        $paletteItems[] = ['id' => 'act-copy',    'label' => __('Open Copy Studio'),     'hint' => __('Write AI-powered ad copy'), 'href' => route('user.copy.studio'),     'tag' => __('Action'), 'tagClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300', 'iconBg' => 'bg-emerald-100 dark:bg-emerald-500/15', 'icon' => 'pencil'];
        $paletteItems[] = ['id' => 'act-gallery', 'label' => __('View Gallery'),         'hint' => __('All your generated creatives'), 'href' => route('user.studio.gallery'), 'tag' => __('Action'), 'tagClass' => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200', 'iconBg' => 'bg-zinc-100 dark:bg-white/5', 'icon' => 'images'];
        $paletteItems[] = ['id' => 'act-library', 'label' => __('Copy Library'),          'hint' => __('Your saved ad copies'),      'href' => route('user.copy.library'),    'tag' => __('Action'), 'tagClass' => 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200', 'iconBg' => 'bg-zinc-100 dark:bg-white/5', 'icon' => 'book-open'];
        $paletteItems[] = ['id' => 'act-brand-new', 'label' => __('Create new brand'),    'hint' => __('Add a brand kit'),           'href' => route('user.brands.create'),   'tag' => __('Action'), 'tagClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300', 'iconBg' => 'bg-amber-100 dark:bg-amber-500/15', 'icon' => 'sparkles'];
        $paletteItems[] = ['id' => 'act-brand-list', 'label' => __('Manage brands'),      'hint' => __('All your brand kits'),       'href' => route('user.brands.index'),    'tag' => __('Action'), 'tagClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300', 'iconBg' => 'bg-amber-100 dark:bg-amber-500/15', 'icon' => 'squares-2x2'];

        // Image presets
        foreach ($presetLibrary as $p) {
            $paletteItems[] = [
                'id' => 'preset-' . $p['type'] . '-' . $p['slug'],
                'label' => $p['label'],
                'hint' => $p['width'] . '×' . $p['height'] . ' · ' . $p['ratio'] . ' · ' . ucfirst($p['type']),
                'href' => route($p['route']) . '?preset=' . $p['slug'],
                'tag' => $p['type'] === 'video' ? __('Video') : __('Preset'),
                'tagClass' => $p['type'] === 'video'
                    ? 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300'
                    : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
                'iconBg' => $p['type'] === 'video' ? 'bg-violet-100 dark:bg-violet-500/15' : 'bg-indigo-100 dark:bg-indigo-500/15',
                'icon' => $p['type'] === 'video' ? 'film' : 'image-plus',
            ];
        }

        // Ad copy platforms
        foreach (config('ad-copy.platforms', []) as $pkey => $platform) {
            $paletteItems[] = [
                'id' => 'platform-' . $pkey,
                'label' => __('Write for') . ' ' . $platform['label'],
                'hint' => $platform['description'] ?? __('Open Copy Studio with this platform preselected'),
                'href' => route('user.copy.studio') . '?platform=' . $pkey,
                'tag' => __('Copy'),
                'tagClass' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                'iconBg' => 'bg-emerald-100 dark:bg-emerald-500/15',
                'icon' => $platform['icon'] ?? 'pencil',
            ];
        }

        // User brands
        foreach ($this->brands as $brand) {
            $paletteItems[] = [
                'id' => 'brand-' . $brand->id,
                'label' => $brand->name,
                'hint' => $brand->industry ?: __('Edit brand kit'),
                'href' => route('user.brands.edit', $brand->id),
                'tag' => __('Brand'),
                'tagClass' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                'iconBg' => 'bg-amber-100 dark:bg-amber-500/15',
                'icon' => 'sparkles',
            ];
        }

        // Map flux icon names to inline SVG for the palette (so we don't have to render Blade components client-side)
        $iconSvgMap = [
            'image-plus'  => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75v10.5A2.25 2.25 0 006 19.5h10.5M3.75 6.75A2.25 2.25 0 016 4.5h7.5M3.75 6.75l6.75 6 3-2.25 3.75 3M18 7.5v6m3-3h-6"/></svg>',
            'film'        => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 9h18M3 13.5h18M3 18h18M7 4.5v15M17 4.5v15"/></svg>',
            'pencil'      => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.687a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>',
            'images'      => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>',
            'book-open'   => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
            'sparkles'    => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.814a4.5 4.5 0 00-3.09 3.09zM18 18.75V22.5m0-3.75L15.75 20.25M18 18.75l2.25 1.5"/></svg>',
            'squares-2x2' => '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        ];
        // fallback icon for anything else
        $defaultSvg = '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15v15h-15z"/></svg>';

        $paletteItems = array_map(function ($item) use ($iconSvgMap, $defaultSvg) {
            $item['iconSvg'] = $iconSvgMap[$item['icon']] ?? $defaultSvg;
            unset($item['icon']);
            return $item;
        }, $paletteItems);
    @endphp

    <script>
        function userDashboard() {
            return {
                paletteOpen: false,
                paletteQuery: '',
                paletteIndex: 0,
                paletteItems: @json($paletteItems),

                // Create-menu popover (teleported to body, positioned from trigger rect)
                createOpen: false,
                createMenuStyle: '',

                init() {
                    const reposition = () => { if (this.createOpen) this.positionCreateMenu(); };
                    window.addEventListener('scroll', reposition, true);
                    window.addEventListener('resize', reposition);
                },

                toggleCreateMenu() {
                    this.createOpen = !this.createOpen;
                    if (this.createOpen) this.$nextTick(() => this.positionCreateMenu());
                },

                positionCreateMenu() {
                    const btn = this.$refs.createTrigger;
                    if (!btn) return;
                    const r = btn.getBoundingClientRect();
                    const menuW = 240;
                    const gap = 8;
                    // Prefer left-align with trigger; clamp to viewport with 12px padding.
                    let left = r.left;
                    const maxLeft = window.innerWidth - menuW - 12;
                    if (left > maxLeft) left = maxLeft;
                    if (left < 12) left = 12;
                    const top = r.bottom + gap;
                    this.createMenuStyle = `left:${left}px; top:${top}px;`;
                },

                paletteFiltered() {
                    const q = this.paletteQuery.trim().toLowerCase();
                    if (!q) return this.paletteItems.slice(0, 60);
                    return this.paletteItems
                        .filter(i => (i.label + ' ' + i.hint + ' ' + i.tag).toLowerCase().includes(q))
                        .slice(0, 60);
                },
                paletteMove(dir) {
                    const list = this.paletteFiltered();
                    if (!list.length) return;
                    this.paletteIndex = (this.paletteIndex + dir + list.length) % list.length;
                    this.$nextTick(() => {
                        const el = this.$refs.paletteList?.children[this.paletteIndex];
                        el?.scrollIntoView({ block: 'nearest' });
                    });
                },
                paletteCommit() {
                    const item = this.paletteFiltered()[this.paletteIndex];
                    if (item) window.location.href = item.href;
                },
            };
        }
    </script>
</div>
