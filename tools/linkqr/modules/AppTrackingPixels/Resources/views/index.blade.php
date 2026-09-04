@php
    $totalPixels = $pixels->count();
    $activePixels = $pixels->where('status', 'active')->count();
    $providers = $pixels->pluck('provider')->unique()->count();
    $providerMeta = [
        'meta' => ['label' => 'Meta', 'icon' => 'fa-brands fa-meta', 'color' => '#2563eb'],
        'google' => ['label' => 'Google', 'icon' => 'fa-brands fa-google', 'color' => '#ea4335'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'fa-brands fa-tiktok', 'color' => '#111827'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'color' => '#0a66c2'],
        'custom' => ['label' => 'Custom', 'icon' => 'fa-light fa-code', 'color' => '#7c3aed'],
    ];
    $selectedProvider = $providerMeta[$form['provider']] ?? $providerMeta['meta'];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.72fr)]">
                <div class="p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-bullseye-arrow"></i>
                            {{ __('Brand attribution') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-cyan-50 px-3 py-1.5 text-[11px] font-semibold text-cyan-700">
                            <i class="fa-light fa-signal-stream"></i>
                            {{ __('Campaign ready') }}
                        </span>
                    </div>
                    <h1 class="mt-5 text-[2.25rem] font-semibold leading-tight tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('Tracking Pixels') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Manage remarketing and attribution pixels once, then attach them to QR campaigns, Bio pages, product pages, menus, and lead forms.') }}</p>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Pixels'), 'value' => $totalPixels, 'icon' => 'fa-bullseye-arrow', 'color' => '#2563eb'],
                            ['label' => __('Active'), 'value' => $activePixels, 'icon' => 'fa-circle-check', 'color' => '#059669'],
                            ['label' => __('Providers'), 'value' => $providers, 'icon' => 'fa-grid-2', 'color' => '#7c3aed'],
                        ] as $stat)
                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <p class="mt-3 text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($stat['value']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(124,58,237,0.10), rgba(6,182,212,0.12) 48%, rgba(16,185,129,0.12));">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_70px_-46px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Attribution flow') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Scan -> Visit -> Retarget') }}</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                <i class="fa-light fa-chart-network"></i>
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3">
                            @foreach ([
                                ['label' => __('QR scan'), 'value' => __('Visitor enters campaign'), 'icon' => 'fa-qrcode', 'color' => '#2563eb'],
                                ['label' => __('Landing event'), 'value' => __('Page view and CTA tracked'), 'icon' => 'fa-browser', 'color' => '#06b6d4'],
                                ['label' => __('Ad audience'), 'value' => __('Pixel sends conversion signal'), 'icon' => 'fa-bullseye-arrow', 'color' => '#7c3aed'],
                            ] as $step)
                                <div class="flex items-center gap-3 rounded-[0.95rem] border px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $step['color'] }}14; color: {{ $step['color'] }};">
                                        <i class="fa-light {{ $step['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $step['label'] }}</p>
                                        <p class="mt-0.5 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $step['value'] }}</p>
                                    </div>
                                    <i class="fa-light fa-arrow-right text-xs" style="color: var(--theme-muted-text-color);"></i>
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
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Add tracking pixel') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Store provider IDs once and reuse them when publishing campaigns.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: {{ $selectedProvider['color'] }}14; color: {{ $selectedProvider['color'] }};">
                        <i class="{{ $selectedProvider['icon'] }}"></i>
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.input wire:model.defer="form.name" :label="__('Pixel name')" :placeholder="__('Meta retargeting')" />

                    <div class="grid gap-3 md:grid-cols-2">
                        <x-ui.select wire:model.live="form.provider" :label="__('Provider')">
                            <option value="meta">{{ __('Meta') }}</option>
                            <option value="google">{{ __('Google') }}</option>
                            <option value="tiktok">{{ __('TikTok') }}</option>
                            <option value="linkedin">{{ __('LinkedIn') }}</option>
                            <option value="custom">{{ __('Custom') }}</option>
                        </x-ui.select>
                        <x-ui.select wire:model.defer="form.status" :label="__('Status')">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="paused">{{ __('Paused') }}</option>
                        </x-ui.select>
                    </div>

                    <x-ui.input wire:model.defer="form.pixel_id" :label="__('Pixel ID')" :placeholder="$form['provider'] === 'google' ? __('G-XXXXXXXXXX or AW-123456') : __('1234567890')" />

                    <x-ui.checkbox
                        wire:model.defer="form.is_default"
                        :checked="(bool) ($form['is_default'] ?? false)"
                        :label="__('Use as default for new Short Links, Link Bio pages, and QR Codes')"
                    />

                    @if ($form['provider'] === 'custom')
                        <x-ui.textarea wire:model.defer="form.script" :label="__('Custom pixel script')" :error="$errors->first('script')" rows="7" placeholder="{{ __('<script>...</script>') }}"></x-ui.textarea>
                    @endif

                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: {{ $selectedProvider['color'] }}14; color: {{ $selectedProvider['color'] }};">
                                <i class="{{ $selectedProvider['icon'] }}"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedProvider['label'] }} {{ __('pixel') }}</p>
                                <p class="mt-0.5 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Attach this pixel to public pages that need remarketing or conversion reporting.') }}</p>
                            </div>
                        </div>
                    </div>

                    <x-ui.button type="button" wire:click="addPixel" wire:loading.attr="disabled" wire:target="addPixel" class="w-full justify-center">
                        <i class="fa-light fa-plus" wire:loading.remove wire:target="addPixel"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="addPixel"></i>
                        <span wire:loading.remove wire:target="addPixel">{{ __('Add pixel') }}</span>
                        <span wire:loading wire:target="addPixel">{{ __('Adding...') }}</span>
                    </x-ui.button>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Pixel library') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Provider IDs, publishing status, and attribution controls.') }}</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                        <i class="fa-light fa-signal-stream"></i>
                        {{ __('Attribution ready') }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($pixels as $pixel)
                        @php($meta = $providerMeta[$pixel->provider] ?? $providerMeta['custom'])
                        <div class="rounded-[1.05rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-[0_20px_42px_-34px_rgba(15,23,42,0.42)]" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background: linear-gradient(180deg, var(--theme-surface-base), var(--theme-surface-soft));">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.95rem]" style="background-color: {{ $meta['color'] }}14; color: {{ $meta['color'] }};">
                                        <i class="{{ $meta['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $pixel->name }}</p>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.08em]" style="background-color: {{ $meta['color'] }}14; color: {{ $meta['color'] }};">{{ $meta['label'] }}</span>
                                            @if ($pixel->is_default)
                                                <x-ui.badge variant="success">{{ __('Default') }}</x-ui.badge>
                                            @endif
                                            <x-ui.badge :variant="$pixel->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($pixel->status) }}</x-ui.badge>
                                        </div>
                                        <p class="mt-1 truncate font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $pixel->pixel_id }}</p>
                                    </div>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    @if ($pixel->is_default)
                                        <x-ui.button type="button" wire:click="unsetDefault({{ $pixel->id }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star"></i>
                                            {{ __('Unset') }}
                                        </x-ui.button>
                                    @else
                                        <x-ui.button type="button" wire:click="setDefault({{ $pixel->id }})" variant="outline" size="sm">
                                            <i class="fa-light fa-star"></i>
                                            {{ __('Set default') }}
                                        </x-ui.button>
                                    @endif
                                    <x-ui.button type="button" wire:click="toggleStatus({{ $pixel->id }})" variant="outline" size="sm">
                                        <i class="fa-light {{ $pixel->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i>
                                        {{ $pixel->status === 'active' ? __('Pause') : __('Activate') }}
                                    </x-ui.button>
                                    <x-ui.dialog :title="__('Delete tracking pixel?')" :description="__('This removes the pixel from future campaign publishing.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <x-ui.button type="button" variant="outline" size="sm" class="h-9 w-9 px-0 hover:!border-red-200 hover:!text-red-600"><i class="fa-light fa-trash-can"></i></x-ui.button>
                                        </x-slot:trigger>
                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" wire:click="deletePixel({{ $pixel->id }})" x-on:click="open = false">{{ __('Delete') }}</x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(124,58,237,0.06), rgba(6,182,212,0.08));">
                            <div class="grid gap-4 lg:grid-cols-[minmax(0,0.58fr)_minmax(0,0.42fr)] lg:items-center">
                                <div>
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1rem] shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                        <i class="fa-light fa-bullseye-arrow text-lg"></i>
                                    </span>
                                    <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No tracking pixels yet') }}</h3>
                                    <p class="mt-2 max-w-lg text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Add Meta, Google, TikTok, LinkedIn, or custom pixels before enabling campaign attribution.') }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 rounded-[1rem] p-3 shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                                    @foreach ($providerMeta as $meta)
                                        <div class="flex items-center gap-2 rounded-[0.8rem] px-3 py-2" style="background-color: {{ $meta['color'] }}10; color: {{ $meta['color'] }};">
                                            <i class="{{ $meta['icon'] }} w-4 text-center"></i>
                                            <span class="text-xs font-semibold">{{ $meta['label'] }}</span>
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
