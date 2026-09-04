@component(theme_view('layouts.app', 'app'), ['title' => __('Credit Usage')])
    @php
        $activeAction = request('action', '');
        $remainingCredits = ($creditSummary['unlimited'] ?? false) ? __('Unlimited') : number_format((int) ($creditSummary['remaining'] ?? 0));
        $creditTotal = ($creditSummary['unlimited'] ?? false)
            ? null
            : max(0, (int) ($creditSummary['used'] ?? 0) + (int) ($creditSummary['remaining'] ?? 0));
        $creditUsedPercent = (! ($creditSummary['unlimited'] ?? false) && $creditTotal > 0)
            ? min(100, max(0, round(((int) ($creditSummary['used'] ?? 0) / $creditTotal) * 100)))
            : 0;
        $creditRemainingPercent = ($creditSummary['unlimited'] ?? false) ? 100 : max(0, 100 - $creditUsedPercent);
        $creditMetrics = [
            [
                'label' => __('Entries'),
                'value' => number_format($entriesCount),
                'detail' => __('Logged AI Studio credit events.'),
                'icon' => 'fa-light fa-list-check',
                'tone' => 'rgba(59, 130, 246, 0.10)',
                'color' => 'rgb(37, 99, 235)',
            ],
            [
                'label' => __('Credits used'),
                'value' => number_format($creditsUsed),
                'detail' => __('Total credits consumed by AI Studio actions.'),
                'icon' => 'fa-light fa-bolt',
                'tone' => 'rgba(245, 158, 11, 0.12)',
                'color' => 'rgb(217, 119, 6)',
            ],
            [
                'label' => __('Actions used'),
                'value' => number_format($actionsCount),
                'detail' => __('Distinct AI Studio actions that consumed credits.'),
                'icon' => 'fa-light fa-wand-magic-sparkles',
                'tone' => 'rgba(168, 85, 247, 0.10)',
                'color' => 'rgb(126, 34, 206)',
            ],
            [
                'label' => __('Remaining'),
                'value' => $remainingCredits,
                'detail' => __('Credits still available for the current plan period.'),
                'icon' => 'fa-light fa-coins',
                'tone' => 'rgba(16, 185, 129, 0.10)',
                'color' => 'rgb(5, 150, 105)',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-wand-magic-sparkles"></i>
                            {{ __('AI Studio credits') }}
                        </span>
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Credit Usage') }}</h1>
                                <x-ui.badge variant="neutral">{{ trans_choice(':count record|:count records', $logs->total(), ['count' => number_format($logs->total())]) }}</x-ui.badge>
                            </div>
                            <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                                {{ __('Track credit spending, remaining balance, top-up packs, and AI Studio usage events from one workspace.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if ($canBuyMoreCredits && $creditPacks->isNotEmpty())
                            <x-ui.button :href="route('portal.credits').'#credit-packs'" wire:navigate>
                                <i class="fa-light fa-coins"></i>
                                {{ __('Buy more credits') }}
                            </x-ui.button>
                        @endif
                        <x-ui.button :href="route('portal.ai-studio.prompt-history')" variant="outline" wire:navigate>
                            <i class="fa-light fa-clock-rotate-left"></i>
                            {{ __('Prompt History') }}
                        </x-ui.button>
                        <x-ui.button :href="route('portal.ai-studio.settings')" variant="outline" wire:navigate>
                            <i class="fa-light fa-sliders"></i>
                            {{ __('AI Settings') }}
                        </x-ui.button>
                    </div>
                </div>

                <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Credit balance') }}</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $remainingCredits }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Top-up balance') }}: {{ number_format((int) $creditTopupRemaining) }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                            <i class="fa-light fa-coins text-lg"></i>
                        </span>
                    </div>

                    <div class="mt-5 h-2.5 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.45);">
                        <div
                            class="h-full rounded-full transition-all"
                            style="width: {{ $creditRemainingPercent }}%; background: {{ $creditSummary['low_balance'] ?? false ? 'rgb(245, 158, 11)' : 'linear-gradient(90deg, var(--theme-accent-color), color-mix(in srgb, var(--theme-accent-color) 72%, #22c55e))' }};"
                        ></div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs" style="color: var(--theme-muted-text-color);">
                        <span>{{ ($creditSummary['unlimited'] ?? false) ? __('Unlimited access') : __('Remaining balance') }}</span>
                        <span>{{ ($creditSummary['unlimited'] ?? false) ? __('No cap') : $creditRemainingPercent.'%' }}</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($creditMetrics as $metric)
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

        @if ($canBuyMoreCredits && $creditPacks->isNotEmpty())
            <x-ui.card id="credit-packs" class="space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Top-up store') }}</p>
                        <h3 class="mt-2 flex items-center gap-2 text-[1.3rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">
                            <i class="fa-light fa-cart-shopping text-[var(--theme-accent)]"></i>
                            {{ __('Buy more credits') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ __('Top-up credits are cumulative, non-expiring, and can be spent before plan credits when the engine priority is configured that way.') }}
                        </p>
                    </div>
                    <div class="rounded-[1rem] border px-4 py-3" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Top-up balance') }}</p>
                        <p class="mt-2 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $creditTopupRemaining) }}</p>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($creditPacks as $pack)
                        <div class="rounded-[1.15rem] border p-5 transition hover:-translate-y-1 hover:shadow-[0_24px_60px_-42px_rgba(15,23,42,0.32)]" style="border-color: {{ $pack->featured ? 'rgba(var(--theme-accent-rgb), 0.34)' : 'var(--theme-border-color)' }}; background: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-coins"></i>
                                    </span>
                                    <div>
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $pack->name }}</p>
                                    @if ($pack->description)
                                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $pack->description }}</p>
                                    @endif
                                    </div>
                                </div>
                                @if ($pack->featured)
                                    <x-ui.badge variant="success">{{ __('Featured') }}</x-ui.badge>
                                @endif
                            </div>

                            <div class="mt-4 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-3xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $pack->credits) }}</p>
                                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Credits') }}</p>
                                </div>
                                <p class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ $pack->currency_symbol }}{{ number_format((float) $pack->price, 2) }}</p>
                            </div>

                            <div class="mt-5">
                                <x-ui.button :href="route('payment.credits', $pack->slug)" class="w-full justify-center" wire:navigate>
                                    <i class="fa-light fa-plus"></i>
                                    {{ __('Buy more credits') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        <x-ui.card class="space-y-4">
            <form method="GET" action="{{ route('portal.credits') }}" class="grid gap-4 md:grid-cols-[240px_auto]">
                <x-ui.select name="action" :label="__('Action')">
                    <option value="">{{ __('All actions') }}</option>
                    @foreach ($actionOptions as $key => $label)
                        <option value="{{ $key }}" @selected($activeAction === $key)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <div class="flex items-end gap-3">
                    <x-ui.button type="submit"><i class="fa-light fa-filter"></i>{{ __('Filter') }}</x-ui.button>
                    <x-ui.button :href="route('portal.credits')" variant="outline" wire:navigate><i class="fa-light fa-rotate-left"></i>{{ __('Reset') }}</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        @if ($topupLedger->isNotEmpty())
            <x-ui.datatable-shell :title="__('Top-up ledger')" :info="__('Credit pack purchases and top-up consumption entries recorded on this account.')">
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('Type') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Remaining') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Pack') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @foreach ($topupLedger as $entry)
                            <x-ui.table-row>
                                <x-ui.table-cell>
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                            <i class="fa-light {{ in_array($entry->type, ['purchase', 'adjustment'], true) ? 'fa-plus' : 'fa-minus' }}"></i>
                                        </span>
                                        <x-ui.badge :variant="in_array($entry->type, ['purchase', 'adjustment'], true) ? 'success' : 'neutral'">
                                            {{ __(ucfirst(str_replace('_', ' ', $entry->type))) }}
                                        </x-ui.badge>
                                    </div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);">
                                        {{ $entry->amount > 0 ? '+' : '' }}{{ number_format((int) $entry->amount) }}
                                    </span>
                                </x-ui.table-cell>
                                <x-ui.table-cell>{{ number_format((int) $entry->remaining) }}</x-ui.table-cell>
                                <x-ui.table-cell>{{ $entry->creditPack?->name ?: __('Manual adjustment') }}</x-ui.table-cell>
                                <x-ui.table-cell>{{ $entry->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>
        @endif

        <x-ui.datatable-shell :title="__('Usage log')" :info="__('Each row represents credits consumed by one AI Studio action.')">
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Action') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Credits') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Quantity') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Balance') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Metadata') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Time') }}</x-ui.table-cell>
                </x-ui.table-head>
                <x-ui.table-body>
                    @forelse ($logs as $log)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-wand-magic-sparkles"></i>
                                    </span>
                                    <div class="space-y-1">
                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ $actionOptions[$log->action_key] ?? $log->action_key }}</p>
                                        @if ($log->feature)
                                            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $log->feature }}</p>
                                        @endif
                                    </div>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell><span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $log->amount) }}</span></x-ui.table-cell>
                            <x-ui.table-cell>{{ number_format((int) $log->quantity) }}</x-ui.table-cell>
                            <x-ui.table-cell>
                                @if ($log->is_unlimited)
                                    <x-ui.badge variant="success">{{ __('Unlimited') }}</x-ui.badge>
                                @else
                                    <div class="space-y-1 text-sm">
                                        <p style="color: var(--theme-header-text-color);">{{ __('Before: :value', ['value' => number_format((int) $log->credits_before)]) }}</p>
                                        <p style="color: var(--theme-muted-text-color);">{{ __('After: :value', ['value' => number_format((int) $log->credits_after)]) }}</p>
                                    </div>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <p class="text-xs leading-6" style="color: var(--theme-muted-text-color);">
                                    {{ collect((array) $log->metadata)->map(fn ($value, $key) => $key.': '.(is_array($value) ? implode(', ', array_map('strval', $value)) : (is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))))->implode(' • ') ?: '—' }}
                                </p>
                            </x-ui.table-cell>
                            <x-ui.table-cell>{{ $log->created_at?->format('Y-m-d H:i:s') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="6" class="py-10">
                                <x-ui.empty icon="fa-light fa-coins" :title="__('No credit usage logged yet.')" :description="__('AI Studio usage entries will appear here after credit-consuming actions run.')" />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.datatable-shell>

        @if ($logs->hasPages())
            <div>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endcomponent
