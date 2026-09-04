@php
    $metrics = $metrics ?? [];
    $routes = $routes ?? [];
    $charts = $charts ?? [];
    $topQrCodes = collect($topQrCodes ?? []);
    $topBioPages = collect($topBioPages ?? []);

    $qrTotal = max(1, (int) ($metrics['qr_codes'] ?? 0));
    $bioTotal = max(1, (int) ($metrics['bio_pages'] ?? 0));
    $bioEvents = max(1, (int) ($metrics['bio_events'] ?? 0));
    $activationRate = round(((int) ($metrics['active_qr_codes'] ?? 0) / $qrTotal) * 100);
    $publishedRate = round(((int) ($metrics['published_bio_pages'] ?? 0) / $bioTotal) * 100);
    $bioCtr = round(((int) ($metrics['bio_clicks'] ?? 0) / $bioEvents) * 100);
    $activityMax = max(1, collect($charts['activity'] ?? [])->max(fn ($row) => ((int) ($row['scans'] ?? 0)) + ((int) ($row['events'] ?? 0))));
    $typeMax = max(1, collect($charts['qr_types'] ?? [])->max('value'));
    $statusMax = max(1, collect($charts['qr_status'] ?? [])->max('value'));
    $eventMax = max(1, collect($charts['bio_events'] ?? [])->max('value'));

    $cards = [
        ['label' => __('Bio Pages'), 'value' => $metrics['bio_pages'] ?? 0, 'note' => __(':count published · :draft drafts', ['count' => number_format((int) ($metrics['published_bio_pages'] ?? 0)), 'draft' => number_format((int) ($metrics['draft_bio_pages'] ?? 0))]), 'icon' => 'fa-link', 'color' => '#7c3aed'],
        ['label' => __('QR Codes'), 'value' => $metrics['qr_codes'] ?? 0, 'note' => __(':count active · :draft drafts', ['count' => number_format((int) ($metrics['active_qr_codes'] ?? 0)), 'draft' => number_format((int) ($metrics['draft_qr_codes'] ?? 0))]), 'icon' => 'fa-qrcode', 'color' => '#2563eb'],
        ['label' => __('QR Scans'), 'value' => $metrics['qr_scans'] ?? 0, 'note' => __(':week last 7d · :month last 30d', ['week' => number_format((int) ($metrics['qr_scans_7d'] ?? 0)), 'month' => number_format((int) ($metrics['qr_scans_30d'] ?? 0))]), 'icon' => 'fa-chart-line', 'color' => '#0f766e'],
        ['label' => __('Active Owners'), 'value' => $metrics['total_owners'] ?? 0, 'note' => __(':qr QR owners · :bio Bio owners', ['qr' => number_format((int) ($metrics['owners'] ?? 0)), 'bio' => number_format((int) ($metrics['bio_owners'] ?? 0))]), 'icon' => 'fa-users', 'color' => '#d97706'],
    ];

    $health = [
        ['label' => __('Activation rate'), 'value' => $activationRate.'%', 'note' => __('Active QR campaigns'), 'width' => $activationRate, 'color' => '#2563eb'],
        ['label' => __('Published Bio share'), 'value' => $publishedRate.'%', 'note' => __('Bio pages visible publicly'), 'width' => $publishedRate, 'color' => '#7c3aed'],
        ['label' => __('Bio CTR'), 'value' => $bioCtr.'%', 'note' => __('Clicks divided by Bio events'), 'width' => $bioCtr, 'color' => '#14b8a6'],
        ['label' => __('Bio engagement 7d'), 'value' => number_format((int) ($metrics['bio_events_7d'] ?? 0)), 'note' => __('Recent views and clicks'), 'width' => min(100, (int) ($metrics['bio_events_7d'] ?? 0) * 12), 'color' => '#f59e0b'],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Growth Tools')"
    :title="__('Link Bio and QR adoption')"
    :description="__('Monitor creation, publishing, scans, Bio engagement, owner coverage, and campaign mix across the platform.')"
>
    <x-slot:actions>
        @if (! empty($routes['analytics']))
            <x-ui.button :href="$routes['analytics']" size="sm" wire:navigate>{{ __('Open analytics') }}</x-ui.button>
        @endif
        @if (! empty($routes['users']))
            <x-ui.button :href="$routes['users']" variant="outline" size="sm" wire:navigate>{{ __('Users') }}</x-ui.button>
        @endif
    </x-slot:actions>

    <div class="overflow-hidden rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: radial-gradient(circle at 16% 0%, rgba(var(--theme-accent-rgb),0.12), transparent 34%), radial-gradient(circle at 92% 8%, rgba(20,184,166,0.10), transparent 34%), var(--theme-surface-base);">
        <div class="grid gap-3 md:grid-cols-4">
            @foreach ($cards as $card)
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.62);">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                            <p class="mt-2 text-[1.85rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $card['value']) }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background: {{ $card['color'] }}18; color: {{ $card['color'] }};">
                            <i class="fa-light {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-4">
            @foreach ($health as $item)
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.60);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                            <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $item['value'] }}</p>
                        </div>
                        <span class="mt-1 h-2.5 w-2.5 rounded-full" style="background-color: {{ $item['color'] }};"></span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                        <div class="h-full rounded-full" style="width: {{ max(3, min(100, (int) $item['width'])) }}%; background-color: {{ $item['color'] }};"></div>
                    </div>
                    <p class="mt-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $item['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)]">
            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.52);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Last 7 days') }}</p>
                        <h3 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Scan and Bio activity') }}</h3>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                        <span class="inline-flex items-center gap-1"><i class="h-2 w-2 rounded-full" style="background:#2563eb;"></i>{{ __('Scans') }}</span>
                        <span class="inline-flex items-center gap-1"><i class="h-2 w-2 rounded-full" style="background:#14b8a6;"></i>{{ __('Bio') }}</span>
                    </div>
                </div>
                <div class="mt-5 grid h-40 grid-cols-7 items-end gap-2">
                    @foreach (($charts['activity'] ?? []) as $row)
                        @php
                            $scans = (int) ($row['scans'] ?? 0);
                            $events = (int) ($row['events'] ?? 0);
                            $scanHeight = max(4, round(($scans / $activityMax) * 100));
                            $eventHeight = max(4, round(($events / $activityMax) * 100));
                        @endphp
                        <div class="flex h-full min-w-0 flex-col justify-end gap-2">
                            <div class="flex h-full items-end justify-center gap-1 rounded-[0.75rem] px-1 py-2" style="background-color: rgba(var(--theme-border-color-rgb),0.08);">
                                <span class="w-3 rounded-full" title="{{ __('Scans') }}: {{ $scans }}" style="height: {{ $scanHeight }}%; background: linear-gradient(180deg,#2563eb,#0891b2);"></span>
                                <span class="w-3 rounded-full" title="{{ __('Bio events') }}: {{ $events }}" style="height: {{ $eventHeight }}%; background: linear-gradient(180deg,#14b8a6,#22c55e);"></span>
                            </div>
                            <span class="truncate text-center text-[10px] font-semibold" style="color: var(--theme-muted-text-color);">{{ $row['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.58);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Bio event mix') }}</p>
                <h3 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Visitor intent') }}</h3>
                <div class="mt-5 space-y-4">
                    @forelse (($charts['bio_events'] ?? []) as $row)
                        @php($width = max(4, round(((int) $row['value'] / $eventMax) * 100)))
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</span>
                                <span style="color: var(--theme-muted-text-color);">{{ number_format((int) $row['value']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                                <div class="h-full rounded-full" style="width: {{ $width }}%; background: linear-gradient(90deg,#14b8a6,#2563eb);"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('No public Bio events recorded yet.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-2">
            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Campaign mix') }}</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR types') }}</h3>
                        @forelse (($charts['qr_types'] ?? []) as $row)
                            @php($width = max(5, round(((int) $row['value'] / $typeMax) * 100)))
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                    <span class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</span>
                                    <span style="color: var(--theme-muted-text-color);">{{ number_format((int) $row['value']) }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                                    <div class="h-full rounded-full" style="width: {{ $width }}%; background-color: #2563eb;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No QR campaigns yet.') }}</p>
                        @endforelse
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR status') }}</h3>
                        @forelse (($charts['qr_status'] ?? []) as $row)
                            @php($width = max(5, round(((int) $row['value'] / $statusMax) * 100)))
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                    <span class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $row['label'] }}</span>
                                    <span style="color: var(--theme-muted-text-color);">{{ number_format((int) $row['value']) }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                                    <div class="h-full rounded-full" style="width: {{ $width }}%; background-color: #f59e0b;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No status data yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.52);">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Top QR') }}</p>
                        <div class="mt-3 space-y-2">
                            @forelse ($topQrCodes as $qr)
                                <div class="rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.54);">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $qr->name }}</p>
                                        <span class="text-xs font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $qr->scans_count) }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ str((string) $qr->type)->replace('_', ' ')->title() }} · {{ ucfirst((string) $qr->status) }}</p>
                                </div>
                            @empty
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No QR campaigns yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Top Bio pages') }}</p>
                        <div class="mt-3 space-y-2">
                            @forelse ($topBioPages as $page)
                                <div class="rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.54);">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $page->title }}</p>
                                        <span class="text-xs font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $page->events_count) }}</span>
                                    </div>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">/{{ $page->slug }} · {{ $page->is_published ? __('Published') : __('Draft') }}</p>
                                </div>
                            @empty
                                <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No Bio pages yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-ui.dashboard-module>
