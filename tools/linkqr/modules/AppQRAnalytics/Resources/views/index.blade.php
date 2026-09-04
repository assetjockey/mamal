@php
    $statCards = [
        ['label' => __('Total scans'), 'value' => $stats['scans'], 'icon' => 'fa-light fa-chart-line', 'color' => '#2563eb', 'meta' => __('selected range')],
        ['label' => __('Today'), 'value' => $stats['today'], 'icon' => 'fa-light fa-calendar-day', 'color' => '#0f766e', 'meta' => __('current day')],
        ['label' => __('Unique visitors'), 'value' => $stats['uniqueVisitors'], 'icon' => 'fa-light fa-user-magnifying-glass', 'color' => '#7c3aed', 'meta' => __('unique IPs')],
        ['label' => __('Active QR'), 'value' => $stats['active'], 'icon' => 'fa-light fa-circle-check', 'color' => '#d97706', 'meta' => __('live campaigns')],
        ['label' => __('QR codes'), 'value' => $stats['qrCodes'], 'icon' => 'fa-light fa-qrcode', 'color' => '#0891b2', 'meta' => __('workspace total')],
    ];
@endphp

<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 7% 0%, rgba(37,99,235,0.18), transparent 31%),
        radial-gradient(circle at 94% 10%, rgba(20,184,166,0.12), transparent 34%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),1), rgba(var(--theme-surface-base-rgb,255,255,255),0.92));">
        <div class="grid gap-6 p-5 sm:p-7 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-center">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl" style="background-color: rgba(37,99,235,0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-chart-line"></i>
                    </span>
                    <span class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-accent);">{{ __('QR analytics') }}</span>
                    <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb),0.64); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.64);">{{ number_format($stats['scans']) }} {{ __('scans') }}</span>
                </div>
                <h1 class="mt-4 max-w-3xl text-[2rem] font-semibold leading-[1.08] tracking-[-0.035em] sm:text-[2.65rem]" style="color: var(--theme-header-text-color);">{{ $isCampaignAnalytics ? $campaign->name : __('QR performance dashboard') }}</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $isCampaignAnalytics ? __('Campaign-level scan performance, source, device, and event analytics.') : __('Track scan momentum, campaign concentration, traffic source, device mix, and scan events for QR campaigns.') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 xl:justify-end">
                <x-ui.button href="{{ route('portal.qr-codes.index') }}" variant="outline" wire:navigate>
                    <i class="fa-light fa-qrcode"></i>
                    {{ __('Campaigns') }}
                </x-ui.button>
                @if ($isCampaignAnalytics)
                    <x-ui.button href="{{ route('portal.qr-codes.edit', ['qrCode' => $campaign->id]) }}" wire:navigate>
                        <i class="fa-light fa-pen-to-square"></i>
                        {{ __('Edit QR') }}
                    </x-ui.button>
                @else
                    <x-ui.button href="{{ route('portal.qr-codes.create') }}" wire:navigate>
                        <i class="fa-light fa-plus"></i>
                        {{ __('Create QR') }}
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div class="grid border-t md:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.58);">
            @foreach ($summaryCards as $card)
                <div class="border-t p-4 md:border-l md:border-t-0 first:md:border-l-0" style="border-color: rgba(var(--theme-border-color-rgb),0.48);">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background-color: rgba(37,99,235,0.10); color: var(--theme-accent);">
                            <i class="{{ $card['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $card['title'] }}</p>
                            <p class="mt-1 line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $card['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-[1.25rem] border p-4 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[220px_220px]">
                <x-ui.date-picker wire:model.live="fromDate" name="from_date" :label="__('From')" :value="$fromDate" :placeholder="__('Choose start date')" />
                <x-ui.date-picker wire:model.live="toDate" name="to_date" :label="__('To')" :value="$toDate" :placeholder="__('Choose end date')" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button type="button" wire:click="$refresh">{{ __('Apply') }}</x-ui.button>
                <x-ui.button type="button" variant="outline" wire:click="resetFilters">{{ __('Reset') }}</x-ui.button>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">{{ __('Range') }}: {{ $rangeLabel }}</span>
            <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">{{ __('Avg/day') }}: {{ number_format($stats['avgDaily'], 1) }}</span>
            <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">{{ __('Unique IPs') }}: {{ number_format($stats['uniqueVisitors']) }}</span>
        </div>
    </section>

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
        @foreach ($statCards as $stat)
            <div class="rounded-[1rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $stat['value']) }}</p>
                        <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $stat['meta'] }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background-color: {{ $stat['color'] }}18; color: {{ $stat['color'] }};">
                        <i class="{{ $stat['icon'] }}"></i>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
        <x-ui.chart
            :title="__('Scan momentum')"
            :description="__('Daily scan volume with unique visitor signal for the selected reporting window.')"
            type="areaspline"
            :options="$scanTrendOptions"
            :height="340"
            :footer-stats="[
                ['label' => __('Scans'), 'value' => $stats['scans']],
                ['label' => __('Unique visitors'), 'value' => $stats['uniqueVisitors']],
            ]"
        />

        <x-ui.chart
            :title="__('QR type mix')"
                :description="$isCampaignAnalytics ? __('Scan mix for this selected campaign.') : __('Which QR purposes are generating scan traffic.')"
            type="donut"
            :options="$typeOptions"
            :height="340"
            :legend="true"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <x-ui.chart
            :title="__('Hour of day')"
            :description="__('Scan distribution by hour, useful for campaign timing and staffing.')"
            type="column"
            :options="$hourlyOptions"
            :height="320"
        />

        <x-ui.chart
            :title="__('Top QR codes')"
            :description="__('Campaigns ranked by all-time scan count.')"
            type="bar"
            :options="$topQrOptions"
            :height="320"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <x-ui.chart
            :title="__('Traffic sources')"
            :description="__('Referrers and direct short-link traffic.')"
            type="bar"
            :options="$sourceOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Browsers')"
            :description="__('Browser family detected from scan user agents.')"
            type="bar"
            :options="$browserOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Devices')"
            :description="__('Desktop, mobile, tablet, and bot traffic split.')"
            type="donut"
            :options="$deviceOptions"
            :height="330"
            :legend="true"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <x-ui.chart
            :title="__('Brand domains')"
            :description="__('Scans split by custom domain or default application host.')"
            type="bar"
            :options="$domainOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('UTM presets')"
            :description="__('Campaign tagging presets attached to scanned QR campaigns.')"
            type="bar"
            :options="$utmOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Tracking pixels')"
            :description="__('Pixel libraries attached to scanned campaign destinations.')"
            type="bar"
            :options="$pixelOptions"
            :height="330"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-3">
        <x-ui.chart
            :title="__('Operating systems')"
            :description="__('Operating system mix detected from scan user agents.')"
            type="bar"
            :options="$osOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Device brands')"
            :description="__('Estimated device brand from available user-agent hints.')"
            type="bar"
            :options="$brandOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Countries')"
            :description="__('Country codes stored on scan events or metadata.')"
            type="bar"
            :options="$countryOptions"
            :height="330"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <x-ui.chart
            :title="__('Cities')"
            :description="__('City values from scan metadata when geo enrichment is available.')"
            type="bar"
            :options="$cityOptions"
            :height="330"
        />

        <x-ui.chart
            :title="__('Languages')"
            :description="__('Preferred browser language captured from scan requests.')"
            type="bar"
            :options="$languageOptions"
            :height="330"
        />
    </div>

    <div class="grid gap-5 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.section-card
            :title="__('Campaign leaderboard')"
            :description="__('The highest performing QR campaigns in this workspace.')"
            header-class="py-4"
            title-class="mt-1 text-[1.15rem] tracking-[-0.025em]"
            description-class="mt-1 leading-6"
        >
            <div class="space-y-3 px-6 py-6">
                @forelse ($topQrCodes as $qrCode)
                    <div class="flex items-center justify-between gap-4 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $qrCode->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.08em]" style="color: var(--theme-muted-text-color);">{{ str_replace('_', ' ', $qrCode->type) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $qrCode->scans_count) }}</p>
                            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('scans') }}</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty icon="fa-light fa-qrcode" :title="__('No QR campaigns yet')" :description="__('Create QR campaigns first, then ranking data will appear here.')" />
                @endforelse
            </div>
        </x-ui.section-card>

        <x-ui.section-card
            :title="__('Recent scan events')"
            :description="__('Latest dynamic QR opens with detected device and source context.')"
            header-class="py-4"
            title-class="mt-1 text-[1.15rem] tracking-[-0.025em]"
            description-class="mt-1 leading-6"
        >
            <div class="space-y-3 px-6 py-6">
                @forelse ($events as $event)
                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $event->qrCode?->name ?: __('QR code') }}</p>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $event->created_at?->format('Y-m-d H:i') }} &middot; {{ $event->ip_address ?: __('Unknown IP') }}</p>
                            </div>
                            <x-ui.badge variant="neutral">{{ data_get($event->metadata, 'device') ?: __('Scan') }}</x-ui.badge>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <x-ui.badge variant="neutral">{{ data_get($event->metadata, 'browser') ?: __('Browser unknown') }}</x-ui.badge>
                            <x-ui.badge variant="neutral">{{ data_get($event->metadata, 'os') ?: __('OS unknown') }}</x-ui.badge>
                            <x-ui.badge variant="neutral">{{ data_get($event->metadata, 'city') ?: ($event->country ?: __('Location unknown')) }}</x-ui.badge>
                            <x-ui.badge variant="neutral">{{ $event->referer ? parse_url($event->referer, PHP_URL_HOST) : __('Direct') }}</x-ui.badge>
                        </div>
                    </div>
                @empty
                    <x-ui.empty icon="fa-light fa-chart-line" :title="__('No scan events in this range')" :description="__('Scans will appear here after users open dynamic QR links.')" />
                @endforelse
            </div>
        </x-ui.section-card>
    </div>
</div>
