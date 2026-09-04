<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @push('head')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Poppins:wght@400;700&family=Lato:wght@400;700&family=Nunito:wght@400;700&family=Playfair+Display:wght@600;700&family=Merriweather:wght@400;700&family=Oswald:wght@400;700&family=Raleway:wght@400;700&family=Noto+Sans:wght@400;700&family=Noto+Serif:wght@400;700&family=Noto+Sans+Arabic:wght@400;700&family=Noto+Sans+JP:wght@400;700&family=Noto+Sans+SC:wght@400;700&family=Noto+Sans+Devanagari:wght@400;700&display=swap" rel="stylesheet">
    @endpush

    <section class="rounded-[1.25rem] border px-5 py-7 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-accent);">{{ __('Link Bio') }}</p>
        <h1 class="mt-2 text-[2rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Bio QR & Share') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create a branded QR, short link, and scan sticker for each Link Bio page.') }}</p>
    </section>

    @if ($pages->isEmpty())
        <x-ui.card>
            <x-ui.empty icon="fa-light fa-qrcode" :title="__('No QR codes yet')" :description="__('Create a bio page first, then QR codes will appear here.')" />
        </x-ui.card>
    @else
        @php
            $palettes = [
                ['foreground' => '#0f172a', 'background' => '#ffffff', 'gradient' => null],
                ['foreground' => '#0f766e', 'background' => '#fbbf24', 'gradient' => 'warm'],
                ['foreground' => '#1d4ed8', 'background' => '#ecfeff', 'gradient' => 'ocean'],
                ['foreground' => '#9f1239', 'background' => '#fff1f2', 'gradient' => 'rose'],
                ['foreground' => '#166534', 'background' => '#f0fdf4', 'gradient' => 'fresh'],
                ['foreground' => '#7c3aed', 'background' => '#faf5ff', 'gradient' => 'violet'],
            ];
            $patterns = [
                ['key' => 'square', 'label' => __('Square'), 'icon' => 'fa-grid-2'],
                ['key' => 'dots', 'label' => __('Dots'), 'icon' => 'fa-circle-small'],
                ['key' => 'rounded', 'label' => __('Rounded'), 'icon' => 'fa-square-rounded'],
                ['key' => 'mosaic', 'label' => __('Mosaic'), 'icon' => 'fa-sparkles'],
                ['key' => 'outlined', 'label' => __('Outlined'), 'icon' => 'fa-shapes'],
                ['key' => 'sticker', 'label' => __('Sticker'), 'icon' => 'fa-ticket-simple'],
            ];
        @endphp

        <div class="space-y-5">
            @foreach ($pages as $page)
                @php
                    $shortLink = $shortLinks[$page->id] ?? null;
                    $shortUrl = $shortLink ? route('link-bio.short.show', ['code' => $shortLink->code]) : route('link-bio.public.show', ['slug' => $page->slug]);
                    $foreground = data_get($designState, $page->id.'.foreground_color', '#0f172a');
                    $background = data_get($designState, $page->id.'.background_color', '#ffffff');
                    $gradient = data_get($designState, $page->id.'.gradient_type');
                    $pattern = data_get($designState, $page->id.'.pattern', 'square');
                    $logoUrl = data_get($designState, $page->id.'.logo_url', '');
                    $stickerText = data_get($designState, $page->id.'.sticker_text', __('Scan me'));
                    $stickerFont = data_get($designState, $page->id.'.sticker_font', 'Inter');
                    $previewBackground = match ($gradient) {
                        'warm' => 'linear-gradient(135deg, #fbbf24 0%, #fb7185 100%)',
                        'ocean' => 'linear-gradient(135deg, #dbeafe 0%, #67e8f9 100%)',
                        'rose' => 'linear-gradient(135deg, #fff1f2 0%, #fda4af 100%)',
                        'fresh' => 'linear-gradient(135deg, #ecfccb 0%, #86efac 100%)',
                        'violet' => 'linear-gradient(135deg, #f5f3ff 0%, #c4b5fd 100%)',
                        default => $background,
                    };
                    $qrShellClass = match ($pattern) {
                        'dots' => 'rounded-[1.4rem] p-3',
                        'rounded' => 'rounded-[1.6rem] p-3',
                        'mosaic' => 'rounded-[1.1rem] p-3',
                        'outlined' => 'rounded-[1.25rem] p-3',
                        'sticker' => 'rounded-[1.15rem] p-3',
                        default => 'rounded-[1rem] p-3',
                    };
                    $qrShellStyle = match ($pattern) {
                        'dots' => 'background: radial-gradient(circle at 18% 18%, rgba(255,255,255,0.98) 0 2px, transparent 3px), '.$previewBackground.';',
                        'rounded' => 'background: '.$background.';',
                        'mosaic' => 'background: linear-gradient(135deg, rgba(255,255,255,0.96), rgba(255,255,255,0.72)), repeating-linear-gradient(45deg, '.$foreground.'22 0 8px, transparent 8px 16px);',
                        'outlined' => 'background: transparent; border: 2px dashed '.$foreground.';',
                        'sticker' => 'background: '.$previewBackground.';',
                        default => 'background: '.$background.';',
                    };
                    $qrSvgClass = match ($pattern) {
                        'rounded' => 'rounded-[0.8rem]',
                        'outlined' => 'rounded-[0.6rem] ring-2 ring-white',
                        default => 'rounded-[0.45rem]',
                    };
                @endphp

                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="grid min-h-[34rem] xl:grid-cols-[minmax(0,1fr)_24rem]">
                        <div class="space-y-6 p-5 sm:p-7">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge :variant="$page->is_published ? 'success' : 'neutral'">{{ $page->is_published ? __('Published') : __('Draft') }}</x-ui.badge>
                                        <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb),0.22); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb),0.08);">{{ __('Dynamic QR') }}</span>
                                    </div>
                                    <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $page->title }}</h2>
                                    <p class="mt-1 break-all text-sm" style="color: var(--theme-muted-text-color);">{{ $shortUrl }}</p>
                                </div>

                                <div class="flex shrink-0 gap-2">
                                    <x-ui.button href="{{ $shortUrl }}" target="_blank" variant="outline">
                                        <i class="fa-light fa-arrow-up-right-from-square"></i>
                                        {{ __('Open') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" wire:click="saveDesign({{ $page->id }})">
                                        <i class="fa-light fa-floppy-disk"></i>
                                        {{ __('Save') }}
                                    </x-ui.button>
                                </div>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-accent-rgb),0.2); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.07), rgba(20,184,166,0.05));">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.9rem]" style="background-color: rgba(var(--theme-accent-rgb),0.12); color: var(--theme-accent);">
                                            <i class="fa-light fa-link-horizontal"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Connected bio page') }}</p>
                                            <h3 class="mt-1 truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR opens this Link Bio page') }}</h3>
                                            <p class="mt-1 break-all text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $shortUrl }}</p>
                                        </div>
                                    </div>
                                    <x-ui.button href="{{ route('portal.link-bio.edit', ['page' => $page->id]) }}" variant="outline">
                                        <i class="fa-light fa-pen-to-square"></i>
                                        {{ __('Edit bio page') }}
                                    </x-ui.button>
                                </div>
                            </div>

                            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: rgba(var(--theme-border-color-rgb),0.035);">
                                <div class="grid gap-4 lg:grid-cols-[1fr_12rem]">
                                    <x-ui.input :label="__('Short link')" :value="$shortUrl" readonly />
                                    <x-ui.input :label="__('Public slug')" :value="$page->slug" readonly />
                                </div>
                            </div>

                            <div class="grid gap-5 lg:grid-cols-2">
                                <div class="space-y-4">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <x-ui.input wire:model.live.debounce.250ms="designState.{{ $page->id }}.sticker_text" :label="__('Sticker text')" />
                                        <x-ui.select wire:model.live="designState.{{ $page->id }}.sticker_font" :label="__('Font')">
                                            @foreach ($fontOptions as $fontOption)
                                                <option value="{{ $fontOption }}">{{ $fontOption }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <x-ui.color-picker wire:model.live="designState.{{ $page->id }}.foreground_color" :label="__('Foreground')" :value="$foreground" />
                                        <x-ui.color-picker wire:model.live="designState.{{ $page->id }}.background_color" :label="__('Background')" :value="$background" />
                                    </div>

                                    <div class="space-y-2">
                                        <x-ui.label>{{ __('Gradient presets') }}</x-ui.label>
                                        <div class="grid grid-cols-6 gap-2">
                                            @foreach ($palettes as $palette)
                                                <button
                                                    type="button"
                                                    wire:click="applyPalette({{ $page->id }}, '{{ $palette['foreground'] }}', '{{ $palette['background'] }}', {!! $palette['gradient'] ? "'".$palette['gradient']."'" : 'null' !!})"
                                                    class="h-11 rounded-[0.85rem] border shadow-sm transition hover:-translate-y-0.5"
                                                    style="border-color: {{ strtolower($foreground) === strtolower($palette['foreground']) && strtolower($background) === strtolower($palette['background']) ? 'var(--theme-accent)' : 'var(--theme-border-color)' }}; background: linear-gradient(135deg, {{ $palette['foreground'] }} 0%, {{ $palette['background'] }} 100%);"
                                                    aria-label="{{ __('Choose palette') }}"
                                                ></button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <x-ui.label>{{ __('Patterns & shapes') }}</x-ui.label>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ($patterns as $patternOption)
                                                <button
                                                    type="button"
                                                    wire:click="applyPattern({{ $page->id }}, '{{ $patternOption['key'] }}')"
                                                    class="flex h-12 items-center justify-center gap-2 rounded-[0.85rem] border px-3 text-sm font-semibold transition hover:-translate-y-0.5"
                                                    style="{{ $pattern === $patternOption['key'] ? 'border-color: rgba(var(--theme-accent-rgb),0.58); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb),0.08);' : 'border-color: var(--theme-border-color); color: var(--theme-header-text-color); background-color: var(--theme-input-surface);' }}"
                                                >
                                                    <i class="fa-light {{ $patternOption['icon'] }}"></i>
                                                    <span class="truncate">{{ $patternOption['label'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <x-ui.image-picker
                                        wire:model.defer="designState.{{ $page->id }}.logo_url"
                                        :name="'share_kit_logo_'.$page->id"
                                        context="portal"
                                        value-field="url"
                                        :value="$logoUrl"
                                        :preview="$logoUrl"
                                        :label="__('Logo')"
                                        :button-label="__('Choose logo')"
                                        :dialog-title="__('Choose QR logo')"
                                        :dialog-description="__('Select a transparent logo or brand mark from Files.')"
                                    />
                                </div>
                            </div>
                        </div>

                        <aside class="border-t p-5 sm:p-7 xl:border-l xl:border-t-0" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                            <div class="sticky top-6 space-y-5">
                                <div class="rounded-[1.15rem] border p-5 shadow-[0_22px_50px_-34px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.76); background-color: var(--theme-surface-base);">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Live Preview') }}</p>
                                            <h3 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __(ucfirst($pattern).' QR') }}</h3>
                                        </div>
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                            <i class="fa-light fa-qrcode"></i>
                                        </span>
                                    </div>

                                    <div class="mt-5 rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: {{ $previewBackground }};">
                                        <div class="mx-auto flex min-h-[16rem] max-w-[18rem] flex-col items-center justify-center rounded-[1rem] bg-white/92 p-4 shadow-[0_20px_38px_-32px_rgba(15,23,42,0.5)]">
                                            @if ($pattern === 'sticker')
                                                <div class="relative w-full max-w-[12.5rem] overflow-hidden rounded-[1.35rem] p-3 shadow-[0_18px_36px_-28px_rgba(15,23,42,0.5)]" style="background: {{ $previewBackground }}; border: 1px solid rgba(var(--theme-border-color-rgb),0.76);">
                                                    <div class="relative rounded-[1rem] bg-white p-3 shadow-sm">
                                                        <div class="mx-auto rounded-[0.7rem] bg-white p-1.5 [&_svg]:h-[9rem] [&_svg]:w-[9rem]">
                                                            {!! $qrCodes[$page->id] ?? '' !!}
                                                        </div>
                                                        @if (filled($logoUrl))
                                                            <span class="absolute left-1/2 top-1/2 inline-flex h-7 w-7 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-[0.45rem] border bg-white p-0.5 shadow-[0_8px_18px_-12px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                                                                <img src="{{ $logoUrl }}" alt="" class="h-full w-full rounded-[0.32rem] object-cover">
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="mx-auto mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-full bg-white px-3 text-center text-sm font-bold shadow-sm" style="font-family: {{ $stickerFont }}, sans-serif; color: {{ $foreground }};">
                                                        <span class="truncate">{{ $stickerText ?: __('Scan me') }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="{{ $qrShellClass }} relative shadow-sm" style="{{ $qrShellStyle }}" wire:key="qr-shell-{{ $page->id }}-{{ md5($foreground.'|'.$background.'|'.$logoUrl.'|'.$pattern) }}">
                                                    @if ($pattern === 'outlined')
                                                        <span class="pointer-events-none absolute -right-2 -top-2 h-8 w-8 rounded-full bg-white shadow-sm" style="border: 2px solid {{ $foreground }};"></span>
                                                        <span class="pointer-events-none absolute -bottom-2 -left-2 h-7 w-7 rounded-full bg-white shadow-sm" style="border: 2px solid {{ $foreground }};"></span>
                                                    @endif

                                                    <div class="{{ $qrSvgClass }} bg-white p-2">
                                                        {!! $qrCodes[$page->id] ?? '' !!}
                                                    </div>

                                                    @if (filled($logoUrl))
                                                        <span class="absolute left-1/2 top-1/2 inline-flex h-9 w-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-[0.65rem] border bg-white p-1 shadow-[0_10px_24px_-16px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                                                            <img src="{{ $logoUrl }}" alt="" class="h-full w-full rounded-[0.45rem] object-cover">
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if ($pattern !== 'sticker' && filled($stickerText))
                                                <div class="mt-4 inline-flex min-h-11 max-w-full items-center justify-center gap-2 rounded-full border bg-white px-3.5 py-2 text-sm font-semibold shadow-[0_16px_32px_-26px_rgba(15,23,42,0.45)]" style="font-family: {{ $stickerFont }}, sans-serif; color: {{ $foreground }}; border-color: {{ $foreground }};">
                                                    @if (filled($stickerText))
                                                        <span class="truncate">{{ $stickerText }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2">
                                        <x-ui.button href="{{ route('portal.link-bio.edit', ['page' => $page->id]) }}" variant="outline" block>
                                            <i class="fa-light fa-pen-to-square"></i>
                                            {{ __('Edit Bio') }}
                                        </x-ui.button>
                                        <x-ui.button type="button" wire:click="saveDesign({{ $page->id }})" block>
                                            <i class="fa-light fa-floppy-disk"></i>
                                            {{ __('Save QR') }}
                                        </x-ui.button>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</div>
