<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    @php
        $categoryColors = [
            'all' => '#2563eb',
            'campaign' => '#0891b2',
            'business' => '#4f46e5',
            'commerce' => '#16a34a',
            'communication' => '#db2777',
            'payments' => '#d97706',
            'static' => '#64748b',
        ];

        $typeColors = [
            'dynamic_url' => '#2563eb', 'website_builder' => '#0891b2', 'event' => '#7c3aed', 'booking' => '#0d9488', 'app_download' => '#0284c7', 'resume_qr_code' => '#475569', 'file_upload' => '#ea580c',
            'bio_links' => '#4f46e5', 'business_profile' => '#0f766e', 'business_review' => '#ca8a04', 'google_review' => '#2563eb', 'vcard_plus' => '#6366f1', 'vcard' => '#64748b', 'lead_form' => '#be185d',
            'restaurant_menu' => '#f97316', 'product_catalogue' => '#16a34a',
            'email' => '#0ea5e9', 'email_dynamic' => '#0284c7', 'sms' => '#06b6d4', 'sms_dynamic' => '#0891b2', 'call' => '#22c55e', 'whatsapp' => '#16a34a', 'facetime' => '#6366f1', 'telegram' => '#0ea5e9', 'messenger' => '#2563eb', 'viber' => '#7c3aed', 'zoom' => '#2563eb',
            'donation' => '#e11d48', 'paypal' => '#2563eb', 'upi_static' => '#059669', 'upi_dynamic' => '#10b981', 'crypto' => '#f59e0b', 'brazilian_pix' => '#14b8a6',
            'static_url' => '#475569', 'wifi' => '#0f766e', 'location' => '#f97316',
        ];
    @endphp

    <div class="mx-auto max-w-[78rem] space-y-5">
        <section class="overflow-hidden rounded-[1.25rem] border px-5 py-6 sm:px-7" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.12), rgba(20,184,166,0.08) 48%, rgba(249,115,22,0.07)), var(--theme-surface-base);">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('portal.qr-codes.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-arrow-left"></i>
                        {{ __('QR Codes') }}
                    </a>
                    <h1 class="mt-4 text-[1.9rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Create QR Code') }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose a QR purpose, then continue to enter its data and design.') }}</p>
                </div>
                <x-ui.button type="button" wire:click="createSelectedType" :disabled="$selectedType === 'bio_links' && blank($selectedBioPageId)">
                    <i class="fa-light fa-arrow-right"></i>
                    {{ __('Continue') }}
                </x-ui.button>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[15rem_minmax(0,1fr)]">
            <aside class="rounded-[1.1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Categories') }}</p>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" style="background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);">{{ $categoryCounts['all'] ?? 0 }}</span>
                </div>
                <div class="mt-3 space-y-0.5">
                    @foreach ($categories as $key => $item)
                        @php $categoryColor = $categoryColors[$key] ?? '#2563eb'; @endphp
                        <button
                            type="button"
                            wire:click="selectCategory('{{ $key }}')"
                            class="group flex w-full items-center gap-2.5 rounded-[0.8rem] px-2.5 py-2.5 text-left text-sm font-semibold transition"
                            style="{{ $category === $key ? 'background-color: '.$categoryColor.'14; color: '.$categoryColor.'; box-shadow: inset 2px 0 0 '.$categoryColor.';' : 'color: var(--theme-muted-text-color);' }}"
                        >
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full" style="{{ $category === $key ? 'background-color: '.$categoryColor.'18; color: '.$categoryColor.';' : 'background-color: rgba(var(--theme-border-color-rgb),0.10); color: var(--theme-muted-text-color);' }}">
                                <i class="fa-light {{ $item['icon'] }} text-[13px]"></i>
                            </span>
                            <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" style="{{ $category === $key ? 'background-color: '.$categoryColor.'18; color: '.$categoryColor.';' : 'background-color: rgba(var(--theme-border-color-rgb),0.10); color: var(--theme-muted-text-color);' }}">{{ $categoryCounts[$key] ?? 0 }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <x-ui.card class="space-y-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose a QR purpose') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">
                            {{ __('Selected:') }}
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedTypeMeta['label'] }}</span>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative w-full sm:w-72">
                            <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color: var(--theme-muted-text-color);"></i>
                            <x-ui.input wire:model.live.debounce.250ms="search" class="pl-8" :placeholder="__('Search types...')" />
                        </div>
                        <x-ui.button type="button" wire:click="createSelectedType" :disabled="$selectedType === 'bio_links' && blank($selectedBioPageId)">
                            <i class="fa-light fa-arrow-right"></i>
                            {{ __('Continue') }}
                        </x-ui.button>
                    </div>
                </div>

                @if ($selectedType === 'bio_links')
                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(79,70,229,0.24); background: linear-gradient(135deg, rgba(79,70,229,0.08), rgba(20,184,166,0.05));">
                        <div class="grid gap-4 lg:grid-cols-[17rem_minmax(0,1fr)] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.75rem]" style="background-color: rgba(79,70,229,0.12); color: #4f46e5;">
                                        <i class="fa-light fa-link"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Choose Bio Page') }}</p>
                                        <p class="text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('This QR will open the selected Link Bio page.') }}</p>
                                    </div>
                                </div>
                            </div>

                            @if ($hasBioPages)
                                <div class="min-w-0 space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link Bio page') }}</label>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:flex-row">
                                        <div
                                            x-data="{ open: false }"
                                            x-on:click.outside="open = false"
                                            class="relative min-w-0 flex-1"
                                        >
                                            <div
                                                class="flex min-h-11 items-center gap-2 rounded-[0.8rem] border px-3 shadow-sm transition focus-within:ring-4"
                                                style="border-color: rgba(var(--theme-border-color-rgb),0.82); background-color: var(--theme-surface-base); --tw-ring-color: rgba(79,70,229,0.12);"
                                            >
                                                <i class="fa-light fa-magnifying-glass text-xs" style="color: var(--theme-muted-text-color);"></i>
                                                <input
                                                    type="search"
                                                    wire:model.live.debounce.200ms="bioPageSearch"
                                                    x-on:focus="open = true"
                                                    x-on:click="open = true"
                                                    class="min-w-0 flex-1 border-0 bg-transparent py-2.5 text-sm font-medium outline-none ring-0 focus:ring-0"
                                                    style="color: var(--theme-header-text-color);"
                                                    placeholder="{{ __('Search or select a Bio Page...') }}"
                                                >
                                                @if ($selectedBioPageId)
                                                    <span class="hidden rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] sm:inline-flex" style="background-color: rgba(79,70,229,0.1); color: #4f46e5;">
                                                        {{ __('Selected') }}
                                                    </span>
                                                @endif
                                                <button type="button" x-on:click="open = ! open" class="inline-flex h-7 w-7 items-center justify-center rounded-full transition hover:bg-black/[0.04]" style="color: var(--theme-muted-text-color);">
                                                    <i class="fa-light fa-chevron-down text-xs"></i>
                                                </button>
                                            </div>

                                            <div
                                                x-cloak
                                                x-show="open"
                                                x-transition.origin.top
                                                class="absolute left-0 right-0 top-[calc(100%+0.35rem)] z-30 overflow-hidden rounded-[0.9rem] border shadow-xl"
                                                style="border-color: rgba(var(--theme-border-color-rgb),0.78); background-color: var(--theme-surface-base);"
                                            >
                                                <div class="max-h-64 overflow-y-auto p-1.5">
                                                    @forelse ($bioPages as $bioPage)
                                                        @php $isBioPageSelected = (int) $selectedBioPageId === (int) $bioPage->id; @endphp
                                                        <button
                                                            type="button"
                                                            wire:click="selectBioPage({{ $bioPage->id }})"
                                                            x-on:click="open = false"
                                                            class="flex w-full items-center gap-3 rounded-[0.75rem] px-3 py-2.5 text-left transition hover:bg-black/[0.025]"
                                                            style="{{ $isBioPageSelected ? 'background-color: rgba(79,70,229,0.1); color: #4f46e5;' : 'color: var(--theme-header-text-color);' }}"
                                                        >
                                                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem]" style="{{ $isBioPageSelected ? 'background-color: rgba(79,70,229,0.14); color: #4f46e5;' : 'background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);' }}">
                                                                <i class="fa-light {{ $isBioPageSelected ? 'fa-check' : 'fa-link' }}"></i>
                                                            </span>
                                                            <span class="min-w-0 flex-1">
                                                                <span class="block truncate text-sm font-semibold">{{ $bioPage->title }}</span>
                                                                <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">/{{ $bioPage->slug }}</span>
                                                            </span>
                                                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em]" style="{{ $bioPage->is_published ? 'background-color: rgba(16,185,129,0.12); color: #059669;' : 'background-color: rgba(var(--theme-border-color-rgb),0.14); color: var(--theme-muted-text-color);' }}">
                                                                {{ $bioPage->is_published ? __('Published') : __('Draft') }}
                                                            </span>
                                                        </button>
                                                    @empty
                                                        <div class="px-3 py-6 text-center text-sm" style="color: var(--theme-muted-text-color);">
                                                            {{ __('No Bio Pages match your search.') }}
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('portal.link-bio.create') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-[0.8rem] border px-4 text-sm font-semibold shadow-sm transition hover:-translate-y-px" style="border-color: rgba(var(--theme-border-color-rgb),0.82); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                                            <i class="fa-light fa-plus text-xs" style="color: #4f46e5;"></i>
                                            {{ __('New') }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-[0.85rem] border border-dashed p-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.85); color: var(--theme-muted-text-color);">
                                    {{ __('Create a Link Bio page first, then return here to generate its QR.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="grid gap-2 md:grid-cols-2 2xl:grid-cols-3">
                    @forelse ($types as $type)
                        @php
                            $typeColor = $typeColors[$type['key']] ?? '#2563eb';
                            $isSelected = $selectedType === $type['key'];
                        @endphp
                        <button
                            type="button"
                            wire:click="selectType('{{ $type['key'] }}')"
                            wire:dblclick="createSelectedType"
                            class="group relative flex min-h-[7.5rem] flex-col overflow-hidden rounded-[0.95rem] border p-3 text-left transition hover:-translate-y-0.5 hover:shadow-md"
                            style="{{ $isSelected ? 'border-color: '.$typeColor.'; background: linear-gradient(180deg, '.$typeColor.'16, rgba(var(--theme-surface-base-rgb,255,255,255),0.92)); box-shadow: 0 14px 32px '.$typeColor.'20, 0 0 0 1px '.$typeColor.'22;' : 'border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);' }}"
                        >
                            <span class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $typeColor }};"></span>
                            <span class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $typeColor }}24; color: {{ $typeColor }};">
                                    <i class="fa-light {{ $type['icon'] }}"></i>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.08em]" style="{{ $type['kind'] === __('dynamic') ? 'background-color: '.$typeColor.'14; color: '.$typeColor.';' : 'background-color: rgba(var(--theme-border-color-rgb),0.16); color: var(--theme-muted-text-color);' }}">
                                    @if ($isSelected)
                                        <i class="fa-solid fa-check text-[9px]"></i>
                                    @endif
                                    {{ $type['kind'] }}
                                </span>
                            </span>
                            <span class="mt-3 block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $type['label'] }}</span>
                            <span class="mt-1 line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $type['description'] }}</span>
                        </button>
                    @empty
                        <div class="md:col-span-2 2xl:col-span-3 rounded-[1rem] border border-dashed p-8 text-center" style="border-color: rgba(var(--theme-border-color-rgb),0.85); color: var(--theme-muted-text-color);">
                            {{ __('No QR types match your search.') }}
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
