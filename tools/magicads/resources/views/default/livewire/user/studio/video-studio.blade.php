<div @if($this->latestPending) wire:poll.5s="$refresh" @endif x-data="{
    step: 1,
    totalSteps: 5,
    showHistory: false,

    {{-- Alpine-first state via @entangle for instant UI --}}
    goal: @entangle('videoGoal'),
    platform: @entangle('platform'),
    preset: @entangle('selectedPreset'),
    duration: @entangle('selectedDuration'),
    resolution: @entangle('selectedResolution'),
    motionType: @entangle('motionType'),
    videoStyle: @entangle('videoStyle'),
    mood: @entangle('videoMood'),
    model: @entangle('selectedModel'),
    overlayText: @entangle('overlayText'),
    ctaText: @entangle('ctaText'),

    get progress() { return (this.step / this.totalSteps) * 100 },
    get canProceed() {
        if (this.step === 1) return this.goal !== '' && this.platform !== '';
        if (this.step === 2) return this.preset !== '';
        if (this.step === 3) return true;
        if (this.step === 4) return $wire.prompt !== '';
        return true;
    }
}">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- ========================================== --}}
            {{-- Top Navigation — Modern Pill Toolbar        --}}
            {{-- ========================================== --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Creative Tools') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Video Studio') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('user.studio.gallery') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200  transition dark:text-zinc-300 dark:hover:text-white dark:bg-(--default-element-bg-color) dark:hover:bg-white/5 dark:border-white/8">
                        <flux:icon.squares-2x2 class="size-3.5" /> {{ __('Gallery') }}
                    </a>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Hero Banner — clean, image-studio style      --}}
            {{-- ========================================== --}}
            <div class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40
                        bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(79,70,229,0.26),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(245,158,11,0.16),transparent),radial-gradient(ellipse_60%_40%_at_50%_50%,rgba(79,70,229,0.10),transparent)]">

                {{-- Top shimmer line --}}
                <div class="absolute top-0 inset-x-0 h-px"
                     style="background: linear-gradient(90deg, transparent, rgba(79,70,229,0.60), transparent);"></div>

                <div class="relative px-6 md:px-8 py-10 flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        {{-- Brand-gradient icon frame --}}
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.film class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            {{-- Step pill --}}
                            <div class="flex flex-wrap items-center gap-2 mb-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-white/8 border border-white/15 text-[9px] font-bold uppercase tracking-[0.18em] text-white">
                                    <flux:icon.film class="size-2.5" /> {{ __('Video Studio') }}
                                </span>
                                <span class="text-[10px] text-zinc-400 font-mono">{{ __('Step') }} <span x-text="step"></span> / 5</span>
                            </div>

                            {{-- Dynamic title + subtitle --}}
                            <h1 class="text-xl md:text-2xl font-extrabold text-white leading-tight tracking-tight">
                                <span x-show="step === 1">{{ __('What kind of video ad?') }}</span>
                                <span x-show="step === 2">{{ __('Choose format & duration') }}</span>
                                <span x-show="step === 3">{{ __('Set the creative direction') }}</span>
                                <span x-show="step === 4">{{ __('Describe your scene') }}</span>
                                <span x-show="step === 5">{{ __('Review & Generate') }}</span>
                            </h1>
                            <p class="text-xs text-zinc-400 mt-1 max-w-lg">
                                <span x-show="step === 1">{{ __('Pick a goal and target platform — then grab a trending template to jump-start your concept.') }}</span>
                                <span x-show="step === 2">{{ __('Lock in the aspect ratio and clip length. Video AI is strict about these — choose wisely.') }}</span>
                                <span x-show="step === 3">{{ __('Define visual style, camera motion, and mood. This is the directorial brief for your AI.') }}</span>
                                <span x-show="step === 4">{{ __('Write a vivid scene description and optional overlay text. Reference images improve coherence.') }}</span>
                                <span x-show="step === 5">{{ __('Final review before launch. Video renders typically take 2–5 minutes.') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Single Credits chip — matches image-studio exactly --}}
                    <div class="grid grid-cols-1 gap-2 w-full xl:w-auto">
                        <div class="px-3 py-2.5 rounded-xl bg-white/4 backdrop-blur-sm border border-white/10">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-lg bg-linear-to-br from-amber-400/20 to-orange-500/20 border border-amber-400/30 flex items-center justify-center">
                                    <flux:icon.bolt class="size-3.5 text-amber-400" />
                                </div>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Credits') }}</span>
                            </div>
                            <div class="text-base font-bold text-white leading-none tabular-nums text-right">{{ number_format($creditBalance) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Step tracker — slim, integrated --}}
                <div class="relative px-6 md:px-8 pb-10">
                    @php
                        $stepFlow = [
                            1 => ['label' => __('Goal'),    'icon' => 'flag'],
                            2 => ['label' => __('Format'),  'icon' => 'rectangle-group'],
                            3 => ['label' => __('Style'),   'icon' => 'paint-brush'],
                            4 => ['label' => __('Scene'),   'icon' => 'video-camera'],
                            5 => ['label' => __('Launch'),  'icon' => 'rocket-launch'],
                        ];
                    @endphp
                    <div class="flex items-center">
                        @foreach($stepFlow as $n => $info)
                            <div class="flex items-center {{ $n < 5 ? 'flex-1' : '' }}">
                                <button type="button" @click="step = {{ $n }}" class="relative group flex flex-col items-center focus:outline-none">
                                    {{-- Step chip — active uses the same gradient-frame + dark inner style as the hero icon --}}
                                    <div class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300"
                                         :class="step === {{ $n }} ? 'p-px bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 shadow-lg shadow-indigo-500/40 scale-110' : step > {{ $n }} ? 'border bg-emerald-500/15 border-emerald-400/40' : 'border bg-white/5 border-white/10 group-hover:border-white/20'">
                                        <div class="relative w-full h-full rounded-[10px] flex items-center justify-center"
                                             :class="step === {{ $n }} ? 'bg-zinc-950' : ''">
                                            <flux:icon.check class="absolute size-4 text-emerald-300" x-show="step > {{ $n }}" x-cloak />
                                            @if($info['icon'] === 'flag')
                                                <flux:icon.flag class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'rectangle-group')
                                                <flux:icon.rectangle-group class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'paint-brush')
                                                <flux:icon.paint-brush class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'video-camera')
                                                <flux:icon.video-camera class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'rocket-launch')
                                                <flux:icon.rocket-launch class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @endif
                                        </div>
                                        <span class="absolute inset-0 rounded-xl bg-indigo-400/30 animate-ping" x-show="step === {{ $n }}" x-cloak style="animation-duration: 2s"></span>
                                    </div>
                                    <span class="absolute top-12 whitespace-nowrap text-[10px] font-semibold uppercase tracking-wider transition-colors"
                                          :class="step === {{ $n }} ? 'text-white' : step > {{ $n }} ? 'text-emerald-400/80' : 'text-zinc-500 group-hover:text-zinc-400'">{{ $info['label'] }}</span>
                                </button>
                                @if($n < 5)
                                    <div class="flex-1 h-[2px] mx-2 rounded-full overflow-hidden bg-white/5">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="step > {{ $n }} ? 'w-full bg-linear-to-r from-emerald-400 to-indigo-400' : step === {{ $n }} ? 'w-1/2 bg-linear-to-r from-indigo-400 to-indigo-400/0' : 'w-0'"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 1: Video Goal & Platform                --}}
            {{-- ============================================ --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:items-stretch">

                    {{-- Video Goal --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color) lg:flex lg:flex-col">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.rocket-launch class="size-4 text-amber-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Video Goal') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('What should this video achieve?') }}</p>

                        <div class="grid grid-cols-2 gap-2 lg:flex-1 lg:content-start">
                            @php
                                // Lucide-style monochrome line icons
                                $goals = [
                                    'product_showcase' => [
                                        'label' => __('Product Showcase'), 'desc' => __('Highlight features'),
                                        'svg' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
                                    ],
                                    'brand_story' => [
                                        'label' => __('Brand Story'), 'desc' => __('Tell your narrative'),
                                        'svg' => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>',
                                    ],
                                    'promo_sale' => [
                                        'label' => __('Promo / Sale'), 'desc' => __('Drive urgency'),
                                        'svg' => '<path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/>',
                                    ],
                                    'testimonial' => [
                                        'label' => __('Testimonial'), 'desc' => __('Social proof'),
                                        'svg' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                                    ],
                                    'explainer' => [
                                        'label' => __('Explainer'), 'desc' => __('Educate viewers'),
                                        'svg' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
                                    ],
                                    'app_demo' => [
                                        'label' => __('App Demo'), 'desc' => __('Show the experience'),
                                        'svg' => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>',
                                    ],
                                    'event_teaser' => [
                                        'label' => __('Event Teaser'), 'desc' => __('Build anticipation'),
                                        'svg' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>',
                                    ],
                                    'ugc_style' => [
                                        'label' => __('UGC Style'), 'desc' => __('Authentic & raw'),
                                        'svg' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($goals as $key => $g)
                                <button @click="goal = '{{ $key }}'" class="group relative flex items-start gap-2.5 p-3 rounded-xl border text-left transition-all hover:shadow-md" :class="goal === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="mt-0.5 inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                          :class="goal === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $g['svg'] !!}</svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold" :class="goal === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $g['label'] }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $g['desc'] }}</div>
                                    </div>
                                    <div x-show="goal === '{{ $key }}'" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Target Platform --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color) lg:flex lg:flex-col">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.device-phone-mobile class="size-4 text-emerald-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Target Platform') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Where will this video run?') }}</p>

                        <div class="grid grid-cols-3 gap-2 lg:flex-1 lg:content-start">
                            @php
                                // Lucide-style monochrome line icons
                                $platforms = [
                                    'tiktok' => [
                                        'label' => __('TikTok'), 'trend' => __('9:16 vertical'),
                                        'svg' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>',
                                    ],
                                    'instagram_reels' => [
                                        'label' => __('IG Reels'), 'trend' => __('9:16 vertical'),
                                        'svg' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
                                    ],
                                    'youtube_shorts' => [
                                        'label' => __('YT Shorts'), 'trend' => __('9:16 vertical'),
                                        'svg' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
                                    ],
                                    'facebook_feed' => [
                                        'label' => __('FB Feed'), 'trend' => __('1:1 or 4:5'),
                                        'svg' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
                                    ],
                                    'youtube_ad' => [
                                        'label' => __('YouTube Ad'), 'trend' => __('16:9 landscape'),
                                        'svg' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
                                    ],
                                    'linkedin' => [
                                        'label' => __('LinkedIn'), 'trend' => __('1:1 or 16:9'),
                                        'svg' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
                                    ],
                                    'twitter_x' => [
                                        'label' => __('X / Twitter'), 'trend' => __('16:9 or 1:1'),
                                        'svg' => '<path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/>',
                                    ],
                                    'snapchat' => [
                                        'label' => __('Snapchat'), 'trend' => __('9:16 vertical'),
                                        'svg' => '<path d="M12 2c4.5 0 6.5 3 6.5 7 0 1 .5 2 1.5 2.5.5.25.5 1 0 1.25-.7.35-1.5.5-1.5 1.5 0 1.5.5 2.5 2 3-.5 1.5-3 2.5-5 2.5-.5 0-1 .5-1 1 0 .25-.25.5-.5.5h-4c-.25 0-.5-.25-.5-.5 0-.5-.5-1-1-1-2 0-4.5-1-5-2.5 1.5-.5 2-1.5 2-3 0-1-.8-1.15-1.5-1.5-.5-.25-.5-1 0-1.25 1-.5 1.5-1.5 1.5-2.5 0-4 2-7 6.5-7z"/>',
                                    ],
                                    'pinterest' => [
                                        'label' => __('Pinterest'), 'trend' => __('9:16 or 2:3'),
                                        'svg' => '<line x1="8" x2="12" y1="22" y2="14"/><path d="M9.5 14.5a4 4 0 1 0 6 -5"/><circle cx="12" cy="12" r="10"/>',
                                    ],
                                    'connected_tv' => [
                                        'label' => __('CTV / OTT'), 'trend' => __('16:9 landscape'),
                                        'svg' => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>',
                                    ],
                                    'website_hero' => [
                                        'label' => __('Website Hero'), 'trend' => __('16:9 landscape'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
                                    ],
                                    'email' => [
                                        'label' => __('Email / GIF'), 'trend' => __('1:1 square'),
                                        'svg' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($platforms as $key => $pl)
                                <button @click="platform = '{{ $key }}'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all hover:shadow-md" :class="platform === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition-colors"
                                          :class="platform === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $pl['svg'] !!}</svg>
                                    </span>
                                    <span class="text-[10px] font-semibold text-center" :class="platform === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $pl['label'] }}</span>
                                    <span class="text-[8px] text-zinc-400">{{ $pl['trend'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>



                <div class="flex justify-end mt-6">
                    <button type="button" @click="if(canProceed) step = 2"
                            :disabled="!canProceed"
                            class="group/cta relative flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                            style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                              style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                        <span class="relative flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                <flux:icon.arrow-right class="size-4 group-hover/cta:translate-x-0.5 transition-transform" />
                            </span>
                            <span>{{ __('Next: Format & Duration') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 2: Format & Duration                    --}}
            {{-- ============================================ --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:items-start">

                    {{-- Aspect Ratio --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-cyan-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.rectangle-stack class="size-4 text-cyan-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Aspect Ratio') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Choose the video frame format') }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($presets as $p)
                                @php
                                    $maxH = 64; $maxW = 80;
                                    $r = $p['width'] / max($p['height'], 1);
                                    $dW = $r >= 1 ? $maxW : (int)($maxH * $r);
                                    $dH = $r >= 1 ? (int)($maxW / $r) : $maxH;
                                    $bestFor = match($p['slug']) {
                                        'vid-landscape' => __('YouTube, CTV, Website'),
                                        'vid-portrait' => __('TikTok, Reels, Shorts'),
                                        'vid-square' => __('Facebook, LinkedIn, Email'),
                                        'vid-vertical' => __('Instagram Feed, Facebook'),
                                        default => '',
                                    };
                                @endphp
                                <button @click="preset = '{{ $p['slug'] }}'" class="group relative rounded-xl border p-4 text-center transition-all hover:shadow-md hover:-translate-y-0.5"
                                        :class="preset === '{{ $p['slug'] }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm ring-1 ring-indigo-500/20' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-(--default-element-light-bg-color) dark:hover:border-neutral-600'">
                                    {{-- Top accent line --}}
                                    <div class="h-1 w-8 rounded-full mb-2.5 mx-auto group-hover:w-12 transition-all"
                                         :class="preset === '{{ $p['slug'] }}' ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-200 dark:bg-neutral-700'"></div>
                                    <div class="flex items-center justify-center mb-3 h-16">
                                        <div class="rounded border-2 border-dashed flex items-center justify-center transition-colors"
                                             :class="preset === '{{ $p['slug'] }}' ? 'border-indigo-400 bg-indigo-100/50 dark:bg-indigo-900/30' : 'border-zinc-300 bg-zinc-50 dark:border-neutral-600 dark:bg-neutral-700/50'"
                                             style="width: {{ $dW }}px; height: {{ $dH }}px;">
                                            <span class="text-[9px] font-bold tabular-nums" :class="preset === '{{ $p['slug'] }}' ? 'text-indigo-500' : 'text-zinc-400'">{{ $p['ratio'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-xs font-semibold" :class="preset === '{{ $p['slug'] }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $p['label'] }}</div>
                                    <div class="text-[9px] text-zinc-400 mt-0.5 font-mono tabular-nums">{{ $p['width'] }}×{{ $p['height'] }}</div>
                                    @if($bestFor)
                                        <div class="text-[8px] text-zinc-400 mt-1">{{ $bestFor }}</div>
                                    @endif
                                    <div x-show="preset === '{{ $p['slug'] }}'" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shadow-sm"><flux:icon.check class="size-3 text-white" /></div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Duration + AI Engine stack --}}
                    <div class="space-y-6">
                        {{-- Duration --}}
                        @php
                            // Map of provider key → its supported clip lengths.
                            // Used by Alpine x-show to gate the duration buttons
                            // when the user switches engines.
                            $durationMap = collect($providers)
                                ->mapWithKeys(fn ($p) => [$p['key'] => $p['durations'] ?? []])
                                ->toArray();
                        @endphp
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)"
                             x-data="{ providerDurations: {{ \Illuminate\Support\Js::from($durationMap) }} }">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                        <flux:icon.clock class="size-4 text-amber-400" />
                                    </span>
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Duration') }}</h3>
                                </div>
                                <span class="text-[10px] text-zinc-400 font-mono">
                                    <span x-text="model"></span> · <span x-text="(providerDurations[model] || []).join('s · ') + 's'"></span>
                                </span>
                            </div>
                            <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Clip lengths supported by the selected engine') }}</p>
                            <div class="space-y-2">
                                @foreach($durations as $dur)
                                    @php
                                        $durMeta = match($dur) {
                                            5  => ['label' => __('5 seconds'),  'desc' => __('Bumper ad · Quick hook · Story ad'), 'tag' => __('Best for TikTok & Stories'), 'svg' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>'],
                                            8  => ['label' => __('8 seconds'),  'desc' => __('Veo native length · Hook + reveal + CTA'), 'tag' => __('Native length on Veo 3.1'), 'svg' => '<polygon points="6 3 20 12 6 21 6 3"/>'],
                                            10 => ['label' => __('10 seconds'), 'desc' => __('Standard ad · Product demo · Explainer'), 'tag' => __('Most popular format'), 'svg' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'],
                                            15 => ['label' => __('15 seconds'), 'desc' => __('Brand film · Detailed showcase · Narrative'), 'tag' => __('Kling Master mode only'), 'svg' => '<path d="m4 11 8.5 8.5a4.95 4.95 0 1 0 7-7L11 4"/><path d="M14.5 8.5 19 4"/>'],
                                            default => ['label' => $dur . 's', 'desc' => '', 'tag' => '', 'svg' => '<polygon points="6 3 20 12 6 21 6 3"/>'],
                                        };
                                    @endphp
                                    <button @click="duration = {{ $dur }}; $wire.set('selectedDuration', {{ $dur }})"
                                            x-show="(providerDurations[model] || []).includes({{ $dur }})"
                                            x-cloak
                                            class="w-full relative flex items-center gap-3 p-4 rounded-xl border text-left transition-all hover:shadow-md"
                                            :class="duration === {{ $dur }} ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                        <span class="inline-flex w-9 h-9 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                              :class="duration === {{ $dur }} ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $durMeta['svg'] !!}</svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-bold" :class="duration === {{ $dur }} ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'">{{ $durMeta['label'] }}</span>
                                                @if($dur === 10)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-[9px] font-bold uppercase tracking-wider text-amber-700 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300">
                                                        <span class="w-1 h-1 rounded-full bg-amber-500"></span> {{ __('Popular') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $durMeta['desc'] }}</div>
                                            <div class="text-[9px] text-zinc-400 mt-0.5">{{ $durMeta['tag'] }}</div>
                                        </div>
                                        <div x-show="duration === {{ $dur }}" class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shrink-0"><flux:icon.check class="size-3 text-white" /></div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Resolution / Quality tier --}}
                        {{-- Only shown for engines that expose selectable quality
                             tiers (e.g. Seedance). Other engines derive resolution
                             from the chosen format and this card stays hidden. --}}
                        @php
                            $resolutionMap = collect($providers)
                                ->mapWithKeys(fn ($p) => [$p['key'] => $p['resolutions'] ?? []])
                                ->toArray();
                            $resMeta = [
                                '480p'  => ['label' => __('480p'),          'desc' => __('Fast draft · Quick tests'),        'svg' => '<rect width="20" height="14" x="2" y="5" rx="2"/>'],
                                '720p'  => ['label' => __('720p HD'),        'desc' => __('Balanced · Social-ready'),         'svg' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M7 20h10"/>'],
                                '1080p' => ['label' => __('1080p Full HD'),  'desc' => __('Crisp · Premium feel'),            'svg' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M7 20h10"/><path d="M12 16v4"/>'],
                                '4k'    => ['label' => __('4K Ultra HD'),    'desc' => __('Maximum detail · Big screens'),    'svg' => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="m9 9 3 3 3-3"/>'],
                            ];
                        @endphp
                        <div x-data="{ resolutionMap: {{ \Illuminate\Support\Js::from($resolutionMap) }} }"
                             x-show="(resolutionMap[model] || []).length > 0" x-cloak
                             class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-indigo-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                        <flux:icon.sparkles class="size-4 text-indigo-400" />
                                    </span>
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Quality') }}</h3>
                                </div>
                            </div>
                            <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Higher resolutions cost more credits per second') }}</p>
                            <div class="space-y-2">
                                @foreach($resMeta as $tier => $meta)
                                    <button type="button"
                                            x-show="(resolutionMap[model] || []).some(r => r.tier === '{{ $tier }}')"
                                            x-cloak
                                            @click="resolution = '{{ $tier }}'; $wire.set('selectedResolution', '{{ $tier }}')"
                                            class="w-full relative flex items-center gap-3 p-4 rounded-xl border text-left transition-all hover:shadow-md"
                                            :class="resolution === '{{ $tier }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                        <span class="inline-flex w-9 h-9 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                              :class="resolution === '{{ $tier }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $meta['svg'] !!}</svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold" :class="resolution === '{{ $tier }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'">{{ $meta['label'] }}</div>
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $meta['desc'] }}</div>
                                        </div>
                                        <span class="text-[10px] font-mono tabular-nums font-bold text-zinc-500 dark:text-zinc-400 shrink-0"
                                              x-text="(() => { const r = (resolutionMap[model] || []).find(r => r.tier === '{{ $tier }}'); return r ? r.credit_cost + ' {{ __('cr') }}/{{ __('sec') }}' : ''; })()"></span>
                                        <div x-show="resolution === '{{ $tier }}'" class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shrink-0"><flux:icon.check class="size-3 text-white" /></div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- AI Engine --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start justify-between gap-3 mb-4 flex-wrap">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                        <flux:icon.cpu-chip class="size-4 text-emerald-400" />
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('AI Engine') }}</h3>
                                        <p class="text-[11px] text-zinc-400">{{ __('Pick the video model — credit cost and clip length depend on your choice.') }}</p>
                                    </div>
                                </div>
                                @if(count($providers) > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ count($providers) }} {{ trans_choice('engine|engines', count($providers)) }} {{ __('enabled') }}
                                    </span>
                                @endif
                            </div>

                            @if(count($providers) > 0)
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($providers as $provider)
                                        @php
                                            $tier = $provider['tier'] ?? null;
                                            $tierMeta = match($tier) {
                                                'premium' => ['label' => __('Premium'), 'classes' => 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-950/30 dark:border-indigo-900/40 dark:text-indigo-300'],
                                                'mid'     => ['label' => __('Mid'),     'classes' => 'bg-zinc-100 border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300'],
                                                'budget'  => ['label' => __('Budget'),  'classes' => 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300'],
                                                default   => null,
                                            };
                                            $maxDuration = $provider['max_duration'] ?? null;
                                            $cost = $provider['credit_cost'] ?? null;
                                        @endphp
                                        <button type="button" @click="model = '{{ $provider['key'] }}'; $wire.set('selectedModel', '{{ $provider['key'] }}')"
                                                class="group/eng relative rounded-xl border p-4 text-left transition-all hover:shadow-md hover:-translate-y-0.5"
                                                :class="model === '{{ $provider['key'] }}' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/30 ring-1 ring-indigo-500/20 shadow-sm' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-neutral-600'">

                                            {{-- Top row: icon tile + name + tier/recommended chips --}}
                                            <div class="flex items-start gap-3">
                                                <span class="inline-flex w-9 h-9 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                                      :class="model === '{{ $provider['key'] }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                                    @if(! empty($provider['icon_svg']))
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $provider['icon_svg'] !!}</svg>
                                                    @else
                                                        <flux:icon.cpu-chip class="size-4" />
                                                    @endif
                                                </span>
                                                <div class="min-w-0 flex-1 pr-7">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-sm font-bold truncate" :class="model === '{{ $provider['key'] }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'">{{ $provider['label'] }}</span>
                                                        @if($provider['recommended'] ?? false)
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full bg-amber-50 border border-amber-200 text-[9px] font-bold uppercase tracking-wider text-amber-700 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300">
                                                                <span class="w-1 h-1 rounded-full bg-amber-500"></span> {{ __('Recommended') }}
                                                            </span>
                                                        @endif
                                                        @if($tierMeta)
                                                            <span class="inline-flex items-center px-1.5 py-px rounded-full border text-[9px] font-bold uppercase tracking-wider {{ $tierMeta['classes'] }}">{{ $tierMeta['label'] }}</span>
                                                        @endif
                                                        @if($provider['audio'] ?? false)
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full bg-zinc-100 border border-zinc-200 text-[9px] font-bold uppercase tracking-wider text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300" title="{{ __('Native synchronised audio + dialogue') }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                                                                {{ __('Audio') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if(! empty($provider['sub_label']))
                                                        <div class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5 truncate">{{ $provider['sub_label'] }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Description --}}
                                            @if(! empty($provider['description']))
                                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-2 leading-relaxed">{{ $provider['description'] }}</p>
                                            @endif

                                            {{-- Tag pills + cost/duration footer --}}
                                            <div class="mt-3 flex items-center justify-between gap-2 flex-wrap">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach(($provider['tags'] ?? []) as $tag)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-zinc-100 text-[9px] font-semibold text-zinc-600 dark:bg-(--default-element-bg-color) dark:text-zinc-400">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                                <div class="flex items-center gap-2 text-[10px] font-mono tabular-nums text-zinc-500 dark:text-zinc-400">
                                                    @if($maxDuration)
                                                        <span class="inline-flex items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                            {{ __('up to') }} {{ $maxDuration }}s
                                                        </span>
                                                    @endif
                                                    @if($cost)
                                                        <span class="inline-flex items-center gap-1 font-bold text-zinc-700 dark:text-zinc-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                                            {{ $cost }} {{ __('cr') }}/{{ __('sec') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div x-show="model === '{{ $provider['key'] }}'" class="absolute top-3 right-3 w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shadow-sm"><flux:icon.check class="size-3 text-white" /></div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-900/40 dark:bg-amber-950/20">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex w-8 h-8 shrink-0 items-center justify-center rounded-lg bg-white border border-amber-200 text-amber-600 dark:bg-(--default-element-bg-color) dark:border-amber-800/40">
                                            <flux:icon.exclamation-triangle class="size-4" />
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-amber-900 dark:text-amber-200">{{ __('No video AI models configured') }}</p>
                                            <p class="text-[11px] text-amber-800 dark:text-amber-300/80 mt-0.5">{{ __('Ask your administrator to enable a video provider.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" @click="step = 1" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-zinc-700 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition shadow-xs dark:bg-(--default-element-bg-color) dark:text-zinc-300 dark:border-white/8 dark:hover:border-neutral-600 dark:hover:bg-white/5">
                        <flux:icon.arrow-left class="size-4" /> {{ __('Back') }}
                    </button>
                    <button type="button" @click="if(canProceed) step = 3"
                            :disabled="!canProceed"
                            class="group/cta relative flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                            style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                              style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                        <span class="relative flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                <flux:icon.arrow-right class="size-4 group-hover/cta:translate-x-0.5 transition-transform" />
                            </span>
                            <span>{{ __('Next: Creative Direction') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 3: Creative Direction                   --}}
            {{-- ============================================ --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Camera & Motion --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-cyan-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.video-camera class="size-4 text-cyan-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Camera & Motion') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('How should the camera move?') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                // Lucide-style monochrome line icons
                                $motions = [
                                    'zoom_in' => [
                                        'label' => __('Zoom In'), 'desc' => __('Dramatic focus pull'),
                                        'svg' => '<circle cx="11" cy="11" r="8"/><line x1="21" x2="16.65" y1="21" y2="16.65"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/>',
                                    ],
                                    'orbit' => [
                                        'label' => __('Orbit / 360°'), 'desc' => __('Rotating around subject'),
                                        'svg' => '<path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1.06 6.7 2.82"/><path d="M21 3v6h-6"/>',
                                    ],
                                    'dolly' => [
                                        'label' => __('Dolly / Track'), 'desc' => __('Smooth forward motion'),
                                        'svg' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
                                    ],
                                    'smooth_pan' => [
                                        'label' => __('Smooth Pan'), 'desc' => __('Horizontal sweep'),
                                        'svg' => '<path d="M8 3 4 7l4 4"/><path d="M4 7h16"/><path d="m16 21 4-4-4-4"/><path d="M20 17H4"/>',
                                    ],
                                    'handheld' => [
                                        'label' => __('Handheld'), 'desc' => __('Authentic & organic'),
                                        'svg' => '<path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/><path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>',
                                    ],
                                    'static' => [
                                        'label' => __('Static / Locked'), 'desc' => __('Tripod stability'),
                                        'svg' => '<path d="M12 2v8"/><path d="M12 14v8"/><path d="m4 8 8 6 8-6"/><path d="m4 22 8-8 8 8"/>',
                                    ],
                                    'aerial' => [
                                        'label' => __('Aerial / Drone'), 'desc' => __('Bird\'s eye view'),
                                        'svg' => '<rect width="6" height="6" x="9" y="9" rx="1"/><circle cx="4" cy="4" r="2"/><circle cx="20" cy="4" r="2"/><circle cx="4" cy="20" r="2"/><circle cx="20" cy="20" r="2"/><path d="M6 4h4"/><path d="M14 4h4"/><path d="M6 20h4"/><path d="M14 20h4"/><path d="M4 6v4"/><path d="M4 14v4"/><path d="M20 6v4"/><path d="M20 14v4"/>',
                                    ],
                                    'quick_cuts' => [
                                        'label' => __('Quick Cuts'), 'desc' => __('Fast-paced montage'),
                                        'svg' => '<circle cx="6" cy="6" r="3"/><path d="m9 9 12 12"/><path d="m9 15 12-12"/><circle cx="6" cy="18" r="3"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($motions as $key => $m)
                                <button @click="motionType = '{{ $key }}'" class="group relative flex items-start gap-2.5 p-3 rounded-xl border text-left transition-all hover:shadow-md" :class="motionType === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="mt-0.5 inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                          :class="motionType === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $m['svg'] !!}</svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold" :class="motionType === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $m['label'] }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $m['desc'] }}</div>
                                    </div>
                                    <div x-show="motionType === '{{ $key }}'" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Visual Style --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-pink-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.paint-brush class="size-4 text-pink-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Visual Style') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Art direction for your video') }}</p>
                        <div class="grid grid-cols-3 gap-2">
                            @php
                                // Lucide-style monochrome line icons
                                $styles = [
                                    'cinematic' => [
                                        'label' => __('Cinematic'),
                                        'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 3v18"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>',
                                    ],
                                    'motion_graphics' => [
                                        'label' => __('Motion Graphics'),
                                        'svg' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
                                    ],
                                    'raw_authentic' => [
                                        'label' => __('Raw / UGC'),
                                        'svg' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11h-6"/><path d="M19 8v6"/>',
                                    ],
                                    '3d_animation' => [
                                        'label' => __('3D Animation'),
                                        'svg' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
                                    ],
                                    'dynamic_cuts' => [
                                        'label' => __('Dynamic Cuts'),
                                        'svg' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                                    ],
                                    'stop_motion' => [
                                        'label' => __('Stop Motion'),
                                        'svg' => '<rect width="20" height="14" x="2" y="6" rx="2"/><path d="M2 10h20"/><path d="M7 14h.01"/><path d="M11 14h.01"/><path d="M15 14h.01"/>',
                                    ],
                                    'neon_glow' => [
                                        'label' => __('Neon / Glow'),
                                        'svg' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
                                    ],
                                    'minimal_clean' => [
                                        'label' => __('Minimal'),
                                        'svg' => '<line x1="5" x2="19" y1="12" y2="12"/>',
                                    ],
                                    'retro_vintage' => [
                                        'label' => __('Retro'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><path d="M2 12h4"/><path d="M18 12h4"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($styles as $key => $s)
                                <button @click="videoStyle = '{{ $key }}'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all hover:shadow-md" :class="videoStyle === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition-colors"
                                          :class="videoStyle === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['svg'] !!}</svg>
                                    </span>
                                    <span class="text-[10px] font-semibold text-center" :class="videoStyle === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $s['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mood --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color) lg:col-span-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.musical-note class="size-4 text-amber-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Mood & Energy') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('The emotional feel of your video') }}</p>
                        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                            @php
                                // Lucide-style monochrome line icons
                                $moods = [
                                    'energetic' => [
                                        'label' => __('Energetic'),
                                        'svg' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                                    ],
                                    'calm' => [
                                        'label' => __('Calm'),
                                        'svg' => '<path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/><path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>',
                                    ],
                                    'luxurious' => [
                                        'label' => __('Luxurious'),
                                        'svg' => '<path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/>',
                                    ],
                                    'urgent' => [
                                        'label' => __('Urgent'),
                                        'svg' => '<path d="M8.56 3.69a9 9 0 0 0-2.92 1.95"/><path d="M3.69 8.56A9 9 0 0 0 3 12"/><path d="M3.69 15.44a9 9 0 0 0 1.95 2.92"/><path d="M8.56 20.31A9 9 0 0 0 12 21"/><path d="M15.44 20.31a9 9 0 0 0 2.92-1.95"/><path d="M20.31 15.44A9 9 0 0 0 21 12"/><path d="M20.31 8.56a9 9 0 0 0-1.95-2.92"/><path d="M15.44 3.69A9 9 0 0 0 12 3"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
                                    ],
                                    'playful' => [
                                        'label' => __('Playful'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
                                    ],
                                    'professional' => [
                                        'label' => __('Professional'),
                                        'svg' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                    ],
                                    'inspirational' => [
                                        'label' => __('Inspirational'),
                                        'svg' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
                                    ],
                                    'mysterious' => [
                                        'label' => __('Mysterious'),
                                        'svg' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9z"/>',
                                    ],
                                    'nostalgic' => [
                                        'label' => __('Nostalgic'),
                                        'svg' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
                                    ],
                                    'futuristic' => [
                                        'label' => __('Futuristic'),
                                        'svg' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
                                    ],
                                    'warm' => [
                                        'label' => __('Warm'),
                                        'svg' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
                                    ],
                                    'dramatic' => [
                                        'label' => __('Dramatic'),
                                        'svg' => '<path d="M21 12c.552 0 1.005-.449.95-.998a10 10 0 0 0-8.953-8.951c-.55-.055-.998.398-.998.95v8a1 1 0 0 0 1 1z"/><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($moods as $key => $md)
                                <button @click="mood = '{{ $key }}'" class="flex flex-col items-center gap-1.5 p-2.5 rounded-xl border transition-all hover:shadow-md" :class="mood === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-lg border transition-colors"
                                          :class="mood === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $md['svg'] !!}</svg>
                                    </span>
                                    <span class="text-[9px] font-semibold text-center" :class="mood === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $md['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" @click="step = 2" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-zinc-700 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition shadow-xs dark:bg-(--default-element-bg-color) dark:text-zinc-300 dark:border-white/8 dark:hover:border-neutral-600 dark:hover:bg-white/5">
                        <flux:icon.arrow-left class="size-4" /> {{ __('Back') }}
                    </button>
                    <button type="button" @click="step = 4"
                            class="group/cta relative flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden"
                            style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                              style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                        <span class="relative flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                <flux:icon.arrow-right class="size-4 group-hover/cta:translate-x-0.5 transition-transform" />
                            </span>
                            <span>{{ __('Next: Scene Description') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 4: Scene Description & Copy             --}}
            {{-- ============================================ --}}
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-3 space-y-5">

                        {{-- Scene Prompt --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                        <flux:icon.sparkles class="size-4 text-amber-400" />
                                    </span>
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Scene Description') }}</h3>
                                    <span class="hidden sm:inline-flex items-center gap-1 px-1.5 py-px rounded-full bg-rose-50 border border-rose-200 text-[9px] font-bold uppercase tracking-wider text-rose-700 dark:bg-rose-950/30 dark:border-rose-900/40 dark:text-rose-300 ml-1">{{ __('Required') }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button"
                                            x-on:click="$dispatch('open-prompt-library', { context: 'video' }); $dispatch('modal-show', { name: 'prompt-library' })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-zinc-900 hover:bg-indigo-600 shadow-sm transition active:scale-95 dark:bg-neutral-950 dark:hover:bg-neutral-900 dark:border dark:border-white/8">
                                        <flux:icon.book-open class="size-3.5" />
                                        {{ __('Prompt Library') }}
                                    </button>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-md tabular-nums {{ strlen($prompt) > 1800 ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-zinc-100 text-zinc-500 dark:bg-neutral-700 dark:text-zinc-400' }}">{{ strlen($prompt) }}/2000</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Describe what happens in the video — the scene, subjects, actions, transitions') }}</p>

                            <textarea wire:model.live.debounce.500ms="prompt" rows="6" maxlength="2000"
                                      placeholder="{{ __("A sleek smartphone floating in mid-air, slowly rotating to reveal its screen. Soft particles of light drift around it. The camera pulls back to reveal a minimalist desk setup. Text fades in: 'The Future is Here'. Ends with logo and CTA button.") }}"
                                      aria-label="{{ __('Video scene description') }}"
                                      class="w-full px-3 py-2.5 rounded-lg border border-zinc-200 bg-white text-sm leading-relaxed text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition resize-y min-h-[140px] dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500"></textarea>
                            <flux:error name="prompt" />

                            {{-- Toolbar — quick scene ideas --}}
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Scene ideas') }}</span>
                                @php
                                    $sceneSuggestions = match($videoGoal) {
                                        'product_showcase' => [__('Product rotating on reflective surface with dramatic lighting'), __('Unboxing sequence with close-up detail shots'), __('Product in lifestyle setting with depth of field')],
                                        'promo_sale' => [__('Bold price tag slamming into frame with particle burst'), __('Countdown timer with products flying past'), __('Split screen before/after with price comparison')],
                                        'ugc_style' => [__('Person excitedly showing product to camera, natural setting'), __('POV unboxing with genuine reaction'), __('Day-in-the-life using the product')],
                                        'brand_story' => [__('Cinematic landscape opening, transition to brand in action'), __('Montage of people using product in different settings'), __('Emotional journey from problem to solution')],
                                        default => [__('Product hero shot with cinematic lighting'), __('Dynamic text animation with brand colors'), __('Lifestyle scene transitioning to product close-up'), __('Abstract motion graphics with logo reveal')],
                                    };
                                @endphp
                                @foreach($sceneSuggestions as $sug)
                                    <button type="button" wire:click="$set('prompt', '{{ addslashes($sug) }}')" class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600 hover:bg-indigo-100 hover:text-indigo-700 transition dark:bg-neutral-700 dark:text-zinc-400 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-300">+ {{ $sug }}</button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Text Overlay & CTA --}}
                        @php
                            // Map of provider key → text_rendering quality.
                            // Drives the auto-default for the rendering toggle:
                            //   'native' → AI renders the text (toggle off by default)
                            //   'weak'   → FFmpeg burns the text (toggle on by default)
                            $textRenderingMap = collect($providers)
                                ->mapWithKeys(fn ($p) => [$p['key'] => $p['text_rendering'] ?? 'weak'])
                                ->toArray();
                        @endphp
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)"
                             x-data="{
                                textRenderingMap: {{ \Illuminate\Support\Js::from($textRenderingMap) }},
                                forceOverlay: @entangle('forceOverlay'),
                                get engineNative() { return (this.textRenderingMap[this.model] || 'weak') === 'native'; },
                                get overlayActive() {
                                    if (this.forceOverlay === true)  return true;
                                    if (this.forceOverlay === false) return false;
                                    return !this.engineNative;
                                },
                                setOverlay(val) { this.forceOverlay = val; }
                             }">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-blue-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    <flux:icon.pencil-square class="size-4 text-blue-400" />
                                </span>
                                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Text & CTA') }}</h3>
                                <span class="text-[10px] text-zinc-400 ml-1">{{ __('(optional — overlays in your video)') }}</span>
                            </div>
                            <div class="space-y-4">
                                {{-- Overlay --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="overlayText" class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Text Overlay') }}</label>
                                        <span class="text-[10px] text-zinc-400 tabular-nums">{{ Str::length($overlayText ?? '') }}/100</span>
                                    </div>
                                    <input id="overlayText" type="text" wire:model.blur="overlayText" maxlength="100"
                                           placeholder="{{ __('e.g. Summer Collection 2026 — Now Live') }}"
                                           class="w-full h-10 px-3 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500" />
                                    <p class="mt-1 text-[10px] text-zinc-400">{{ __('Main text that appears on screen') }}</p>
                                </div>

                                {{-- CTA --}}
                                <div>
                                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">{{ __('End-screen CTA') }}</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 mb-2">
                                        @foreach([__('Shop Now'), __('Learn More'), __('Download'), __('Subscribe'), __('Watch More'), __('Swipe Up'), __('Get Offer'), __('Try Free')] as $cta)
                                            <button type="button" @click="ctaText = @js($cta)"
                                                    class="text-[11px] py-1.5 px-2 rounded-lg border transition-all text-center"
                                                    :class="ctaText === @js($cta) ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/30 dark:text-indigo-300' : 'border-zinc-200 bg-white text-zinc-600 hover:border-indigo-200 hover:text-indigo-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:border-indigo-700/40'">{{ $cta }}</button>
                                        @endforeach
                                    </div>
                                    <input type="text" x-model="ctaText" maxlength="50"
                                           placeholder="{{ __('Or type your own CTA...') }}"
                                           class="w-full h-10 px-3 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500" />
                                </div>

                                {{-- Rendering mode toggle — only shown when there's text to render --}}
                                <div x-show="(overlayText || ctaText || '').trim() !== ''" x-cloak x-transition
                                     class="rounded-xl border border-zinc-200 bg-zinc-50/60 p-3 dark:border-white/8 dark:bg-neutral-900/40">
                                    <div class="flex items-start justify-between gap-3 flex-wrap">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <label class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Text rendering') }}</label>
                                                <span x-show="forceOverlay === null" x-cloak class="inline-flex items-center px-1.5 py-px rounded-full bg-zinc-200/70 text-[8px] font-bold uppercase tracking-wider text-zinc-600 dark:bg-neutral-700 dark:text-zinc-300">{{ __('Auto') }}</span>
                                            </div>
                                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                <span x-show="overlayActive">{{ __('Pixel-perfect text burned in via FFmpeg post-processing.') }}</span>
                                                <span x-show="!overlayActive">{{ __('Text rendered by the AI engine — integrated into the scene.') }}</span>
                                            </p>
                                        </div>

                                        {{-- Segmented control --}}
                                        <div class="inline-flex items-center rounded-lg border border-zinc-200 bg-white p-0.5 dark:border-white/8 dark:bg-(--default-element-light-bg-color) shrink-0">
                                            <button type="button" @click="setOverlay(false)"
                                                    class="px-2.5 h-7 rounded-md text-[10px] font-semibold transition-colors"
                                                    :class="!overlayActive ? 'bg-indigo-600 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'">
                                                {{ __('AI rendered') }}
                                            </button>
                                            <button type="button" @click="setOverlay(true)"
                                                    class="px-2.5 h-7 rounded-md text-[10px] font-semibold transition-colors"
                                                    :class="overlayActive ? 'bg-indigo-600 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white'">
                                                {{ __('Post-processed') }}
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Auto reset link, only shown after the user manually overrode --}}
                                    <button type="button" x-show="forceOverlay !== null" x-cloak
                                            @click="setOverlay(null)"
                                            class="mt-2 text-[10px] text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200 font-semibold">
                                        ↺ {{ __('Reset to engine default') }}
                                    </button>

                                    {{-- Hint when AI rendering picked but engine is weak at it --}}
                                    <p x-show="forceOverlay === false && !engineNative" x-cloak
                                       class="mt-2 text-[10px] text-amber-600 dark:text-amber-300 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                                        {{ __("This engine struggles with in-image text. The AI may produce garbled letters. Switch back to post-processing for reliable results.") }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Reference Image --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-sky-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    <flux:icon.photo class="size-4 text-sky-400" />
                                </span>
                                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('First Frame / Reference') }}</h3>
                                <span class="text-[10px] text-zinc-400">{{ __('(optional)') }}</span>
                            </div>

                            <label wire:loading.class="opacity-60" wire:target="referenceImage"
                                   class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded-xl cursor-pointer transition-colors {{ $referenceImage ? 'border-sky-400 bg-sky-50 dark:bg-sky-950/20' : 'border-zinc-300 hover:border-sky-400 bg-zinc-50/50 dark:border-neutral-600 dark:bg-(--default-element-bg-color)' }}">
                                <span wire:loading.flex wire:target="referenceImage" class="flex items-center gap-2 text-xs text-zinc-500">
                                    <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                        <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    {{ __('Uploading...') }}
                                </span>
                                <span wire:loading.remove.flex wire:target="referenceImage" class="flex flex-col items-center justify-center">
                                    @if($referenceImage)
                                        <flux:icon.check-circle class="size-6 text-sky-500 mb-1" />
                                        <p class="text-xs text-sky-600 font-semibold">{{ __('Image uploaded — will be used as first frame') }}</p>
                                        <p class="text-[10px] text-zinc-500 mt-0.5">{{ __('Click to replace') }}</p>
                                    @else
                                        <flux:icon.cloud-arrow-up class="size-6 text-zinc-400 mb-1" />
                                        <p class="text-[12px] text-zinc-600 font-semibold dark:text-zinc-300">{{ __('Drop an image or click to upload') }}</p>
                                        <p class="text-[10px] text-zinc-400 mt-0.5">{{ __('PNG, JPG or WebP — used as the opening frame or style guide') }}</p>
                                    @endif
                                </span>
                                <input type="file" wire:model="referenceImage" accept="image/*" class="hidden" aria-label="{{ __('Upload reference image') }}" />
                            </label>
                        </div>
                    </div>

                    {{-- Right column: live brief + canvas preview --}}
                    <div class="lg:col-span-2 space-y-5">
                        <div class="lg:sticky lg:top-4 space-y-5">
                            @php
                                $presetData = collect($presets)->firstWhere('slug', $selectedPreset);
                                $pw = $presetData['width'] ?? 1920; $ph = $presetData['height'] ?? 1080;
                                $aspectPct = number_format(($ph / max($pw, 1)) * 100, 4, '.', '');
                                if ((float) $aspectPct > 150) { $aspectPct = '150'; }
                            @endphp

                            {{-- Canvas preview --}}
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                <div class="px-5 py-3 flex items-center justify-between border-b border-zinc-100 dark:border-white/8">
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                                        <flux:icon.eye class="size-4 text-indigo-500" />
                                        {{ __('Live preview') }}
                                    </h3>
                                    <span class="text-[10px] text-zinc-400 font-mono tabular-nums">{{ $pw }}×{{ $ph }}</span>
                                </div>
                                <div class="relative bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.06),transparent_60%)] dark:bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.12),transparent_60%)] p-5">
                                    <div class="relative w-full mx-auto rounded-xl border-2 border-dashed border-indigo-300 dark:border-indigo-700 overflow-hidden shadow-inner"
                                         style="padding-bottom: {{ $aspectPct }}%; background: linear-gradient(135deg, rgba(79,70,229,0.12), rgba(15,23,42,0.12));">
                                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center gap-2">
                                            <flux:icon.play class="size-7 text-indigo-500/70" />
                                            <span class="text-[9px] font-mono tabular-nums text-zinc-500 dark:text-zinc-400">{{ $pw }} × {{ $ph }}</span>
                                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold" x-text="duration + 's'"></span>
                                            <span x-show="(ctaText || '').trim() !== ''" x-cloak
                                                  x-text="(ctaText || '').slice(0, 40)"
                                                  class="mt-1 inline-flex items-center gap-1 px-3 py-1 rounded-md text-[10px] font-bold text-white shadow-sm" style="background-color: #4F46E5;"></span>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-[10px] text-center text-zinc-400">{{ __('Frame mock-up — your AI engine renders the final clip.') }}</p>
                                </div>
                            </div>

                            {{-- Video Brief Summary --}}
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <div class="px-5 py-3 flex items-center gap-2 border-b border-zinc-100 dark:border-white/8">
                                    <flux:icon.clipboard-document-list class="size-4 text-indigo-500" />
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Video Brief') }}</h3>
                                </div>
                                <div class="p-4 space-y-1 text-[12px]"
                                     x-data="{ pretty(v) { return v ? v.replace(/_|-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'; } }">
                                    @php
                                        $rows = [
                                            ['label' => __('Goal'),      'value' => 'pretty(goal)',      'svg' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'],
                                            ['label' => __('Platform'),  'value' => 'pretty(platform)',  'svg' => '<rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>'],
                                            ['label' => __('Format'),    'value' => 'pretty(preset)',    'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/>'],
                                            ['label' => __('Length'),    'value' => 'duration + " s"',   'svg' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
                                            ['label' => __('Motion'),    'value' => 'pretty(motionType)','svg' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>'],
                                            ['label' => __('Style'),     'value' => 'pretty(videoStyle)','svg' => '<path d="M9.06 11.9 12 14l3.9-2.1L18 9l-3-3-3 2-3-2-3 3z"/><path d="M12 14v6"/>'],
                                            ['label' => __('Mood'),      'value' => 'pretty(mood)',      'svg' => '<path d="M3 11h3l4-7v18l-4-7H3z"/><path d="M14 6.5a5 5 0 0 1 0 11"/>'],
                                            ['label' => __('AI Engine'), 'value' => 'model || "—"',      'svg' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2"/><path d="M15 2v2"/><path d="M9 20v2"/><path d="M15 20v2"/><path d="M2 9h2"/><path d="M2 15h2"/><path d="M20 9h2"/><path d="M20 15h2"/>'],
                                        ];
                                    @endphp
                                    @foreach($rows as $row)
                                        <div class="flex items-center gap-3 py-2 border-b border-zinc-100 last:border-0 dark:border-white/6">
                                            <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 dark:bg-(--default-element-bg-color) dark:text-zinc-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $row['svg'] !!}</svg>
                                            </span>
                                            <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 w-20 shrink-0">{{ $row['label'] }}</span>
                                            <span class="text-zinc-800 dark:text-zinc-200 font-medium truncate flex-1" x-text="{{ $row['value'] }}"></span>
                                        </div>
                                    @endforeach

                                    {{-- CTA --}}
                                    <div class="flex items-center gap-3 py-2 border-b border-zinc-100 dark:border-white/6">
                                        <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 dark:bg-(--default-element-bg-color) dark:text-zinc-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </span>
                                        <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 w-20 shrink-0">{{ __('CTA') }}</span>
                                        <span class="flex-1">
                                            <span x-show="(ctaText || '').trim()" x-cloak class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[11px] font-semibold dark:bg-indigo-950/40 dark:text-indigo-300" x-text="ctaText"></span>
                                            <span x-show="!(ctaText || '').trim()" class="text-zinc-400">—</span>
                                        </span>
                                    </div>

                                    {{-- Scene preview --}}
                                    <div class="flex items-start gap-3 py-2">
                                        <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 mt-0.5 dark:bg-(--default-element-bg-color) dark:text-zinc-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M21 3 13 11"/><path d="M8 21H3v-5"/><path d="M3 21l8-8"/></svg>
                                        </span>
                                        <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 w-20 shrink-0 mt-0.5">{{ __('Scene') }}</span>
                                        <span class="text-zinc-700 dark:text-zinc-300 leading-relaxed flex-1 break-words">{{ $prompt ? Str::limit($prompt, 140) : __('Add a scene description on the left to see it here.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" @click="step = 3" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-zinc-700 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition shadow-xs dark:bg-(--default-element-bg-color) dark:text-zinc-300 dark:border-white/8 dark:hover:border-neutral-600 dark:hover:bg-white/5">
                        <flux:icon.arrow-left class="size-4" /> {{ __('Back') }}
                    </button>
                    <button type="button" @click="if(canProceed) step = 5"
                            :disabled="!canProceed"
                            class="group/cta relative flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                            style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                              style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                        <span class="relative flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                <flux:icon.arrow-right class="size-4 group-hover/cta:translate-x-0.5 transition-transform" />
                            </span>
                            <span>{{ __('Next: Review & Generate') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 5: Review & Generate                    --}}
            {{-- ============================================ --}}
            <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

                @if($this->latestPending)
                    {{-- GENERATING STATE --}}
                    @php
                        $startedAt = $this->latestPending->updated_at ?? $this->latestPending->created_at;
                        $elapsedSeconds = (int) max(0, now()->diffInSeconds($startedAt));
                    @endphp

                    <div x-data="{
                            startedAt: {{ $startedAt->getTimestamp() }} * 1000,
                            elapsed: {{ $elapsedSeconds }},
                            tick: null,
                            init() {
                                this.tick = setInterval(() => {
                                    this.elapsed = Math.max(0, Math.round((Date.now() - this.startedAt) / 1000));
                                }, 1000);
                            },
                            destroy() { if (this.tick) clearInterval(this.tick); },
                            get progressPercent() {
                                if (this.elapsed <= 0) return 6;
                                if (this.elapsed >= 240) return 92;
                                return Math.min(92, 6 + (this.elapsed / 240) * 86);
                            },
                            mmss() {
                                const m = Math.floor(this.elapsed / 60);
                                const s = this.elapsed % 60;
                                return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                            }
                         }"
                         wire:poll.5s
                         class="max-w-2xl mx-auto">
                        <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-10 text-center">

                            {{-- Spinner — brand cool gradient core --}}
                            <div class="relative w-24 h-24 mx-auto mb-6">
                                <div class="absolute inset-0 rounded-full border-4 border-indigo-100 dark:border-indigo-900/40"></div>
                                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-indigo-600 animate-spin" style="animation-duration: 1.5s"></div>
                                <div class="absolute inset-1 rounded-full border-4 border-transparent border-b-amber-500 animate-spin" style="animation-duration: 2.5s; animation-direction: reverse"></div>
                                <div class="absolute inset-4 rounded-full flex items-center justify-center shadow-lg shadow-indigo-500/30"
                                     style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                    <flux:icon.film class="size-7 text-white animate-pulse" />
                                </div>
                            </div>

                            <h3 class="text-xl font-extrabold text-zinc-800 dark:text-zinc-200 mb-1">{{ __('AI is rendering your video') }}</h3>
                            <p class="text-sm text-zinc-500 mb-1">{{ __('Video generation takes longer than images — hang tight.') }}</p>

                            {{-- Live elapsed timer --}}
                            <p class="text-[11px] text-zinc-400 font-mono tabular-nums mb-6"><span x-text="mmss()"></span> {{ __('elapsed · typically 1-3 minutes') }}</p>

                            {{-- Pipeline checklist --}}
                            <div class="max-w-xs mx-auto space-y-2 text-left mb-6">
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Scene brief analyzed') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Prompt optimized for AI model') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-indigo-500 flex items-center justify-center animate-pulse"><div class="w-1.5 h-1.5 rounded-full bg-white"></div></div>
                                    <span class="text-zinc-800 dark:text-zinc-200 font-medium">{{ __('Rendering frames...') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-zinc-200 dark:bg-neutral-700"></div>
                                    <span class="text-zinc-400">{{ __('Encoding & post-processing') }}</span>
                                </div>
                            </div>

                            {{-- Brand cool progress bar with animated stripes --}}
                            <div class="w-full bg-zinc-100 dark:bg-neutral-700 rounded-full h-1.5 overflow-hidden relative">
                                <div class="h-full rounded-full transition-[width] duration-700 ease-out"
                                     :style="`width: ${progressPercent}%; background: linear-gradient(120deg, #4F46E5, #0F172A);`"></div>
                                <div class="absolute inset-0 opacity-30 mix-blend-overlay pointer-events-none"
                                     style="background: repeating-linear-gradient(45deg, rgba(255,255,255,0.4) 0 6px, transparent 6px 12px); animation: db-stripe 1.2s linear infinite;"></div>
                            </div>
                            <style>@keyframes db-stripe { from { background-position: 0 0; } to { background-position: 24px 0; } }</style>

                            {{-- Escape hatch — appears after ~3 min so a user is never stranded --}}
                            <div x-show="elapsed >= 180" x-cloak x-transition class="mt-6 pt-6 border-t border-zinc-100 dark:border-white/8">
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-left dark:border-amber-900/40 dark:bg-amber-950/20">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex w-8 h-8 shrink-0 items-center justify-center rounded-lg bg-white border border-amber-200 text-amber-600 dark:bg-(--default-element-bg-color) dark:border-amber-800/40">
                                            <flux:icon.exclamation-triangle class="size-4" />
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-amber-900 dark:text-amber-200">{{ __('Taking longer than expected') }}</p>
                                            <p class="text-[11px] text-amber-800 dark:text-amber-300/80 mt-0.5">{{ __('Video rendering can take a few minutes. This page updates automatically when it is ready — you can safely leave and come back. If it never finishes, you can cancel and try again.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex justify-end">
                                        <button type="button" wire:click="cancelStuckGeneration"
                                                class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg text-[11px] font-semibold text-zinc-700 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition dark:bg-(--default-element-bg-color) dark:text-zinc-300 dark:border-white/8 dark:hover:border-neutral-600 dark:hover:bg-white/5">
                                            <flux:icon.x-mark class="size-3.5" /> {{ __('Cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif($this->latestFailed)
                    {{-- FAILED STATE --}}
                    <div class="max-w-2xl mx-auto">
                        <div class="rounded-2xl border border-rose-200 bg-white dark:border-rose-900/40 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                            <div class="px-6 py-3 flex items-center gap-2 border-b border-rose-200 dark:border-rose-900/40 bg-rose-50/60 dark:bg-rose-950/20">
                                <flux:icon.exclamation-triangle class="size-5 text-rose-600 dark:text-rose-400" />
                                <h3 class="text-sm font-bold text-rose-700 dark:text-rose-300">{{ __('Video generation failed') }}</h3>
                                <span class="ml-auto text-[10px] text-rose-500/70">{{ $this->latestFailed->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="p-5 space-y-4">
                                @if($this->latestFailed->error_message)
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-[12px] text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300 break-words">
                                        {{ $this->latestFailed->error_message }}
                                    </div>
                                @endif
                                <p class="text-[12px] text-zinc-500 dark:text-zinc-400">
                                    {{ __('Edit your brief and try again.') }}
                                </p>
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <button type="button" @click="step = 4" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-lg text-[12px] font-semibold text-zinc-700 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 transition dark:bg-(--default-element-bg-color) dark:text-zinc-300 dark:border-white/8 dark:hover:border-neutral-600 dark:hover:bg-white/5">
                                        <flux:icon.arrow-left class="size-4" /> {{ __('Edit brief') }}
                                    </button>
                                    <button type="button" wire:click="generate" class="inline-flex items-center gap-1.5 px-3 h-9 rounded-lg text-[12px] font-semibold text-white transition" style="background-color: #4F46E5;">
                                        <flux:icon.arrow-path class="size-4" /> {{ __('Try again') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    @php $latestVideo = $this->currentResult; @endphp

                    @if($latestVideo && $latestVideo->file_path)
                        {{-- RESULT STATE --}}
                        <div class="max-w-4xl mx-auto">
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                <div class="px-6 py-3 flex items-center justify-between" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                    <div class="flex items-center gap-2 text-white"><flux:icon.check-circle class="size-5" /><span class="text-sm font-bold">{{ __('Your video ad is ready!') }}</span></div>
                                    <span class="text-[10px] text-white/70">{{ $latestVideo->duration }}s · {{ $latestVideo->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="bg-zinc-900 p-6">
                                    <video controls class="w-full max-h-[500px] rounded-xl mx-auto" preload="metadata">
                                        <source src="{{ $latestVideo->fileUrl() }}" type="{{ $latestVideo->mime_type }}">
                                    </video>
                                </div>
                                <div class="p-5 space-y-4">
                                    <div>
                                        <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Scene') }}</div>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ Str::limit($latestVideo->prompt, 200) }}</p>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)"><div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Size') }}</div><div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 font-mono">{{ $latestVideo->width }}×{{ $latestVideo->height }}</div></div>
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)"><div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Length') }}</div><div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 font-mono">{{ $latestVideo->duration }}s</div></div>
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)"><div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Model') }}</div><div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $latestVideo->provider }}</div></div>
                                    </div>
                                    <div class="space-y-2 pt-1">
                                        <a href="{{ route('user.studio.download', $latestVideo->id) }}"
                                           class="group/cta relative w-full flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl text-sm font-semibold text-white shadow-xs shadow-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden"
                                           style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                            <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                                                  style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                                            <span class="relative flex items-center gap-2"><flux:icon.arrow-down-tray class="size-4" /> {{ __('Download Video') }}</span>
                                        </a>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button @click="step = 4" class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-xl text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/10 transition"><flux:icon.arrow-path class="size-3.5" /> {{ __('Regenerate') }}</button>
                                            <button wire:click="startNewVideo" @click="step = 1" class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-xl text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/10 transition"><flux:icon.plus class="size-3.5" /> {{ __('New Video') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- REVIEW & LAUNCH --}}
                        <div class="max-w-3xl mx-auto">
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">

                                {{-- Header --}}
                                <div class="px-6 py-4 flex items-center gap-3" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                    <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm">
                                        <flux:icon.clipboard-document-check class="size-5 text-white" />
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-bold text-white">{{ __('Review your video brief') }}</h3>
                                        <p class="text-xs text-white/70 mt-0.5">{{ __('Everything looks good? Generate your AI video ad.') }}</p>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
                                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Goal') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200" x-text="goal ? goal.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'"></div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Platform') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200" x-text="platform ? platform.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'"></div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Format') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200"><span x-text="preset ? preset.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'"></span> · <span x-text="duration + 's'"></span></div>
                                        </div>
                                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('AI Engine') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200" x-text="model || '—'"></div>
                                        </div>
                                        <div x-show="motionType || videoStyle" class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Motion & Style') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200"><span x-text="motionType ? motionType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''"></span><span x-show="motionType && videoStyle"> · </span><span x-text="videoStyle ? videoStyle.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''"></span></div>
                                        </div>
                                        <div x-show="mood" class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Mood') }}</div>
                                            <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200" x-text="mood ? mood.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : ''"></div>
                                        </div>
                                    </div>

                                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6 mb-4">
                                        <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-2">{{ __('Scene Description') }}</div>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">{{ $prompt }}</p>
                                    </div>

                                    @if($overlayText || $ctaText)
                                        <div class="grid grid-cols-2 gap-3 mb-4">
                                            @if($overlayText)
                                                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                                    <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Overlay') }}</div>
                                                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">"{{ $overlayText }}"</div>
                                                </div>
                                            @endif
                                            @if($ctaText)
                                                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-(--default-element-bg-color) border border-zinc-100 dark:border-white/6">
                                                    <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('CTA') }}</div>
                                                    <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">"{{ $ctaText }}"</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="border-t border-zinc-100 dark:border-white/8 pt-5 mt-2">
                                        {{-- Rendering mode hint — visible only when there's text to render --}}
                                        @if($overlayText || $ctaText)
                                            <div class="mb-3 inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-zinc-50 border border-zinc-200 dark:bg-(--default-element-bg-color) dark:border-white/8">
                                                @if($this->overlayWillRun)
                                                    <span class="inline-flex w-5 h-5 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/></svg>
                                                    </span>
                                                    <span class="text-[11px] text-zinc-700 dark:text-zinc-300">
                                                        <span class="font-semibold">{{ __('Text overlay:') }}</span> {{ __('Post-processed (FFmpeg)') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex w-5 h-5 shrink-0 items-center justify-center rounded-md bg-zinc-700 text-white dark:bg-zinc-200 dark:text-zinc-900">
                                                        <flux:icon.sparkles class="size-3" />
                                                    </span>
                                                    <span class="text-[11px] text-zinc-700 dark:text-zinc-300">
                                                        <span class="font-semibold">{{ __('Text overlay:') }}</span> {{ __('AI-rendered into the scene') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between mb-4">
                                            <div class="text-sm text-zinc-500">{{ __('Cost') }}: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $this->currentCost }} {{ __('credits') }}</span> <span class="text-xs text-zinc-400">({{ $this->currentRate }} {{ __('cr') }}/{{ __('sec') }} × {{ $selectedDuration }}s)</span></div>
                                            <div class="text-sm text-zinc-500">{{ __('Balance') }}: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ number_format($creditBalance) }}</span></div>
                                        </div>
                                        <button wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                                                class="group/cta relative w-full flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                                                style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                            <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                                                  style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                                            <span class="relative flex items-center gap-2.5" wire:loading.remove wire:target="generate">
                                                <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                                    <flux:icon.sparkles class="size-4" />
                                                </span>
                                                {{ __('Generate Video Ad') }}
                                            </span>
                                            <span class="relative flex items-center gap-2" wire:loading wire:target="generate">
                                                <div class="animate-spin rounded-full h-5 w-5 border-2 border-white/30 border-t-white"></div>
                                                {{ __('Launching AI...') }}
                                            </span>
                                        </button>
                                        <div class="flex justify-center mt-3">
                                            <button @click="step = 4" class="text-xs text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition flex items-center gap-1"><flux:icon.arrow-left class="size-3" /> {{ __('Go back and edit') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Recent Videos --}}
                @if($this->recentGenerations->count() > 0)
                    <div class="max-w-4xl mx-auto mt-8">
                        <button @click="showHistory = !showHistory" class="w-full flex items-center justify-between p-4 rounded-xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) text-left">
                            <h3 class="text-xs font-bold text-zinc-600 dark:text-zinc-400 flex items-center gap-2"><flux:icon.clock class="size-3.5" /> {{ __('Previous Videos') }} <span class="bg-zinc-100 dark:bg-neutral-700 text-zinc-500 px-1.5 py-0.5 rounded text-[10px]">{{ $this->recentGenerations->count() }}</span></h3>
                            <flux:icon.chevron-down class="size-3.5 text-zinc-400 transition-transform" ::class="showHistory && 'rotate-180'" />
                        </button>
                        <div x-show="showHistory" x-collapse class="mt-2 rounded-xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                            <div class="p-3 space-y-1.5 max-h-64 overflow-y-auto">
                                @foreach($this->recentGenerations as $asset)
                                    <button wire:click="loadFromAsset({{ $asset->id }})" @click="step = 4" class="w-full flex items-center gap-2.5 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-white/10/50 transition text-left">
                                        <div class="w-10 h-10 rounded-md bg-zinc-900 flex items-center justify-center ring-1 ring-zinc-700"><flux:icon.play class="size-4 text-indigo-400" /></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] truncate text-zinc-700 dark:text-zinc-300">{{ Str::limit($asset->prompt, 50) }}</p>
                                            <p class="text-[9px] text-zinc-400">{{ $asset->duration }}s · {{ $asset->created_at->diffForHumans() }}</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Prompt Library modal (video context) --}}
    <livewire:user.prompts.prompt-library context="video" />
</div>
