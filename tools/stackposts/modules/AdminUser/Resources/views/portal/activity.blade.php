@component(theme_view('layouts.app', 'app'), ['title' => __('Activity Log')])
    @php
        $formatMetadataValue = static function (mixed $value): string {
            if (is_array($value)) {
                return collect($value)
                    ->map(fn (mixed $nestedValue, mixed $nestedKey) => is_string($nestedKey)
                        ? $nestedKey.': '.(is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                        : (is_scalar($nestedValue) || $nestedValue === null ? (string) $nestedValue : json_encode($nestedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))
                    ->implode(', ');
            }

            if (is_object($value)) {
                $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                return $json !== false ? $json : '[object]';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value === null ? '-' : (string) $value;
        };
        $latestLog = $logs->first();
        $areaCount = $logs->pluck('area')->unique()->count();
        $activityMetrics = [
            [
                'label' => __('Entries'),
                'value' => number_format($logs->count()),
                'detail' => __('Recent activity records loaded for your account.'),
                'icon' => 'fa-light fa-list-timeline',
                'tone' => 'rgba(59, 130, 246, 0.10)',
                'color' => 'rgb(37, 99, 235)',
            ],
            [
                'label' => __('Latest event'),
                'value' => $latestLog?->event ?? __('No activity yet'),
                'detail' => $latestLog?->created_at?->format('Y-m-d H:i') ?? __('Activity will appear here after key actions.'),
                'icon' => 'fa-light fa-clock-rotate-left',
                'tone' => 'rgba(245, 158, 11, 0.12)',
                'color' => 'rgb(217, 119, 6)',
            ],
            [
                'label' => __('Areas'),
                'value' => number_format($areaCount),
                'detail' => __('Portal and admin actions are recorded together for this user.'),
                'icon' => 'fa-light fa-layer-group',
                'tone' => 'rgba(168, 85, 247, 0.10)',
                'color' => 'rgb(126, 34, 206)',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-shield-check"></i>
                            {{ __('User portal') }}
                        </span>
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Activity Log') }}</h1>
                                <x-ui.badge variant="neutral">{{ trans_choice(':count record|:count records', $logs->count(), ['count' => number_format($logs->count())]) }}</x-ui.badge>
                            </div>
                            <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                                {{ __('Review recent portal and admin actions tied to your account, including routes, areas, and event metadata.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-ui.button href="{{ route('portal.dashboard') }}" variant="outline" wire:navigate>
                            <i class="fa-light fa-arrow-left"></i>
                            {{ __('Back to dashboard') }}
                        </x-ui.button>
                    </div>
                </div>

                <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Latest event') }}</p>
                            <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $latestLog?->event ?? __('No activity yet') }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $latestLog?->created_at?->format('Y-m-d H:i:s') ?? __('Activity will appear after key actions.') }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                            <i class="fa-light fa-clock-rotate-left text-lg"></i>
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Entries') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($logs->count()) }}</p>
                        </div>
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Areas') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($areaCount) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <section class="grid gap-5 md:grid-cols-3">
            @foreach ($activityMetrics as $metric)
                <x-ui.card class="overflow-hidden">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 space-y-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ $metric['label'] }}</p>
                            <p class="truncate text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $metric['value'] }}</p>
                            <p class="line-clamp-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $metric['detail'] }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: {{ $metric['tone'] }}; color: {{ $metric['color'] }};">
                            <i class="{{ $metric['icon'] }} text-lg"></i>
                        </span>
                    </div>
                </x-ui.card>
            @endforeach
        </section>

        <x-ui.datatable-shell :title="__('Recent activity')" :info="__('Chronological action history for your account.')">
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Event') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Description') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Area') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Route') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                </x-ui.table-head>

                <x-ui.table-body>
                    @forelse ($logs as $log)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-bolt"></i>
                                    </span>
                                    <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $log->event }}</p>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <div class="space-y-1">
                                    <p class="font-medium" style="color: var(--theme-header-text-color);">{{ $log->description ?: '—' }}</p>
                                    @if (! empty($log->metadata))
                                        <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ collect($log->metadata)->map(fn ($value, $key) => $key.': '.$formatMetadataValue($value))->implode(' · ') }}</p>
                                    @endif
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell><x-ui.badge :variant="$log->area === 'user' ? 'primary' : 'neutral'">{{ strtoupper($log->area) }}</x-ui.badge></x-ui.table-cell>
                            <x-ui.table-cell><span class="font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $log->route_name ?: '—' }}</span></x-ui.table-cell>
                            <x-ui.table-cell>{{ $log->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="5" class="py-10">
                                <x-ui.empty icon="fa-light fa-list-timeline" :title="__('No activity recorded yet.')" :description="__('Portal and account actions will appear here after activity is captured.')" />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.datatable-shell>
    </div>
@endcomponent
