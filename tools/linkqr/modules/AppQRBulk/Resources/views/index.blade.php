@php
    $typeCollection = collect($types);
    $rows = collect(preg_split('/\r\n|\r|\n/', trim($csv)) ?: [])
        ->filter(fn ($line) => trim((string) $line) !== '')
        ->count();
    $dynamicTypes = $typeCollection->filter(fn ($type) => (string) ($type['kind'] ?? '') === __('dynamic'))->count();
    $staticTypes = max(0, $typeCollection->count() - $dynamicTypes);
    $typesByKind = $typeCollection
        ->groupBy(fn ($type) => (string) ($type['kind'] ?? '') === __('dynamic') ? 'dynamic' : 'static')
        ->map(fn ($items) => $items->values());
    $templateRows = [
        ['name' => 'Landing Page', 'type' => 'dynamic_url', 'url' => 'https://example.com'],
        ['name' => 'Menu QR', 'type' => 'restaurant_menu', 'url' => 'https://example.com/menu'],
        ['name' => 'Review QR', 'type' => 'business_review', 'url' => 'https://example.com/review'],
    ];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.5rem] border shadow-[0_28px_80px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background-color: var(--theme-surface-base);">
            <div class="grid xl:grid-cols-[minmax(0,1fr)_minmax(30rem,0.76fr)]">
                <div class="p-5 sm:p-7">
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-file-csv"></i>
                        {{ __('QR batch import') }}
                    </div>

                    <h1 class="mt-5 text-[2.35rem] font-semibold leading-tight tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('Bulk Generation') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generate campaign records from CSV, keep dynamic redirects editable, and move large QR operations out of manual one-by-one creation.') }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Rows ready'), 'value' => $rows, 'icon' => 'fa-table-rows', 'tone' => '#2563eb'],
                            ['label' => __('Dynamic keys'), 'value' => $dynamicTypes, 'icon' => 'fa-link', 'tone' => '#0f766e'],
                            ['label' => __('Created'), 'value' => $createdCount, 'icon' => 'fa-circle-check', 'tone' => '#059669'],
                        ] as $stat)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</span>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $stat['tone'] }}14; color: {{ $stat['tone'] }};">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($stat['value']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: radial-gradient(circle at 16% 8%, rgba(var(--theme-accent-rgb),0.20), transparent 28%), radial-gradient(circle at 88% 18%, rgba(20,184,166,0.18), transparent 32%), linear-gradient(135deg, rgba(var(--theme-surface-soft-rgb,248,250,252),0.80), rgba(var(--theme-surface-base-rgb,255,255,255),0.70));">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_70px_-46px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.84);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Import pipeline') }}</p>
                                <p class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('CSV to campaigns') }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.95rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-qrcode"></i>
                            </span>
                        </div>

                        <div class="mt-4 space-y-2 rounded-[0.95rem] border p-3 font-mono text-xs leading-5" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.72); color: var(--theme-header-text-color);">
                            @foreach ($templateRows as $row)
                                <div class="grid gap-2 rounded-[0.7rem] px-3 py-2 sm:grid-cols-[1fr_0.9fr_1.35fr]" style="background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.86);">
                                    <span class="truncate">{{ $row['name'] }}</span>
                                    <span class="truncate" style="color: var(--theme-accent);">{{ $row['type'] }}</span>
                                    <span class="truncate" style="color: var(--theme-muted-text-color);">{{ $row['url'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach ([
                                ['label' => __('Validate'), 'icon' => 'fa-shield-check', 'tone' => '#2563eb'],
                                ['label' => __('Publish'), 'icon' => 'fa-rocket-launch', 'tone' => '#0891b2'],
                                ['label' => __('Track'), 'icon' => 'fa-chart-line', 'tone' => '#059669'],
                            ] as $step)
                                <div class="rounded-[0.9rem] border px-3 py-3 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.70);">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $step['tone'] }}12; color: {{ $step['tone'] }};">
                                        <i class="fa-light {{ $step['icon'] }}"></i>
                                    </span>
                                    <p class="mt-2 text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $step['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(23rem,0.38fr)]">
            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Prepare CSV rows') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Required columns: campaign name, QR type key, destination URL. Invalid rows are skipped and valid rows are generated immediately.') }}</p>
                    </div>
                    @if ($createdCount > 0)
                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                            <i class="fa-light fa-circle-check"></i>
                            {{ __('Created :count QR codes', ['count' => $createdCount]) }}
                        </span>
                    @endif
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    @foreach ([
                        ['label' => __('Column 1'), 'value' => 'name'],
                        ['label' => __('Column 2'), 'value' => 'type_key'],
                        ['label' => __('Column 3'), 'value' => 'url'],
                    ] as $column)
                        <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $column['label'] }}</p>
                            <p class="mt-1 font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $column['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5">
                    <x-ui.textarea wire:model.live.debounce.300ms="csv" :label="__('CSV rows')" rows="14"></x-ui.textarea>
                </div>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-table-rows"></i>
                            {{ number_format($rows) }} {{ __('rows') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(6,182,212,0.1); color: #0891b2;">
                            <i class="fa-light fa-file-csv"></i>
                            {{ __('CSV import') }}
                        </span>
                    </div>

                    <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
                        <i class="fa-light fa-file-csv" wire:loading.remove wire:target="generate"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="generate"></i>
                        <span wire:loading.remove wire:target="generate">{{ __('Generate QR codes') }}</span>
                        <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
                    </x-ui.button>
                </div>
            </section>

            <aside class="space-y-5">
                <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <div class="p-5">
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.12em]" style="background-color: rgba(16,185,129,0.1); color: #059669;">
                            <i class="fa-light fa-sparkles"></i>
                            {{ __('Brand-aware QR') }}
                        </div>
                        <h2 class="mt-4 text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Design after import') }}</h2>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Bulk import should create clean records first. Then use QR design to add logo color sampling, mosaic dots, and scan-safe exports per campaign.') }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 border-t p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: linear-gradient(135deg, rgba(15,23,42,0.04), rgba(6,182,212,0.08));">
                        @foreach ([
                            ['label' => __('Logo'), 'icon' => 'fa-image', 'tone' => '#2563eb'],
                            ['label' => __('Mosaic'), 'icon' => 'fa-grid-2-plus', 'tone' => '#0f766e'],
                            ['label' => __('Export'), 'icon' => 'fa-download', 'tone' => '#c2410c'],
                        ] as $item)
                            <div class="rounded-[0.9rem] px-3 py-3 text-center" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                <span class="mx-auto inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $item['tone'] }}12; color: {{ $item['tone'] }};">
                                    <i class="fa-light {{ $item['icon'] }}"></i>
                                </span>
                                <p class="mt-2 text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $item['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('CSV reference') }}</h2>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use these exact type_key values in column 2. Dynamic keys create editable short links; static keys store the payload directly.') }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">{{ $typeCollection->count() }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="rounded-[0.9rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Dynamic') }}</p>
                            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $dynamicTypes }}</p>
                        </div>
                        <div class="rounded-[0.9rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Static') }}</p>
                            <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $staticTypes }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        @foreach ([['key' => 'dynamic', 'label' => __('Dynamic type keys')], ['key' => 'static', 'label' => __('Static type keys')]] as $group)
                            @php($groupTypes = $typesByKind->get($group['key'], collect()))
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $group['label'] }}</p>
                                    <span class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ $groupTypes->count() }}</span>
                                </div>
                                <div class="max-h-56 space-y-2 overflow-y-auto pr-1">
                                    @foreach ($groupTypes as $type)
                                        <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-light {{ $type['icon'] ?? 'fa-qrcode' }} text-xs" style="color: var(--theme-accent);"></i>
                                                <code class="min-w-0 flex-1 truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $type['key'] }}</code>
                                            </div>
                                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $type['label'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.05);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">{{ __('CSV format') }}</p>
                        <p class="mt-2 font-mono text-xs leading-5" style="color: var(--theme-header-text-color);">Campaign Name,type_key,https://example.com/path</p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
