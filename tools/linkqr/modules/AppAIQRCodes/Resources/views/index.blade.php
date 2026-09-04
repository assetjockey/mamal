<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @if ($isCreatePage)
    <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: var(--theme-surface-base);">
        <div class="grid lg:grid-cols-[minmax(0,1fr)_28rem]">
            <div class="px-6 py-6 sm:px-7">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                    <i class="fa-light fa-qrcode text-[11px] leading-none"></i>
                        {{ __('AI QR Codes') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold" style="background-color: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="fa-light fa-wand-magic-sparkles"></i>
                        {{ __('Prompt to QR image') }}
                    </span>
                </div>

                <h1 class="mt-5 text-[1.75rem] font-semibold tracking-[-0.055em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">{{ __('Create AI QR code') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                    {{ __('Generate an image from a prompt and place a scan-safe QR code on the artwork, then save it as a tracked QR campaign.') }}
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Credits') }}</p>
                                <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $creditCost }}</p>
                            </div>
                            <span class="grid h-8 w-8 place-items-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-coins"></i>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Remaining') }}</p>
                                <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ ($creditSummary['unlimited'] ?? false) ? '∞' : number_format((int) ($creditSummary['remaining'] ?? 0)) }}</p>
                            </div>
                            <span class="grid h-8 w-8 place-items-center rounded-[0.8rem]" style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
                                <i class="fa-light fa-battery-full"></i>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Size') }}</p>
                                <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('1024') }}</p>
                            </div>
                            <span class="grid h-8 w-8 place-items-center rounded-[0.8rem]" style="background-color: rgba(124, 58, 237, 0.1); color: #7c3aed;">
                                <i class="fa-light fa-expand"></i>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Exports') }}</p>
                                <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('3') }}</p>
                            </div>
                            <span class="grid h-8 w-8 place-items-center rounded-[0.8rem]" style="background-color: rgba(249, 115, 22, 0.1); color: #ea580c;">
                                <i class="fa-light fa-download"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t p-6 sm:p-7 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.1), transparent 38%),
                color-mix(in srgb, var(--theme-surface-soft) 92%, transparent);">
                <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">1</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Write a prompt') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Describe the scene or subject you want around the QR.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">2</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generate and test') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Create the artwork, then scan it before using it publicly.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">3</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Save tracked QR') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Keep tracking, editing, and exports in the QR workspace.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    @unless ($isCreatePage)
    <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: var(--theme-surface-base);">
        <div class="grid lg:grid-cols-[minmax(0,1fr)_28rem]">
            <div class="px-6 py-6 sm:px-7">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                        <i class="fa-light fa-qrcode"></i>
                        {{ __('AI QR Codes') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold" style="background-color: rgba(16, 185, 129, 0.12); color: #10b981;">
                        <i class="fa-light fa-wand-magic-sparkles"></i>
                        {{ __('Replicate QR ControlNet') }}
                    </span>
                </div>

                <h1 class="mt-5 text-[1.75rem] font-semibold tracking-[-0.055em] sm:text-[2rem]" style="color: var(--theme-header-text-color);">{{ __('AI QR codes') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                    {{ __('Create scannable AI QR artwork, keep each code tracked, and manage generated QR images from one dedicated workspace.') }}
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Saved') }}</p>
                        <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($savedAiQrCodesTotal) }}</p>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Credits') }}</p>
                        <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $creditCost }}</p>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Remaining') }}</p>
                        <p class="mt-3 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ ($creditSummary['unlimited'] ?? false) ? '∞' : number_format((int) ($creditSummary['remaining'] ?? 0)) }}</p>
                    </div>

                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background-color: color-mix(in srgb, var(--theme-surface-soft) 94%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Model') }}</p>
                        <p class="mt-3 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('qr_code_controlnet') }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t p-6 sm:p-7 lg:border-l lg:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background:
                radial-gradient(circle at top right, rgba(var(--theme-accent-rgb), 0.1), transparent 38%),
                color-mix(in srgb, var(--theme-surface-soft) 92%, transparent);">
                <div class="rounded-[1.2rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.56); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">1</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create artwork') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Generate a QR-controlled image from a prompt and destination URL.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">2</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Save separately') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Saved AI QR codes stay in this dedicated list.') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-semibold" style="background-color: rgba(var(--theme-accent-rgb), 0.14); color: var(--theme-accent);">3</span>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Track and edit') }}</p>
                                <p class="mt-1 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Open the edit screen only when you need to change destination or tracking settings.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.25rem] border p-5 sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                        <span class="inline-grid h-5 w-5 shrink-0 place-items-center rounded-[0.45rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-robot text-[11px] leading-none"></i>
                        </span>
                    <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Saved AI QR codes') }}</h2>
                </div>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Generated AI QR artwork is stored here while each code remains trackable and editable.') }}</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <label class="relative block min-w-0 sm:w-72">
                    <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center" style="color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-magnifying-glass text-xs leading-none"></i>
                    </span>
                    <input
                        type="search"
                        wire:model.live.debounce.350ms="search"
                        placeholder="{{ __('Search AI QR codes...') }}"
                        class="h-11 w-full rounded-[0.85rem] border bg-transparent pl-9 pr-3 text-sm outline-none transition focus:ring-2"
                        style="border-color: rgba(var(--theme-border-color-rgb), 0.64); color: var(--theme-header-text-color); --tw-ring-color: rgba(var(--theme-accent-rgb), 0.22);"
                    >
                </label>
                <x-ui.button href="{{ route('portal.qr-codes.ai.create') }}" type="button">
                    <i class="fa-light fa-circle-plus"></i>
                    {{ __('Create QR') }}
                </x-ui.button>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.6);">
            <div class="hidden border-b px-4 py-3 text-xs font-semibold uppercase tracking-[0.16em] sm:grid sm:grid-cols-[minmax(0,1fr)_8rem_8rem] sm:items-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-border-color-rgb), 0.035);">
                <span>{{ __('Name') }}</span>
                <span>{{ __('Created') }}</span>
                <span class="text-right">{{ __('Actions') }}</span>
            </div>

            @forelse ($savedAiQrCodes as $item)
                <article class="grid gap-3 border-b px-4 py-4 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_8rem_8rem] sm:items-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.46);">
                    <div class="flex min-w-0 items-center gap-4">
                        <a href="{{ $item['edit_url'] }}" class="block h-14 w-14 shrink-0 overflow-hidden rounded-[0.75rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); background-color: var(--theme-surface-soft);">
                            @if ($item['image_url'])
                                <img src="{{ $item['image_url'] }}" alt="" class="h-full w-full object-cover" onerror="this.classList.add('hidden'); const fallback = this.nextElementSibling; if (fallback) { fallback.classList.remove('hidden'); fallback.style.display = 'grid'; }">
                                <span class="hidden h-full w-full place-items-center" style="color: var(--theme-accent);">
                                    <i class="fa-light fa-qrcode"></i>
                                </span>
                            @else
                                <span class="grid h-full w-full place-items-center" style="color: var(--theme-accent);">
                                    <i class="fa-light fa-qrcode"></i>
                                </span>
                            @endif
                        </a>
                        <div class="min-w-0">
                            <a href="{{ $item['edit_url'] }}" class="block truncate text-sm font-semibold transition hover:opacity-75" style="color: var(--theme-accent);">{{ $item['name'] }}</a>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $item['short_url'] ?: $item['destination_url'] }}</p>
                        </div>
                    </div>

                    <div class="text-xs sm:text-sm" style="color: var(--theme-muted-text-color);">{{ $item['created_label'] }}</div>

                    <div class="flex items-center gap-2 sm:justify-end">
                        @if ($item['open_url'])
                            <a href="{{ $item['open_url'] }}" target="_blank" class="inline-grid h-9 w-9 shrink-0 place-items-center rounded-[0.75rem] border leading-none transition hover:opacity-75" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-header-text-color);" title="{{ __('Open') }}" aria-label="{{ __('Open') }}">
                                <i class="fa-light fa-arrow-up-right-from-square block text-[13px] leading-none"></i>
                            </a>
                        @endif
                        @if ($item['download_url'])
                            <a href="{{ $item['download_url'] }}" class="inline-grid h-9 w-9 shrink-0 place-items-center rounded-[0.75rem] border leading-none transition hover:opacity-75" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-accent);" title="{{ __('Download') }}" aria-label="{{ __('Download') }}">
                                <i class="fa-light fa-download block text-[13px] leading-none"></i>
                            </a>
                        @endif
                        <a href="{{ $item['edit_url'] }}" class="inline-grid h-9 w-9 shrink-0 place-items-center rounded-[0.75rem] border leading-none transition hover:opacity-75" style="border-color: rgba(var(--theme-border-color-rgb), 0.62); color: var(--theme-header-text-color);" title="{{ __('Edit') }}" aria-label="{{ __('Edit') }}">
                            <i class="fa-light fa-pen block text-[13px] leading-none"></i>
                        </a>
                        <button type="button" wire:click="deleteSaved({{ $item['id'] }})" wire:confirm="{{ __('Delete this AI QR code?') }}" class="inline-grid h-9 w-9 shrink-0 place-items-center rounded-[0.75rem] border leading-none transition hover:opacity-75" style="border-color: rgba(239, 68, 68, 0.34); color: #ef4444;" title="{{ __('Delete') }}" aria-label="{{ __('Delete') }}">
                            <i class="fa-light fa-trash block text-[13px] leading-none"></i>
                        </button>
                    </div>
                </article>
            @empty
                <div class="px-4 py-8 text-center">
                    <i class="fa-light fa-qrcode text-2xl" style="color: var(--theme-accent);"></i>
                    <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No AI QR codes yet') }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Generate and save your first AI QR code below.') }}</p>
                </div>
            @endforelse
        </div>

        @if ($savedAiQrCodes->hasPages())
            <div class="mt-4">
                {{ $savedAiQrCodes->links() }}
            </div>
        @endif
    </section>
    @endunless

    @if ($isCreatePage)
    <section id="create-ai-qr" class="grid gap-5 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <aside class="rounded-[1.25rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);">
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                    <i class="fa-light fa-sliders text-base"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR setup') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Set the campaign target and visual direction.') }}</p>
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <x-ui.input wire:model.defer="name" :label="__('Name')" />

                <div>
                    <x-ui.textarea wire:model.live.debounce.500ms="prompt" :label="__('Visual prompt')" rows="5" :placeholder="__('Dogs in a warm outdoor scene, soft natural light, premium campaign look')" />
                    <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Describe only the background scene. The QR code is added automatically.') }}</p>
                </div>

                <x-ui.input wire:model.live.debounce.500ms="textUrl" :label="__('URL')" :placeholder="__('https://example.com/')" />

                <details class="group rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent);">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-light fa-wand-magic-sparkles"></i>
                            {{ __('QR pattern') }}
                        </span>
                        <i class="fa-light fa-chevron-down text-xs transition group-open:rotate-180"></i>
                    </summary>

                    <div class="space-y-3 border-t p-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.46);">
                        @foreach ($qrStyleOptions as $option)
                            <label
                                class="block cursor-pointer rounded-[0.9rem] border px-3 py-3 transition"
                                style="border-color: {{ $qrStyle === $option['key'] ? 'rgba(var(--theme-accent-rgb), 0.52)' : 'rgba(var(--theme-border-color-rgb), 0.38)' }}; background-color: {{ $qrStyle === $option['key'] ? 'rgba(var(--theme-accent-rgb), 0.07)' : 'transparent' }};"
                            >
                                <input type="radio" wire:model.live="qrStyle" value="{{ $option['key'] }}" class="sr-only">
                                <span class="flex items-start gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-[0.85rem]" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                        <i class="fa-light {{ $option['icon'] }}"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $option['label'] }}</span>
                                        <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $option['description'] }}</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </details>

                @if ($errors->any())
                    <div class="rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(239, 68, 68, 0.28); background-color: rgba(239, 68, 68, 0.08); color: var(--theme-danger-color);">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid gap-3">
                    <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">
                        <i class="fa-light fa-wand-magic-sparkles" wire:loading.remove wire:target="generate"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="generate"></i>
                        <span wire:loading.remove wire:target="generate">{{ __('Generate AI QR code') }}</span>
                        <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
                    </x-ui.button>

                    <p class="-mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Save this AI QR code with tracking and editable destination.') }}</p>

                    <x-ui.button type="button" variant="outline" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <i class="fa-light fa-floppy-disk"></i>
                        {{ __('Save to AI QR codes') }}
                    </x-ui.button>
                </div>
            </div>
        </aside>

        <section
            class="rounded-[1.25rem] border p-5 sm:p-6"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: color-mix(in srgb, var(--theme-surface-overlay) 97%, transparent);"
            x-data="{
                svg: @entangle('previewSvg'),
                currentSvg() {
                    const rendered = this.$refs.previewSvgHost?.querySelector('svg');

                    return rendered ? rendered.outerHTML : this.svg;
                },
                async downloadSvg() {
                    const svg = this.currentSvg();
                    if (!svg) return;
                    const blob = new Blob([await this.inlineSvgImages(svg)], { type: 'image/svg+xml;charset=utf-8' });
                    this.download(blob, 'ai-qr-code.svg');
                },
                downloadPng() {
                    const svg = this.currentSvg();
                    if (!svg) return;
                    this.svgToPngBlob(svg).then(blob => blob && this.download(blob, 'ai-qr-code.png'));
                },
                print() {
                    const svg = this.currentSvg();
                    if (!svg) return;
                    this.svgToPngBlob(svg).then(blob => {
                        if (!blob) return;
                        const imageUrl = URL.createObjectURL(blob);
                        const win = window.open('', '_blank');

                        if (!win) {
                            URL.revokeObjectURL(imageUrl);
                            return;
                        }

                        win.document.write(`
                            <html>
                                <head>
                                    <title>AI QR Code</title>
                                    <style>
                                        @page { size: auto; margin: 12mm; }
                                        html, body { margin: 0; min-height: 100%; background: #fff; }
                                        body { display: grid; place-items: center; }
                                        img { display: block; width: min(92vw, 180mm); height: auto; }
                                    </style>
                                </head>
                                <body>
                                    <img src='${imageUrl}' alt='AI QR Code'>
                                </body>
                            </html>
                        `);
                        win.document.close();

                        const printable = win.document.querySelector('img');
                        printable.onload = () => {
                            win.focus();
                            setTimeout(() => {
                                win.print();
                                URL.revokeObjectURL(imageUrl);
                            }, 150);
                        };
                    });
                },
                async svgToPngBlob(svg) {
                    const inlinedSvg = await this.inlineSvgImages(svg);

                    return new Promise(resolve => {
                        const img = new Image();
                        const url = URL.createObjectURL(new Blob([inlinedSvg], { type: 'image/svg+xml;charset=utf-8' }));

                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            canvas.width = 1024;
                            canvas.height = 1024;
                            const ctx = canvas.getContext('2d');
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, 1024, 1024);
                            ctx.drawImage(img, 0, 0, 1024, 1024);
                            URL.revokeObjectURL(url);
                            canvas.toBlob(blob => resolve(blob), 'image/png');
                        };

                        img.onerror = () => {
                            URL.revokeObjectURL(url);
                            resolve(null);
                        };

                        img.src = url;
                    });
                },
                async inlineSvgImages(svg) {
                    const doc = new DOMParser().parseFromString(svg, 'image/svg+xml');
                    const images = Array.from(doc.querySelectorAll('image'));

                    await Promise.all(images.map(async image => {
                        const href = image.getAttribute('href') || image.getAttribute('xlink:href');

                        if (!href || href.startsWith('data:')) return;

                        const response = await fetch(href, {
                            cache: 'no-store',
                            credentials: 'same-origin',
                        });

                        if (!response.ok) return;

                        const blob = await response.blob();
                        const dataUrl = await new Promise(resolve => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result);
                            reader.readAsDataURL(blob);
                        });

                        image.setAttribute('href', dataUrl);
                    }));

                    return new XMLSerializer().serializeToString(doc.documentElement);
                },
                download(blob, name) {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = name;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
                }
            }"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview') }}</p>
                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Scan-test the final code before production use.') }}</p>
                </div>
                @if ($previewSvg && ! $previewStale)
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.22); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        <i class="fa-light fa-circle-check"></i>
                        {{ __('Ready') }}
                    </span>
                @endif
            </div>

            <div class="mt-5 rounded-[1.15rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background:
                linear-gradient(180deg, rgba(var(--theme-accent-rgb), 0.045), transparent 22%),
                color-mix(in srgb, var(--theme-surface-base) 96%, transparent);">
                @if ($previewSvg && ! $previewStale)
                    <div x-ref="previewSvgHost" class="mx-auto aspect-square w-full max-w-[34rem] overflow-hidden rounded-[1rem] shadow-[0_30px_80px_-52px_rgba(15,23,42,0.65)] [&>svg]:h-full [&>svg]:w-full">
                        {!! $previewSvg !!}
                    </div>
                @elseif ($previewStale)
                    <div class="mx-auto flex aspect-square w-full max-w-[34rem] flex-col items-center justify-center rounded-[1rem] border border-dashed px-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.7); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-rotate mb-3 text-2xl"></i>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt changed') }}</p>
                        <p class="mt-2 text-xs leading-5">{{ __('Generate again to create a new AI background for this prompt.') }}</p>
                    </div>
                @else
                    <div class="mx-auto flex aspect-square w-full max-w-[34rem] flex-col items-center justify-center rounded-[1rem] border border-dashed px-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.7); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-qrcode mb-3 text-2xl"></i>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Generate a preview') }}</p>
                        <p class="mt-2 text-xs leading-5">{{ __('Your generated QR artwork will appear here.') }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <x-ui.button type="button" variant="outline" x-on:click="print()">
                    <i class="fa-light fa-print"></i>
                    {{ __('Print') }}
                </x-ui.button>
                <x-ui.button type="button" variant="outline" x-on:click="downloadSvg()">
                    <i class="fa-light fa-code"></i>
                    {{ __('SVG') }}
                </x-ui.button>
                <x-ui.button type="button" x-on:click="downloadPng()">
                    <i class="fa-light fa-download"></i>
                    {{ __('PNG') }}
                </x-ui.button>
            </div>
        </section>
    </section>
    @endif
</div>
