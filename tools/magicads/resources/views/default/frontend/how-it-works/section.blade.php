{{-- How it works — offset vertical timeline with big display numerals. --}}
@php
    $steps = [
        [
            'num' => '01',
            'title' => __('Brief it'),
            'body' => __('One sentence or one paragraph. Tell the studio the offer, audience, and tone — or paste a URL and let it infer.'),
            'tag'  => __('~20 seconds'),
            'preview' => 'brief',
        ],
        [
            'num' => '02',
            'title' => __('Pick a canvas'),
            'body' => __('Twenty-plus presets for Meta, TikTok, YouTube, LinkedIn, and Google Display — or render every size at once.'),
            'tag'  => __('1 click'),
            'preview' => 'presets',
        ],
        [
            'num' => '03',
            'title' => __('Generate and ship'),
            'body' => __('Preview all sizes side-by-side, remix the winners, and export production-ready files with captions.'),
            'tag'  => __('<30 seconds'),
            'preview' => 'ship',
        ],
    ];
@endphp

<section id="how-it-works" class="relative border-y border-[var(--l-hairline)] bg-[var(--l-bg-2)] py-24 sm:py-32">
    <div aria-hidden="true" class="l-grid absolute inset-0 opacity-50"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('The flow') }}
            </span>
            <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                {{ __('Three steps.') }}
                <span class="l-accent">{{ __('Zero handoffs.') }}</span>
            </h2>
        </div>

        <div class="relative mt-16 space-y-6">
            {{-- Connecting line --}}
            <div aria-hidden="true" class="absolute left-1/2 top-0 bottom-0 hidden w-px -translate-x-1/2 l-vdash lg:block"></div>

            @foreach ($steps as $i => $step)
                @php $isEven = $i % 2 === 1; @endphp
                <div class="grid items-center gap-6 lg:grid-cols-2 lg:gap-14">
                    {{-- Copy column --}}
                    <div class="@if($isEven) lg:col-start-2 lg:row-start-1 @endif">
                        <div class="l-card relative overflow-hidden p-7 sm:p-8">
                            <div class="flex items-start justify-between">
                                <span class="l-display text-6xl font-extrabold tracking-[-0.04em] text-black/10 sm:text-7xl">{{ $step['num'] }}</span>
                                <span class="l-chip">
                                    <span class="h-1 w-1 rounded-full bg-[#4F46E5]"></span>
                                    {{ $step['tag'] }}
                                </span>
                            </div>
                            <h3 class="mt-4 text-2xl font-bold tracking-tight text-black sm:text-3xl">{{ $step['title'] }}</h3>
                            <p class="mt-3 max-w-md text-[14px] leading-relaxed text-black/65">{{ $step['body'] }}</p>
                        </div>
                    </div>

                    {{-- Visual column --}}
                    <div class="@if($isEven) lg:col-start-1 lg:row-start-1 @endif">
                        <div class="relative mx-auto max-w-md">
                            {{-- Indigo node dot on the spine --}}
                            <div aria-hidden="true"
                                 class="absolute left-1/2 top-1/2 z-10 hidden h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#4F46E5] ring-4 ring-[var(--l-bg-2)] lg:block
                                        {{ $isEven ? 'lg:left-auto lg:right-0 lg:translate-x-1/2' : 'lg:left-0 lg:-translate-x-1/2' }}"
                                 style="box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.3), 0 10px 24px -6px rgba(79, 70, 229, 0.5);">
                            </div>

                            @if ($step['preview'] === 'brief')
                                <div class="l-card p-5">
                                    <div class="flex items-center gap-2">
                                        <span class="l-mono text-[10px] uppercase tracking-wider text-black/45">{{ __('Brief input') }}</span>
                                        <span class="l-pulse inline-block h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                                    </div>
                                    <div class="mt-3 rounded-lg border border-[var(--l-hairline)] bg-white p-3">
                                        <p class="text-[13px] text-black">
                                            <span class="text-black">{{ __('Autumn drop.') }}</span>
                                            <span class="text-black/60">{{ __(' Playful tone, amber & plum. Target urban millennials.') }}</span>
                                            <span class="ml-0.5 inline-block h-3.5 w-[2px] translate-y-[2px] animate-pulse bg-[#4F46E5]"></span>
                                        </p>
                                    </div>
                                    <div class="mt-3 flex items-center gap-2 text-[11px] text-black/45">
                                        <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 1v10M1 6h10"/></svg>
                                        <span>{{ __('Drop files · paste URL') }}</span>
                                    </div>
                                </div>
                            @elseif ($step['preview'] === 'presets')
                                <div class="l-card p-5">
                                    <div class="flex items-center justify-between">
                                        <span class="l-mono text-[10px] uppercase tracking-wider text-black/45">{{ __('Canvases') }}</span>
                                        <span class="l-chip l-chip--ink !px-2 !py-0 !text-[9px]">12 ✓</span>
                                    </div>
                                    <div class="mt-4 grid grid-cols-6 gap-1.5">
                                        @foreach ([
                                            ['name' => 'Instagram', 'ratio' => '1/1'],
                                            ['name' => 'Reel',      'ratio' => '9/16'],
                                            ['name' => 'Facebook',  'ratio' => '1/1'],
                                            ['name' => 'YouTube',   'ratio' => '16/9'],
                                            ['name' => 'Twitter',   'ratio' => '1/1'],
                                            ['name' => 'Story',     'ratio' => '4/5'],
                                            ['name' => 'TikTok',    'ratio' => '9/16'],
                                            ['name' => 'LinkedIn',  'ratio' => '1/1'],
                                            ['name' => 'Banner',    'ratio' => '1.91/1'],
                                            ['name' => 'Pinterest', 'ratio' => '1/1'],
                                            ['name' => 'Shorts',    'ratio' => '9/16'],
                                            ['name' => 'Threads',   'ratio' => '1/1'],
                                        ] as $canvas)
                                            @php [$w,$h] = explode('/', $canvas['ratio']); @endphp
                                            <div class="flex flex-col items-center justify-center gap-0.5 rounded-sm p-0.5 text-center"
                                                 style="aspect-ratio: {{ $w }}/{{ $h }};
                                                        background: var(--l-bg-3);
                                                        border: 1px solid var(--l-hairline);">
                                                <span class="l-mono text-[8px] font-bold leading-tight text-black">{{ $canvas['name'] }}</span>
                                                <span class="l-mono text-[7px] font-bold leading-tight text-black/70">{{ str_replace('/', ':', $canvas['ratio']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="l-card l-card--ink p-5">
                                    <div class="flex items-center justify-between">
                                        <span class="l-mono text-[10px] uppercase tracking-wider text-white/50">{{ __('Export queue') }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#4F46E5] px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">
                                            <span class="h-1 w-1 rounded-full bg-white"></span>
                                            LIVE
                                        </span>
                                    </div>
                                    <div class="mt-4 space-y-1.5">
                                        @foreach (['IG Feed · 1080×1080', 'IG Reel · 1080×1920', 'FB Feed · 1200×628', 'YT Short · 1080×1920'] as $idx => $item)
                                            <div class="flex items-center gap-3 rounded-md border border-white/10 bg-white/5 px-2.5 py-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                <span class="l-mono flex-1 text-[10px] text-white/70">{{ $item }}</span>
                                                <span class="l-mono text-[10px] text-white">100%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
