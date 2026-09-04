@php
    $chartCategories = collect($chart)->pluck('label')->values()->all();
    $chartData = collect($chart)->pluck('clicks')->map(fn ($value) => (int) $value)->values()->all();
    $pointData = fn ($items) => collect($items)->map(fn ($item) => [
        'name' => (string) $item['label'],
        'y' => (int) $item['count'],
    ])->values()->all();
    $barOptions = fn ($items) => [
        'chart' => ['type' => 'bar', 'height' => max(240, collect($items)->count() * 42)],
        'legend' => ['enabled' => false],
        'xAxis' => ['categories' => collect($items)->pluck('label')->values()->all()],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks'],
        'plotOptions' => ['bar' => ['borderRadius' => 8, 'pointPadding' => 0.08, 'groupPadding' => 0.12]],
        'series' => [['name' => __('Clicks'), 'data' => collect($items)->pluck('count')->map(fn ($value) => (int) $value)->values()->all()]],
    ];
    $donutOptions = fn ($items) => [
        'chart' => ['type' => 'pie', 'height' => 260],
        'legend' => ['enabled' => true, 'align' => 'center', 'verticalAlign' => 'bottom'],
        'plotOptions' => ['pie' => ['innerSize' => '62%', 'size' => '82%', 'dataLabels' => ['enabled' => false], 'showInLegend' => true]],
        'series' => [['name' => __('Clicks'), 'data' => $pointData($items)]],
    ];
    $performanceOptions = [
        'chart' => ['type' => 'areaspline', 'height' => 330],
        'legend' => ['enabled' => false],
        'xAxis' => ['categories' => $chartCategories],
        'tooltip' => ['pointFormat' => '<b>{point.y}</b> clicks'],
        'plotOptions' => ['areaspline' => ['fillOpacity' => 0.18, 'lineWidth' => 3, 'marker' => ['enabled' => true, 'radius' => 3]]],
        'series' => [['name' => __('Clicks'), 'data' => $chartData]],
    ];
    $stats = [
        ['label' => __('Links'), 'value' => $metrics['links'], 'note' => __(':count clients', ['count' => number_format((int) $metrics['clients'])]), 'icon' => 'fa-link-simple', 'color' => '#2563eb'],
        ['label' => __('Active'), 'value' => $metrics['active'], 'note' => __('Ready to redirect'), 'icon' => 'fa-circle-check', 'color' => '#059669'],
        ['label' => __('Blocked'), 'value' => $metrics['blocked'], 'note' => __('Moderation controls'), 'icon' => 'fa-ban', 'color' => '#dc2626'],
        ['label' => __('Clicks'), 'value' => $metrics['clicks'], 'note' => __(':count last 30 days', ['count' => number_format((int) $metrics['clicks_30d'])]), 'icon' => 'fa-arrow-pointer', 'color' => '#7c3aed'],
        ['label' => __('Reports'), 'value' => $metrics['abuse_reports'], 'note' => __('Open abuse reports'), 'icon' => 'fa-flag', 'color' => '#f59e0b'],
    ];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-5">
        <section class="overflow-hidden rounded-[1.35rem] border shadow-[0_30px_90px_-70px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_28rem]">
                <div class="p-5 sm:p-7">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-shield-check"></i>
                        {{ __('Admin short links') }}
                    </span>
                    <h1 class="mt-5 text-[2rem] font-semibold leading-tight tracking-[-0.04em]" style="color: var(--theme-header-text-color);">
                        {{ $selectedClient ? __('Client analytics') : __('Short Links analytics') }}
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Monitor all client short links, drill into a single client, review growth, and moderate abusive destinations from one dashboard.') }}</p>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: radial-gradient(circle at 20% 0%, rgba(var(--theme-accent-rgb),0.12), transparent 34%), var(--theme-surface-soft);">
                    <div class="mb-3 flex justify-end">
                        <x-ui.button :href="route('admin.short-links.manage')" variant="outline" size="sm" wire:navigate>
                            <i class="fa-light fa-list"></i>
                            {{ __('Manage links') }}
                        </x-ui.button>
                    </div>
                    <div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
                        <button type="button" x-on:click="open = ! open; $nextTick(() => $refs.clientSearch?.focus())" class="flex h-12 w-full items-center justify-between gap-3 rounded-[0.9rem] border px-3 text-left text-sm shadow-sm" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                            <span class="flex min-w-0 items-center gap-2">
                                <i class="fa-light fa-users shrink-0" style="color: var(--theme-muted-text-color);"></i>
                                <span class="truncate">{{ $selectedClient ? $selectedClient->name.' - '.$selectedClient->email : __('All clients') }}</span>
                            </span>
                            <i class="fa-light fa-chevron-down shrink-0 text-xs" style="color: var(--theme-muted-text-color);"></i>
                        </button>

                        <div x-show="open" x-cloak class="absolute left-0 right-0 z-40 mt-2 overflow-hidden rounded-[0.95rem] border shadow-[0_24px_70px_-36px_rgba(15,23,42,0.5)]" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                            <div class="border-b p-2" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                                <div class="relative">
                                    <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--theme-muted-text-color);"></i>
                                    <input x-ref="clientSearch" wire:model.live.debounce.300ms="clientSearch" type="search" placeholder="{{ __('Search clients') }}" class="h-10 w-full rounded-[0.75rem] border pl-8 pr-3 text-sm outline-none" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto p-1">
                                <button type="button" wire:click="clearClient" x-on:click="open = false" class="flex w-full items-center justify-between rounded-[0.75rem] px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-900/30" style="color: var(--theme-header-text-color);">
                                    <span>
                                        <span class="block font-semibold">{{ __('All clients') }}</span>
                                        <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Platform-wide analytics') }}</span>
                                    </span>
                                    @if (! $selectedClient)<i class="fa-light fa-check" style="color: var(--theme-accent);"></i>@endif
                                </button>
                                @forelse ($clientOptions as $client)
                                    <button type="button" wire:click="$set('clientId', {{ $client->id }})" x-on:click="open = false" class="flex w-full items-center justify-between gap-3 rounded-[0.75rem] px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 dark:hover:bg-slate-900/30" style="color: var(--theme-header-text-color);">
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold">{{ $client->name }}</span>
                                            <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $client->email }}</span>
                                        </span>
                                        @if ((int) $clientId === (int) $client->id)<i class="fa-light fa-check shrink-0" style="color: var(--theme-accent);"></i>@endif
                                    </button>
                                @empty
                                    <div class="px-3 py-6 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No clients found.') }}</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 border-t p-5 sm:grid-cols-2 xl:grid-cols-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                @foreach ($stats as $stat)
                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-3xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $stat['value']) }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};"><i class="fa-light {{ $stat['icon'] }}"></i></span>
                        </div>
                        <p class="mt-3 text-xs" style="color: var(--theme-muted-text-color);">{{ $stat['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[1.2rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Click performance') }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $selectedClient ? __('Clicks for the selected client.') : __('Clicks across every client workspace.') }}</p>
                </div>
                <div class="inline-flex w-fit rounded-[0.8rem] border p-1" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                    @foreach (['days' => __('Days'), 'months' => __('Months')] as $value => $label)
                        <button type="button" wire:click="$set('period', '{{ $value }}')" class="h-9 rounded-[0.65rem] px-4 text-sm font-semibold {{ $period === $value ? 'shadow-sm' : '' }}" style="{{ $period === $value ? 'background-color: var(--theme-surface-base); color: var(--theme-header-text-color);' : 'color: var(--theme-muted-text-color);' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="mt-5">
                <x-shared::highchart id="admin-short-links-performance-{{ $clientId ?: 'all' }}-{{ $period }}" :options="$performanceOptions" height="330px" />
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(24rem,0.75fr)]">
            <section class="rounded-[1.2rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedClient ? __('Top links') : __('Top clients') }}</h2>
                <div class="mt-5 space-y-3">
                    @if ($selectedClient)
                        @forelse ($topLinks as $link)
                            <div class="rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->shortUrl() }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $link->clicks_count) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No short links yet.') }}</p>
                        @endforelse
                    @else
                        @forelse ($topClients as $client)
                            <button type="button" wire:click="$set('clientId', {{ $client['id'] }})" class="w-full rounded-[0.95rem] border p-3 text-left transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $client['name'] }}</p>
                                        <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $client['email'] }} - {{ number_format($client['links']) }} {{ __('links') }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold" style="color: var(--theme-accent);">{{ number_format($client['clicks']) }}</span>
                                </div>
                            </button>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No client activity yet.') }}</p>
                        @endforelse
                    @endif
                </div>
            </section>

            <section class="grid gap-5">
                @foreach ([['title' => __('Top countries'), 'items' => $countries, 'options' => $barOptions($countries)], ['title' => __('Top devices'), 'items' => $devices, 'options' => $donutOptions($devices)], ['title' => __('Top sources'), 'items' => $referrers, 'options' => $barOptions($referrers)]] as $panel)
                    <div class="rounded-[1.2rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                        <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $panel['title'] }}</h3>
                        <div class="mt-4">
                            @if (collect($panel['items'])->isNotEmpty())
                                <x-shared::highchart id="admin-short-links-{{ \Illuminate\Support\Str::slug($panel['title']) }}-{{ $clientId ?: 'all' }}-{{ $period }}" :options="$panel['options']" height="260px" />
                            @else
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No data recorded yet.') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        </div>

    </div>
</div>
