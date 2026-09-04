@php
    $metrics = $metrics ?? [];
    $routes = $routes ?? [];
    $qrLimits = collect($qrLimits ?? []);
    $recentQrCodes = $recentQrCodes ?? collect();
    $charts = $charts ?? [];
    $onboardingChecklist = collect($onboardingChecklist ?? []);
    $onboardingDone = $onboardingChecklist->filter(fn ($item) => (bool) ($item['done'] ?? false))->count();
    $onboardingTotal = max(1, $onboardingChecklist->count());
    $onboardingPercent = (int) round(($onboardingDone / $onboardingTotal) * 100);
    $activity = collect($charts['activity'] ?? []);
    $qrTypes = collect($charts['qr_types'] ?? []);
    $bioEvents = collect($charts['bio_events'] ?? []);
    $maxActivity = max(1, (int) $activity->map(fn ($item) => max((int) ($item['scans'] ?? 0), (int) ($item['events'] ?? 0)))->max());
    $maxQrType = max(1, (int) $qrTypes->max('value'));
    $maxBioEvent = max(1, (int) $bioEvents->max('value'));

    $cards = [
        ['label' => __('Bio Pages'), 'value' => $metrics['bio_pages'] ?? 0, 'note' => __(':count published', ['count' => number_format((int) ($metrics['published_bio_pages'] ?? 0))]), 'icon' => 'fa-link', 'color' => '#4f46e5'],
        ['label' => __('QR Codes'), 'value' => $metrics['qr_codes'] ?? 0, 'note' => __(':count active', ['count' => number_format((int) ($metrics['active_qr_codes'] ?? 0))]), 'icon' => 'fa-qrcode', 'color' => '#2563eb'],
        ['label' => __('QR Scans'), 'value' => $metrics['qr_scans'] ?? 0, 'note' => __('Dynamic campaign scans'), 'icon' => 'fa-chart-line', 'color' => '#0f766e'],
        ['label' => __('Bio Events'), 'value' => $metrics['bio_events'] ?? 0, 'note' => __('Views and button clicks'), 'icon' => 'fa-arrow-pointer', 'color' => '#d97706'],
    ];
@endphp

<x-ui.dashboard-module
    class="overflow-hidden rounded-[1.75rem] border p-5 shadow-[0_30px_90px_-62px_rgba(15,23,42,0.5)] sm:p-6"
    style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 0% 8%, rgba(37,99,235,0.16), transparent 25%),
        radial-gradient(circle at 42% 0%, rgba(20,184,166,0.14), transparent 26%),
        radial-gradient(circle at 100% 16%, rgba(249,115,22,0.13), transparent 26%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.94), rgba(var(--theme-surface-soft-rgb,248,250,252),0.86));"
    :eyebrow="__('Link Bio & QR')"
    :title="__('Campaign dashboard')"
    :description="__('Track Bio pages, QR campaigns, scans, clicks, and the campaign mix from one workspace snapshot.')"
>
    <x-slot:actions>
        @if (! empty($routes['create_qr']))
            <x-ui.button :href="$routes['create_qr']" size="sm" wire:navigate>
                <i class="fa-light fa-plus"></i>
                {{ __('Create QR') }}
            </x-ui.button>
        @endif
        @if (! empty($routes['analytics']))
            <x-ui.button :href="$routes['analytics']" variant="outline" size="sm" wire:navigate>
                {{ __('Analytics') }}
            </x-ui.button>
        @endif
    </x-slot:actions>

    <div class="space-y-4">
        @if ($onboardingChecklist->isNotEmpty() && $onboardingDone < $onboardingChecklist->count())
            <section class="overflow-hidden rounded-[1.25rem] border shadow-sm" style="border-color: rgba(var(--theme-accent-rgb),0.24); background:
                radial-gradient(circle at 6% 0%, rgba(var(--theme-accent-rgb),0.14), transparent 28%),
                radial-gradient(circle at 92% 0%, rgba(20,184,166,0.12), transparent 30%),
                rgba(var(--theme-surface-base-rgb,255,255,255),0.88);">
                <div class="grid gap-0 xl:grid-cols-[minmax(0,0.35fr)_minmax(0,0.65fr)]">
                    <div class="border-b p-5 xl:border-b-0 xl:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                            <i class="fa-light fa-list-check"></i>
                            {{ __('Getting started') }}
                        </div>
                        <h2 class="mt-4 text-xl font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('Launch your workspace') }}</h2>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Complete the core setup steps so visitors can scan, open, and measure your campaigns.') }}</p>
                        <div class="mt-5">
                            <div class="flex items-center justify-between gap-3 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                                <span>{{ __('Progress') }}</span>
                                <span>{{ $onboardingDone }}/{{ $onboardingChecklist->count() }}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                                <div class="h-full rounded-full" style="width: {{ $onboardingPercent }}%; background: linear-gradient(90deg, var(--theme-accent), #14b8a6);"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 p-5 md:grid-cols-2">
                        @foreach ($onboardingChecklist as $item)
                            @php
                                $done = (bool) ($item['done'] ?? false);
                                $href = $done ? ($item['done_route'] ?? $item['route'] ?? '#') : ($item['route'] ?? '#');
                                $disabled = $href === '#';
                            @endphp
                            <a
                                href="{{ $href }}"
                                @if (! $disabled) wire:navigate @endif
                                class="group rounded-[1rem] border p-4 transition hover:-translate-y-0.5 hover:shadow-[0_22px_48px_-38px_rgba(15,23,42,0.42)]"
                                style="border-color: {{ $done ? 'rgba(20,184,166,0.34)' : 'rgba(var(--theme-border-color-rgb),0.58)' }}; background-color: {{ $done ? 'rgba(20,184,166,0.10)' : 'rgba(var(--theme-surface-soft-rgb,248,250,252),0.58)' }}; {{ $disabled ? 'pointer-events:none; opacity:.62;' : '' }}"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: {{ $done ? 'rgba(20,184,166,0.14)' : (($item['color'] ?? '#2563eb').'18') }}; color: {{ $done ? '#0f766e' : ($item['color'] ?? '#2563eb') }};">
                                        <i class="fa-light {{ $done ? 'fa-check' : ($item['icon'] ?? 'fa-circle') }}"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['label'] }}</span>
                                        <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $item['description'] }}</span>
                                        <span class="mt-3 inline-flex items-center gap-2 text-xs font-semibold" style="color: {{ $done ? '#0f766e' : 'var(--theme-accent)' }};">
                                            {{ $done ? __('Done') : ($item['action'] ?? __('Open')) }}
                                            <i class="fa-light {{ $done ? 'fa-circle-check' : 'fa-arrow-right' }} transition group-hover:translate-x-0.5"></i>
                                        </span>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <div class="rounded-[1.1rem] border p-4 shadow-sm" style="border-color: {{ $card['color'] }}2e; background: linear-gradient(135deg, {{ $card['color'] }}14, rgba(var(--theme-surface-base-rgb,255,255,255),0.86));">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $card['label'] }}</p>
                            <p class="mt-2 text-[1.9rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $card['value']) }}</p>
                            <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ $card['note'] }}</p>
                        </div>
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background: color-mix(in srgb, {{ $card['color'] }} 12%, transparent); color: {{ $card['color'] }};">
                            <i class="fa-light {{ $card['icon'] }}"></i>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($qrLimits->isNotEmpty())
            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(20,184,166,0.24); background: linear-gradient(135deg, rgba(20,184,166,0.13), rgba(37,99,235,0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR plan limits') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Visible usage limits for creation, scan analytics, dynamic rules, and bulk imports.') }}</p>
                    </div>
                    @if (! empty($routes['qr_codes']))
                        <a href="{{ $routes['qr_codes'] }}" wire:navigate class="text-xs font-semibold" style="color: var(--theme-accent);">{{ __('Manage campaigns') }}</a>
                    @endif
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
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
                        <div class="rounded-[1rem] border p-4 shadow-sm" style="border-color: {{ $limit['color'] }}2e; background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $limit['label'] }}</p>
                                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $limit['description'] }}</p>
                                </div>
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem]" style="background: color-mix(in srgb, {{ $limit['color'] }} 12%, transparent); color: {{ $limit['color'] }};">
                                    <i class="{{ $limit['icon'] }} text-xs"></i>
                                </span>
                            </div>
                            <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $valueLabel }}</p>
                            @if ($hasUsage && ! $isUnlimited)
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background: var(--theme-surface-subtle);">
                                    <div class="h-full rounded-full" style="width: {{ $percent }}%; background: {{ $limit['color'] }};"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.65fr)]">
            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(37,99,235,0.18); background:
                linear-gradient(135deg, rgba(37,99,235,0.10), rgba(20,184,166,0.07)),
                rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Scan and Bio activity') }}</p>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Last 7 days') }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>{{ __('Scans') }}</span>
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span>{{ __('Bio events') }}</span>
                    </div>
                </div>

                <div class="mt-6 grid h-56 grid-cols-7 items-end gap-3 rounded-[1rem] border px-4 pb-3 pt-5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.70);">
                    @foreach ($activity as $point)
                        @php
                            $scanHeight = max(8, (int) round(((int) ($point['scans'] ?? 0) / $maxActivity) * 100));
                            $eventHeight = max(8, (int) round(((int) ($point['events'] ?? 0) / $maxActivity) * 100));
                        @endphp
                        <div class="flex h-full min-w-0 flex-col justify-end gap-2">
                            <div class="flex h-full items-end justify-center gap-1.5">
                                <span class="w-3 rounded-t-full" style="height: {{ $scanHeight }}%; background: linear-gradient(180deg, #2563eb, #60a5fa);"></span>
                                <span class="w-3 rounded-t-full" style="height: {{ $eventHeight }}%; background: linear-gradient(180deg, #14b8a6, #5eead4);"></span>
                            </div>
                            <p class="truncate text-center text-[11px] font-medium" style="color: var(--theme-muted-text-color);">{{ $point['label'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('7 day scans') }}</p>
                        <p class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $activity->sum('scans')) }}</p>
                    </div>
                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('7 day events') }}</p>
                        <p class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $activity->sum('events')) }}</p>
                    </div>
                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Active share') }}</p>
                        <p class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ (int) ($metrics['qr_codes'] ?? 0) > 0 ? round(((int) ($metrics['active_qr_codes'] ?? 0) / (int) $metrics['qr_codes']) * 100) : 0 }}%</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(249,115,22,0.22); background: linear-gradient(135deg, rgba(249,115,22,0.11), rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR type mix') }}</p>
                <div class="mt-5 space-y-4">
                    @forelse ($qrTypes as $type)
                        @php $width = max(8, (int) round(((int) ($type['value'] ?? 0) / $maxQrType) * 100)); @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $type['label'] }}</p>
                                <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ number_format((int) $type['value']) }}</p>
                            </div>
                            <div class="h-2 rounded-full" style="background: var(--theme-surface-subtle);">
                                <div class="h-2 rounded-full bg-blue-600" style="width: {{ $width }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('QR type distribution appears after you create campaigns.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.90);">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent QR campaigns') }}</p>
                    @if (! empty($routes['qr_codes']))
                        <a href="{{ $routes['qr_codes'] }}" wire:navigate class="text-xs font-semibold" style="color: var(--theme-accent);">{{ __('View all') }}</a>
                    @endif
                </div>

                <div class="mt-4 grid gap-3">
                    @forelse ($recentQrCodes as $qrCode)
                        <div class="grid gap-3 rounded-[0.95rem] border px-4 py-3 sm:grid-cols-[minmax(0,1fr)_8rem_5rem]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $qrCode->name }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ str_replace('_', ' ', (string) $qrCode->type) }}</p>
                            </div>
                            <div class="flex items-center sm:justify-end">
                                <x-ui.badge :variant="$qrCode->status === 'active' ? 'success' : 'neutral'">{{ str($qrCode->status)->title() }}</x-ui.badge>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $qrCode->scans_count) }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('scans') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[0.95rem] border px-4 py-6 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.58); color: var(--theme-muted-text-color);">{{ __('No QR campaigns yet. Create one from an existing Bio page or a new QR type.') }}</div>
                    @endforelse
                </div>
            </section>

            <aside class="grid gap-4">
                <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(20,184,166,0.22); background: linear-gradient(135deg, rgba(20,184,166,0.11), rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Bio event mix') }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($bioEvents as $event)
                            @php $width = max(8, (int) round(((int) ($event['value'] ?? 0) / $maxBioEvent) * 100)); @endphp
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $event['label'] }}</p>
                                    <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ number_format((int) $event['value']) }}</p>
                                </div>
                                <div class="h-2 rounded-full" style="background: var(--theme-surface-subtle);">
                                    <div class="h-2 rounded-full bg-teal-500" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Bio events appear after visitors view or click Bio pages.') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.90);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Quick actions') }}</p>
                    <div class="mt-4 grid gap-2">
                        @if (! empty($routes['create_bio']))
                            <x-ui.button :href="$routes['create_bio']" variant="outline" size="sm" class="justify-center" wire:navigate>{{ __('New Bio Page') }}</x-ui.button>
                        @endif
                        @if (! empty($routes['create_qr']))
                            <x-ui.button :href="$routes['create_qr']" variant="outline" size="sm" class="justify-center" wire:navigate>{{ __('New QR Code') }}</x-ui.button>
                        @endif
                        @if (! empty($routes['analytics']))
                            <x-ui.button :href="$routes['analytics']" variant="outline" size="sm" class="justify-center" wire:navigate>{{ __('QR Analytics') }}</x-ui.button>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-ui.dashboard-module>
