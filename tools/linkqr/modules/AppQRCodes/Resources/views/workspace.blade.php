<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[92rem] space-y-5">
        <section class="overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.72fr)]">
                <div class="p-5 sm:p-7">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-qrcode"></i>
                            {{ __('QR Platform') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                            <i class="fa-light fa-link"></i>
                            {{ __('Dynamic campaigns') }}
                        </span>
                    </div>
                    <h1 class="mt-5 text-[2.25rem] font-semibold leading-tight tracking-[-0.045em]" style="color: var(--theme-header-text-color);">{{ __('QR Campaigns') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create branded QR campaigns, change destinations after printing, track scans, and manage every public QR asset from one operations workspace.') }}</p>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <x-ui.button href="{{ route('portal.qr-codes.create') }}">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Create QR') }}
                        </x-ui.button>
                        @if (Route::has('portal.qr-codes.analytics'))
                            <x-ui.button href="{{ route('portal.qr-codes.analytics') }}" variant="outline">
                                <i class="fa-light fa-chart-line"></i>
                                {{ __('Analytics') }}
                            </x-ui.button>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-4">
                        @foreach ([
                            ['label' => __('Total QR'), 'value' => $stats['total'], 'icon' => 'fa-qrcode', 'color' => '#2563eb'],
                            ['label' => __('Dynamic'), 'value' => $stats['dynamic'], 'icon' => 'fa-link', 'color' => '#0f766e'],
                            ['label' => __('Scans'), 'value' => $stats['scans'], 'icon' => 'fa-chart-line', 'color' => '#7c3aed'],
                            ['label' => __('Active'), 'value' => $stats['active'], 'icon' => 'fa-circle-check', 'color' => '#059669'],
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

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($qrLimits as $limit)
                            @php
                                $hasUsage = $limit['used'] !== null;
                                $isUnlimited = $limit['limit'] === null;
                                $percent = $hasUsage && ! $isUnlimited && (int) $limit['limit'] > 0
                                    ? min(100, (int) round(((int) $limit['used'] / (int) $limit['limit']) * 100))
                                    : 0;
                                $valueLabel = $hasUsage
                                    ? number_format((int) $limit['used']).' / '.($isUnlimited ? __('Unlimited') : number_format((int) $limit['limit']))
                                    : ($isUnlimited ? __('Unlimited') : number_format((int) $limit['limit']));
                            @endphp
                            <div class="rounded-[1rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-[11px] font-semibold" style="color: var(--theme-header-text-color);">{{ $limit['label'] }}</p>
                                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $limit['description'] }}</p>
                                    </div>
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.7rem]" style="background-color: {{ $limit['color'] }}14; color: {{ $limit['color'] }};">
                                        <i class="{{ $limit['icon'] }} text-xs"></i>
                                    </span>
                                </div>
                                <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $valueLabel }}</p>
                                @if ($hasUsage && ! $isUnlimited)
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);">
                                        <div class="h-full rounded-full" style="width: {{ $percent }}%; background-color: {{ $limit['color'] }};"></div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t p-5 xl:border-l xl:border-t-0" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.10), rgba(6,182,212,0.12) 48%, rgba(16,185,129,0.12));">
                    <div class="rounded-[1.25rem] border p-4 shadow-[0_28px_70px_-46px_rgba(15,23,42,0.5)]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Campaign lifecycle') }}</p>
                                <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Build -> Publish -> Optimize') }}</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                <i class="fa-light fa-route"></i>
                            </span>
                        </div>

                        <div class="mt-5 grid gap-3">
                            @foreach ([
                                ['label' => __('Design QR'), 'value' => __('Logo, color, sticker, pattern'), 'icon' => 'fa-palette', 'color' => '#2563eb'],
                                ['label' => __('Dynamic link'), 'value' => __('Edit destination after printing'), 'icon' => 'fa-link', 'color' => '#0f766e'],
                                ['label' => __('Scan intelligence'), 'value' => __('Views, clicks, CTA and sources'), 'icon' => 'fa-chart-line', 'color' => '#7c3aed'],
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

                        <div class="mt-4 rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan limits') }}</p>
                            <div class="mt-3 grid gap-2">
                                @foreach ($qrLimits as $limit)
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <span class="truncate" style="color: var(--theme-muted-text-color);">{{ $limit['label'] }}</span>
                                        <span class="font-semibold" style="color: var(--theme-header-text-color);">
                                            @if ($limit['used'] !== null)
                                                {{ number_format((int) $limit['used']) }} /
                                            @endif
                                            {{ $limit['limit'] === null ? __('Unlimited') : number_format((int) $limit['limit']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div
            wire:key="qr-campaign-selection-{{ md5(json_encode($visibleQrCodeIds).$search.$statusFilter.$typeFilter) }}"
            x-data="{
                selected: [],
                visible: @js($visibleQrCodeIds),
                get selectedCount() {
                    return this.selected.length;
                },
                get allVisibleSelected() {
                    return this.visible.length > 0 && this.visible.every((id) => this.selected.includes(id));
                },
                toggleAllVisible() {
                    if (this.allVisibleSelected) {
                        this.selected = this.selected.filter((id) => !this.visible.includes(id));
                        return;
                    }

                    this.selected = Array.from(new Set([...this.selected, ...this.visible]));
                },
                toggleOne(id, checked) {
                    id = Number(id);

                    if (checked) {
                        this.selected = Array.from(new Set([...this.selected, id]));
                        return;
                    }

                    this.selected = this.selected.filter((selectedId) => selectedId !== id);
                },
                isSelected(id) {
                    return this.selected.includes(Number(id));
                },
            }"
            class="contents"
        >
            <section class="space-y-4 rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Campaign list') }}</h2>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.1em]" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">{{ __('Operations') }}</span>
                    </div>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Manage records, public links, performance signals, and editing actions for every QR type.') }}</p>
                </div>
                <div class="grid gap-2 md:grid-cols-3 2xl:w-[44rem]">
                    <x-ui.input wire:model.live.debounce.250ms="search" :placeholder="__('Search name, type, URL...')" />
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="all">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="draft">{{ __('Draft') }}</option>
                    </x-ui.select>
                    <x-ui.select wire:model.live="typeFilter">
                        <option value="all">{{ __('All types') }}</option>
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>

            <div x-cloak x-show="selectedCount > 0" x-transition.opacity.duration.100ms class="flex flex-col gap-3 rounded-[1rem] border px-4 py-3 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-accent-rgb),0.24); background-color: rgba(var(--theme-accent-rgb),0.06);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                        <span x-text="selectedCount"></span>
                        <span>{{ __('campaigns selected') }}</span>
                    </p>
                    <x-ui.dialog :title="__('Delete selected QR campaigns?')" :description="__('This permanently removes all selected QR campaigns and their scan events. This action cannot be undone.')" width="sm" dismissible>
                        <x-slot:trigger>
                            <x-ui.button type="button" variant="danger" size="sm">
                                <i class="fa-light fa-trash-can"></i>
                                {{ __('Delete selected') }}
                            </x-ui.button>
                        </x-slot:trigger>
                        <x-slot:footer>
                            <div class="flex justify-end gap-3">
                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                <x-ui.button type="button" variant="danger" x-on:click="$wire.deleteSelectedQrCodes(selected); selected = []; open = false">
                                    <i class="fa-light fa-trash-can"></i>
                                    {{ __('Delete selected') }}
                                </x-ui.button>
                            </div>
                        </x-slot:footer>
                    </x-ui.dialog>
                </div>

            @if ($qrCodes->isEmpty())
                <div class="rounded-[1.15rem] border p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.06), rgba(6,182,212,0.08));">
                    <div class="grid gap-4 lg:grid-cols-[minmax(0,0.58fr)_minmax(0,0.42fr)] lg:items-center">
                        <div>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-[1rem] shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.88); color: var(--theme-accent);">
                                <i class="fa-light fa-qrcode text-lg"></i>
                            </span>
                            <h3 class="mt-4 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('No QR campaigns found') }}</h3>
                            <p class="mt-2 max-w-lg text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create a campaign or adjust filters to show existing QR assets.') }}</p>
                            <x-ui.button href="{{ route('portal.qr-codes.create') }}" class="mt-4">
                                <i class="fa-light fa-plus"></i>
                                {{ __('Create QR') }}
                            </x-ui.button>
                        </div>
                        <div class="space-y-2 rounded-[1rem] p-3 shadow-sm" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                            @foreach ([__('Choose QR type'), __('Add destination'), __('Design and publish')] as $index => $label)
                                <div class="flex items-center gap-3 rounded-[0.8rem] px-3 py-2" style="background-color: rgba(var(--theme-border-color-rgb),0.06);">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">{{ $index + 1 }}</span>
                                    <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="overflow-visible rounded-[1.1rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                    <div class="hidden grid-cols-[2.25rem_minmax(18rem,1.5fr)_11rem_8rem_7rem_14.75rem] gap-3 px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.12em] lg:grid" style="background: linear-gradient(180deg, var(--theme-surface-soft), rgba(var(--theme-border-color-rgb),0.04)); color: var(--theme-muted-text-color);">
                        <span>
                            <button
                                type="button"
                                x-on:click="toggleAllVisible()"
                                x-bind:aria-pressed="allVisibleSelected.toString()"
                                aria-label="{{ __('Select all visible campaigns') }}"
                                class="inline-flex h-5 w-5 items-center justify-center rounded-[0.4rem] border transition-all duration-150"
                                x-bind:class="allVisibleSelected ? 'border-[color:var(--theme-accent)] bg-[color:var(--theme-accent)] text-white shadow-[0_0_0_3px_rgba(var(--theme-accent-rgb),0.14)]' : 'border-[color:rgba(var(--theme-border-color-rgb),0.72)] bg-[color:var(--theme-surface-base)] text-transparent hover:border-[color:rgba(var(--theme-accent-rgb),0.45)]'"
                            >
                                <i class="fa-solid fa-check text-[10px]" x-show="allVisibleSelected"></i>
                            </button>
                        </span>
                        <span>{{ __('Campaign') }}</span>
                        <span>{{ __('Type') }}</span>
                        <span>{{ __('Status') }}</span>
                        <span>{{ __('Scans') }}</span>
                        <span class="text-right">{{ __('Actions') }}</span>
                    </div>

                    <div class="divide-y" style="--tw-divide-color: rgba(var(--theme-border-color-rgb),0.64);">
                        @foreach ($qrCodes as $qrCode)
                            @php
                                $typeColor = match ($qrCode->type) {
                                    'restaurant_menu' => '#f59e0b',
                                    'product_catalogue' => '#10b981',
                                    'file_upload' => '#7c3aed',
                                    'business_review' => '#0ea5e9',
                                    'dynamic_url' => '#2563eb',
                                    default => '#64748b',
                                };
                                $qrDownloadSvg = $this->downloadableQrSvg($qrCode);
                                $aiQrArtworkDataUri = $this->aiQrArtworkDataUri($qrCode);
                            @endphp
                            <div class="grid gap-3 px-4 py-4 transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.035)] lg:grid-cols-[2.25rem_minmax(18rem,1.5fr)_11rem_8rem_7rem_14.75rem] lg:items-center">
                                <div class="flex items-center">
                                    <button
                                        type="button"
                                        x-on:click="toggleOne({{ $qrCode->id }}, !isSelected({{ $qrCode->id }}))"
                                        x-bind:aria-pressed="isSelected({{ $qrCode->id }}).toString()"
                                        aria-label="{{ __('Select campaign') }}"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded-[0.4rem] border transition-all duration-150"
                                        x-bind:class="isSelected({{ $qrCode->id }}) ? 'border-[color:var(--theme-accent)] bg-[color:var(--theme-accent)] text-white shadow-[0_0_0_3px_rgba(var(--theme-accent-rgb),0.14)]' : 'border-[color:rgba(var(--theme-border-color-rgb),0.72)] bg-[color:var(--theme-surface-base)] text-transparent hover:border-[color:rgba(var(--theme-accent-rgb),0.45)]'"
                                    >
                                        <i class="fa-solid fa-check text-[10px]" x-show="isSelected({{ $qrCode->id }})"></i>
                                    </button>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-[0.9rem] border bg-white p-1.5 shadow-sm [&_svg]:h-full [&_svg]:w-full" style="border-color: {{ $typeColor }}24;">
                                            @if ($aiQrArtworkDataUri)
                                                <img src="{{ $aiQrArtworkDataUri }}" alt="" class="h-full w-full rounded-[0.55rem] object-cover" onerror="this.classList.add('hidden'); const fallback = this.nextElementSibling; if (fallback) { fallback.classList.remove('hidden'); fallback.style.display = 'grid'; }">
                                                <span class="hidden h-full w-full place-items-center rounded-[0.55rem]" style="color: {{ $typeColor }}; background-color: {{ $typeColor }}10;">
                                                    <i class="fa-light fa-qrcode"></i>
                                                </span>
                                            @else
                                                {!! $qrDownloadSvg !!}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $qrCode->name }}</p>
                                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $qrCode->shortUrl() ?: $qrCode->destination_url }}</p>
                                            @php
                                                $analytics = (array) data_get($qrCode->settings, 'analytics', []);
                                                $views = (int) $qrCode->scans_count;
                                                $clicks = (int) ($analytics['clicks'] ?? $analytics['link_clicks'] ?? 0);
                                                $ctaClicks = (int) ($analytics['cta_clicks'] ?? $analytics['cta'] ?? 0);
                                            @endphp
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                                    <i class="fa-light fa-eye text-[10px]"></i>
                                                    {{ __('Views') }} {{ number_format($views) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background-color: rgba(14,165,233,0.08); color: #0284c7;">
                                                    <i class="fa-light fa-arrow-pointer text-[10px]"></i>
                                                    {{ __('Clicks') }} {{ number_format($clicks) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background-color: rgba(16,185,129,0.09); color: #059669;">
                                                    <i class="fa-light fa-bullseye-arrow text-[10px]"></i>
                                                    {{ __('CTA') }} {{ number_format($ctaClicks) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.08em]" style="background-color: {{ $typeColor }}12; color: {{ $typeColor }};">{{ str_replace('_', ' ', $qrCode->type) }}</span>
                                </div>
                                <div>
                                    <x-ui.badge :variant="$qrCode->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($qrCode->status) }}</x-ui.badge>
                                </div>
                                <div class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $qrCode->scans_count }}</div>
                                <div class="flex flex-wrap justify-start gap-2 lg:flex-nowrap lg:justify-end">
                                    @php
                                        $downloadName = \Illuminate\Support\Str::slug($qrCode->name ?: 'qr-campaign-'.$qrCode->id) ?: 'qr-campaign-'.$qrCode->id;
                                    @endphp
                                    @if (Route::has('portal.qr-codes.campaign.analytics'))
                                        <x-ui.button href="{{ route('portal.qr-codes.campaign.analytics', ['qrCode' => $qrCode->id]) }}" variant="outline" size="sm" title="{{ __('Analytics') }}" aria-label="{{ __('Analytics') }}" class="h-9 w-9 px-0">
                                            <i class="fa-light fa-chart-line"></i>
                                            <span class="sr-only">{{ __('Analytics') }}</span>
                                        </x-ui.button>
                                    @endif
                                    @if (($canUseQrTypeBuilders ?? true) && $qrCode->type !== 'bio_links' && Route::has('portal.qr-codes.type-content'))
                                        <x-ui.button href="{{ route('portal.qr-codes.type-content', ['qrCode' => $qrCode->id]) }}" variant="outline" size="sm" title="{{ __('Edit content') }}" aria-label="{{ __('Edit content') }}" class="h-9 w-9 px-0">
                                            <i class="fa-light fa-list-check"></i>
                                            <span class="sr-only">{{ __('Edit content') }}</span>
                                        </x-ui.button>
                                    @endif
                                    <x-ui.button href="{{ route('portal.qr-codes.edit', ['qrCode' => $qrCode->id]) }}" variant="outline" size="sm" title="{{ __('Edit design') }}" aria-label="{{ __('Edit design') }}" class="h-9 w-9 px-0">
                                        <i class="fa-light fa-pen-to-square"></i>
                                        <span class="sr-only">{{ __('Edit design') }}</span>
                                    </x-ui.button>
                                    @if ($qrCode->shortUrl())
                                        <x-ui.button href="{{ $qrCode->shortUrl() }}" target="_blank" variant="outline" size="sm" title="{{ __('Open') }}" aria-label="{{ __('Open') }}" class="h-9 w-9 px-0">
                                            <i class="fa-light fa-arrow-up-right-from-square"></i>
                                            <span class="sr-only">{{ __('Open') }}</span>
                                        </x-ui.button>
                                    @endif
                                    <div class="relative" x-data="{ open: false }">
                                        <x-ui.button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            title="{{ __('Download') }}"
                                            aria-label="{{ __('Download') }}"
                                            class="h-9 w-9 px-0"
                                            x-on:click.stop="open = ! open"
                                        >
                                            <i class="fa-light fa-download"></i>
                                            <span class="sr-only">{{ __('Download') }}</span>
                                        </x-ui.button>
                                        <div
                                            x-cloak
                                            x-show="open"
                                            x-transition.opacity.duration.100ms
                                            x-on:click.outside="open = false"
                                            class="absolute bottom-full right-0 z-50 mb-2 w-44 overflow-hidden rounded-[0.85rem] border p-1 shadow-[0_20px_55px_-35px_rgba(15,23,42,0.55)]"
                                            style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base);"
                                        >
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-2 rounded-[0.65rem] px-3 py-2 text-left text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                                style="color: var(--theme-header-text-color);"
                                                x-on:click="downloadQrCampaign({{ $qrCode->id }}, 'png', @js($downloadName)); open = false"
                                            >
                                                <i class="fa-light fa-file-image w-4 text-center" style="color: var(--theme-accent);"></i>
                                                {{ __('Download PNG') }}
                                            </button>
                                            <button
                                                type="button"
                                                class="flex w-full items-center gap-2 rounded-[0.65rem] px-3 py-2 text-left text-sm font-semibold transition hover:bg-[color:rgba(var(--theme-accent-rgb),0.08)]"
                                                style="color: var(--theme-header-text-color);"
                                                x-on:click="downloadQrCampaign({{ $qrCode->id }}, 'svg', @js($downloadName)); open = false"
                                            >
                                                <i class="fa-light fa-code w-4 text-center" style="color: var(--theme-accent);"></i>
                                                {{ __('Download SVG') }}
                                            </button>
                                        </div>
                                    </div>
                                    <x-ui.dialog wire:key="qr-campaign-delete-dialog-{{ $qrCode->id }}" :title="__('Delete this QR campaign?')" :description="__('This permanently removes the QR campaign and its scan events. This action cannot be undone.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <x-ui.button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                title="{{ __('Delete') }}"
                                                aria-label="{{ __('Delete') }}"
                                                class="h-9 w-9 px-0 hover:!border-red-200 hover:!text-red-600"
                                            >
                                                <i class="fa-light fa-trash-can"></i>
                                                <span class="sr-only">{{ __('Delete') }}</span>
                                            </x-ui.button>
                                        </x-slot:trigger>
                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" wire:click="deleteQrCode({{ $qrCode->id }})" x-on:click="open = false">
                                                    <i class="fa-light fa-trash-can"></i>
                                                    {{ __('Delete') }}
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                    <template id="qr-campaign-svg-{{ $qrCode->id }}">{!! $qrDownloadSvg !!}</template>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($qrCodes->hasPages())
                        <div class="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.64); background-color: var(--theme-surface-soft);">
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">
                                {{ __('Showing') }}
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($qrCodes->firstItem()) }}</span>
                                {{ __('to') }}
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($qrCodes->lastItem()) }}</span>
                                {{ __('of') }}
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format($qrCodes->total()) }}</span>
                            </p>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    wire:click="previousPage"
                                    wire:loading.attr="disabled"
                                    :disabled="$qrCodes->onFirstPage()"
                                    class="{{ $qrCodes->onFirstPage() ? 'opacity-45' : '' }}"
                                >
                                    <i class="fa-light fa-arrow-left"></i>
                                    {{ __('Previous') }}
                                </x-ui.button>

                                <span class="inline-flex h-9 items-center rounded-[0.75rem] border px-3 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);">
                                    {{ __('Page') }} {{ number_format($qrCodes->currentPage()) }} / {{ number_format($qrCodes->lastPage()) }}
                                </span>

                                <x-ui.button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    wire:click="nextPage"
                                    wire:loading.attr="disabled"
                                    :disabled="! $qrCodes->hasMorePages()"
                                    class="{{ ! $qrCodes->hasMorePages() ? 'opacity-45' : '' }}"
                                >
                                    {{ __('Next') }}
                                    <i class="fa-light fa-arrow-right"></i>
                                </x-ui.button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </section>
        </div>
    </div>
</div>

@once
    <script>
        window.downloadQrCampaign = async function (id, format, filename) {
            const template = document.getElementById(`qr-campaign-svg-${id}`);

            if (! template) {
                window.dispatchEvent(new CustomEvent('app-toast', {
                    detail: { type: 'error', message: 'QR source is not available.' },
                }));

                return;
            }

            const source = template.innerHTML.trim();
            const safeName = (filename || `qr-campaign-${id}`).replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || `qr-campaign-${id}`;

            if (format === 'svg') {
                downloadQrCampaignBlob(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }), `${safeName}.svg`);

                return;
            }

            const svgBlob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            const image = new Image();

            image.onload = function () {
                const size = 1536;
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.width = size;
                canvas.height = size;

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, size, size);
                context.drawImage(image, 0, 0, size, size);

                canvas.toBlob((blob) => {
                    URL.revokeObjectURL(url);

                    if (blob) {
                        downloadQrCampaignBlob(blob, `${safeName}.png`);
                    }
                }, 'image/png');
            };

            image.onerror = function () {
                URL.revokeObjectURL(url);
                window.dispatchEvent(new CustomEvent('app-toast', {
                    detail: { type: 'error', message: 'Could not export this QR image.' },
                }));
            };

            image.src = url;
        };

        window.downloadQrCampaignBlob = function (blob, name) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = name;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        };
    </script>
@endonce
