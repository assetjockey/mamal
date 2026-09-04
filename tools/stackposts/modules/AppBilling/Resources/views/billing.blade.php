@component(theme_view('layouts.app', 'app'), ['title' => __('Billing')])
    @php
        $billingCycle = $plan ? match ((int) $plan->type) {
            2 => __('Yearly'),
            3 => __('Lifetime'),
            default => __('Monthly'),
        } : __('No billing cycle');
        $nextBillingCycle = $user?->nextPlan ? match ((int) $user->nextPlan->type) {
            2 => __('Yearly'),
            3 => __('Lifetime'),
            default => __('Monthly'),
        } : null;
        $planStatusLabel = $user?->isInPlanTrial()
            ? __('Trial active')
            : ($user?->hasActivePlan() ? __('Active') : __('Inactive'));
        $planStatusVariant = $user?->hasActivePlan() ? 'success' : 'neutral';
        $creditTotal = $creditSummary['unlimited']
            ? null
            : max(0, (int) ($creditSummary['used'] ?? 0) + (int) ($creditSummary['remaining'] ?? 0));
        $creditUsedPercent = (! $creditSummary['unlimited'] && $creditTotal > 0)
            ? min(100, max(0, round(((int) ($creditSummary['used'] ?? 0) / $creditTotal) * 100)))
            : 0;
        $creditRemainingPercent = $creditSummary['unlimited'] ? 100 : max(0, 100 - $creditUsedPercent);
        $billingMetrics = [
            [
                'label' => __('Subscription'),
                'value' => $latestSubscription?->statusLabel() ?? __('Inactive'),
                'detail' => $latestSubscription?->source ?: __('No payment source'),
                'icon' => 'fa-light fa-arrows-rotate',
                'tone' => 'rgba(16, 185, 129, 0.10)',
                'color' => 'rgb(5, 150, 105)',
            ],
            [
                'label' => __('Credits remaining'),
                'value' => $creditSummary['unlimited'] ? __('Unlimited') : number_format((int) ($creditSummary['remaining'] ?? 0)),
                'detail' => $creditSummary['unlimited'] ? __('No usage cap on this plan.') : __('Plan and top-up balance available.'),
                'icon' => 'fa-light fa-coins',
                'tone' => 'rgba(245, 158, 11, 0.12)',
                'color' => 'rgb(217, 119, 6)',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-credit-card"></i>
                            {{ __('Billing workspace') }}
                        </span>
                        <div class="space-y-3">
                            <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Billing') }}</h1>
                            <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                                {{ __('Review your active plan, credit coverage, subscription status, and recent payment activity from one workspace.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if (credit_topup_service()->canUserBuyTopup($user))
                            <x-ui.button href="{{ route('portal.credits') }}" wire:navigate>
                                <i class="fa-light fa-coins"></i>
                                {{ __('Buy more credits') }}
                            </x-ui.button>
                        @endif
                        <x-ui.button href="{{ route('portal.invoices') }}" variant="outline" wire:navigate>
                            <i class="fa-light fa-file-invoice"></i>
                            {{ __('Open invoices') }}
                        </x-ui.button>
                    </div>
                </div>

                <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Current plan') }}</p>
                            <h2 class="mt-3 truncate text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $plan?->name ?? __('No plan assigned') }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $billingCycle }} · {{ $planStatusLabel }}</p>
                        </div>
                        <x-ui.badge :variant="$planStatusVariant">{{ $planStatusLabel }}</x-ui.badge>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Invoices paid') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($summary['paidInvoices']) }}</p>
                        </div>
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Lifetime value') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ ($plan?->currency_symbol ?? '$').' '.number_format($summary['lifetimeValue'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        @if ($activeRecurringSubscription)
            <x-ui.card class="space-y-3" style="border-color: rgba(var(--theme-accent-rgb), 0.18); background: rgba(var(--theme-accent-rgb), 0.045);">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border bg-white" style="border-color: rgba(var(--theme-accent-rgb), 0.18); color: var(--theme-accent-color);">
                        <i class="fa-light fa-arrows-rotate"></i>
                    </span>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                            {{ __('Active recurring subscription') }}
                        </p>
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ __('Your account is currently on an auto-renewing subscription for :plan. Repurchasing the same recurring plan is blocked. To change plan, choose a different plan from pricing. The old recurring agreement will be cancelled after the new one is confirmed.', ['plan' => $activeRecurringSubscription->plan?->name ?: __('your current plan')]) }}
                        </p>
                    </div>
                </div>
            </x-ui.card>
        @endif

        @if ($user?->isInPlanTrial())
            <x-ui.card class="space-y-3" style="border-color: rgba(245,158,11, 0.24); background: rgba(245,158,11, 0.06);">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border" style="border-color: rgba(245,158,11, 0.22); background: rgba(245,158,11, 0.10); color: var(--theme-warning-color);">
                        <i class="fa-light fa-hourglass-clock"></i>
                    </span>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                            {{ __('Trial active') }}
                        </p>
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ __('Your :plan trial is active until :date. During this window you keep access to the plan features before the normal billing cycle takes over for future renewals or plan changes.', ['plan' => $plan?->name ?: __('current plan'), 'date' => $user?->trialEndsAt()?->format('Y-m-d H:i') ?: __('the trial end date')]) }}
                        </p>
                    </div>
                </div>
            </x-ui.card>
        @endif

        @if ($user?->nextPlan)
            <x-ui.card class="space-y-3">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft); color: var(--theme-accent-color);">
                        <i class="fa-light fa-calendar-clock"></i>
                    </span>
                    <div class="space-y-2">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">
                            {{ __('Scheduled next plan') }}
                        </p>
                        <p class="text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ __('Your account is scheduled to switch to :plan (:cycle) when the current billing period ends on :date.', ['plan' => $user->nextPlan->name, 'cycle' => $nextBillingCycle, 'date' => $user->plan_expires_at?->format('Y-m-d') ?: __('the next billing date')]) }}
                        </p>
                    </div>
                </div>
            </x-ui.card>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($billingMetrics as $metric)
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
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.05fr_0.95fr]">
            <x-ui.card class="space-y-4">
                <div>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Plan overview') }}</p>
                            <h3 class="mt-2 flex items-center gap-2 text-[1.3rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">
                                <i class="fa-light fa-crown text-[var(--theme-accent)]"></i>
                                {{ $plan?->name ?? __('No plan assigned') }}
                            </h3>
                        </div>
                        <x-ui.badge :variant="$planStatusVariant">{{ $planStatusLabel }}</x-ui.badge>
                    </div>
                    <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">
                        {{ $plan ? __('This is the plan currently attached to your account.') : __('Ask an administrator to assign or activate a billing plan for this account.') }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-chart-surface);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Price') }}</p>
                        <p class="mt-3 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $plan ? $plan->currency_symbol.' '.number_format((float) $plan->price, 2) : __('N/A') }}</p>
                    </div>
                    <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-chart-surface);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Plan status') }}</p>
                        <p class="mt-3 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $planStatusLabel }}</p>
                    </div>
                    <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-chart-surface);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ $user?->isInPlanTrial() ? __('Trial ends') : __('Expiry') }}</p>
                        <p class="mt-3 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ ($user?->isInPlanTrial() ? $user?->trialEndsAt() : $user?->plan_expires_at)?->format('Y-m-d') ?? __('No expiry') }}</p>
                    </div>
                    <div class="rounded-[1rem] border p-4 md:col-span-3" style="border-color: var(--theme-border-color); background: var(--theme-chart-surface);">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Credits Usage') }}</p>
                                <p class="text-lg font-semibold" style="color: var(--theme-header-text-color);">
                                @if ($creditSummary['unlimited'])
                                    {{ __('Unlimited') }}
                                @else
                                    {{ number_format((int) ($creditSummary['remaining'] ?? 0)) }} {{ __('credits remaining') }}
                                @endif
                            </p>
                            </div>

                            <p class="text-sm" style="color: var(--theme-muted-text-color);">
                                @if ($creditSummary['unlimited'])
                                    {{ __('This plan does not enforce a credit cap.') }}
                                @else
                                    {{ __('Plan left') }}: {{ number_format((int) ($creditSummary['plan_remaining'] ?? 0)) }}
                                    /
                                    {{ __('Top-up left') }}: {{ number_format((int) ($creditSummary['topup_remaining'] ?? 0)) }}
                                @endif
                            </p>
                        </div>
                        <div class="mt-4 h-2.5 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.45);">
                            <div
                                class="h-full rounded-full transition-all"
                                style="width: {{ $creditRemainingPercent }}%; background: {{ $creditSummary['low_balance'] ?? false ? 'rgb(245, 158, 11)' : 'linear-gradient(90deg, var(--theme-accent-color), color-mix(in srgb, var(--theme-accent-color) 72%, #22c55e))' }};"
                            ></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs" style="color: var(--theme-muted-text-color);">
                            <span>{{ $creditSummary['unlimited'] ? __('Unlimited access') : __('Remaining balance') }}</span>
                            <span>{{ $creditSummary['unlimited'] ? __('No cap') : $creditRemainingPercent.'%' }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Latest subscription') }}</p>
                        <h3 class="mt-2 text-[1.3rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ $latestSubscription?->subscription_id ?: __('No active subscription') }}</h3>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                        <i class="fa-light fa-receipt text-lg"></i>
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4 rounded-[0.95rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <span class="inline-flex items-center gap-2" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-credit-card"></i>{{ __('Gateway') }}</span>
                        <span class="font-medium" style="color: var(--theme-header-text-color);">{{ $latestSubscription?->source ?: __('N/A') }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-[0.95rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <span class="inline-flex items-center gap-2" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-plug"></i>{{ __('Service') }}</span>
                        <span class="font-medium" style="color: var(--theme-header-text-color);">{{ $latestSubscription?->service ?: __('N/A') }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-[0.95rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <span class="inline-flex items-center gap-2" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-signal-bars"></i>{{ __('Status') }}</span>
                        @if ($latestSubscription)
                            <x-ui.badge :variant="$latestSubscription->statusVariant()">{{ $latestSubscription->statusLabel() }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ __('Inactive') }}</x-ui.badge>
                        @endif
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-[0.95rem] border p-4" style="border-color: var(--theme-border-color); background: var(--theme-surface-soft);">
                        <span class="inline-flex items-center gap-2" style="color: var(--theme-muted-text-color);"><i class="fa-light fa-calendar-days"></i>{{ __('Started') }}</span>
                        <span class="font-medium" style="color: var(--theme-header-text-color);">{{ $latestSubscription?->createdAtFormatted('Y-m-d') ?? __('N/A') }}</span>
                    </div>
                </div>

                @if ($latestSubscription?->canBeCancelledByUser())
                    <div class="border-t pt-4" style="border-color: var(--theme-border-color);">
                        @livewire(\Modules\AppBilling\Livewire\CancelRecurringButton::class, ['subscriptionId' => $latestSubscription->id], key('cancel-recurring-'.$latestSubscription->id))
                    </div>
                @endif
            </x-ui.card>
        </div>

        <x-ui.datatable-shell :title="__('Recent payments')" :info="__('The latest payment attempts and successful charges tied to your account.')">
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Invoice') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Plan') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Gateway') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Created') }}</x-ui.table-cell>
                </x-ui.table-head>
                <x-ui.table-body>
                    @forelse ($recentPayments as $payment)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-receipt"></i>
                                    </span>
                                    <div class="min-w-0 space-y-1">
                                        <p class="truncate font-semibold uppercase" style="color: var(--theme-header-text-color);">{{ $payment->id_secure ?: __('N/A') }}</p>
                                        <p class="truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $payment->transaction_id }}</p>
                                    </div>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell>{{ $payment->plan?->name ?: __('N/A') }}</x-ui.table-cell>
                            <x-ui.table-cell>{{ $payment->from ?: __('N/A') }}</x-ui.table-cell>
                            <x-ui.table-cell>{{ ($payment->currency ?: 'USD').' '.number_format((float) $payment->amount, 2) }}</x-ui.table-cell>
                            <x-ui.table-cell><x-ui.badge :variant="$payment->statusVariant()">{{ $payment->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            <x-ui.table-cell>{{ $payment->createdAtFormatted('Y-m-d H:i') ?: __('N/A') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="6" class="py-10 text-center text-zinc-500 dark:text-zinc-400">{{ __('No payment records found yet.') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.datatable-shell>
    </div>
@endcomponent
