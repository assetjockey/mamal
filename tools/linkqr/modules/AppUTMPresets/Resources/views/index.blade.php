@php
    $totalPresets = $presets->count();
    $defaultPreset = $presets->firstWhere('is_default', true);
    $filledFields = collect(['source', 'medium', 'campaign', 'content', 'term'])
        ->filter(fn ($field) => filled($form[$field] ?? ''))
        ->count();
    $previewQuery = collect([
        'utm_source' => $form['source'] ?: 'instagram',
        'utm_medium' => $form['medium'] ?: 'social',
        'utm_campaign' => $form['campaign'] ?: 'spring_launch',
        'utm_content' => $form['content'] ?: 'bio_button',
        'utm_term' => $form['term'] ?: null,
    ])->filter()->map(fn ($value, $key) => $key.'='.$value)->implode('&');
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.72fr)]">
                <div class="p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-tags"></i>
                            {{ __('Campaign taxonomy') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-3 py-1.5 text-[11px] font-semibold text-violet-700">
                            <i class="fa-light fa-link"></i>
                            {{ __('Attribution tags') }}
                        </span>
                    </div>
                    <h1 class="mt-5 text-[2.25rem] font-semibold leading-tight tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('UTM Presets') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create reusable tracking recipes for QR redirects, Link Bio buttons, paid ads, partners, and product campaigns. Keep analytics clean before links go public.') }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Presets'), 'value' => $totalPresets, 'icon' => 'fa-tags', 'color' => '#2563eb'],
                            ['label' => __('Default'), 'value' => $defaultPreset ? 1 : 0, 'icon' => 'fa-star', 'color' => '#f59e0b'],
                            ['label' => __('Fields ready'), 'value' => $filledFields.'/5', 'icon' => 'fa-list-check', 'color' => '#7c3aed'],
                        ] as $stat)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.10), rgba(124,58,237,0.12) 48%, rgba(236,72,153,0.10));">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_70px_-46px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Preview URL') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">go.yourbrand.com/spring</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                <i class="fa-light fa-link"></i>
                            </span>
                        </div>

                        <div class="mt-4 rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                            <p class="break-all font-mono text-xs leading-5" style="color: var(--theme-header-text-color);">?{{ $previewQuery }}</p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach ([
                                ['label' => 'source', 'value' => $form['source'] ?: 'instagram'],
                                ['label' => 'medium', 'value' => $form['medium'] ?: 'social'],
                                ['label' => 'campaign', 'value' => $form['campaign'] ?: 'spring_launch'],
                                ['label' => 'content', 'value' => $form['content'] ?: 'bio_button'],
                            ] as $tag)
                                <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">utm_{{ $tag['label'] }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $tag['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(25rem,0.42fr)_minmax(0,0.58fr)]">
            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Create UTM recipe') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Save a consistent naming pattern for teams to apply while publishing links.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-tags"></i>
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.input wire:model.live.debounce.250ms="form.name" :label="__('Preset name')" :placeholder="__('Spring campaign')" />
                    <x-ui.input wire:model.live.debounce.250ms="form.campaign_key" :label="__('Campaign key')" :placeholder="__('spring_launch')" />

                    <div class="grid gap-3 md:grid-cols-2">
                        <x-ui.input wire:model.live.debounce.250ms="form.source" :label="__('utm_source')" :placeholder="__('instagram')" />
                        <x-ui.input wire:model.live.debounce.250ms="form.medium" :label="__('utm_medium')" :placeholder="__('social')" />
                        <x-ui.input wire:model.live.debounce.250ms="form.campaign" :label="__('utm_campaign')" :placeholder="__('spring_launch')" />
                        <x-ui.input wire:model.live.debounce.250ms="form.content" :label="__('utm_content')" :placeholder="__('bio_button')" />
                    </div>
                    <x-ui.input wire:model.live.debounce.250ms="form.term" :label="__('utm_term')" :placeholder="__('optional keyword')" />
                    <x-ui.checkbox wire:model.live="form.auto_apply" :checked="(bool) ($form['auto_apply'] ?? false)" :label="__('Auto apply when campaign key matches')" :description="__('Use this preset automatically when future QR or Bio campaign metadata uses the same campaign key.')" />
                    <x-ui.checkbox wire:model.live="form.is_default" :checked="(bool) ($form['is_default'] ?? false)" :label="__('Use as default UTM preset')" :description="__('New Short Links, QR Codes, and Link Bio pages will select this preset automatically.')" />

                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-wand-magic-sparkles"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Naming rule') }}</p>
                                <p class="truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Use lowercase words, underscores, and stable campaign names.') }}</p>
                            </div>
                        </div>
                    </div>

                    <x-ui.button type="button" wire:click="addPreset" wire:loading.attr="disabled" wire:target="addPreset" class="w-full justify-center">
                        <i class="fa-light fa-plus" wire:loading.remove wire:target="addPreset"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="addPreset"></i>
                        <span wire:loading.remove wire:target="addPreset">{{ __('Add preset') }}</span>
                        <span wire:loading wire:target="addPreset">{{ __('Adding...') }}</span>
                    </x-ui.button>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Preset library') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Reusable UTM recipes for every public campaign surface.') }}</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                        <i class="fa-light fa-link"></i>
                        {{ __('Attribution tags') }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($presets as $preset)
                        <div class="rounded-[1.05rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-[0_20px_42px_-34px_rgba(15,23,42,0.42)]" style="border-color: {{ $preset->is_default ? 'rgba(16,185,129,0.48)' : 'rgba(var(--theme-border-color-rgb),0.7)' }}; background: {{ $preset->is_default ? 'linear-gradient(180deg, rgba(16,185,129,0.08), var(--theme-surface-soft))' : 'linear-gradient(180deg, var(--theme-surface-base), var(--theme-surface-soft))' }};">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.9rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                                            <i class="fa-light fa-tags"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $preset->name }}</p>
                                                @if ($preset->is_default)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(16,185,129,0.35); background-color: rgba(16,185,129,0.10); color: #047857;">
                                                        <i class="fa-light fa-star"></i>
                                                        {{ __('Default') }}
                                                    </span>
                                                @endif
                                                @if ($preset->auto_apply)
                                                    <x-ui.badge variant="info">{{ __('Auto') }}</x-ui.badge>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ $preset->campaign_key ? __('Campaign key:').' '.$preset->campaign_key : __('Reusable campaign tagging recipe') }}</p>
                                            @if ($preset->is_default)
                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    @foreach ([__('Short Links'), __('QR Codes'), __('Link Bio')] as $surface)
                                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold" style="background-color: rgba(16,185,129,0.09); color: #047857;">
                                                            <i class="fa-light fa-check"></i>
                                                            {{ $surface }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                                        <p class="break-all font-mono text-xs leading-5" style="color: var(--theme-muted-text-color);">utm_source={{ $preset->source ?: '-' }}&utm_medium={{ $preset->medium ?: '-' }}&utm_campaign={{ $preset->campaign ?: '-' }}&utm_content={{ $preset->content ?: '-' }}{{ $preset->term ? '&utm_term='.$preset->term : '' }}</p>
                                    </div>
                                </div>

                                <div class="flex shrink-0 gap-2">
                                    @if ($preset->is_default)
                                        <x-ui.button type="button" wire:click="clearDefault({{ $preset->id }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star-sharp-half-stroke"></i>
                                            {{ __('Unset') }}
                                        </x-ui.button>
                                    @else
                                        <x-ui.button type="button" wire:click="setDefault({{ $preset->id }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star"></i>
                                            {{ __('Default') }}
                                        </x-ui.button>
                                    @endif
                                    <x-ui.dialog :title="__('Delete UTM preset?')" :description="__('This removes the preset from future campaign selection.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <x-ui.button type="button" variant="outline" size="sm" class="h-9 w-9 px-0 hover:!border-red-200 hover:!text-red-600"><i class="fa-light fa-trash-can"></i></x-ui.button>
                                        </x-slot:trigger>
                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" wire:click="deletePreset({{ $preset->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(124,58,237,0.08));">
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,0.58fr)_minmax(0,0.42fr)] lg:items-center">
                                <div>
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1rem] shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                        <i class="fa-light fa-tags text-lg"></i>
                                    </span>
                                    <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No UTM presets yet') }}</h3>
                                    <p class="mt-2 max-w-lg text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create your first preset so every QR campaign and Bio button follows the same analytics naming rules.') }}</p>
                                </div>
                                <div class="space-y-2 rounded-[1rem] p-3 shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                    @foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'] as $tag)
                                        <div class="flex items-center justify-between rounded-[0.8rem] px-3 py-2" style="background-color: rgba(var(--theme-border-color-rgb),0.06);">
                                            <span class="text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $tag }}</span>
                                            <i class="fa-light fa-check text-xs" style="color: var(--theme-accent);"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
