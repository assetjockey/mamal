@php
    $flatItems = collect($items)->flatMap(fn ($blockItems, $blockIndex) => collect($blockItems)->map(fn ($item, $itemIndex) => [
        'blockIndex' => $blockIndex,
        'itemIndex' => $itemIndex,
        'item' => $item,
    ])->values())->values();
@endphp

<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.25rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
        <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_28rem]">
            <div class="p-5 sm:p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-flask"></i>
                        {{ __('Link Bio') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                        <i class="fa-light fa-flask"></i>
                        {{ __('Button experiments') }}
                    </span>
                </div>
                <h1 class="mt-4 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('A/B Tests') }}</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">
                    {{ __('Test another label, destination, or supporting text for a button. Visitors are split by weight, and clicks store the selected variant for analytics.') }}
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label' => __('Buttons'), 'value' => $stats['buttons'], 'icon' => 'fa-link', 'color' => '#2563eb'],
                        ['label' => __('Running tests'), 'value' => $stats['tests'], 'icon' => 'fa-flask', 'color' => '#059669'],
                        ['label' => __('Variants on'), 'value' => $stats['variants'], 'icon' => 'fa-chart-simple', 'color' => '#7c3aed'],
                        ['label' => __('UTM tagged'), 'value' => $stats['utm'], 'icon' => 'fa-tags', 'color' => '#ea580c'],
                    ] as $stat)
                        <div class="rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.13em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                    <i class="fa-light {{ $stat['icon'] }}"></i>
                                </span>
                            </div>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($stat['value']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(16,185,129,0.10));">
                <div class="space-y-3 rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.86); box-shadow: inset 0 1px 0 rgba(var(--theme-surface-soft-rgb,248,250,252),0.18);">
                    <div class="grid grid-cols-[2rem_minmax(0,1fr)] gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">1</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose a page') }}</p>
                            <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Only buttons with a URL are shown here.') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-[2rem_minmax(0,1fr)] gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">2</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Enable a variant') }}</p>
                            <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Leave blank fields to inherit the original button.') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-[2rem_minmax(0,1fr)] gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">3</span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Save and watch clicks') }}</p>
                            <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Analytics stores the variant key on each click.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid gap-2 sm:min-w-[20rem]">
                <x-ui.select wire:model.live="pageId" :label="__('Page')">
                    @foreach ($pages as $option)
                        <option value="{{ $option->id }}">{{ $option->title }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($page)
                    <x-ui.button href="{{ route('portal.link-bio.edit', ['page' => $page->id]) }}" variant="outline">
                        <i class="fa-light fa-pen-to-square"></i>
                        {{ __('Edit page') }}
                    </x-ui.button>
                @endif
                <x-ui.button type="button" wire:click="save">
                    <i class="fa-light fa-floppy-disk"></i>
                    {{ __('Save tests') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    @if (! $page)
        <x-ui.empty icon="fa-light fa-flask" :title="__('No Link Bio page')" :description="__('Create a page first, then add tests for its buttons.')" />
    @else
        <div class="grid gap-4">
            @forelse ($flatItems as $row)
                @php
                    $blockIndex = $row['blockIndex'];
                    $itemIndex = $row['itemIndex'];
                    $item = $row['item'];
                    $enabledCount = collect((array) data_get($item, 'ab_variants', []))->filter(fn ($variant) => (bool) data_get($variant, 'enabled', false))->count();
                @endphp
                <section class="overflow-hidden rounded-[1.1rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <div class="grid gap-0 xl:grid-cols-[22rem_minmax(0,1fr)]">
                        <aside class="border-b p-4 xl:border-b-0 xl:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ data_get($item, 'block_title') ?: __('Block :number', ['number' => $blockIndex + 1]) }}</p>
                                    <h2 class="mt-2 truncate text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($item, 'label') ?: __('Button') }}</h2>
                                </div>
                                <x-ui.badge :variant="$enabledCount > 0 ? 'success' : 'neutral'">{{ $enabledCount > 0 ? __('Testing') : __('Off') }}</x-ui.badge>
                            </div>

                            <div class="mt-4 rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Original button') }}</p>
                                <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($item, 'label') }}</p>
                                @if (filled(data_get($item, 'note')))
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ data_get($item, 'note') }}</p>
                                @endif
                                <p class="mt-3 break-all font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ data_get($item, 'url') }}</p>
                            </div>

                            <div class="mt-4">
                                <x-ui.input wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.utm_content" :label="__('UTM content label')" :placeholder="'bio_'.$blockIndex.'_'.$itemIndex" />
                                <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Used only for analytics attribution, not shown to visitors.') }}</p>
                            </div>

                            <x-ui.button type="button" variant="outline" size="sm" wire:click="clearItem({{ $blockIndex }}, {{ $itemIndex }})" class="mt-4">
                                <i class="fa-light fa-eraser"></i>
                                {{ __('Clear test') }}
                            </x-ui.button>
                        </aside>

                        <div class="grid gap-4 p-4 lg:grid-cols-2">
                            @foreach ([0, 1] as $variantIndex)
                                @php($variantKey = chr(65 + $variantIndex))
                                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Variant') }} {{ $variantKey }}</p>
                                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Override only what you want to test.') }}</p>
                                        </div>
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                                            <input type="checkbox" wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.enabled" class="h-4 w-4 rounded border-slate-300 accent-[var(--theme-accent)]">
                                            {{ __('Run') }}
                                        </label>
                                    </div>
                                    <input type="hidden" wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.key">
                                    <div class="grid gap-3">
                                        <div class="grid gap-3 sm:grid-cols-[7rem_minmax(0,1fr)]">
                                            <x-ui.input type="number" min="1" max="100" wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.weight" :label="__('Weight')" />
                                            <x-ui.input wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.label" :label="__('Button label')" :placeholder="data_get($item, 'label')" />
                                        </div>
                                        <x-ui.input wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.url" :label="__('Destination URL')" :placeholder="data_get($item, 'url')" />
                                        <x-ui.input wire:model.defer="items.{{ $blockIndex }}.{{ $itemIndex }}.ab_variants.{{ $variantIndex }}.note" :label="__('Supporting text')" :placeholder="data_get($item, 'note') ?: __('Optional')" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @empty
                <x-ui.empty icon="fa-light fa-link" :title="__('No testable buttons')" :description="__('This page has no buttons with URLs. Add URLs in the page editor first.')" />
            @endforelse
        </div>
    @endif
</div>
