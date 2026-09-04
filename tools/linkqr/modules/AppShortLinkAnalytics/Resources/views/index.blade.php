@php
    $chartCategories = collect($chart)->pluck('label')->values()->all();
    $chartData = collect($chart)->pluck('clicks')->map(fn ($value) => (int) $value)->values()->all();
    $pointData = fn ($items) => collect($items)->map(fn ($item) => [
        'name' => (string) $item['label'],
        'y' => (int) $item['count'],
    ])->values()->all();
    $categoryData = fn ($items) => collect($items)->map(fn ($item) => (string) $item['label'])->values()->all();
    $countData = fn ($items) => collect($items)->map(fn ($item) => (int) $item['count'])->values()->all();
    $donutOptions = fn ($items) => [
        'chart' => ['type' => 'pie', 'height' => 260],
        'legend' => ['enabled' => true, 'align' => 'center', 'verticalAlign' => 'bottom'],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks ({point.percentage:.1f}%)'],
        'plotOptions' => [
            'pie' => [
                'innerSize' => '62%',
                'size' => '82%',
                'dataLabels' => ['enabled' => false],
                'showInLegend' => true,
            ],
        ],
        'series' => [[
            'name' => __('Clicks'),
            'data' => $pointData($items),
        ]],
    ];
    $barOptions = fn ($items) => [
        'chart' => ['type' => 'bar', 'height' => max(260, collect($items)->count() * 42)],
        'legend' => ['enabled' => false],
        'xAxis' => ['categories' => $categoryData($items)],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks'],
        'plotOptions' => [
            'bar' => [
                'borderRadius' => 8,
                'pointPadding' => 0.08,
                'groupPadding' => 0.12,
            ],
        ],
        'series' => [[
            'name' => __('Clicks'),
            'data' => $countData($items),
        ]],
    ];
    $performanceChartOptions = [
        'chart' => ['type' => 'areaspline', 'height' => 340],
        'legend' => ['enabled' => false],
        'xAxis' => ['categories' => $chartCategories],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks'],
        'plotOptions' => [
            'areaspline' => [
                'fillOpacity' => 0.18,
                'lineWidth' => 3,
                'marker' => ['enabled' => true, 'radius' => 3],
            ],
        ],
        'series' => [[
            'name' => __('Clicks'),
            'data' => $chartData,
        ]],
    ];
    $variantChartOptions = [
        'chart' => ['type' => 'bar', 'height' => max(260, collect($variantStats ?? [])->count() * 58)],
        'legend' => ['enabled' => false],
        'xAxis' => ['categories' => collect($variantStats ?? [])->map(fn ($item) => (string) $item['variant_key'])->values()->all()],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks'],
        'plotOptions' => [
            'bar' => [
                'borderRadius' => 8,
                'pointPadding' => 0.08,
                'groupPadding' => 0.14,
            ],
        ],
        'series' => [[
            'name' => __('Variant clicks'),
            'data' => collect($variantStats ?? [])->map(fn ($item) => (int) $item['clicks'])->values()->all(),
        ]],
    ];
    $panelCharts = [
        ['title' => __('Click type'), 'items' => $clickTypes, 'icon' => 'fa-arrow-pointer', 'type' => 'click_type', 'options' => $donutOptions($clickTypes)],
        ['title' => __('Top sources'), 'items' => $referrers, 'icon' => 'fa-share-nodes', 'type' => 'source', 'options' => $barOptions($referrers)],
        ['title' => __('Top social media platforms'), 'items' => $socials, 'icon' => 'fa-hashtag', 'type' => 'social', 'options' => $donutOptions($socials)],
        ['title' => __('Top browsers'), 'items' => $browsers, 'icon' => 'fa-window', 'type' => 'browser', 'options' => $donutOptions($browsers)],
        ['title' => __('Top devices'), 'items' => $devices, 'icon' => 'fa-mobile-screen', 'type' => 'device', 'options' => $donutOptions($devices)],
        ['title' => __('Top platforms'), 'items' => $platforms, 'icon' => 'fa-laptop-mobile', 'type' => 'platform', 'options' => $barOptions($platforms)],
        ['title' => __('Top cities'), 'items' => $cities, 'icon' => 'fa-city', 'type' => 'city', 'options' => $barOptions($cities)],
        ['title' => __('Top languages'), 'items' => $languages, 'icon' => 'fa-language', 'type' => 'language', 'options' => $barOptions($languages)],
    ];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-5">
        <section class="rounded-[1.1rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-4 border-b p-5 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.85rem] bg-yellow-300 text-slate-950">
                        <i class="fa-light fa-chart-mixed"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Short links report') }}</p>
                        <h1 class="mt-1 truncate text-2xl font-semibold" style="color: var(--theme-header-text-color);">
                            {{ $selectedLink ? $selectedLink->name : __('All links analytics') }}
                        </h1>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div x-data="{ open: false }" x-on:click.outside="open = false" class="relative w-full sm:w-[24rem]">
                        <button type="button" x-on:click="open = ! open; $nextTick(() => $refs.linkSearch?.focus())" class="flex h-11 w-full items-center justify-between gap-3 rounded-[0.8rem] border px-3 text-left text-sm shadow-sm" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                            <span class="flex min-w-0 items-center gap-2">
                                <i class="fa-light fa-link-simple shrink-0" style="color: var(--theme-muted-text-color);"></i>
                                <span class="truncate">{{ $selectedLink ? $selectedLink->name.' - '.$selectedLink->short_code : __('All short links').' ('.number_format($totalLinks).')' }}</span>
                            </span>
                            <i class="fa-light fa-chevron-down shrink-0 text-xs" style="color: var(--theme-muted-text-color);"></i>
                        </button>

                        <div x-show="open" x-cloak class="absolute left-0 right-0 z-40 mt-2 overflow-hidden rounded-[0.95rem] border shadow-[0_22px_60px_-30px_rgba(15,23,42,0.45)]" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                            <div class="border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                                <div class="relative">
                                    <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--theme-muted-text-color);"></i>
                                    <input x-ref="linkSearch" wire:model.live.debounce.300ms="linkSearch" type="search" placeholder="{{ __('Search links') }}" class="h-10 w-full rounded-[0.75rem] border pl-8 pr-3 text-sm outline-none" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                                </div>
                            </div>

                            <div class="max-h-72 overflow-y-auto p-1">
                                <button type="button" wire:click="clearSelectedLink" x-on:click="open = false" class="flex w-full items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-900/30" style="color: var(--theme-header-text-color);">
                                    <span class="min-w-0">
                                        <span class="block truncate font-semibold">{{ __('All short links') }}</span>
                                        <span class="mt-0.5 block text-xs" style="color: var(--theme-muted-text-color);">{{ number_format($totalLinks) }} {{ __('links') }}</span>
                                    </span>
                                    @if (! $selectedLink)
                                        <i class="fa-light fa-check shrink-0" style="color: var(--theme-accent);"></i>
                                    @endif
                                </button>

                                @forelse ($linkOptions as $shortLink)
                                    <button type="button" wire:click="$set('link', {{ $shortLink->id }})" x-on:click="open = false" class="flex w-full items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-900/30" style="color: var(--theme-header-text-color);">
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold">{{ $shortLink->name }}</span>
                                            <span class="mt-0.5 block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $shortLink->short_code }} - {{ number_format((int) $shortLink->clicks_count) }} {{ __('clicks') }}</span>
                                        </span>
                                        @if ((int) $link === (int) $shortLink->id)
                                            <i class="fa-light fa-check shrink-0" style="color: var(--theme-accent);"></i>
                                        @endif
                                    </button>
                                @empty
                                    <div class="px-3 py-6 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No links found.') }}</div>
                                @endforelse
                            </div>

                            <div class="border-t px-3 py-2 text-[11px]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">
                                {{ __('Showing up to :count matches.', ['count' => number_format($linkOptions->count())]) }}
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="exportCsv" class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-download"></i>
                        {{ __('Export') }}
                    </button>
                    <button type="button" wire:click="resetStats" x-data x-on:click="if (! confirm(@js(__('Reset analytics stats?')))) { $event.preventDefault(); $event.stopImmediatePropagation(); }" class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] border px-4 text-sm font-semibold text-red-600" style="border-color: rgba(220,38,38,0.35);">
                        <i class="fa-light fa-rotate-left"></i>
                        {{ __('Reset stats') }}
                    </button>
                    <a href="{{ route('portal.short-links.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] bg-black px-4 text-sm font-semibold text-white">
                        <i class="fa-light fa-link-simple"></i>
                        {{ __('Links') }}
                    </a>
                </div>
            </div>

            @if ($selectedLink)
                <div class="grid gap-3 border-b p-5 lg:grid-cols-[minmax(0,1fr)_auto]" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <div class="min-w-0">
                        <p class="break-all font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedLink->shortUrl() }}</p>
                        <p class="mt-1 truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $selectedLink->destination_url }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <x-ui.badge :variant="$selectedLink->status === 'active' && $selectedLink->moderation_status !== 'blocked' ? 'success' : 'neutral'">{{ $selectedLink->moderation_status === 'blocked' ? __('Blocked') : str($selectedLink->status)->title() }}</x-ui.badge>
                        @if ($selectedLink->campaign)<x-ui.badge variant="primary">{{ $selectedLink->campaign }}</x-ui.badge>@endif
                        @if ($selectedLink->folder)<x-ui.badge variant="neutral">{{ $selectedLink->folder }}</x-ui.badge>@endif
                    </div>
                </div>
            @endif

            <div class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ([
                    ['label' => __('Total clicks'), 'value' => number_format($metrics['clicks']), 'icon' => 'fa-arrow-pointer', 'color' => '#2563eb'],
                    ['label' => __('Unique clicks'), 'value' => number_format($metrics['unique_clicks']), 'icon' => 'fa-user-check', 'color' => '#059669'],
                    ['label' => __('Clicks today'), 'value' => number_format($metrics['clicks_today']), 'icon' => 'fa-calendar-day', 'color' => '#f59e0b'],
                    ['label' => __('Active links'), 'value' => number_format($metrics['active_links']), 'icon' => 'fa-circle-check', 'color' => '#7c3aed'],
                    ['label' => __('Last click'), 'value' => $metrics['last_click'] ? $metrics['last_click']->diffForHumans() : '-', 'icon' => 'fa-clock', 'color' => '#0f766e'],
                ] as $stat)
                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                <i class="fa-light {{ $stat['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-2 truncate text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[1.1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Overall performance') }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Clicks over time for the selected scope.') }}</p>
                </div>
                <div class="inline-flex w-fit rounded-[0.8rem] border p-1" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                    @foreach (['hours' => __('Hours'), 'days' => __('Days'), 'months' => __('Months')] as $value => $label)
                        <button type="button" wire:click="$set('period', '{{ $value }}')" class="h-9 rounded-[0.65rem] px-4 text-sm font-semibold {{ $period === $value ? 'shadow-sm' : '' }}" style="{{ $period === $value ? 'background-color: var(--theme-surface-base); color: var(--theme-header-text-color);' : 'color: var(--theme-muted-text-color);' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="mt-5">
                <x-shared::highchart
                    id="short-link-performance-{{ $link ?: 'all' }}-{{ $period }}"
                    :options="$performanceChartOptions"
                    height="340px"
                />
            </div>
        </section>

        @if (($variantStats ?? collect())->isNotEmpty())
            <section id="ab-testing" class="scroll-mt-24 rounded-[1.1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem] bg-violet-50 text-violet-700">
                                <i class="fa-light fa-flask"></i>
                            </span>
                            <div>
                                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('A/B destination testing') }}</h2>
                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Compare variant split, click share, status, and winner for this short link.') }}</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('portal.short-links.edit', ['shortLink' => $selectedLink->id]) }}" wire:navigate class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-pen"></i>
                        {{ __('Manage A/B test') }}
                    </a>
                </div>

                <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,0.48fr)_minmax(0,0.52fr)]">
                    <div class="rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: var(--theme-surface-soft);">
                        <x-shared::highchart
                            id="short-link-ab-variants-{{ $selectedLink->id }}-{{ $period }}"
                            :options="$variantChartOptions"
                            height="300px"
                        />
                    </div>
                    <div class="space-y-3">
                        @foreach ($variantStats as $variant)
                            <button type="button" wire:click="setDrilldown('variant', @js(trim(($variant['variant_key'] ?? '').' '.($variant['name'] ?? ''))))" class="w-full rounded-[0.95rem] border p-3 text-left transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-[0.65rem] bg-blue-600 px-2 text-xs font-bold text-white">{{ $variant['variant_key'] }}</span>
                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $variant['name'] }}</p>
                                            @if (($variant['winner'] ?? false))
                                                <x-ui.badge variant="success">{{ __('Winner') }}</x-ui.badge>
                                            @endif
                                            @if (($variant['status'] ?? 'active') === 'paused')
                                                <x-ui.badge variant="neutral">{{ __('Paused') }}</x-ui.badge>
                                            @endif
                                        </div>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $variant['url'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $variant['clicks']) }} {{ __('clicks') }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Weight') }} {{ $variant['weight'] }}% - {{ __('Observed') }} {{ $variant['split'] }}% - {{ __('CTR index') }} {{ $variant['ctr_index'] }}%</p>
                                    </div>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">
                                    <div class="h-full rounded-full {{ ($variant['winner'] ?? false) ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $variant['bar'] }}%;"></div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,0.58fr)_minmax(24rem,0.42fr)]">
            <section class="rounded-[1.1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top countries') }}</h2>
                    <i class="fa-light fa-globe text-lg" style="color: var(--theme-muted-text-color);"></i>
                </div>
                <div class="mt-5 rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: var(--theme-surface-soft);">
                    @if ($countries->isNotEmpty())
                        <x-shared::highchart
                            id="short-link-countries-{{ $link ?: 'all' }}-{{ $period }}"
                            :options="$barOptions($countries)"
                            height="360px"
                        />
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($countries->take(4) as $country)
                                <button type="button" wire:click="setDrilldown('country', @js($country['label']))" class="flex items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2 text-left" style="background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                                    <span class="truncate text-xs font-semibold">{{ $country['label'] }}</span>
                                    <span class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);">{{ number_format($country['count']) }} - {{ $country['percent'] }}%</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="flex min-h-72 items-center justify-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No geographic data yet.') }}</div>
                    @endif
                </div>
            </section>

            <section class="rounded-[1.1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $drilldownLabel ? __('Click details') : ($selectedLink ? __('Recent clicks') : __('Top links')) }}</h2>
                    @if ($drilldownLabel)
                        <button type="button" wire:click="clearDrilldown" class="inline-flex h-8 items-center gap-2 rounded-[0.65rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                            <i class="fa-light fa-xmark"></i>
                            {{ $drilldownLabel }}
                        </button>
                    @endif
                </div>
                <div class="mt-5 max-h-[38rem] space-y-3 overflow-y-auto pr-1">
                    @if ($selectedLink || $drilldownLabel)
                        @forelse ($recentClicks as $click)
                            <div class="rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.6); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($click->metadata, 'browser') ?: __('Unknown browser') }} - {{ data_get($click->metadata, 'device') ?: __('Unknown device') }}</p>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $click->shortLink?->name }} - {{ data_get($click->metadata, 'city') ?: ($click->country ?: __('Location unknown')) }} - {{ $click->referer ?: __('Direct') }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);">{{ $click->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No clicks recorded yet.') }}</p>
                        @endforelse
                    @else
                        @forelse ($topLinks as $shortLink)
                            <a href="{{ route('portal.short-links.analytics', ['link' => $shortLink->id]) }}" class="block rounded-[0.95rem] border p-3 transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.6); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $shortLink->name }}</p>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $shortLink->shortUrl() }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $shortLink->clicks_count) }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No links yet.') }}</p>
                        @endforelse
                    @endif
                </div>
            </section>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            @foreach ($panelCharts as $panel)
                <section class="rounded-[1.1rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $panel['title'] }}</h2>
                        <i class="fa-light {{ $panel['icon'] }}" style="color: var(--theme-muted-text-color);"></i>
                    </div>
                    <div class="mt-5">
                        @if (collect($panel['items'])->isNotEmpty())
                            <x-shared::highchart
                                id="short-link-panel-{{ \Illuminate\Support\Str::slug($panel['title']) }}-{{ $link ?: 'all' }}-{{ $period }}"
                                :options="$panel['options']"
                                height="280px"
                            />
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @foreach (collect($panel['items'])->take(4) as $item)
                                    <button type="button" wire:click="setDrilldown('{{ $panel['type'] }}', @js($item['label']))" class="flex items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2 text-left" style="background-color: var(--theme-surface-soft);">
                                        <p class="truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $item['label'] }}</p>
                                        <p class="shrink-0 text-xs" style="color: var(--theme-muted-text-color);">{{ number_format($item['count']) }} - {{ $item['percent'] }}%</p>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No data recorded yet.') }}</p>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
