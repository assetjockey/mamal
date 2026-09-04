@php
    $activityMax = max(1, (int) collect($activityDays)->max('total'));
    $pageMax = max(1, (int) collect($pageBreakdown)->max('value'));
    $typeTotal = max(1, (int) collect($typeBreakdown)->sum('value'));
    $viewEvents = (int) data_get(collect($typeBreakdown)->firstWhere('label', 'View'), 'value', 0);
    $viewsShare = $typeTotal > 0 ? round(($viewEvents / $typeTotal) * 100) : 0;
    $clicksShare = max(0, 100 - $viewsShare);
@endphp

<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background: linear-gradient(135deg, rgba(37,99,235,0.10), rgba(20,184,166,0.08) 48%, var(--theme-surface-base));">
        <div class="grid gap-6 p-5 sm:p-7 xl:grid-cols-[minmax(0,1fr)_420px] xl:items-end">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-accent);">{{ __('Link Bio Analytics') }}</p>
                <h1 class="mt-3 max-w-3xl text-[2rem] font-semibold leading-[1.05] tracking-[-0.035em] sm:text-[2.7rem]" style="color: var(--theme-header-text-color);">{{ __('Know which Bio pages turn visitors into clicks.') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Track views, outbound clicks, domains, UTM presets, and pixels from one analytics workspace.') }}</p>
            </div>

            <div class="rounded-[1rem] border p-4 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('14 day signal') }}</p>
                        <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($totals['views']) }} {{ __('views') }} · {{ number_format($totals['clicks']) }} {{ __('clicks') }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(37,99,235,0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-line"></i>
                    </span>
                </div>
                <div class="mt-5 flex h-28 items-end gap-2">
                    @foreach ($activityDays as $day)
                        @php($height = max(8, round(($day['total'] / $activityMax) * 100)))
                        <div class="group flex min-w-0 flex-1 flex-col items-center gap-2">
                            <div class="flex h-24 w-full items-end rounded-full p-1" style="background-color: rgba(var(--theme-border-color-rgb),0.24);">
                                <div class="w-full rounded-full" style="height: {{ $height }}%; background: linear-gradient(180deg, #2563eb, #14b8a6);"></div>
                            </div>
                            <span class="text-[10px] font-semibold" style="color: var(--theme-muted-text-color);">{{ $day['short'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Pages'), 'value' => number_format($totals['pages']), 'meta' => __('Bio pages in workspace'), 'icon' => 'fa-light fa-link', 'color' => '#2563eb'],
            ['label' => __('Views'), 'value' => number_format($totals['views']), 'meta' => __('Public page opens'), 'icon' => 'fa-light fa-eye', 'color' => '#0f766e'],
            ['label' => __('Clicks'), 'value' => number_format($totals['clicks']), 'meta' => __('Outbound link clicks'), 'icon' => 'fa-light fa-arrow-pointer', 'color' => '#d97706'],
            ['label' => __('CTR'), 'value' => $totals['ctr'].'%', 'meta' => __('Clicks divided by views'), 'icon' => 'fa-light fa-percent', 'color' => '#7c3aed'],
        ] as $metric)
            <div class="rounded-[1rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $metric['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $metric['value'] }}</p>
                        <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $metric['meta'] }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $metric['color'] }}18; color: {{ $metric['color'] }};">
                        <i class="{{ $metric['icon'] }}"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
        <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Page activity') }}</p>
                    <h2 class="mt-2 text-xl font-semibold tracking-[-0.025em]" style="color: var(--theme-header-text-color);">{{ __('Top Bio pages') }}</h2>
                </div>
                <span class="rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.7); color: var(--theme-muted-text-color);">{{ __('Views + clicks') }}</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($pageBreakdown as $row)
                    @php($width = max(5, round(($row['value'] / $pageMax) * 100)))
                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.12em]" style="background-color: rgba(37,99,235,0.10); color: #2563eb;">{{ $row['status'] }}</span>
                                </div>
                                <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">/{{ $row['slug'] }}</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);">
                                    <div class="h-full rounded-full" style="width: {{ $width }}%; background: linear-gradient(90deg, #2563eb, #14b8a6);"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-right">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($row['views']) }}</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Views') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($row['clicks']) }}</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Clicks') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $row['ctr'] }}%</p>
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('CTR') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-ui.empty icon="fa-light fa-chart-mixed" :title="__('No pages to analyze yet')" :description="__('Create a bio page first, then insights will appear here.')" />
                @endforelse
            </div>
        </section>

        <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Event mix') }}</p>
            <h2 class="mt-2 text-xl font-semibold tracking-[-0.025em]" style="color: var(--theme-header-text-color);">{{ __('Visitor intent') }}</h2>
            <div class="mt-5 grid place-items-center">
                <div class="grid h-44 w-44 place-items-center rounded-full" style="background: conic-gradient(#2563eb 0 {{ $viewsShare }}%, #14b8a6 {{ $viewsShare }}% 100%);">
                    <div class="grid h-28 w-28 place-items-center rounded-full text-center" style="background-color: var(--theme-surface-base);">
                        <div>
                            <p class="text-3xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $totals['ctr'] }}%</p>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('CTR') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 space-y-3">
                @foreach ([['label' => __('Views'), 'value' => $totals['views'], 'share' => $viewsShare, 'color' => '#2563eb'], ['label' => __('Clicks'), 'value' => $totals['clicks'], 'share' => $clicksShare, 'color' => '#14b8a6']] as $mix)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $mix['label'] }}</span>
                            <span style="color: var(--theme-muted-text-color);">{{ number_format($mix['value']) }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);">
                            <div class="h-full rounded-full" style="width: {{ max(2, $mix['share']) }}%; background-color: {{ $mix['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        @foreach ($attributionCards as $card)
            @php($cardMax = max(1, (int) collect($card['rows'])->max('value')))
            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ $card['label'] }}</p>
                        <h2 class="mt-2 text-lg font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $card['title'] }}</h2>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: {{ $card['color'] }}18; color: {{ $card['color'] }};">
                        <i class="{{ $card['icon'] }}"></i>
                    </span>
                </div>
                <p class="mt-4 text-2xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ number_format($card['count']) }}</p>
                <div class="mt-5 space-y-3">
                    @forelse ($card['rows']->take(4) as $row)
                        @php($width = max(4, round(($row['value'] / $cardMax) * 100)))
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                <span class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</span>
                                <span style="color: var(--theme-muted-text-color);">{{ number_format($row['value']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);">
                                <div class="h-full rounded-full" style="width: {{ $width }}%; background-color: {{ $card['color'] }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('No attribution events yet.') }}</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <section class="rounded-[1.25rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b px-5 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Live feed') }}</p>
                <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Recent bio events') }}</h2>
            </div>
            <span class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Latest views and clicks') }}</span>
        </div>
        <div class="divide-y" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
            @forelse ($recentEvents as $event)
                <div class="grid gap-3 px-5 py-4 md:grid-cols-[minmax(0,1fr)_170px_170px] md:items-center">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $event->page?->title ?: __('Bio page') }}</p>
                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $event->url ?: '/'.$event->page?->slug }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="background-color: {{ $event->type === 'click' ? 'rgba(20,184,166,0.12); color: #0f766e' : 'rgba(37,99,235,0.12); color: #2563eb' }};">{{ ucfirst($event->type) }}</span>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.72); color: var(--theme-muted-text-color);">{{ data_get($event->metadata, 'utm_preset_id') ? __('UTM') : __('No UTM') }}</span>
                    </div>
                    <p class="text-xs md:text-right" style="color: var(--theme-muted-text-color);">{{ $event->created_at?->format('Y-m-d H:i') }}</p>
                </div>
            @empty
                <div class="p-8">
                    <x-ui.empty icon="fa-light fa-chart-mixed" :title="__('No bio events yet')" :description="__('Views and clicks will appear after visitors open public bio pages.')" />
                </div>
            @endforelse
        </div>
    </section>
</div>
