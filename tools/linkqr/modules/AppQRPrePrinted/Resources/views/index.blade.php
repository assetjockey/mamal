@php
    $templateQr = $qrCodes->firstWhere('id', $templateQrCodeId);
    $selectedTypeMeta = $dynamicTypes->firstWhere('key', $selectedType) ?? $dynamicTypes->first();
    $selectedQrStyle = $qrStyleTemplates->firstWhere('key', $qrStyleTemplate) ?? $qrStyleTemplates->first();
    $selectedPublicTemplate = $publicTemplates->firstWhere('key', $publicTemplate) ?? $publicTemplates->first();
    $count = max(1, min(500, (int) $count));
    $shortUrl = $templateQr?->shortUrl();
    $sheetSlots = min(24, max(6, $count));
    $hasTemplateDestination = filled($templateQr?->destination_url);
    $miniQrBits = [
        1, 1, 1, 0, 1, 1, 1,
        1, 0, 1, 0, 0, 0, 1,
        1, 1, 1, 0, 1, 1, 1,
        0, 0, 0, 1, 0, 1, 0,
        1, 0, 1, 1, 1, 0, 1,
        1, 1, 0, 0, 1, 1, 0,
        0, 1, 1, 1, 0, 1, 1,
    ];
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-5">
        <section class="overflow-hidden rounded-[1.35rem] border shadow-[0_34px_100px_-72px_rgba(15,23,42,0.6)]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
            radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.12), transparent 30%),
            linear-gradient(135deg, color-mix(in srgb, var(--theme-surface-base) 98%, transparent), color-mix(in srgb, var(--theme-surface-soft) 90%, transparent));">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(26rem,0.5fr)]">
                <div class="p-6 sm:p-7">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <div class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.2); background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                <i class="fa-light fa-print"></i>
                                {{ __('Print Batch Studio') }}
                            </div>
                            <h1 class="mt-4 text-[2rem] font-semibold leading-tight tracking-[-0.045em] sm:text-[2.35rem]" style="color: var(--theme-header-text-color);">{{ __('Pre-Printed QR') }}</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Create print-ready QR batches with unique short links, reusable QR styles, and dynamic public pages for labels, cards, packaging, and events.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2">
                            @if (Route::has('portal.qr-codes.index'))
                                <x-ui.button href="{{ route('portal.qr-codes.index') }}" variant="outline" class="justify-center">
                                    <i class="fa-light fa-qrcode"></i>
                                    {{ __('Campaigns') }}
                                </x-ui.button>
                            @endif
                            @if (Route::has('portal.qr-codes.create'))
                                <x-ui.button href="{{ route('portal.qr-codes.create') }}" class="justify-center">
                                    <i class="fa-light fa-plus"></i>
                                    {{ __('New QR') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>

                    @if ($generatedCount > 0)
                        <div class="mt-5 rounded-[1rem] border px-4 py-3" style="border-color: rgba(16,185,129,0.34); background-color: rgba(16,185,129,0.08); color: #047857;">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold">{{ __('Created :count QR campaigns for this print batch.', ['count' => number_format($generatedCount)]) }}</p>
                                    <p class="mt-1 font-mono text-xs">{{ $lastBatchId }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($lastBatchId && Route::has('portal.qr-codes.pre-printed.export'))
                                        <a href="{{ route('portal.qr-codes.pre-printed.export', ['batch' => $lastBatchId]) }}" class="inline-flex items-center gap-2 rounded-[0.75rem] bg-white px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                                            <i class="fa-light fa-file-csv"></i>
                                            {{ __('Export CSV') }}
                                        </a>
                                    @endif
                                    @if (Route::has('portal.qr-codes.index'))
                                        <a href="{{ route('portal.qr-codes.index') }}" class="inline-flex items-center gap-2 rounded-[0.75rem] bg-white px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm">
                                            {{ __('Open campaigns') }}
                                            <i class="fa-light fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-7 grid gap-3 sm:grid-cols-3">
                        @foreach ([
                            ['label' => __('Batch size'), 'value' => number_format($count), 'icon' => 'fa-layer-group'],
                            ['label' => __('QR design'), 'value' => $selectedQrStyle['label'] ?? __('Default QR'), 'icon' => 'fa-palette'],
                            ['label' => __('Dynamic view'), 'value' => $selectedPublicTemplate['label'] ?? __('Split showcase'), 'icon' => $selectedPublicTemplate['icon'] ?? 'fa-window'],
                        ] as $stat)
                            <div class="rounded-[1rem] border px-4 py-3 shadow-[0_16px_44px_-38px_rgba(15,23,42,0.44)]" style="border-color: rgba(var(--theme-border-color-rgb),0.46); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.74);">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.85rem] shadow-sm" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                        <p class="mt-1 truncate text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $stat['value'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                        @foreach ([__('Unique links'), __('CSV export'), __('PNG/SVG downloads'), __('Editable destinations')] as $badge)
                            <span class="rounded-full border px-3 py-1.5" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.62);">{{ $badge }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background:
                    radial-gradient(circle at top right, rgba(var(--theme-accent-rgb),0.12), transparent 35%),
                    color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_80px_-56px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Batch preview') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedFormat['label'] ?? __('Square sticker') }} / {{ $selectedFormat['grid'] ?? '-' }}</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.95rem] shadow-sm" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-grid-2"></i>
                            </span>
                        </div>

                        <div class="mt-4 rounded-[1.05rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.48); background:
                            linear-gradient(180deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.74), rgba(var(--theme-surface-base-rgb,255,255,255),0.34));">
                            <div class="mb-3 flex items-center justify-between rounded-[0.8rem] px-3 py-2" style="background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-muted-text-color);">
                                <span class="text-[10px] font-semibold uppercase tracking-[0.16em]">{{ __('Print sheet') }}</span>
                                <span class="text-[10px] font-semibold">{{ str_pad((string) $sheetSlots, 2, '0', STR_PAD_LEFT) }} {{ __('codes') }}</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2.5">
                            @for ($slot = 1; $slot <= $sheetSlots; $slot++)
                                <div class="aspect-square rounded-[0.75rem] border p-1.5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.34); background-color: {{ $selectedQrStyle['surface'] ?? 'var(--theme-surface-base)' }};">
                                    <div class="flex h-full flex-col items-center justify-center rounded-[0.55rem] border text-center shadow-[inset_0_1px_0_rgba(255,255,255,0.38)]" style="border-color: {{ $selectedQrStyle['accent'] ?? 'rgba(var(--theme-accent-rgb),0.28)' }}; background-color: {{ $selectedQrStyle['background'] ?? '#ffffff' }}; color: {{ $selectedQrStyle['foreground'] ?? 'var(--theme-header-text-color)' }};">
                                        <span class="grid h-5 w-5 grid-cols-7 gap-[1px]" aria-hidden="true">
                                            @foreach ($miniQrBits as $bit)
                                                <span class="block aspect-square rounded-[1px]" style="background-color: {{ $bit ? 'currentColor' : 'transparent' }};"></span>
                                            @endforeach
                                        </span>
                                        <span class="mt-1 text-[9px] font-semibold">#{{ str_pad((string) $slot, 3, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            @endfor
                            </div>
                        </div>

                        <p class="mt-3 truncate rounded-full px-3 py-2 text-center font-mono text-xs" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.68); color: var(--theme-muted-text-color);">{{ $shortUrl ?: __('Unique short links will be generated for each code') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(22rem,0.32fr)_minmax(0,1fr)_minmax(20rem,0.28fr)]">
            <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Batch setup') }}</h2>
                <div class="mt-4 space-y-4">
                    <x-ui.input type="text" wire:model.live.debounce.300ms="batchName" name="batchName" :label="__('Batch name')" />
                    <x-ui.input type="number" min="1" max="500" wire:model.live.debounce.300ms="count" name="count" :label="__('QR quantity')" />

                    <label class="block">
                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Copy content from QR') }}</span>
                        <select wire:model.live="templateQrCodeId" class="mt-2 w-full rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            <option value="">{{ __('Blank editable QR codes') }}</option>
                            @foreach ($qrCodes as $qrCode)
                                <option value="{{ $qrCode->id }}">{{ $qrCode->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Optional. Use this when the batch should copy an existing QR destination, domain, and logo.') }}</p>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Dynamic QR type') }}</span>
                        <select wire:model.live="selectedType" class="mt-2 w-full rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            @foreach ($dynamicTypes as $type)
                                <option value="{{ $type['key'] }}">{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div>
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR design template') }}</h2>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('These are real QR Builder style templates. Generated codes keep this pattern, color, and style_template setting.') }}</p>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($qrStyleTemplates as $style)
                        <button type="button" wire:click="$set('qrStyleTemplate', '{{ $style['key'] }}')" class="group rounded-[1rem] border p-4 text-left transition" style="border-color: {{ $qrStyleTemplate === $style['key'] ? 'rgba(var(--theme-accent-rgb),0.5)' : 'rgba(var(--theme-border-color-rgb),0.62)' }}; background-color: {{ $qrStyleTemplate === $style['key'] ? 'rgba(var(--theme-accent-rgb),0.08)' : 'var(--theme-surface-soft)' }};">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[0.85rem]" style="background-color: {{ $style['surface'] ?? '#ffffff' }}; color: {{ $style['foreground'] ?? '#0f172a' }}; border: 2px solid {{ $style['accent'] ?? '#2563eb' }};">
                                    <span class="grid h-6 w-6 grid-cols-7 gap-[1px]" aria-hidden="true">
                                        @foreach ($miniQrBits as $bit)
                                            <span class="block aspect-square rounded-[1px]" style="background-color: {{ $bit ? 'currentColor' : 'transparent' }};"></span>
                                        @endforeach
                                    </span>
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $style['label'] }}</span>
                                    <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $style['description'] }}</span>
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-soft);">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Public page view') }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose the visual layout visitors see after scanning this QR.') }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            {{ $publicTemplates->count() }} {{ __('views') }}
                        </span>
                    </div>

                    <div class="mt-4 max-h-[34rem] overflow-y-auto pr-2">
                        <div class="grid grid-cols-[repeat(auto-fit,minmax(10.25rem,1fr))] gap-4">
                            @foreach ($publicTemplates as $template)
                                @php($templateKey = (string) $template['key'])
                                <button type="button" wire:click="$set('publicTemplate', '{{ $templateKey }}')" class="group flex min-h-[15.25rem] flex-col overflow-hidden rounded-[0.95rem] border text-left transition" style="border-color: {{ $publicTemplate === $templateKey ? 'rgba(var(--theme-accent-rgb),0.65)' : 'rgba(var(--theme-border-color-rgb),0.62)' }}; background-color: {{ $publicTemplate === $templateKey ? 'rgba(var(--theme-accent-rgb),0.08)' : 'var(--theme-surface-base)' }};">
                                    <span class="block h-28 border-b p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(148,163,184,0.42);">
                                        <span class="block h-full rounded-[0.65rem] p-2" style="background-color: #172033;">
                                            @switch($templateKey)
                                                @case('mobile_stack')
                                                @case('app_download')
                                                    <span class="mx-auto block h-full w-14 rounded-[0.65rem] bg-white p-1.5">
                                                        <span class="block h-5 rounded-[0.35rem] bg-cyan-400"></span>
                                                        <span class="mt-2 block h-1.5 rounded bg-slate-100"></span>
                                                        <span class="mt-2 block h-1.5 rounded bg-slate-100"></span>
                                                        <span class="mt-2 block h-1.5 rounded bg-slate-100"></span>
                                                    </span>
                                                    @break

                                                @case('catalog_grid')
                                                    <span class="grid h-full grid-cols-2 gap-2">
                                                        <span class="rounded-[0.35rem] bg-slate-200"></span>
                                                        <span class="rounded-[0.35rem] bg-teal-600"></span>
                                                        <span class="rounded-[0.35rem] bg-teal-100"></span>
                                                        <span class="rounded-[0.35rem] bg-slate-800"></span>
                                                    </span>
                                                    @break

                                                @case('menu_board')
                                                    <span class="block h-full border p-3" style="border-color: rgba(255,255,255,0.18); background-color: #111827;">
                                                        <span class="block h-2 w-20 rounded bg-amber-400"></span>
                                                        <span class="mt-4 block h-1.5 w-full rounded bg-slate-200"></span>
                                                        <span class="mt-2 block h-1.5 w-full rounded bg-slate-400"></span>
                                                        <span class="mt-5 block h-2 w-16 rounded bg-amber-400"></span>
                                                    </span>
                                                    @break

                                                @case('lead_capture')
                                                @case('sidebar_profile')
                                                    <span class="grid h-full grid-cols-[1fr_0.65fr] gap-3">
                                                        <span>
                                                            <span class="block h-2 w-20 rounded bg-rose-500/60"></span>
                                                            <span class="mt-2 block h-1.5 w-full rounded bg-slate-500"></span>
                                                            <span class="mt-3 block h-5 w-16 rounded bg-rose-500"></span>
                                                        </span>
                                                        <span class="rounded-[0.55rem] bg-rose-500/80"></span>
                                                    </span>
                                                    @break

                                                @case('event_pass')
                                                @case('booking_card')
                                                    <span class="block h-full rounded-[0.55rem] border p-3" style="border-color: rgba(255,255,255,0.2); background-color: #0f172a;">
                                                        <span class="block h-3 w-24 rounded bg-white"></span>
                                                        <span class="mt-4 block h-8 rounded bg-slate-600"></span>
                                                        <span class="mt-3 block h-2 w-20 rounded bg-blue-500"></span>
                                                    </span>
                                                    @break

                                                @case('review_spotlight')
                                                @case('resume_sheet')
                                                    <span class="block h-full rounded-[0.55rem] p-3" style="background-color: #111827;">
                                                        <span class="block h-2 w-24 rounded bg-white"></span>
                                                        <span class="mt-4 block h-1.5 w-full rounded bg-slate-600"></span>
                                                        <span class="mt-2 block h-1.5 w-4/5 rounded bg-slate-600"></span>
                                                        <span class="mt-5 block h-7 rounded bg-slate-700"></span>
                                                    </span>
                                                    @break

                                                @default
                                                    <span class="grid h-full grid-cols-[1fr_0.75fr] gap-3">
                                                        <span>
                                                            <span class="block h-2 w-24 rounded bg-blue-900"></span>
                                                            <span class="mt-2 block h-1.5 w-full rounded bg-slate-600"></span>
                                                            <span class="mt-3 block h-5 w-16 rounded bg-blue-600"></span>
                                                        </span>
                                                        <span class="rounded-[0.55rem] bg-blue-600"></span>
                                                    </span>
                                            @endswitch
                                        </span>
                                    </span>

                                    <span class="block px-4 py-3">
                                        <span class="flex items-start gap-2">
                                            <i class="fa-light {{ $template['icon'] }} mt-0.5 text-xs" style="color: var(--theme-accent);"></i>
                                            <span class="min-w-0">
                                                <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $template['label'] }}</span>
                                                <span class="mt-1 block line-clamp-3 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $template['description'] }}</span>
                                            </span>
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Print format') }}</span>
                        <select wire:model.live="format" class="mt-2 w-full rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            @foreach ($formats as $formatOption)
                                <option value="{{ $formatOption['key'] }}">{{ $formatOption['label'] }} - {{ $formatOption['size'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Material') }}</span>
                        <select wire:model.live="material" class="mt-2 w-full rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            @foreach ($materials as $materialOption)
                                <option value="{{ $materialOption['key'] }}">{{ $materialOption['label'] }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $selectedMaterial['finish'] ?? '' }}</p>
                    </label>
                </div>
            </section>

            <aside class="space-y-4">
                <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready check') }}</h2>
                    <div class="mt-4 space-y-2">
                        @foreach ([
                            ['label' => __('Unique short links'), 'done' => true],
                            ['label' => __('QR style template'), 'done' => filled($selectedQrStyle)],
                            ['label' => __('Dynamic view template'), 'done' => filled($selectedPublicTemplate)],
                            ['label' => __('Destination copied'), 'done' => $hasTemplateDestination],
                        ] as $item)
                            <div class="flex items-center gap-3 rounded-[0.85rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-soft);">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-[0.65rem]" style="background-color: {{ $item['done'] ? 'rgba(16,185,129,0.12)' : 'rgba(245,158,11,0.12)' }}; color: {{ $item['done'] ? '#059669' : '#d97706' }};">
                                    <i class="fa-light {{ $item['done'] ? 'fa-check' : 'fa-clock' }}"></i>
                                </span>
                                <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Launch') }}</h2>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generate campaigns now. After generation, export CSV for the print shop and open campaigns to edit destinations or download PNG/SVG.') }}</p>
                    <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-[0.85rem] px-5 py-2.5 text-sm font-semibold shadow-[0_16px_35px_-24px_rgba(15,23,42,0.8)] transition disabled:cursor-not-allowed disabled:opacity-60" style="background-color: var(--theme-accent); color: var(--theme-accent-text-color, #ffffff);">
                        <i class="fa-light fa-wand-magic-sparkles" wire:loading.remove wire:target="generate"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="generate"></i>
                        {{ __('Generate print batch') }}
                    </button>
                </section>

                <section class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent batches') }}</h2>
                    <div class="mt-4 space-y-2">
                        @forelse ($recentBatches as $batch)
                            <div class="rounded-[0.9rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: var(--theme-surface-soft);">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $batch['name'] }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ number_format($batch['count']) }} {{ __('QR codes') }} / {{ $batch['style'] }} / {{ $batch['view'] }}</p>
                                    </div>
                                    @if (Route::has('portal.qr-codes.pre-printed.export'))
                                        <a href="{{ route('portal.qr-codes.pre-printed.export', ['batch' => $batch['id']]) }}" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base); color: var(--theme-accent);" title="{{ __('Export CSV') }}" aria-label="{{ __('Export CSV') }}">
                                            <i class="fa-light fa-file-csv"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generated print batches will appear here.') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
