<div @if($this->latestPending) wire:poll.5s="$refresh" @endif x-data="{
    step: 1,
    totalSteps: 5,
    showBrandKit: false,
    showHistory: false,
    platformFilter: 'all',
    previewZoom: false,

    {{-- Local Alpine state for instant UI — synced to Livewire lazily --}}
    objective: @entangle('adObjective'),
    industry: @entangle('adIndustry'),
    preset: @entangle('selectedPreset'),
    tone: @entangle('adTone'),
    style: @entangle('adStyle'),
    colorScheme: @entangle('adColorScheme'),
    model: @entangle('selectedModel'),
    ctaText: @entangle('adCtaText'),
    headline: @entangle('adHeadline'),
    prompt: @entangle('prompt'),
    useBrandKit: @entangle('useBrandKit'),

    /**
     * Move the wizard back to step 1 from anywhere — also clears every
     * per-generation field locally and on the server so a fresh wizard
     * does not show last run's result on the final step.
     */
    goToStartFresh() {
        this.objective = '';
        this.industry = '';
        this.preset = '';
        this.tone = '';
        this.style = '';
        this.colorScheme = '';
        this.ctaText = '';
        this.headline = '';
        this.prompt = '';
        this.platformFilter = 'all';
        this.previewZoom = false;
        this.showBrandKit = false;
        this.showHistory = false;
        $wire.resetWizard();
        this.step = 1;
    },

    get progress() { return (this.step / this.totalSteps) * 100 },
    get canProceed() {
        if (this.step === 1) return this.objective !== '' && this.industry !== '';
        if (this.step === 2) return this.preset !== '';
        if (this.step === 3) return true;
        if (this.step === 4) return (this.prompt || '').trim() !== '';
        return true;
    }
}"
    @prompt-selected.window="prompt = $event.detail.body">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- ========================================== --}}
            {{-- Top Navigation — Modern Pill Toolbar        --}}
            {{-- ========================================== --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Creative Tools') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Image Studio') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('user.studio.gallery') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 transition dark:text-zinc-300 dark:hover:text-white dark:bg-(--default-element-bg-color) dark:hover:bg-white/5 dark:border-white/8">
                        <flux:icon.squares-2x2 class="size-3.5" /> {{ __('Gallery') }}
                    </a>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Hero Banner — clean, copy-studio style       --}}
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
                                    <flux:icon.sparkles class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            {{-- Step pill --}}
                            <div class="flex flex-wrap items-center gap-2 mb-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-white/8 border border-white/15 text-[9px] font-bold uppercase tracking-[0.18em] text-white">
                                    <flux:icon.photo class="size-2.5" /> {{ __('Image Studio') }}
                                </span>
                                <span class="text-[10px] text-zinc-400 font-mono">{{ __('Step') }} <span x-text="step"></span> / {{ count($stepFlow ?? [1,2,3,4,5]) }}</span>
                            </div>

                            {{-- Dynamic title + subtitle --}}
                            <h1 class="text-xl md:text-2xl font-extrabold text-white leading-tight tracking-tight">
                                <span x-show="step === 1">{{ __('What are you advertising?') }}</span>
                                <span x-show="step === 2">{{ __('Where will your ad appear?') }}</span>
                                <span x-show="step === 3">{{ __('Set the creative direction') }}</span>
                                <span x-show="step === 4">{{ __('Describe your vision') }}</span>
                                <span x-show="step === 5">{{ __('Review & generate') }}</span>
                            </h1>
                            <p class="text-xs text-zinc-400 mt-1 max-w-lg">
                                <span x-show="step === 1">{{ __('Define your objective and industry so the AI tailors every detail to your campaign.') }}</span>
                                <span x-show="step === 2">{{ __('Pick a canvas preset for any platform, or craft a custom size pixel-perfect to your placement.') }}</span>
                                <span x-show="step === 3">{{ __('Select tone, visual style, and color palette — the creative DNA of your ad.') }}</span>
                                <span x-show="step === 4">{{ __('Write the creative brief, add headline and CTA, and optionally upload a reference image.') }}</span>
                                <span x-show="step === 5">{{ __('Review every detail one last time and launch generation. Typical render in under 30 seconds.') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Single Credits chip — matches copy-studio exactly --}}
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
                            1 => ['label' => __('Brief'),   'icon' => 'squares-plus'],
                            2 => ['label' => __('Canvas'),  'icon' => 'rectangle-group'],
                            3 => ['label' => __('Style'),   'icon' => 'paint-brush'],
                            4 => ['label' => __('Prompt'),  'icon' => 'pencil-square'],
                            5 => ['label' => __('Launch'),  'icon' => 'rocket-launch'],
                        ];
                    @endphp
                    <div class="flex items-center">
                        @foreach($stepFlow as $n => $info)
                            <div class="flex items-center {{ $n < 5 ? 'flex-1' : '' }}">
                                <button type="button" @click="step === {{ $n }} ? null : ({{ $n }} === 1 ? goToStartFresh() : step = {{ $n }})" class="relative group flex flex-col items-center focus:outline-none">
                                    {{-- Step chip — active uses the same gradient-frame + dark inner style as the hero icon --}}
                                    <div class="relative w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300"
                                         :class="step === {{ $n }} ? 'p-px bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 shadow-lg shadow-indigo-500/40 scale-110' : step > {{ $n }} ? 'border bg-emerald-500/15 border-emerald-400/40' : 'border bg-white/5 border-white/10 group-hover:border-white/20'">
                                        <div class="relative w-full h-full rounded-[10px] flex items-center justify-center"
                                             :class="step === {{ $n }} ? 'bg-zinc-950' : ''">
                                            <flux:icon.check class="absolute size-4 text-emerald-300" x-show="step > {{ $n }}" x-cloak />
                                            @if($info['icon'] === 'squares-plus')
                                                <flux:icon.squares-plus class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'rectangle-group')
                                                <flux:icon.rectangle-group class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'paint-brush')
                                                <flux:icon.paint-brush class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
                                            @elseif($info['icon'] === 'pencil-square')
                                                <flux:icon.pencil-square class="size-4" x-bind:class="step === {{ $n }} ? 'text-indigo-300' : 'text-zinc-500'" x-show="step <= {{ $n }}" />
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
            {{-- STEP 1: Ad Objective & Industry              --}}
            {{-- ============================================ --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:items-stretch">

                    {{-- Ad Objective --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color) lg:flex lg:flex-col">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.rocket-launch class="size-4 text-amber-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Ad Objective') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('What do you want this ad to achieve?') }}</p>

                        <div class="grid grid-cols-2 gap-2 lg:flex-1 lg:content-start">
                            @php
                                // Lucide-style monochrome line icons. Inner SVG paths only — wrapped below.
                                $objectives = [
                                    'brand_awareness' => [
                                        'label' => __('Brand Awareness'), 'desc' => __('Get noticed'),
                                        'svg' => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
                                    ],
                                    'lead_generation' => [
                                        'label' => __('Lead Generation'), 'desc' => __('Capture leads'),
                                        'svg' => '<path d="m6 15-4-4 6.75-6.77a7.79 7.79 0 0 1 11 11L13 22l-4-4 6.39-6.36a2.14 2.14 0 0 0-3-3L6 15"/><path d="m5 8 4 4"/><path d="m12 15 4 4"/>',
                                    ],
                                    'sales_conversion' => [
                                        'label' => __('Sales & Conversion'), 'desc' => __('Drive purchases'),
                                        'svg' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                                    ],
                                    'traffic' => [
                                        'label' => __('Website Traffic'), 'desc' => __('Drive clicks'),
                                        'svg' => '<path d="M14 4.1 12 6"/><path d="m5.1 8-2.9-.8"/><path d="m6 12-1.9 2"/><path d="M7.2 2.2 8 5.1"/><path d="M9.037 9.69a.498.498 0 0 1 .653-.653l11 4.5a.5.5 0 0 1-.074.949l-4.349 1.041a1 1 0 0 0-.74.739l-1.04 4.35a.5.5 0 0 1-.95.074z"/>',
                                    ],
                                    'product_launch' => [
                                        'label' => __('Product Launch'), 'desc' => __('Announce new'),
                                        'svg' => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
                                    ],
                                    'seasonal_promo' => [
                                        'label' => __('Seasonal Promo'), 'desc' => __('Sale & holiday push'),
                                        'svg' => '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
                                    ],
                                    'event_promotion' => [
                                        'label' => __('Event Promotion'), 'desc' => __('Drive attendance'),
                                        'svg' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
                                    ],
                                    'retargeting' => [
                                        'label' => __('Retargeting'), 'desc' => __('Re-engage users'),
                                        'svg' => '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
                                    ],
                                    'app_install' => [
                                        'label' => __('App Install'), 'desc' => __('Drive downloads'),
                                        'svg' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
                                    ],
                                    'video_views' => [
                                        'label' => __('Video Views'), 'desc' => __('Boost watch time'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/>',
                                    ],
                                    'followers' => [
                                        'label' => __('Grow Audience'), 'desc' => __('Build a following'),
                                        'svg' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                                    ],
                                    'engagement' => [
                                        'label' => __('Engagement'), 'desc' => __('Boost interaction'),
                                        'svg' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($objectives as $key => $obj)
                                <button @click="objective = '{{ $key }}'" class="group relative flex items-start gap-2.5 p-3 rounded-xl border text-left transition-all hover:shadow-md" :class="objective === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="mt-0.5 inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                          :class="objective === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $obj['svg'] !!}</svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold" :class="objective === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $obj['label'] }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $obj['desc'] }}</div>
                                    </div>
                                    <div x-show="objective === '{{ $key }}'" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Industry --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color) lg:flex lg:flex-col lg:min-h-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.building-storefront class="size-4 text-emerald-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Industry') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Select your business category for tailored results') }}</p>

                        <div class="grid grid-cols-3 gap-2 lg:flex-1 lg:content-start lg:overflow-y-auto lg:min-h-0 lg:pr-1 -mr-1 [scrollbar-width:thin]">
                            @php
                                // Lucide-style monochrome line icons
                                $industries = [
                                    'ecommerce' => [
                                        'label' => __('E-Commerce'),
                                        'svg' => '<path d="M2 7h20l-2 12a2 2 0 0 1-2 1.7H6a2 2 0 0 1-2-1.7L2 7Z"/><path d="M16 10a4 4 0 0 1-8 0"/><path d="M8 7V5a4 4 0 0 1 8 0v2"/>',
                                    ],
                                    'saas' => [
                                        'label' => __('SaaS / Tech'),
                                        'svg' => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/><path d="m9 10 2 2-2 2"/><path d="M13 14h2"/>',
                                    ],
                                    'agency' => [
                                        'label' => __('Marketing Agency'),
                                        'svg' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
                                    ],
                                    'food_beverage' => [
                                        'label' => __('Food & Drink'),
                                        'svg' => '<path d="M15 11h.01"/><path d="M11 15h.01"/><path d="M16 16h.01"/><path d="m2 16 20 6-6-20A20 20 0 0 0 2 16"/><path d="M5.71 17.11a17.04 17.04 0 0 1 11.4-11.4"/>',
                                    ],
                                    'restaurant_cafe' => [
                                        'label' => __('Restaurant / Café'),
                                        'svg' => '<path d="M3 11h18v3a8 8 0 0 1-8 8h-2a8 8 0 0 1-8-8v-3Z"/><path d="M7 11V6.5a2.5 2.5 0 0 1 5 0V8"/><path d="M12 11V8.5a2.5 2.5 0 0 1 5 0V11"/><line x1="6" x2="18" y1="22" y2="22"/>',
                                    ],
                                    'fashion' => [
                                        'label' => __('Fashion'),
                                        'svg' => '<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>',
                                    ],
                                    'beauty' => [
                                        'label' => __('Beauty / Cosmetics'),
                                        'svg' => '<path d="M14 2v6h6"/><path d="M4 22V8a2 2 0 0 1 2-2h7l5 5v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M9 14h6"/>',
                                    ],
                                    'health_fitness' => [
                                        'label' => __('Health & Fitness'),
                                        'svg' => '<path d="M14.4 14.4 9.6 9.6"/><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z"/><path d="m21.5 21.5-1.4-1.4"/><path d="M3.9 3.9 2.5 2.5"/><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z"/>',
                                    ],
                                    'wellness_spa' => [
                                        'label' => __('Wellness & Spa'),
                                        'svg' => '<path d="M12 22c1-3 1-5 0-7-1.5-3-4-4-7-4 0 3 1 5.5 3 7s4 4 4 4Z"/><path d="M12 22c-1-3-1-5 0-7 1.5-3 4-4 7-4 0 3-1 5.5-3 7s-4 4-4 4Z"/><path d="M12 15c1-2.5 1-4.5 0-6-1-1.5-3-2.5-5-2.5 0 2 .5 4 2 5.5"/>',
                                    ],
                                    'real_estate' => [
                                        'label' => __('Real Estate'),
                                        'svg' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                                    ],
                                    'education' => [
                                        'label' => __('Education'),
                                        'svg' => '<path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>',
                                    ],
                                    'travel' => [
                                        'label' => __('Travel'),
                                        'svg' => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
                                    ],
                                    'finance' => [
                                        'label' => __('Finance'),
                                        'svg' => '<rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
                                    ],
                                    'crypto' => [
                                        'label' => __('Crypto / Web3'),
                                        'svg' => '<path d="m6 3 6 4 6-4"/><path d="m6 21 6-4 6 4"/><path d="m6 3-3 5 3 5"/><path d="m18 3 3 5-3 5"/><path d="M6 13 3 8"/><path d="m18 13 3-5"/><path d="M12 7v10"/>',
                                    ],
                                    'automotive' => [
                                        'label' => __('Automotive'),
                                        'svg' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
                                    ],
                                    'home_services' => [
                                        'label' => __('Home Services'),
                                        'svg' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
                                    ],
                                    'gaming' => [
                                        'label' => __('Gaming / eSports'),
                                        'svg' => '<line x1="6" x2="10" y1="11" y2="11"/><line x1="8" x2="8" y1="9" y2="13"/><line x1="15" x2="15.01" y1="12" y2="12"/><line x1="18" x2="18.01" y1="10" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5"/>',
                                    ],
                                    'entertainment' => [
                                        'label' => __('Entertainment'),
                                        'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 3v18"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>',
                                    ],
                                    'professional_services' => [
                                        'label' => __('Professional Services'),
                                        'svg' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                    ],
                                    'nonprofit' => [
                                        'label' => __('Nonprofit / NGO'),
                                        'svg' => '<path d="M11 17a4 4 0 0 1-8 0V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2Z"/><path d="M16.5 13a4 4 0 0 0 4-4V5a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v8a4 4 0 0 0 4 4Z"/><path d="M11 13h5"/>',
                                    ],
                                    'other' => [
                                        'label' => __('Other'),
                                        'svg' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($industries as $key => $ind)
                                <button @click="industry = '{{ $key }}'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all hover:shadow-md" :class="industry === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition-colors"
                                          :class="industry === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $ind['svg'] !!}</svg>
                                    </span>
                                    <span class="text-[10px] font-semibold text-center" :class="industry === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $ind['label'] }}</span>
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
                            <span>{{ __('Next: Choose Platform') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 2: Platform & Canvas Selection          --}}
            {{-- ============================================ --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

                @php
                    // Lucide-style monochrome icons (path bodies only)
                    $platformMeta = [
                        'all' => [
                            'label' => __('All'),
                            'svg' => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
                        ],
                        'meta' => [
                            'label' => __('Meta'),
                            'svg' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
                        ],
                        'tiktok' => [
                            'label' => __('TikTok'),
                            'svg' => '<path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>',
                        ],
                        'x_twitter' => [
                            'label' => __('X'),
                            'svg' => '<path d="M4 4l11.733 16h4.267l-11.733 -16z"/><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"/>',
                        ],
                        'linkedin' => [
                            'label' => __('LinkedIn'),
                            'svg' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
                        ],
                        'pinterest' => [
                            'label' => __('Pinterest'),
                            'svg' => '<line x1="8" x2="12" y1="22" y2="14"/><path d="M9.5 14.5a4 4 0 1 0 6 -5"/><circle cx="12" cy="12" r="10"/>',
                        ],
                        'snap_reddit' => [
                            'label' => __('Snap & Reddit'),
                            'svg' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
                        ],
                        'youtube' => [
                            'label' => __('YouTube'),
                            'svg' => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
                        ],
                        'google_ads' => [
                            'label' => __('Google Ads'),
                            'svg' => '<path d="m9 17 8-3-3-8-8 3z"/><path d="m9 17 8-3"/><path d="M5 21l4-4"/>',
                        ],
                        'web_display' => [
                            'label' => __('Web'),
                            'svg' => '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>',
                        ],
                        'email' => [
                            'label' => __('Email'),
                            'svg' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                        ],
                        'custom' => [
                            'label' => __('Custom'),
                            'svg' => '<path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/>',
                        ],
                    ];

                    // Per-pill counts. "all" sums every group; "custom" has no presets.
                    $platformCounts = ['all' => collect($presets)->sum(fn ($g) => count($g))];
                    foreach ($presets as $g => $items) { $platformCounts[$g] = count($items); }
                    $platformCounts['custom'] = null;
                @endphp

                {{-- Compact filter chip row — light, soft-active, count badges --}}
                <div class="mb-5 -mx-1 px-1 overflow-x-auto scrollbar-hide">
                    <div class="inline-flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100/70 border border-zinc-200 dark:bg-(--default-element-bg-color) dark:border-white/8">
                        @foreach($platformMeta as $key => $meta)
                            <button type="button" @click="platformFilter = '{{ $key }}'"
                                    class="group/chip relative inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-all"
                                    :class="platformFilter === '{{ $key }}' ? 'bg-white text-zinc-900 shadow-xs ring-1 ring-zinc-200 dark:bg-(--default-element-light-bg-color) dark:text-white dark:ring-neutral-700' : 'text-zinc-500 hover:text-zinc-800 hover:bg-white/60 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-white/5'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 transition-colors"
                                     :class="platformFilter === '{{ $key }}' ? 'text-indigo-600 dark:text-indigo-400' : ''">{!! $meta['svg'] !!}</svg>
                                <span>{{ $meta['label'] }}</span>
                                @if(isset($platformCounts[$key]) && $platformCounts[$key] !== null)
                                    <span class="ml-0.5 inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full text-[9px] font-bold tabular-nums transition-colors"
                                          :class="platformFilter === '{{ $key }}' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-zinc-200/80 text-zinc-500 dark:bg-neutral-700 dark:text-zinc-400'">{{ $platformCounts[$key] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                @php
                    // Group header labels (some keys differ from filter-pill labels)
                    $groupLabels = [
                        'meta'        => __('Meta — Facebook, Instagram, Threads'),
                        'tiktok'      => __('TikTok'),
                        'x_twitter'   => __('X (Twitter)'),
                        'linkedin'    => __('LinkedIn'),
                        'pinterest'   => __('Pinterest'),
                        'snap_reddit' => __('Snapchat & Reddit'),
                        'youtube'     => __('YouTube'),
                        'google_ads'  => __('Google Ads'),
                        'web_display' => __('Web & Display Banners'),
                        'email'       => __('Email'),
                    ];
                @endphp

                @foreach($presets as $group => $groupPresets)
                    <div x-show="platformFilter === 'all' || platformFilter === '{{ $group }}'" x-transition class="mb-6">
                        <h3 class="text-xs font-bold text-zinc-600 dark:text-zinc-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-md bg-white border border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $platformMeta[$group]['svg'] ?? $platformMeta['all']['svg'] !!}</svg>
                            </span>
                            {{ $groupLabels[$group] ?? __(ucwords(str_replace('_', ' ', $group))) }}
                            <span class="text-zinc-400 dark:text-neutral-500 font-normal normal-case tracking-normal">— {{ count($groupPresets) }} {{ __('formats') }}</span>
                        </h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach($groupPresets as $p)
                                @php
                                    $maxH = 52; $maxW = 72;
                                    $r = $p['width'] / max($p['height'], 1);
                                    $dW = $r >= 1 ? $maxW : (int)($maxH * $r);
                                    $dH = $r >= 1 ? min($maxH, (int)($maxW / $r)) : $maxH;
                                @endphp
                                <button type="button" @click="preset = '{{ $p['slug'] }}'"
                                        class="group relative rounded-xl border p-3 text-left transition-all hover:shadow-md hover:-translate-y-0.5"
                                        :class="preset === '{{ $p['slug'] }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm ring-1 ring-indigo-500/20' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-(--default-element-light-bg-color) dark:hover:border-neutral-600'">
                                    {{-- Top accent line — brand cool stroke instead of banned pink/purple --}}
                                    <div class="h-1 w-8 rounded-full mb-2.5 group-hover:w-12 transition-all"
                                         :class="preset === '{{ $p['slug'] }}' ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-200 dark:bg-neutral-700'"></div>
                                    <div class="flex items-center justify-center mb-2.5 h-14">
                                        <div class="rounded border-2 border-dashed flex items-center justify-center transition-colors"
                                             :class="preset === '{{ $p['slug'] }}' ? 'border-indigo-400 bg-indigo-100/50 dark:bg-indigo-900/30' : 'border-zinc-200 bg-zinc-50 group-hover:border-zinc-300 dark:border-neutral-600 dark:bg-neutral-700/50'"
                                             style="width: {{ $dW }}px; height: {{ $dH }}px;">
                                            <span class="text-[8px] font-bold tabular-nums" :class="preset === '{{ $p['slug'] }}' ? 'text-indigo-500' : 'text-zinc-400'">{{ $p['ratio'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-[11px] font-semibold truncate" :class="preset === '{{ $p['slug'] }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $p['label'] }}</div>
                                    <div x-show="preset === '{{ $p['slug'] }}'" class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shadow-sm"><flux:icon.check class="size-3 text-white" /></div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Custom Size --}}
                <div x-show="platformFilter === 'all' || platformFilter === 'custom'" x-transition class="mb-6">
                    <h3 class="text-xs font-bold text-zinc-600 dark:text-zinc-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                        <span class="inline-flex w-6 h-6 items-center justify-center rounded-md bg-white border border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $platformMeta['custom']['svg'] !!}</svg>
                        </span>
                        {{ __('Custom Dimensions') }}
                        <span class="text-zinc-400 dark:text-neutral-500 font-normal normal-case tracking-normal">— {{ __('craft any size pixel-perfect') }}</span>
                    </h3>

                    <div x-data="{
                            w: {{ (int) ($customWidth ?? 1080) }},
                            h: {{ (int) ($customHeight ?? 1080) }},
                            min: 256, max: 4096,
                            // Map of provider key to its native max resolution (longest-side ceiling).
                            // Used to surface a render-above-2K-requires-Gemini-or-GPT-Image warning
                            // when the user pushes the slider past the selected engine's cap.
                            maxResolutionMap: {{ \Illuminate\Support\Js::from(collect($providers)->mapWithKeys(fn ($p) => [$p['key'] => $p['max_resolution'] ?? 4096])->toArray()) }},
                            get engineMax() { return parseInt(this.maxResolutionMap[this.model]) || this.max; },
                            get exceedsEngineMax() {
                                const a = parseInt(this.w) || 0, b = parseInt(this.h) || 0;
                                return Math.max(a, b) > this.engineMax;
                            },
                            get ratio() {
                                const a = parseInt(this.w) || 0, b = parseInt(this.h) || 0;
                                if (!a || !b) return '—';
                                const gcd = (x, y) => y ? gcd(y, x % y) : x;
                                const g = gcd(a, b);
                                const ra = a / g, rb = b / g;
                                if (ra > 50 || rb > 50) return (a / b).toFixed(2) + ':1';
                                return ra + ':' + rb;
                            },
                            get orientation() {
                                const a = parseInt(this.w) || 0, b = parseInt(this.h) || 0;
                                if (!a || !b) return '';
                                if (a === b) return @js(__('Square'));
                                return a > b ? @js(__('Landscape')) : @js(__('Portrait'));
                            },
                            get inRange() {
                                const a = parseInt(this.w) || 0, b = parseInt(this.h) || 0;
                                return a >= this.min && a <= this.max && b >= this.min && b <= this.max;
                            },
                            get previewBox() {
                                const a = parseInt(this.w) || 1, b = parseInt(this.h) || 1;
                                const maxSide = 120;
                                if (a >= b) return { w: maxSide, h: Math.max(20, Math.round(maxSide * b / a)) };
                                return { h: maxSide, w: Math.max(20, Math.round(maxSide * a / b)) };
                            },
                            applyAspect(ar) {
                                const base = parseInt(this.w) || 1080;
                                const [ra, rb] = ar.split(':').map(Number);
                                this.w = base;
                                this.h = Math.round(base * rb / ra);
                                this.push();
                            },
                            swap() { const t = this.w; this.w = this.h; this.h = t; this.push(); },
                            push() {
                                $wire.set('customWidth', parseInt(this.w) || null, false);
                                $wire.set('customHeight', parseInt(this.h) || null, false);
                            },
                            apply() { this.push(); preset = 'custom'; }
                         }"
                         class="rounded-xl border bg-white p-5 transition-colors dark:bg-(--default-element-light-bg-color)"
                         :class="preset === 'custom' ? 'border-indigo-500 ring-1 ring-indigo-500/20 shadow-sm' : 'border-zinc-200 dark:border-white/8'">

                        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-6 items-stretch">

                            {{-- Left: form controls --}}
                            <div class="flex flex-col gap-4 min-w-0">

                                {{-- Width / Height inputs with px suffix and swap button between --}}
                                <div class="flex items-end gap-2">
                                    <div class="flex-1 min-w-0">
                                        <label for="customWidth" class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Width') }}</label>
                                        <div class="relative">
                                            <input id="customWidth" type="number" inputmode="numeric"
                                                   x-model.number="w" @change="push()"
                                                   min="256" max="4096" placeholder="1080"
                                                   class="w-full h-10 pl-3 pr-10 rounded-lg border bg-white text-sm font-medium tabular-nums text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500"
                                                   :class="!inRange && w ? 'border-rose-400 dark:border-rose-500/60' : 'border-zinc-200'">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold uppercase text-zinc-400 pointer-events-none">px</span>
                                        </div>
                                    </div>

                                    <button type="button" @click="swap()"
                                            title="{{ __('Swap orientation') }}"
                                            class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-lg border border-zinc-200 bg-white text-zinc-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition dark:bg-(--default-element-bg-color) dark:text-zinc-400 dark:border-white/8 dark:hover:text-indigo-300 dark:hover:bg-indigo-950/30 dark:hover:border-indigo-700/40"
                                            aria-label="{{ __('Swap width and height') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3 4 7l4 4"/><path d="M4 7h16"/><path d="m16 21 4-4-4-4"/><path d="M20 17H4"/></svg>
                                    </button>

                                    <div class="flex-1 min-w-0">
                                        <label for="customHeight" class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">{{ __('Height') }}</label>
                                        <div class="relative">
                                            <input id="customHeight" type="number" inputmode="numeric"
                                                   x-model.number="h" @change="push()"
                                                   min="256" max="4096" placeholder="1080"
                                                   class="w-full h-10 pl-3 pr-10 rounded-lg border bg-white text-sm font-medium tabular-nums text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500"
                                                   :class="!inRange && h ? 'border-rose-400 dark:border-rose-500/60' : 'border-zinc-200'">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold uppercase text-zinc-400 pointer-events-none">px</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick aspect ratios --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Quick ratios') }}</span>
                                        <span class="text-[10px] text-zinc-400">{{ __('Range') }}: 256–4096px</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['1:1', '4:5', '9:16', '16:9', '3:2', '2:3', '1.91:1', '21:9'] as $ar)
                                            <button type="button" @click="applyAspect('{{ $ar }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 h-7 rounded-md border text-[11px] font-semibold tabular-nums transition"
                                                    :class="ratio === '{{ $ar }}' ? 'border-indigo-300 bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:border-indigo-700/40 dark:text-indigo-300' : 'border-zinc-200 bg-white text-zinc-600 hover:border-indigo-200 hover:text-indigo-600 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:text-indigo-300 dark:hover:border-indigo-700/40'">
                                                {{ $ar }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Apply button --}}
                                <div class="pt-1">
                                    <button type="button" @click="apply()"
                                            :disabled="!inRange"
                                            class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="preset === 'custom' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/25 hover:bg-indigo-700' : 'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white'">
                                        <template x-if="preset === 'custom'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </template>
                                        <span x-text="preset === 'custom' ? '{{ __('Custom size selected') }}' : '{{ __('Use these dimensions') }}'"></span>
                                    </button>
                                    <p x-show="!inRange && (w || h)" class="mt-2 text-[11px] text-rose-500 flex items-center gap-1" x-cloak>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                                        {{ __('Each dimension must be between 256 and 4096 pixels.') }}
                                    </p>
                                    <p x-show="exceedsEngineMax && inRange" class="mt-2 text-[11px] text-amber-600 dark:text-amber-300 flex items-center gap-1" x-cloak>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                                        <span>{{ __('Above this engine\'s native ceiling') }} (<span x-text="engineMax + 'px'" class="tabular-nums font-semibold"></span>) — {{ __('output will be downscaled. Switch to Nano Banana 2 or GPT Image 2 for native 4K.') }}</span>
                                    </p>
                                </div>
                            </div>

                            {{-- Right: live preview --}}
                            <div class="flex md:flex-col items-center md:items-end justify-between md:justify-center gap-4 md:border-l md:pl-6 md:border-zinc-100 md:dark:border-white/8">
                                <div class="relative flex items-center justify-center" style="width: 132px; height: 132px;">
                                    {{-- Frame outline --}}
                                    <div class="absolute inset-0 rounded-lg border border-dashed border-zinc-200 dark:border-white/8"></div>
                                    <div class="rounded-md border-2 transition-all flex items-center justify-center"
                                         :style="`width: ${previewBox.w}px; height: ${previewBox.h}px;`"
                                         :class="preset === 'custom' ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-950/40' : 'border-zinc-300 bg-zinc-50 dark:border-neutral-600 dark:bg-neutral-700/40'">
                                        <span class="text-[9px] font-bold tabular-nums" :class="preset === 'custom' ? 'text-indigo-500' : 'text-zinc-400'" x-text="ratio"></span>
                                    </div>
                                </div>
                                <div class="text-right md:text-right">
                                    <div class="text-[9px] font-semibold uppercase tracking-wider text-zinc-400">{{ __('Preview') }}</div>
                                    <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100 font-mono tabular-nums" x-text="(parseInt(w) || 0) + ' × ' + (parseInt(h) || 0)"></div>
                                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400">
                                        <span x-text="orientation"></span><span x-show="orientation"> · </span><span x-text="ratio"></span>
                                    </div>
                                </div>
                            </div>
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

                    {{-- Tone --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-violet-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.megaphone class="size-4 text-violet-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Tone & Mood') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('How should your ad feel?') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                // Lucide-style monochrome line icons
                                $tones = [
                                    'professional' => [
                                        'label' => __('Professional'), 'desc' => __('Clean & corporate'),
                                        'svg' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                    ],
                                    'playful' => [
                                        'label' => __('Playful'), 'desc' => __('Fun & energetic'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
                                    ],
                                    'luxurious' => [
                                        'label' => __('Luxurious'), 'desc' => __('Premium & elegant'),
                                        'svg' => '<path d="M6 3h12l4 6-10 13L2 9z"/><path d="M11 3 8 9l4 13 4-13-3-6"/><path d="M2 9h20"/>',
                                    ],
                                    'bold' => [
                                        'label' => __('Bold & Edgy'), 'desc' => __('Attention-grabbing'),
                                        'svg' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
                                    ],
                                    'minimal' => [
                                        'label' => __('Minimalist'), 'desc' => __('Less is more'),
                                        'svg' => '<line x1="5" x2="19" y1="12" y2="12"/>',
                                    ],
                                    'warm' => [
                                        'label' => __('Warm & Friendly'), 'desc' => __('Approachable'),
                                        'svg' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
                                    ],
                                    'urgent' => [
                                        'label' => __('Urgent'), 'desc' => __('FOMO & scarcity'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                                    ],
                                    'inspirational' => [
                                        'label' => __('Inspirational'), 'desc' => __('Motivating'),
                                        'svg' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($tones as $key => $t)
                                <button @click="tone = '{{ $key }}'" class="group relative flex items-start gap-2.5 p-3 rounded-xl border text-left transition-all hover:shadow-md" :class="tone === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="mt-0.5 inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-lg border transition-colors"
                                          :class="tone === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $t['svg'] !!}</svg>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold" :class="tone === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300'">{{ $t['label'] }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $t['desc'] }}</div>
                                    </div>
                                    <div x-show="tone === '{{ $key }}'" class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
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
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Choose an art direction') }}</p>
                        <div class="grid grid-cols-3 gap-2">
                            @php
                                // Lucide-style monochrome line icons
                                $styles = [
                                    'photorealistic' => [
                                        'label' => __('Photo-realistic'),
                                        'svg' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
                                    ],
                                    'flat_design' => [
                                        'label' => __('Flat Design'),
                                        'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
                                    ],
                                    '3d_render' => [
                                        'label' => __('3D Render'),
                                        'svg' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
                                    ],
                                    'illustration' => [
                                        'label' => __('Illustration'),
                                        'svg' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
                                    ],
                                    'gradient' => [
                                        'label' => __('Gradient'),
                                        'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 7.5h18"/><path d="M3 12h18"/><path d="M3 16.5h18"/>',
                                    ],
                                    'neon' => [
                                        'label' => __('Neon / Glow'),
                                        'svg' => '<path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>',
                                    ],
                                    'vintage' => [
                                        'label' => __('Vintage'),
                                        'svg' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><path d="M2 12h4"/><path d="M18 12h4"/>',
                                    ],
                                    'geometric' => [
                                        'label' => __('Geometric'),
                                        'svg' => '<path d="M3 3h7v7H3z"/><circle cx="17.5" cy="6.5" r="3.5"/><path d="m13 13 8 8-8 0z"/>',
                                    ],
                                    'watercolor' => [
                                        'label' => __('Watercolor'),
                                        'svg' => '<path d="M8 2v3"/><path d="M16 2v3"/><path d="M5 8h14a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-1l-2 8H8l-2-8H5a2 2 0 0 1-2-2v0a2 2 0 0 1 2-2z"/>',
                                    ],
                                ];
                            @endphp
                            @foreach($styles as $key => $s)
                                <button @click="style = '{{ $key }}'" class="flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all hover:shadow-md" :class="style === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-lg border transition-colors"
                                          :class="style === '{{ $key }}' ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-100 dark:border-zinc-100 dark:text-zinc-900' : 'bg-white border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['svg'] !!}</svg>
                                    </span>
                                    <span class="text-[10px] font-semibold text-center" :class="style === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $s['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Color Scheme --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-cyan-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.swatch class="size-4 text-cyan-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Color Scheme') }}</h3>
                        </div>
                        <p class="text-[11px] text-zinc-400 mb-4 ml-9">{{ __('Preferred color palette') }}</p>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                $colorSchemes = [
                                    'vibrant' => ['colors' => ['#FF6B6B','#4ECDC4','#FFE66D','#95E1D3'], 'label' => __('Vibrant')],
                                    'pastel' => ['colors' => ['#FFB5E8','#B5DEFF','#E7FFAC','#FFC9DE'], 'label' => __('Pastel')],
                                    'dark_moody' => ['colors' => ['#1A1A2E','#16213E','#0F3460','#E94560'], 'label' => __('Dark & Moody')],
                                    'earth_tones' => ['colors' => ['#D4A373','#CCD5AE','#E9EDC9','#FEFAE0'], 'label' => __('Earth Tones')],
                                    'monochrome' => ['colors' => ['#000000','#333333','#666666','#CCCCCC'], 'label' => __('Monochrome')],
                                    'brand_colors' => ['colors' => [$brandPrimaryColor ?: '#6366f1', $brandSecondaryColor ?: '#ec4899', '#ffffff', '#000000'], 'label' => __('My Brand')],
                                ];
                            @endphp
                            @foreach($colorSchemes as $key => $scheme)
                                <button @click="colorScheme = '{{ $key }}'" class="flex items-center gap-3 p-3 rounded-xl border text-left transition-all hover:shadow-md" :class="colorScheme === '{{ $key }}' ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600'">
                                    <div class="flex -space-x-1">
                                        @foreach($scheme['colors'] as $color)
                                            <div class="w-5 h-5 rounded-full border-2 border-white dark:border-white/6 shadow-sm" style="background-color: {{ $color }}"></div>
                                        @endforeach
                                    </div>
                                    <span class="text-[11px] font-semibold" :class="colorScheme === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-400'">{{ $scheme['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Brand Kit --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden"
                         :class="!useBrandKit && 'opacity-95'">
                        <div class="w-full p-5 flex items-center justify-between gap-3">
                            <button type="button" @click="showBrandKit = !showBrandKit" class="flex items-center gap-3 min-w-0 flex-1 text-left hover:opacity-90 transition">
                                <span class="relative w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    <flux:icon.finger-print class="size-4.5 text-amber-400" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Brand Kit') }}</h3>
                                        @if($brandPrimaryColor || $brandSecondaryColor || $brandTagline)
                                            <span x-show="useBrandKit" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[9px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300">
                                                <span class="w-1 h-1 rounded-full bg-emerald-500"></span> {{ __('Active') }}
                                            </span>
                                            <span x-show="!useBrandKit" x-cloak class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-zinc-100 border border-zinc-200 text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:bg-white/5 dark:border-white/8 dark:text-zinc-400">
                                                <span class="w-1 h-1 rounded-full bg-zinc-400"></span> {{ __('Off') }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                        <span x-show="useBrandKit">
                                            @if($brandTagline)
                                                <span class="truncate">{{ Str::limit($brandTagline, 70) }}</span>
                                            @else
                                                {{ __('Saved colors and tagline applied to this generation') }}
                                            @endif
                                        </span>
                                        <span x-show="!useBrandKit" x-cloak>{{ __('Ignored — your selected Color Scheme is used instead') }}</span>
                                    </p>
                                </div>
                            </button>

                            <div class="flex items-center gap-3 shrink-0">
                                {{-- Collapsed-view swatches --}}
                                <div class="hidden sm:flex items-center gap-2" x-show="!showBrandKit && useBrandKit">
                                    @if($brandPrimaryColor || $brandSecondaryColor)
                                        <div class="flex -space-x-1">
                                            @if($brandPrimaryColor)
                                                <div class="w-5 h-5 rounded-full border-2 border-white dark:border-white/6 shadow-sm" style="background-color: {{ $brandPrimaryColor }}"></div>
                                            @endif
                                            @if($brandSecondaryColor)
                                                <div class="w-5 h-5 rounded-full border-2 border-white dark:border-white/6 shadow-sm" style="background-color: {{ $brandSecondaryColor }}"></div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Apply toggle — controls whether the brand kit feeds into generation + summary --}}
                                <button type="button" role="switch" :aria-checked="useBrandKit" @click="useBrandKit = !useBrandKit"
                                        title="{{ __('Apply brand kit to this generation') }}"
                                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                                        :class="useBrandKit ? 'bg-indigo-600' : 'bg-zinc-300 dark:bg-neutral-600'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform" :class="useBrandKit ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>

                                <button type="button" @click="showBrandKit = !showBrandKit" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition" aria-label="{{ __('Toggle brand kit details') }}">
                                    <flux:icon.chevron-down class="size-4 transition-transform" ::class="showBrandKit && 'rotate-180'" />
                                </button>
                            </div>
                        </div>

                        <div x-show="showBrandKit" x-collapse>
                            <div class="px-5 pb-5 pt-4 border-t border-zinc-100 dark:border-white/8 space-y-4">

                                {{-- Brand selector — choose which saved brand drives this generation --}}
                                @if(count($availableBrands) > 0)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Your brands') }}</label>
                                            <span class="text-[10px] text-zinc-400 tabular-nums">{{ count($availableBrands) }} {{ trans_choice('brand|brands', count($availableBrands)) }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($availableBrands as $brand)
                                                @php
                                                    $isSelected = (int) $selectedBrandId === (int) $brand['id'];
                                                    $brandInitial = mb_strtoupper(mb_substr($brand['name'] ?? '?', 0, 1));
                                                @endphp
                                                <button type="button" wire:click="$set('selectedBrandId', {{ $brand['id'] }})" wire:loading.attr="disabled" wire:target="selectedBrandId"
                                                        class="group/brand relative flex items-center gap-3 p-3 rounded-xl border text-left transition-all hover:shadow-md {{ $isSelected ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/30 shadow-sm' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-indigo-600' }}">
                                                    {{-- Logo or initial-avatar fallback --}}
                                                    <span class="relative w-9 h-9 rounded-lg overflow-hidden shrink-0 ring-1 ring-black/5 dark:ring-white/10 flex items-center justify-center">
                                                        @if(!empty($brand['logo_path']))
                                                            <img src="{{ URL::asset($brand['logo_path']) }}" alt="{{ $brand['name'] }}" class="w-full h-full object-cover"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                                            <span class="absolute inset-0 hidden items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B)">{{ $brandInitial }}</span>
                                                        @else
                                                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white" style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B)">{{ $brandInitial }}</span>
                                                        @endif
                                                    </span>

                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-xs font-bold truncate {{ $isSelected ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-200' }}">{{ $brand['name'] }}</span>
                                                            @if(!empty($brand['is_default']))
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-amber-100 border border-amber-200 text-[8px] font-bold uppercase tracking-wider text-amber-700 dark:bg-amber-950/40 dark:border-amber-900/40 dark:text-amber-300">{{ __('Default') }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            @if(!empty($brand['primary_color']) || !empty($brand['secondary_color']))
                                                                <span class="flex -space-x-1">
                                                                    @if(!empty($brand['primary_color']))
                                                                        <span class="w-3 h-3 rounded-full border border-white dark:border-white/10 shadow-sm" style="background-color: {{ $brand['primary_color'] }}"></span>
                                                                    @endif
                                                                    @if(!empty($brand['secondary_color']))
                                                                        <span class="w-3 h-3 rounded-full border border-white dark:border-white/10 shadow-sm" style="background-color: {{ $brand['secondary_color'] }}"></span>
                                                                    @endif
                                                                </span>
                                                            @endif
                                                            <span class="text-[10px] text-zinc-400 truncate">{{ $brand['industry'] ?: __('No industry set') }}</span>
                                                        </div>
                                                    </div>

                                                    {{-- Selected / Active indicator --}}
                                                    @if($isSelected)
                                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-[8px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300 shrink-0">
                                                            <flux:icon.check class="size-2.5" /> {{ __('Selected') }}
                                                        </span>
                                                    @else
                                                        <span class="shrink-0 w-4 h-4 rounded-full border border-zinc-300 dark:border-white/15 group-hover/brand:border-indigo-400 transition"></span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-[10px] text-zinc-400">{{ __('The selected brand supplies the colors and tagline below. Saving writes your edits back to this brand.') }}</p>
                                    </div>

                                    <div class="h-px bg-zinc-100 dark:bg-white/8"></div>
                                @endif

                                {{-- Color pickers — visual swatch + hex input pair --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @php
                                        $brandColorFields = [
                                            ['model' => 'brandPrimaryColor',   'label' => __('Primary color'),   'value' => $brandPrimaryColor ?: '#4F46E5'],
                                            ['model' => 'brandSecondaryColor', 'label' => __('Secondary color'), 'value' => $brandSecondaryColor ?: '#0F172A'],
                                        ];
                                    @endphp
                                    @foreach($brandColorFields as $field)
                                        <div>
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">{{ $field['label'] }}</label>
                                            <div class="flex items-stretch rounded-lg border border-zinc-200 bg-white overflow-hidden focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 dark:border-white/8 dark:bg-(--default-element-bg-color) transition">
                                                <label class="relative shrink-0 w-11 cursor-pointer hover:opacity-90 transition" style="background-color: {{ $field['value'] }}" :style="`background-color: ${$wire.{{ $field['model'] }} || '{{ $field['value'] }}'}`">
                                                    <input type="color" wire:model.live="{{ $field['model'] }}" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" aria-label="{{ $field['label'] }}" />
                                                </label>
                                                <input type="text" wire:model.live.debounce.300ms="{{ $field['model'] }}" maxlength="7" placeholder="#000000"
                                                       class="flex-1 px-3 h-10 bg-transparent text-sm font-mono tabular-nums text-zinc-800 placeholder:text-zinc-400 focus:outline-none dark:text-zinc-200" />
                                            </div>
                                            @error($field['model']) <p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p> @enderror
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Tagline --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Tagline') }}</label>
                                        <span class="text-[10px] text-zinc-400 tabular-nums">{{ Str::length($brandTagline ?? '') }}/120</span>
                                    </div>
                                    <input type="text" wire:model.live.debounce.500ms="brandTagline" maxlength="120" placeholder="{{ __('Your brand tagline...') }}"
                                           class="w-full h-10 px-3 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500" />
                                    @error('brandTagline') <p class="mt-1 text-[10px] text-rose-500">{{ $message }}</p> @enderror
                                </div>

                                {{-- Live preview + actions --}}
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="rounded-lg border border-zinc-200 dark:border-white/8 overflow-hidden flex shrink-0">
                                            <div class="w-8 h-8" style="background-color: {{ $brandPrimaryColor ?: '#4F46E5' }}"></div>
                                            <div class="w-8 h-8" style="background-color: {{ $brandSecondaryColor ?: '#0F172A' }}"></div>
                                        </div>
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 italic truncate">
                                            @if($brandTagline)
                                                "{{ Str::limit($brandTagline, 60) }}"
                                            @else
                                                <span class="not-italic">{{ __('Live preview of your brand identity') }}</span>
                                            @endif
                                        </p>
                                    </div>

                                @php
                                    $selectedBrandName = collect($availableBrands)->firstWhere('id', $selectedBrandId)['name'] ?? null;
                                    $saveLabel = $selectedBrandName
                                        ? __('Save to :name', ['name' => Str::limit($selectedBrandName, 24)])
                                        : __('Save brand kit');
                                @endphp
                                    <button type="button" wire:click="saveBrandKit"
                                            wire:loading.attr="disabled" wire:target="saveBrandKit"
                                            class="group/save inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold text-white shadow-xs shadow-indigo-500/20 hover:shadow-md hover:shadow-indigo-500/30 active:scale-[0.99] transition-all disabled:opacity-60 disabled:cursor-not-allowed shrink-0"
                                            style="background-color: #4F46E5;">
                                        <span class="flex items-center gap-2" wire:loading.remove.flex wire:target="saveBrandKit">
                                            <flux:icon.bookmark class="size-4" />
                                            {{ $saveLabel }}
                                        </span>
                                        <span class="flex items-center gap-2" wire:loading.flex wire:target="saveBrandKit">
                                            <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                            </svg>
                                            {{ __('Saving') }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AI Engine — image model picker --}}
                <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex items-start justify-between gap-3 mb-4 flex-wrap">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.cpu-chip class="size-4 text-emerald-400" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('AI Engine') }}</h3>
                                <p class="text-[11px] text-zinc-400">{{ __('Pick the image model that powers this generation') }}</p>
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
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            @foreach($providers as $provider)
                                @php
                                    $tier = $provider['tier'] ?? null;
                                    $tierMeta = match($tier) {
                                        'premium' => ['label' => __('Premium'), 'classes' => 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-300'],
                                        'mid'     => ['label' => __('Mid'),     'classes' => 'bg-zinc-100 border-zinc-200 text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300'],
                                        'budget'  => ['label' => __('Budget'),  'classes' => 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-950/30 dark:border-emerald-900/40 dark:text-emerald-300'],
                                        default   => null,
                                    };
                                    $textRendering = $provider['text_rendering'] ?? null;
                                    $maxResolution = $provider['max_resolution'] ?? null;
                                    $cost = $provider['credit_cost'] ?? 1;
                                @endphp
                                <button type="button" @click="model = '{{ $provider['key'] }}'; $wire.set('selectedModel', '{{ $provider['key'] }}')"
                                        class="group/eng relative rounded-xl border p-4 text-left transition-all hover:shadow-md hover:-translate-y-0.5"
                                        :class="model === '{{ $provider['key'] }}' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/30 ring-1 ring-indigo-500/20 shadow-sm' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-white/[.04] dark:hover:border-neutral-600'">

                                    {{-- Header — icon tile + name + sub-label --}}
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
                                                <span class="text-sm font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $provider['label'] }}</span>
                                                @if(! empty($provider['recommended']))
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-px rounded-full bg-amber-50 border border-amber-200 text-[9px] font-bold uppercase tracking-wider text-amber-700 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300">
                                                        <flux:icon.star class="size-2.5" /> {{ __('Top pick') }}
                                                    </span>
                                                @endif
                                                @if(! empty($provider['is_default']))
                                                    <span class="inline-flex items-center px-1.5 py-px rounded-full bg-indigo-50 border border-indigo-200 text-[9px] font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-300">{{ __('Default') }}</span>
                                                @endif
                                                @if($tierMeta)
                                                    <span class="inline-flex items-center px-1.5 py-px rounded-full border text-[9px] font-bold uppercase tracking-wider {{ $tierMeta['classes'] }}">{{ $tierMeta['label'] }}</span>
                                                @endif
                                                @if($textRendering === 'best')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full bg-zinc-100 border border-zinc-200 text-[9px] font-bold uppercase tracking-wider text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300" title="{{ __('Renders legible in-image text reliably') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/></svg>
                                                        {{ __('Text') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if(! empty($provider['sub_label']))
                                                <div class="text-[10px] text-zinc-500 dark:text-zinc-400 font-mono tabular-nums truncate">{{ $provider['sub_label'] }}</div>
                                            @endif
                                        </div>

                                        {{-- Selected check --}}
                                        <div class="absolute top-3 right-3 transition-all"
                                             :class="model === '{{ $provider['key'] }}' ? 'opacity-100 scale-100' : 'opacity-0 scale-90'">
                                            <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-indigo-600 shadow-sm">
                                                <flux:icon.check class="size-3 text-white" />
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    @if(! empty($provider['description']))
                                        <p class="mt-2.5 text-[11px] leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $provider['description'] }}</p>
                                    @endif

                                    {{-- Capability tags --}}
                                    @if(! empty($provider['tags']))
                                        <div class="mt-2.5 flex flex-wrap gap-1">
                                            @foreach($provider['tags'] as $tag)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[9px] font-semibold uppercase tracking-wider transition-colors"
                                                      :class="model === '{{ $provider['key'] }}' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-400'">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Footer — max-res + credit cost --}}
                                    <div class="mt-3 pt-3 border-t flex items-center justify-between gap-2 text-[10px]"
                                         :class="model === '{{ $provider['key'] }}' ? 'border-indigo-200/70 dark:border-indigo-500/20' : 'border-zinc-100 dark:border-white/8'">
                                        <span class="inline-flex items-center gap-1 text-zinc-500 dark:text-zinc-400 tabular-nums">
                                            @if($maxResolution)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" x2="14" y1="3" y2="10"/><line x1="3" x2="10" y1="21" y2="14"/></svg>
                                                {{ __('up to') }} {{ $maxResolution >= 4096 ? '4K' : ($maxResolution >= 2048 ? '2K' : $maxResolution . 'px') }}
                                            @else
                                                {{ __('Image generation') }}
                                            @endif
                                        </span>
                                        <span class="inline-flex items-center gap-1 font-bold tabular-nums"
                                              :class="model === '{{ $provider['key'] }}' ? 'text-indigo-600 dark:text-indigo-300' : 'text-zinc-600 dark:text-zinc-300'">
                                            <flux:icon.bolt class="size-3" />
                                            {{ $cost }} {{ trans_choice('credit|credits', $cost) }} <span class="font-normal opacity-70">/ {{ __('image') }}</span>
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-amber-200 bg-amber-50/40 p-6 text-center dark:border-amber-800/40 dark:bg-amber-950/20">
                            <span class="inline-flex w-10 h-10 items-center justify-center rounded-lg bg-white border border-amber-200 text-amber-600 mb-3 dark:bg-(--default-element-bg-color) dark:border-amber-800/40 dark:text-amber-400">
                                <flux:icon.exclamation-triangle class="size-5" />
                            </span>
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ __('No image engines enabled yet') }}</p>
                            <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('Ask an admin to add an API key for at least one image provider in AI Settings.') }}</p>
                        </div>
                    @endif
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
                            <span>{{ __('Next: Write Ad Copy') }}</span>
                        </span>
                    </button>
                </div>
            </div>


            {{-- ============================================ --}}
            {{-- STEP 4: Ad Copy & Detailed Prompt            --}}
            {{-- ============================================ --}}
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                @php
                    [$previewW, $previewH] = $this->resolvePresetDimensions();
                @endphp
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="lg:col-span-3 space-y-5">

                        {{-- Ad Copy --}}
                        @php
                            // Map of provider key → text-rendering quality (best | good | weak).
                            // Used by Alpine to surface a warning when the user types a headline
                            // and the currently-selected engine can't render text reliably.
                            $textRenderingMap = collect($providers)
                                ->mapWithKeys(fn ($p) => [$p['key'] => $p['text_rendering'] ?? 'good'])
                                ->toArray();
                        @endphp
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)"
                             x-data="{ textRenderingMap: {{ \Illuminate\Support\Js::from($textRenderingMap) }},
                                       get textQuality() { return this.textRenderingMap[this.model] || 'good'; },
                                       get hasWeakText() { return this.textQuality === 'weak' && (this.headline || this.ctaText || '').trim() !== ''; }
                                     }">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-blue-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    <flux:icon.pencil-square class="size-4 text-blue-400" />
                                </span>
                                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Ad Copy') }}</h3>
                                <span class="text-[10px] text-zinc-400 ml-1">{{ __('(optional — text overlaid on image)') }}</span>
                            </div>

                            {{-- Per-engine text-rendering warning band --}}
                            <div x-show="hasWeakText" x-cloak x-transition
                                 class="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-950/20">
                                <span class="inline-flex w-7 h-7 shrink-0 items-center justify-center rounded-lg bg-white border border-amber-200 text-amber-600 dark:bg-(--default-element-bg-color) dark:border-amber-800/40">
                                    <flux:icon.exclamation-triangle class="size-3.5" />
                                </span>
                                <div class="flex-1 min-w-0 text-[11px]">
                                    <p class="font-semibold text-amber-900 dark:text-amber-200">{{ __('In-image text may be unreliable on this engine') }}</p>
                                    <p class="text-amber-800 dark:text-amber-300/80 mt-0.5">{{ __('For legible headlines, switch to Ideogram 3.0, GPT Image 2, or Nano Banana 2 — or burn the text in via post-processing.') }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                {{-- Headline --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="adHeadline" class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Headline') }}</label>
                                        <span class="text-[10px] text-zinc-400 tabular-nums" x-text="(headline || '').length + '/300'"></span>
                                    </div>
                                    <input id="adHeadline" type="text" x-model="headline" maxlength="300"
                                           placeholder="{{ __('e.g. Summer Sale — 50% Off Everything') }}"
                                           class="w-full h-10 px-3 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500" />
                                </div>

                                {{-- CTA --}}
                                <div>
                                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">{{ __('Call to action') }}</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 mb-2">
                                        @foreach([__('Shop Now'), __('Learn More'), __('Sign Up'), __('Get Started'), __('Buy Now'), __('Try Free'), __('Book Now'), __('Download')] as $cta)
                                            <button type="button" @click="ctaText = @js($cta)"
                                                    class="text-[11px] py-1.5 px-2 rounded-lg border transition-all text-center"
                                                    :class="ctaText === @js($cta) ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold dark:bg-indigo-950/30 dark:text-indigo-300' : 'border-zinc-200 bg-white text-zinc-600 hover:border-indigo-200 hover:text-indigo-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:border-indigo-700/40'">{{ $cta }}</button>
                                        @endforeach
                                    </div>
                                    <input type="text" x-model="ctaText" maxlength="50"
                                           placeholder="{{ __('Or type your own CTA...') }}"
                                           class="w-full h-10 px-3 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500" />
                                </div>
                            </div>
                        </div>

                        {{-- Creative Description --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                        <flux:icon.sparkles class="size-4 text-amber-400" />
                                    </span>
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Creative Description') }}</h3>
                                    <span class="hidden sm:inline-flex items-center gap-1 px-1.5 py-px rounded-full bg-rose-50 border border-rose-200 text-[9px] font-bold uppercase tracking-wider text-rose-700 dark:bg-rose-950/30 dark:border-rose-900/40 dark:text-rose-300 ml-1">{{ __('Required') }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button"
                                            x-on:click="$dispatch('open-prompt-library', { context: 'image' }); $dispatch('modal-show', { name: 'prompt-library' })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-white bg-zinc-900 hover:bg-indigo-600 shadow-sm transition active:scale-95 dark:bg-neutral-950 dark:hover:bg-neutral-900 dark:border dark:border-white/8">
                                        <flux:icon.book-open class="size-3.5" />
                                        {{ __('Prompt Library') }}
                                    </button>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-md tabular-nums transition-colors"
                                          :class="(prompt || '').length > 4500 ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-zinc-100 text-zinc-500 dark:bg-neutral-700 dark:text-zinc-400'"
                                          x-text="(prompt || '').length + '/5000'"></span>
                                </div>
                            </div>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-3">{{ __('Describe the visual scene, composition, lighting, and elements you want.') }}</p>

                            <textarea x-model="prompt" rows="6" maxlength="5000"
                                      placeholder="{{ __('A vibrant product showcase on a clean white surface, soft natural lighting from the left, subtle shadow, the product centered with a slight angle, blurred lifestyle background with warm tones, professional advertising photography style...') }}"
                                      aria-label="{{ __('Ad description prompt') }}"
                                      class="w-full px-3 py-2.5 rounded-lg border border-zinc-200 bg-white text-sm leading-relaxed text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition resize-y min-h-[120px] dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8 dark:focus:border-indigo-500"></textarea>
                            <flux:error name="prompt" />

                            {{-- Toolbar — quick ideas + clear --}}
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400">{{ __('Quick ideas') }}</span>
                                @php
                                    $suggestions = match($adObjective) {
                                        'sales_conversion' => [__('Product on gradient background with price tag'), __('Before/after comparison layout'), __('Limited time offer with countdown feel')],
                                        'brand_awareness' => [__('Lifestyle scene with brand elements'), __('Abstract brand identity composition'), __('Hero image with brand story')],
                                        'product_launch' => [__('Dramatic product reveal with spotlight'), __('Unboxing moment with confetti'), __('Product floating with particle effects')],
                                        'lead_generation' => [__('Free guide cover with sign-up CTA'), __('Webinar invite with speaker portrait'), __('Lead magnet ebook mockup with bold headline')],
                                        'traffic' => [__('Bold headline with arrow leading to a CTA button'), __('Split layout with website preview on the right'), __('Eye-catching question hook with subtle UI elements')],
                                        'seasonal_promo' => [__('Holiday-themed product display with discount badge'), __('Seasonal background with bold sale percentage'), __('Festive flat-lay with limited-time offer ribbon')],
                                        'event_promotion' => [__('Event poster style with date and location overlay'), __('Stage spotlight composition with speaker silhouette'), __('Bold typography invite with venue mockup')],
                                        'retargeting' => [__('Reminder visual with cart icon and friendly headline'), __('Product close-up with subtle "still thinking?" hook'), __('Personalized scene with returning-customer warmth')],
                                        'app_install' => [__('Phone mockup showcasing the app interface'), __('Hand holding a phone with app screen and 5-star rating'), __('App icon hero with download badges')],
                                        'video_views' => [__('Cinematic still frame with play-button overlay'), __('Bold thumbnail-style headline with portrait subject'), __('Trailer-style hero composition with motion blur')],
                                        'followers' => [__('Friendly portrait with social handle and follow CTA'), __('Community grid collage with welcoming headline'), __('Behind-the-scenes lifestyle moment with brand mark')],
                                        'engagement' => [__('Question or poll-style headline with playful illustration'), __('Carousel-ready composition with curiosity hook'), __('Reaction-driven scene with bold expressive type')],
                                        default => [__('Clean product showcase with soft lighting'), __('Bold typography with gradient background'), __('Lifestyle scene with natural elements'), __('Minimalist design with strong focal point')],
                                    };
                                @endphp
                                @foreach($suggestions as $suggestion)
                                    <button type="button"
                                            @click="prompt = (prompt && prompt.trim().length) ? (prompt.trim() + (prompt.trim().endsWith('.') ? ' ' : '. ') + @js($suggestion)) : @js($suggestion)"
                                            class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-600 hover:bg-indigo-100 hover:text-indigo-700 transition dark:bg-neutral-700 dark:text-zinc-400 dark:hover:bg-indigo-900/40 dark:hover:text-indigo-300">+ {{ $suggestion }}</button>
                                @endforeach
                                <span class="flex-1"></span>
                                <button type="button" x-show="(prompt || '').length > 0" @click="prompt = ''"
                                        class="text-[11px] px-2 py-1 rounded-md text-zinc-500 hover:text-rose-600 hover:bg-rose-50 transition dark:text-zinc-400 dark:hover:text-rose-300 dark:hover:bg-rose-950/30">
                                    {{ __('Clear') }}
                                </button>
                            </div>
                        </div>

                        {{-- Reference Image --}}
                        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-sky-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    <flux:icon.photo class="size-4 text-sky-400" />
                                </span>
                                <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Reference Image') }}</h3>
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
                                        <p class="text-xs text-sky-600 font-semibold">{{ __('Reference uploaded') }}</p>
                                        <p class="text-[10px] text-zinc-500 mt-0.5">{{ __('Click to replace') }}</p>
                                    @else
                                        <flux:icon.cloud-arrow-up class="size-6 text-zinc-400 mb-1" />
                                        <p class="text-[12px] text-zinc-600 font-semibold dark:text-zinc-300">{{ __('Drop a reference image or click to upload') }}</p>
                                        <p class="text-[10px] text-zinc-400 mt-0.5">{{ __('PNG, JPG or WebP — used as a style/composition guide') }}</p>
                                    @endif
                                </span>
                                <input type="file" wire:model="referenceImage" accept="image/*" class="hidden" aria-label="{{ __('Upload reference image') }}" />
                            </label>

                            {{-- Selected preset reference (from the dashboard Preset Library). --}}
                            @if($presetReferenceUrl && ! $referenceImage)
                                <div class="mt-3 flex items-center gap-3 rounded-xl border border-indigo-200 bg-indigo-50/60 p-3 dark:border-indigo-500/30 dark:bg-indigo-500/10">
                                    <div class="w-14 h-14 shrink-0 rounded-lg overflow-hidden ring-1 ring-indigo-200 dark:ring-indigo-500/30 bg-white">
                                        <img src="{{ $presetReferenceUrl }}" alt="{{ __('Selected preset') }}" class="w-full h-full object-cover" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-300">{{ __('Selected preset') }}</div>
                                        <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ __('Using a library preset as your reference') }}</div>
                                        <div class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">{{ __('Upload your own above to replace it.') }}</div>
                                    </div>
                                    <button type="button" wire:click="clearPresetReference"
                                            class="shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                                            aria-label="{{ __('Remove selected preset') }}">
                                        <flux:icon.x-mark class="size-4" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right column: live brief + canvas preview --}}
                    <div class="lg:col-span-2 space-y-5">
                        <div class="lg:sticky lg:top-4 space-y-5">

                            {{-- Canvas preview — large, accurate, in-card --}}
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                <div class="px-5 py-3 flex items-center justify-between border-b border-zinc-100 dark:border-white/8">
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                                        <flux:icon.eye class="size-4 text-indigo-500" />
                                        {{ __('Live preview') }}
                                    </h3>
                                    <span class="text-[10px] text-zinc-400 font-mono tabular-nums">{{ $previewW }}×{{ $previewH }}</span>
                                </div>

                                <div class="relative bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.06),transparent_60%)] dark:bg-[radial-gradient(circle_at_50%_0%,rgba(79,70,229,0.12),transparent_60%)] p-5">
                                    {{-- Outer aspect-locked frame uses padding-bottom trick so it scales to its column width --}}
                                    @php
                                        $aspectPct = number_format(($previewH / max($previewW, 1)) * 100, 4, '.', '');
                                        // Cap excessively tall ratios at 150% so the preview never blows out the card height
                                        if ((float) $aspectPct > 150) { $aspectPct = '150'; }
                                    @endphp
                                    <div class="relative w-full mx-auto rounded-xl border-2 border-dashed border-indigo-300 dark:border-indigo-700 overflow-hidden shadow-inner"
                                         style="padding-bottom: {{ $aspectPct }}%; background: linear-gradient(135deg, {{ $brandPrimaryColor ?: '#4F46E5' }}1f, {{ $brandSecondaryColor ?: '#0F172A' }}1f);">
                                        {{-- Inner content positioned absolutely --}}
                                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center gap-2">
                                            <span x-show="(headline || '').trim() !== ''"
                                                  x-text="(headline || '').slice(0, 60)"
                                                  class="text-sm sm:text-base md:text-lg font-extrabold text-zinc-900 dark:text-white drop-shadow-sm leading-tight max-w-full"></span>
                                            <span x-show="!(headline || '').trim()" class="text-[10px] uppercase tracking-widest text-indigo-500/70 dark:text-indigo-300/60 font-semibold">{{ __('Headline preview') }}</span>

                                            <span class="text-[9px] font-mono tabular-nums text-zinc-500 dark:text-zinc-400">{{ $previewW }} × {{ $previewH }}</span>

                                            <span x-show="(ctaText || '').trim() !== ''" x-cloak
                                                  x-text="(ctaText || '').slice(0, 40)"
                                                  class="mt-1 inline-flex items-center gap-1 px-3 py-1 rounded-md text-[10px] font-bold text-white shadow-sm" :style="`background-color: ${'{{ $brandPrimaryColor ?: '#4F46E5' }}'}`"></span>
                                        </div>
                                    </div>

                                    <p class="mt-3 text-[10px] text-center text-zinc-400">{{ __('Visual approximation — final render uses your AI engine and brand colors.') }}</p>
                                </div>
                            </div>

                            {{-- Ad Brief Summary — Alpine-driven, updates in real time --}}
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <div class="px-5 py-3 flex items-center gap-2 border-b border-zinc-100 dark:border-white/8">
                                    <flux:icon.clipboard-document-list class="size-4 text-indigo-500" />
                                    <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ __('Ad Brief Summary') }}</h3>
                                </div>
                                <div class="p-4 space-y-1 text-[12px]"
                                     x-data="{
                                         pretty(v) { return v ? v.replace(/_|-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'; }
                                     }">
                                    @php
                                        // Each row: alpine value expression + label + lucide svg path body
                                        $rows = [
                                            ['label' => __('Objective'),  'value' => 'pretty(objective)',   'svg' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>'],
                                            ['label' => __('Industry'),   'value' => 'pretty(industry)',    'svg' => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
                                            ['label' => __('Canvas'),     'value' => 'pretty(preset)',      'svg' => '<rect width="18" height="18" x="3" y="3" rx="2"/>'],
                                            ['label' => __('Tone'),       'value' => 'pretty(tone)',        'svg' => '<path d="M3 11h3l4-7v18l-4-7H3z"/><path d="M14 6.5a5 5 0 0 1 0 11"/>'],
                                            ['label' => __('Style'),      'value' => 'pretty(style)',       'svg' => '<path d="M9.06 11.9 12 14l3.9-2.1L18 9l-3-3-3 2-3-2-3 3z"/><path d="M12 14v6"/>'],
                                            ['label' => __('Color'),      'value' => 'pretty(colorScheme)', 'svg' => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>'],
                                            ['label' => __('AI Engine'),  'value' => 'model || "—"',        'svg' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2"/><path d="M15 2v2"/><path d="M9 20v2"/><path d="M15 20v2"/><path d="M2 9h2"/><path d="M2 15h2"/><path d="M20 9h2"/><path d="M20 15h2"/>'],
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

                                    {{-- Headline preview --}}
                                    <div class="flex items-start gap-3 py-2 border-b border-zinc-100 dark:border-white/6">
                                        <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 mt-0.5 dark:bg-(--default-element-bg-color) dark:text-zinc-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16"/><path d="M4 18h16"/><path d="M4 6h16"/></svg>
                                        </span>
                                        <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 w-20 shrink-0 mt-0.5">{{ __('Headline') }}</span>
                                        <span class="text-zinc-800 dark:text-zinc-200 italic flex-1 break-words"
                                              x-text="(headline || '').trim() ? '“' + headline + '”' : '—'"></span>
                                    </div>

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

                                    {{-- Prompt preview --}}
                                    <div class="flex items-start gap-3 py-2">
                                        <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 mt-0.5 dark:bg-(--default-element-bg-color) dark:text-zinc-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M21 3 13 11"/><path d="M8 21H3v-5"/><path d="M3 21l8-8"/></svg>
                                        </span>
                                        <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 w-20 shrink-0 mt-0.5">{{ __('Prompt') }}</span>
                                        <span class="text-zinc-700 dark:text-zinc-300 leading-relaxed flex-1 break-words"
                                              x-text="(prompt || '').trim() ? ((prompt || '').length > 140 ? (prompt.slice(0, 140) + '…') : prompt) : '{{ __('Add a creative description on the left to see it here.') }}'"></span>
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
                        $isStuck = $elapsedSeconds >= 90;
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
                            get stuck() { return this.elapsed >= 90; },
                            get progressPercent() {
                                if (this.elapsed <= 0) return 8;
                                if (this.elapsed >= 60) return 92;
                                return Math.min(92, 8 + (this.elapsed / 60) * 84);
                            },
                            mmss() {
                                const m = Math.floor(this.elapsed / 60);
                                const s = this.elapsed % 60;
                                return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                            }
                         }"
                         wire:init="processQueuedImage"
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
                                    <flux:icon.sparkles class="size-7 text-white animate-pulse" />
                                </div>
                            </div>

                            <h3 class="text-xl font-extrabold text-zinc-800 dark:text-zinc-200 mb-1" x-text="stuck ? '{{ __('Almost there — putting on the final touches') }}' : '{{ __('AI is creating your ad') }}'"></h3>
                            <p class="text-sm text-zinc-500 mb-1" x-text="stuck ? '{{ __('This one is taking a little longer. It will appear here automatically the moment it is ready.') }}' : '{{ __('Typical render is under 30 seconds.') }}'"></p>

                            {{-- Live elapsed timer --}}
                            <p class="text-[11px] text-zinc-400 font-mono tabular-nums mb-6"><span x-text="mmss()"></span> {{ __('elapsed') }}</p>

                            {{-- Pipeline checklist --}}
                            <div class="max-w-xs mx-auto space-y-2 text-left mb-6">
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Brief analyzed') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center"><flux:icon.check class="size-2.5 text-white" /></div>
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Prompt optimized') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-indigo-500 flex items-center justify-center animate-pulse"><div class="w-1.5 h-1.5 rounded-full bg-white"></div></div>
                                    <span class="text-zinc-800 dark:text-zinc-200 font-medium">{{ __('Generating image...') }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <div class="w-4 h-4 rounded-full bg-zinc-200 dark:bg-neutral-700"></div>
                                    <span class="text-zinc-400">{{ __('Post-processing') }}</span>
                                </div>
                            </div>

                            {{-- Brand cool progress bar — animated stripes for indeterminate progress --}}
                            <div class="w-full bg-zinc-100 dark:bg-neutral-700 rounded-full h-1.5 overflow-hidden relative">
                                <div class="h-full rounded-full transition-[width] duration-700 ease-out"
                                     :style="`width: ${progressPercent}%; background: linear-gradient(120deg, #4F46E5, #0F172A);`"></div>
                                <div class="absolute inset-0 opacity-30 mix-blend-overlay pointer-events-none"
                                     style="background: repeating-linear-gradient(45deg, rgba(255,255,255,0.4) 0 6px, transparent 6px 12px); animation: db-stripe 1.2s linear infinite;"></div>
                            </div>
                            <style>@keyframes db-stripe { from { background-position: 0 0; } to { background-position: 24px 0; } }</style>

                            {{-- Reassurance panel — appears when a render takes longer than usual.
                                 The generation continues automatically in the background and the
                                 result drops in on the next refresh; the user can safely wait or
                                 leave. No backend details surfaced. --}}
                            <div x-show="stuck" x-cloak x-transition class="mt-6 pt-6 border-t border-zinc-100 dark:border-white/8">
                                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-left dark:border-indigo-900/40 dark:bg-indigo-950/20">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex w-8 h-8 shrink-0 items-center justify-center rounded-lg bg-white border border-indigo-200 text-indigo-600 dark:bg-(--default-element-bg-color) dark:border-indigo-800/40">
                                            <flux:icon.sparkles class="size-4" />
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-indigo-900 dark:text-indigo-200">{{ __('Still working on it') }}</p>
                                            <p class="text-[11px] text-indigo-800 dark:text-indigo-300/80 mt-0.5">{{ __('Your ad is being finished in the background. You can keep this page open or come back later — it will be waiting for you in your gallery.') }}</p>
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
                                <h3 class="text-sm font-bold text-rose-700 dark:text-rose-300">{{ __('Generation failed') }}</h3>
                                <span class="ml-auto text-[10px] text-rose-500/70">{{ $this->latestFailed->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="p-5 space-y-4">
                                @if($this->latestFailed->error_message)
                                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-[12px] text-zinc-700 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-300 break-words">
                                        {{ $this->latestFailed->error_message }}
                                    </div>
                                @endif
                                <p class="text-[12px] text-zinc-500 dark:text-zinc-400">
                                    {{ __('Edit your brief and try again, or run the same request synchronously.') }}
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
                    @php $latestCompleted = $this->currentRunCompleted; @endphp

                    @if($latestCompleted && $latestCompleted->file_path)
                        {{-- RESULT STATE --}}
                        <div class="max-w-4xl mx-auto">
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-white"><flux:icon.check-circle class="size-5" /><span class="text-sm font-bold">{{ __('Your ad is ready!') }}</span></div>
                                    <span class="text-[10px] text-white/70">{{ $latestCompleted->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-5">
                                    <div class="md:col-span-3 relative bg-zinc-900 flex items-center justify-center p-6 min-h-[350px] group/preview cursor-zoom-in" @click="previewZoom = true">
                                        <img src="{{ $latestCompleted->fileUrl() }}" alt="{{ __('Generated ad') }}" class="max-h-[500px] max-w-full rounded-lg shadow-2xl transition-transform duration-300 group-hover/preview:scale-[1.02]" />
                                        {{-- Explicit expand affordance --}}
                                        <button type="button"
                                                @click.stop="previewZoom = true"
                                                class="absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-black/50 hover:bg-black/70 text-white text-[11px] font-medium backdrop-blur-sm border border-white/15 transition"
                                                aria-label="{{ __('Expand preview') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
                                            {{ __('Expand') }}
                                        </button>
                                    </div>
                                    <div class="md:col-span-2 p-5 space-y-4">
                                        <div>
                                            <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400 mb-1">{{ __('Prompt') }}</div>
                                            <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">{{ Str::limit($latestCompleted->prompt, 200) }}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)"><div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Size') }}</div><div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 font-mono">{{ $latestCompleted->width }}×{{ $latestCompleted->height }}</div></div>
                                            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)"><div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">{{ __('Model') }}</div><div class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $latestCompleted->provider }}</div></div>
                                        </div>
                                        <div class="space-y-2 pt-2">
                                            <a href="{{ route('user.studio.download', $latestCompleted->id) }}"
                                               class="group/cta relative w-full flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl text-sm font-semibold text-white shadow-xs shadow-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden"
                                               style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                                <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                                                      style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>
                                                <span class="relative flex items-center gap-2"><flux:icon.arrow-down-tray class="size-4" /> {{ __('Download HD') }}</span>
                                            </a>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button wire:click="prepareRegeneration" @click="step = 4" class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-xl text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/10 transition"><flux:icon.arrow-path class="size-3.5" /> {{ __('Regenerate') }}</button>
                                                <button @click="goToStartFresh()" class="flex items-center justify-center gap-1.5 px-3 py-2.5 border border-zinc-300 dark:border-neutral-600 rounded-xl text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/10 transition"><flux:icon.plus class="size-3.5" /> {{ __('New Ad') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fullscreen preview lightbox --}}
                        <div x-show="previewZoom"
                             x-cloak
                             x-transition.opacity
                             @keydown.escape.window="previewZoom = false"
                             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 backdrop-blur-sm p-4 sm:p-8"
                             @click="previewZoom = false">
                            <button type="button"
                                    @click.stop="previewZoom = false"
                                    class="absolute top-4 right-4 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-sm transition"
                                    aria-label="{{ __('Close preview') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                            <figure class="flex flex-col items-center gap-4 max-h-full" @click.stop>
                                <img src="{{ $latestCompleted->fileUrl() }}" alt="{{ __('Generated ad') }}" class="max-h-[80vh] max-w-full rounded-lg shadow-2xl object-contain" />
                                <figcaption class="flex items-center gap-4 text-[11px] text-white/70 font-mono">
                                    <span>{{ $latestCompleted->width }}×{{ $latestCompleted->height }}</span>
                                    <span class="text-white/30">·</span>
                                    <span>{{ $latestCompleted->provider }}</span>
                                    <a href="{{ route('user.studio.download', $latestCompleted->id) }}" class="inline-flex items-center gap-1.5 text-white/90 hover:text-white underline-offset-2 hover:underline">
                                        <flux:icon.arrow-down-tray class="size-3.5" /> {{ __('Download HD') }}
                                    </a>
                                </figcaption>
                            </figure>
                        </div>

                    @else
                        {{-- REVIEW & LAUNCH STATE --}}
                        @php
                            [$reviewW, $reviewH] = $this->resolvePresetDimensions();
                            $resolvedPreset = $this->findPreset($this->selectedPreset);
                            $presetLabel = $resolvedPreset['label'] ?? ($this->selectedPreset === 'custom' ? __('Custom Size') : '—');
                            $modelMeta = collect($providers)->firstWhere('key', $this->selectedModel);

                            // Pretty-print snake/kebab keys ("brand_awareness" → "Brand Awareness")
                            $pretty = fn (?string $v) => $v ? ucwords(str_replace(['_', '-'], ' ', $v)) : null;

                            // Lucide-style monochrome icons (path bodies only)
                            $reviewIcons = [
                                'objective'   => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
                                'industry'    => '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                                'canvas'      => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 3v18"/>',
                                'engine'      => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2"/><path d="M15 2v2"/><path d="M9 20v2"/><path d="M15 20v2"/><path d="M2 9h2"/><path d="M2 15h2"/><path d="M20 9h2"/><path d="M20 15h2"/>',
                                'tone'        => '<path d="M3 11h3l4-7v18l-4-7H3z"/><path d="M14 6.5a5 5 0 0 1 0 11"/>',
                                'style'       => '<circle cx="13.5" cy="6.5" r="2.5"/><circle cx="19" cy="13" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="10" cy="20" r="2.5"/>',
                                'colors'      => '<circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>',
                                'headline'    => '<path d="M4 6h16"/><path d="M4 12h10"/><path d="M4 18h16"/>',
                                'cta'         => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                                'prompt'      => '<path d="M16 3h5v5"/><path d="M21 3 13 11"/><path d="M8 21H3v-5"/><path d="M3 21l8-8"/>',
                                'brand'       => '<path d="M20.91 8.84 8.56 21.18a4.2 4.2 0 0 1-5.94-5.94L14.96 2.9a3 3 0 0 1 4.24 0l1.71 1.71a3 3 0 0 1 0 4.23z"/><circle cx="13" cy="9" r="2"/>',
                                'reference'   => '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
                                'check'       => '<polyline points="20 6 9 17 4 12"/>',
                                'sparkle'     => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>',
                            ];

                            // Field rows that always render (Alpine reads through entangled vars)
                            $coreFields = [
                                ['key' => 'objective', 'label' => __('Objective'),  'value' => 'objective'],
                                ['key' => 'industry',  'label' => __('Industry'),   'value' => 'industry'],
                                ['key' => 'tone',      'label' => __('Tone'),       'value' => 'tone'],
                                ['key' => 'style',     'label' => __('Style'),      'value' => 'style'],
                                // Colors only matter when the brand kit is OFF — with it on, the
                                // brand palette is used instead of the chosen color scheme.
                                ['key' => 'colors',    'label' => __('Colors'),     'value' => 'colorScheme', 'hideWhenBrandKit' => true],
                            ];

                            $hasInsufficientCredits = $creditBalance < (int) $this->currentCost;
                        @endphp

                        <div class="max-w-4xl mx-auto">
                            <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">

                                {{-- Header --}}
                                <div class="px-6 py-4 flex items-center gap-3" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                                    <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['check'] !!}</svg>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-bold text-white">{{ __('Review your ad brief') }}</h3>
                                        <p class="text-xs text-white/70 mt-0.5">{{ __('Everything looks good? Hit generate to create your ad.') }}</p>
                                    </div>
                                </div>

                                <div class="p-6 space-y-5">

                                    {{-- Headline preview banner — only if a headline was set --}}
                                    <div x-show="(headline || '').trim() !== ''" x-cloak
                                         class="rounded-xl border border-zinc-200 bg-white p-5 dark:bg-(--default-element-bg-color) dark:border-white/8">
                                        <div class="flex items-center gap-2 text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400 mb-2">
                                            <span class="inline-flex w-5 h-5 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['headline'] !!}</svg>
                                            </span>
                                            {{ __('Headline') }}
                                        </div>
                                        <p class="text-base font-bold text-zinc-800 dark:text-zinc-100" x-text="'“' + (headline || '') + '”'"></p>
                                        <div x-show="(ctaText || '').trim() !== ''" x-cloak class="mt-3">
                                            <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400">
                                                <span class="inline-flex w-5 h-5 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['cta'] !!}</svg>
                                                </span>
                                                {{ __('CTA') }}
                                            </span>
                                            <div class="mt-1.5">
                                                <span class="inline-flex items-center px-3 py-1 rounded-md bg-indigo-50 border border-indigo-200 text-sm font-bold text-indigo-700 dark:bg-indigo-950/40 dark:border-indigo-800/40 dark:text-indigo-300" x-text="ctaText"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Canvas card — preset name + dimensions + ratio + visual --}}
                                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:bg-(--default-element-bg-color) dark:border-white/8 flex items-center gap-5">
                                        <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-lg bg-white border border-zinc-200 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['canvas'] !!}</svg>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Canvas') }}</div>
                                            <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $presetLabel }}</div>
                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 font-mono tabular-nums mt-0.5">
                                                {{ $reviewW }} × {{ $reviewH }}
                                                @if($resolvedPreset && ! empty($resolvedPreset['ratio'])) <span class="text-zinc-400 dark:text-zinc-500">· {{ $resolvedPreset['ratio'] }}</span> @endif
                                            </div>
                                        </div>
                                        @php
                                            // Visual aspect chip (max 56×56)
                                            $maxSide = 56;
                                            $r = $reviewW / max($reviewH, 1);
                                            $aw = $r >= 1 ? $maxSide : (int) round($maxSide * $r);
                                            $ah = $r >= 1 ? (int) round($maxSide / $r) : $maxSide;
                                        @endphp
                                        <div class="hidden sm:flex items-center justify-center w-14 h-14 shrink-0">
                                            <div class="rounded-md border-2 border-dashed border-zinc-300 dark:border-neutral-600 bg-zinc-50 dark:bg-(--default-element-light-bg-color)" style="width: {{ $aw }}px; height: {{ $ah }}px;"></div>
                                        </div>
                                    </div>

                                    {{-- AI Engine card — model name + sub-label + tags --}}
                                    @if($modelMeta)
                                        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:bg-(--default-element-bg-color) dark:border-white/8 flex items-start gap-4">
                                            <span class="inline-flex w-12 h-12 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                                                @if(! empty($modelMeta['icon_svg']))
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $modelMeta['icon_svg'] !!}</svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['engine'] !!}</svg>
                                                @endif
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('AI Engine') }}</div>
                                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $modelMeta['label'] }}</div>
                                                @if(! empty($modelMeta['sub_label']))
                                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 font-mono tabular-nums truncate">{{ $modelMeta['sub_label'] }}</div>
                                                @endif
                                                @if(! empty($modelMeta['tags']))
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        @foreach($modelMeta['tags'] as $tag)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-zinc-100 text-zinc-600 text-[9px] font-semibold uppercase tracking-wider dark:bg-(--default-element-light-bg-color) dark:text-zinc-400">{{ $tag }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Core attributes grid — Objective, Industry, Tone, Style, Colors --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($coreFields as $field)
                                            <div class="p-4 rounded-xl border border-zinc-200 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8"
                                                 x-show="(typeof {{ $field['value'] }} !== 'undefined' && {{ $field['value'] }} && {{ $field['value'] }}.toString().trim() !== ''){{ !empty($field['hideWhenBrandKit']) ? ' && !useBrandKit' : '' }}">
                                                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400 mb-2">
                                                    <span class="inline-flex w-5 h-5 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons[$field['key']] ?? $reviewIcons['check'] !!}</svg>
                                                    </span>
                                                    {{ $field['label'] }}
                                                </div>
                                                <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-100"
                                                     x-text="{{ $field['value'] }} ? {{ $field['value'] }}.toString().replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : '—'"></div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Brand kit card — only if user has set anything AND opted to apply it --}}
                                    @if($brandPrimaryColor || $brandSecondaryColor || $brandTagline || $this->activeBrandName)
                                        <div x-show="useBrandKit" x-cloak class="p-4 rounded-xl border border-zinc-200 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8">
                                            <div class="flex items-center justify-between gap-2 mb-3">
                                                <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                                                    <span class="inline-flex w-5 h-5 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['brand'] !!}</svg>
                                                    </span>
                                                    {{ __('Brand kit') }}
                                                </div>
                                                @if($this->activeBrandName)
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-indigo-50 border border-indigo-200 text-[10px] font-bold text-indigo-700 dark:bg-indigo-950/40 dark:border-indigo-800/40 dark:text-indigo-300">
                                                        <flux:icon.check-badge class="size-3" />
                                                        {{ $this->activeBrandName }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($this->activeBrandName)
                                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-3">
                                                    {{ __('Generation will use the brand ":name". Its palette overrides the color scheme.', ['name' => $this->activeBrandName]) }}
                                                </p>
                                            @endif
                                            <div class="flex items-center gap-4 flex-wrap">
                                                @if($brandPrimaryColor || $brandSecondaryColor)
                                                    <div class="flex items-center gap-2">
                                                        @if($brandPrimaryColor)
                                                            <span class="inline-flex items-center gap-2 text-[11px] text-zinc-600 dark:text-zinc-300">
                                                                <span class="w-5 h-5 rounded-md border border-zinc-200 dark:border-white/8 shadow-sm" style="background-color: {{ $brandPrimaryColor }}"></span>
                                                                <span class="font-mono tabular-nums">{{ $brandPrimaryColor }}</span>
                                                            </span>
                                                        @endif
                                                        @if($brandSecondaryColor)
                                                            <span class="inline-flex items-center gap-2 text-[11px] text-zinc-600 dark:text-zinc-300">
                                                                <span class="w-5 h-5 rounded-md border border-zinc-200 dark:border-white/8 shadow-sm" style="background-color: {{ $brandSecondaryColor }}"></span>
                                                                <span class="font-mono tabular-nums">{{ $brandSecondaryColor }}</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($brandTagline)
                                                    <span class="text-[11px] italic text-zinc-500 dark:text-zinc-400">"{{ $brandTagline }}"</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Reference image card — uploaded file or a library preset --}}
                                    @if($referenceImage)
                                        <div class="p-4 rounded-xl border border-zinc-200 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8 flex items-center gap-3">
                                            <span class="inline-flex w-9 h-9 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['reference'] !!}</svg>
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Reference image') }}</div>
                                                <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ __('Attached as a style/composition guide') }}</div>
                                            </div>
                                            <flux:icon.check-circle class="size-5 text-emerald-500" />
                                        </div>
                                    @elseif($presetReferenceUrl)
                                        <div class="p-4 rounded-xl border border-zinc-200 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8 flex items-center gap-3">
                                            <span class="inline-flex w-11 h-11 shrink-0 items-center justify-center rounded-md overflow-hidden ring-1 ring-zinc-200 dark:ring-white/10 bg-white">
                                                <img src="{{ $presetReferenceUrl }}" alt="{{ __('Selected preset') }}" class="w-full h-full object-cover" />
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __('Reference image') }}</div>
                                                <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ __('Library preset used as a style/composition guide') }}</div>
                                            </div>
                                            <flux:icon.check-circle class="size-5 text-emerald-500" />
                                        </div>
                                    @endif

                                    {{-- Creative Description — full text --}}
                                    <div class="p-5 rounded-xl border border-zinc-200 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                                                <span class="inline-flex w-5 h-5 items-center justify-center rounded-md bg-zinc-100 text-zinc-700 dark:bg-(--default-element-light-bg-color) dark:text-zinc-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['prompt'] !!}</svg>
                                                </span>
                                                {{ __('Creative description') }}
                                            </div>
                                            <span class="text-[10px] text-zinc-400 font-mono tabular-nums">{{ Str::length($prompt ?? '') }} {{ __('chars') }}</span>
                                        </div>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">{{ $prompt ?: __('No description provided.') }}</p>
                                    </div>
                                </div>

                                {{-- Footer — cost, balance, generate --}}
                                <div class="border-t border-zinc-200 dark:border-white/8 px-6 py-5 bg-zinc-50/40 dark:bg-neutral-900/30">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                                        <div class="flex items-center gap-3 text-[12px]">
                                            <span class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                                                <flux:icon.bolt class="size-3.5 text-amber-500" />
                                                {{ __('Cost') }}
                                                <span class="font-bold text-zinc-800 dark:text-zinc-200 tabular-nums">{{ $this->currentCost }} {{ trans_choice('credit|credits', $this->currentCost) }}</span>
                                            </span>
                                            <span class="text-zinc-300 dark:text-neutral-600">·</span>
                                            <span class="inline-flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
                                                {{ __('Balance') }}
                                                <span class="font-bold text-zinc-800 dark:text-zinc-200 tabular-nums">{{ number_format($creditBalance) }}</span>
                                            </span>
                                        </div>
                                        @if($hasInsufficientCredits)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-rose-50 border border-rose-200 text-[11px] font-semibold text-rose-700 dark:bg-rose-950/30 dark:border-rose-900/40 dark:text-rose-300">
                                                <flux:icon.exclamation-triangle class="size-3.5" />
                                                {{ __('Not enough credits to generate') }}
                                            </span>
                                        @endif
                                    </div>

                                    <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                                            @disabled($hasInsufficientCredits)
                                            class="group/cta relative w-full flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                                            style="background: linear-gradient(120deg, #4F46E5, #0F172A);">

                                        {{-- Brighter hover layer --}}
                                        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                                              style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>

                                        {{-- Default label --}}
                                        <span class="relative flex items-center gap-2.5" wire:loading.remove.flex wire:target="generate">
                                            <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $reviewIcons['sparkle'] !!}</svg>
                                            </span>
                                            <span>{{ __('Generate my ad') }}</span>
                                        </span>

                                        {{-- Loading label --}}
                                        <span class="relative flex items-center gap-2.5" wire:loading.flex wire:target="generate">
                                            <svg class="size-5 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                            </svg>
                                            <span class="whitespace-nowrap">{{ __('Launching AI') }}</span>
                                            <span class="inline-flex items-center gap-1 shrink-0">
                                                <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                                <span class="w-1 h-1 rounded-full bg-white animate-pulse" style="animation-delay: 150ms"></span>
                                                <span class="w-1 h-1 rounded-full bg-white animate-pulse" style="animation-delay: 300ms"></span>
                                            </span>
                                        </span>
                                    </button>

                                    <div class="flex justify-center mt-3">
                                        <button type="button" @click="step = 4" class="text-xs text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition flex items-center gap-1">
                                            <flux:icon.arrow-left class="size-3" /> {{ __('Go back and edit') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Recent Generations --}}
                @if($this->recentGenerations->count() > 0)
                    <div class="max-w-4xl mx-auto mt-8">
                        <button @click="showHistory = !showHistory" class="w-full flex items-center justify-between p-4 rounded-xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) text-left">
                            <h3 class="text-xs font-bold text-zinc-600 dark:text-zinc-400 flex items-center gap-2"><flux:icon.clock class="size-3.5" /> {{ __('Previous Generations') }} <span class="bg-zinc-100 dark:bg-neutral-700 text-zinc-500 px-1.5 py-0.5 rounded text-[10px]">{{ $this->recentGenerations->count() }}</span></h3>
                            <flux:icon.chevron-down class="size-3.5 text-zinc-400 transition-transform" ::class="showHistory && 'rotate-180'" />
                        </button>
                        <div x-show="showHistory" x-collapse class="mt-2 rounded-xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 p-3">
                                @foreach($this->recentGenerations as $asset)
                                    <button wire:click="loadFromAsset({{ $asset->id }})" @click="step = 4" class="group rounded-lg overflow-hidden border border-zinc-200 dark:border-white/8 hover:shadow-md transition">
                                        @if($asset->file_path)
                                            <img src="{{ $asset->fileUrl() }}" alt="" class="w-full aspect-square object-cover group-hover:scale-105 transition-transform" />
                                        @else
                                            <div class="w-full aspect-square bg-zinc-100 dark:bg-neutral-700 flex items-center justify-center"><flux:icon.image-plus class="size-6 text-zinc-400" /></div>
                                        @endif
                                        <div class="p-1.5"><p class="text-[9px] truncate text-zinc-500">{{ Str::limit($asset->prompt, 30) }}</p></div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Prompt Library modal (image context) --}}
    <livewire:user.prompts.prompt-library context="image" />
</div>
