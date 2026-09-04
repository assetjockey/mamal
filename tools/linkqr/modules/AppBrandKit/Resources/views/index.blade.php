@php
    $primary = $form['primary_color'] ?: '#2563eb';
    $text = $form['secondary_color'] ?: '#0f172a';
    $accent = $form['accent_color'] ?: '#10b981';
    $brandName = $form['brand_name'] ?: __('Your brand');
    $colorPresets = ['#2563eb', '#0f172a', '#10b981', '#0f766e', '#f59e0b', '#be123c', '#111827', '#06b6d4', '#7c3aed', '#db2777', '#ea580c', '#64748b'];
    $palettePresets = [
        ['#2563eb', '#0f172a', '#10b981', __('SaaS clean')],
        ['#0f766e', '#172554', '#f59e0b', __('Creator commerce')],
        ['#be123c', '#111827', '#06b6d4', __('Launch bold')],
        ['#7c3aed', '#111827', '#f97316', __('Premium studio')],
        ['#0891b2', '#0f172a', '#84cc16', __('Fresh product')],
        ['#dc2626', '#18181b', '#fbbf24', __('Retail punch')],
        ['#4f46e5', '#1e293b', '#ec4899', __('Modern creator')],
        ['#059669', '#064e3b', '#38bdf8', __('Growth brand')],
        ['#9333ea', '#020617', '#22c55e', __('Neon contrast')],
    ];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1.15fr)_minmax(24rem,0.85fr)]">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-accent);">{{ __('Brand Suite') }}</p>
                            <h1 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Brand Kit') }}</h1>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Build one visual system for QR campaigns, Link Bio pages, short links, and public campaign previews.') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Logo'), 'value' => $form['logo_url'] ? __('Connected') : __('Fallback mark'), 'icon' => 'fa-image'],
                            ['label' => __('Palette'), 'value' => __('3 colors'), 'icon' => 'fa-swatchbook'],
                            ['label' => __('Typography'), 'value' => $form['font_family'] ?: 'Inter', 'icon' => 'fa-font'],
                        ] as $stat)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                    <i class="fa-light {{ $stat['icon'] }}" style="color: var(--theme-accent);"></i>
                                </div>
                                <p class="mt-2 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(20,184,166,0.10), rgba(245,158,11,0.10));">
                    <div class="rounded-[1.1rem] border p-4 shadow-[0_24px_55px_-42px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.82);">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-[1rem]" style="background-color: {{ $primary }}; color: white;">
                                @if ($form['logo_url'])
                                    <img src="{{ $form['logo_url'] }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <i class="fa-light fa-sparkles"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $brandName }}</p>
                                <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Ready for publishing') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <div class="h-16 rounded-[0.8rem]" style="background-color: {{ $primary }};"></div>
                            <div class="h-16 rounded-[0.8rem]" style="background-color: {{ $accent }};"></div>
                            <div class="h-16 rounded-[0.8rem]" style="background-color: {{ $text }};"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)]">
            <div class="space-y-5">
                <x-ui.card class="space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Identity system') }}</h2>
                            <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('These defaults feed QR stickers, bio buttons, landing pages, and share assets.') }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.9rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-palette"></i>
                        </span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <x-ui.input wire:model.live.debounce.250ms="form.brand_name" :label="__('Brand name')" :placeholder="__('StackPosts')" />
                        <x-ui.select wire:model.live="form.font_family" :label="__('Font family')">
                            @foreach (['Inter', 'Roboto', 'Poppins', 'Montserrat', 'Playfair Display', 'Lato', 'Nunito Sans', 'Source Sans 3', 'Open Sans'] as $font)
                                <option value="{{ $font }}">{{ $font }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <x-ui.image-picker
                        wire:model.live="form.logo_url"
                        :value="$form['logo_url']"
                        :preview="$form['logo_url']"
                        :label="__('Logo asset')"
                        :help="__('Choose an image from Files. This logo is reused across QR, Link Bio, and branded pages.')"
                        :button-label="__('Choose logo')"
                        :empty-label="__('No logo selected')"
                        :dialog-title="__('Choose brand logo')"
                        :dialog-description="__('Select or upload an image from your file library.')"
                        layout="compact"
                    />
                </x-ui.card>

                <x-ui.card class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Color palette') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use balanced colors for interaction, readable text, and campaign highlights.') }}</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        @foreach ([
                            'primary_color' => ['label' => __('Primary'), 'help' => __('Buttons and active states')],
                            'secondary_color' => ['label' => __('Text'), 'help' => __('Headlines and QR ink')],
                            'accent_color' => ['label' => __('Accent'), 'help' => __('Highlights and badges')],
                        ] as $field => $meta)
                            <div wire:key="brand-kit-color-{{ $field }}-{{ $form[$field] }}" class="rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-base);">
                                <x-ui.color-picker
                                    wire:model.live="form.{{ $field }}"
                                    :label="$meta['label']"
                                    :help="$meta['help']"
                                    :value="$form[$field]"
                                    :presets="$colorPresets"
                                />
                            </div>
                        @endforeach
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($palettePresets as $preset)
                            <button
                                type="button"
                                wire:click="applyPalette('{{ $preset[0] }}', '{{ $preset[1] }}', '{{ $preset[2] }}')"
                                class="rounded-[0.9rem] border p-3 text-left transition hover:-translate-y-0.5"
                                style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-base);"
                            >
                                <div class="flex gap-1.5">
                                    <span class="h-6 flex-1 rounded-[0.45rem]" style="background-color: {{ $preset[0] }};"></span>
                                    <span class="h-6 flex-1 rounded-[0.45rem]" style="background-color: {{ $preset[1] }};"></span>
                                    <span class="h-6 flex-1 rounded-[0.45rem]" style="background-color: {{ $preset[2] }};"></span>
                                </div>
                                <p class="mt-2 text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $preset[3] }}</p>
                            </button>
                        @endforeach
                    </div>

                    <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="w-full justify-center">
                        <i class="fa-light fa-floppy-disk" wire:loading.remove wire:target="save"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="save"></i>
                        {{ __('Save brand kit') }}
                    </x-ui.button>
                </x-ui.card>
            </div>

            <div class="space-y-5">
                <x-ui.card class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Live preview') }}</p>
                            <h2 class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Public experience') }}</h2>
                        </div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(16,185,129,0.1); color: #059669;">{{ __('Brand ready') }}</span>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,0.72fr)_minmax(14rem,0.28fr)]">
                        <div class="overflow-hidden rounded-[1.2rem] border shadow-[0_24px_60px_-44px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                            <div class="p-6" style="background: linear-gradient(135deg, {{ $primary }}, {{ $accent }});">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-[1rem] shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88);">
                                        @if ($form['logo_url'])
                                            <img src="{{ $form['logo_url'] }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <i class="fa-light fa-sparkles text-xl" style="color: {{ $primary }}"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-lg font-semibold text-white">{{ $brandName }}</p>
                                        <p class="mt-1 text-sm text-white/76">{{ __('Bio link campaign') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4 p-5">
                                @foreach ([__('Shop collection'), __('Book a consultation'), __('Download media kit')] as $label)
                                    <div class="flex items-center justify-between rounded-[0.9rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.66);">
                                        <span class="text-sm font-semibold" style="color: {{ $text }};">{{ $label }}</span>
                                        <i class="fa-light fa-arrow-right" style="color: {{ $primary }};"></i>
                                    </div>
                                @endforeach
                                <button type="button" class="w-full rounded-[0.9rem] px-4 py-3 text-sm font-semibold text-white" style="background-color: {{ $primary }};">{{ __('Primary action') }}</button>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-soft);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('QR sticker') }}</p>
                            <div class="mt-4 rounded-[1rem] p-4" style="background-color: color-mix(in srgb, {{ $accent }} 18%, white);">
                                <div class="mx-auto grid h-32 w-32 grid-cols-5 gap-1 rounded-[0.7rem] bg-white p-3 shadow-sm">
                                    @for ($i = 0; $i < 25; $i++)
                                        <span class="rounded-[0.2rem]" style="background-color: {{ in_array($i, [0,4,20,24,6,8,12,16,18,22], true) ? $text : (in_array($i, [2,10,14,19], true) ? $primary : 'rgba(148,163,184,0.28)') }};"></span>
                                    @endfor
                                </div>
                                <div class="mx-auto mt-3 w-fit rounded-full border px-4 py-2 text-xs font-semibold" style="border-color: {{ $primary }}; color: {{ $primary }}; background-color: white;">{{ __('Scan me') }}</div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Bio pages'), 'icon' => 'fa-link-horizontal'],
                            ['label' => __('QR campaigns'), 'icon' => 'fa-qrcode'],
                            ['label' => __('Brand domains'), 'icon' => 'fa-globe-pointer'],
                        ] as $item)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background-color: var(--theme-surface-soft);">
                                <i class="fa-light {{ $item['icon'] }}" style="color: {{ $primary }};"></i>
                                <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['label'] }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Uses this kit when publishing new assets.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</div>
