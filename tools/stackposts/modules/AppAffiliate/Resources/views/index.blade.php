@component(theme_view('layouts.app', 'app'), ['title' => __('Affiliate')])
    @php
        $metricCards = [
            ['label' => __('Clicks'), 'value' => number_format($profile->clicks), 'description' => __('Referral link visits captured from your code.'), 'icon' => 'fa-light fa-arrow-pointer', 'tone' => 'rgba(59, 130, 246, 0.10)', 'color' => 'rgb(37, 99, 235)'],
            ['label' => __('Conversions'), 'value' => number_format($profile->conversions), 'description' => __('Successful commission events attributed to you.'), 'icon' => 'fa-light fa-badge-check', 'tone' => 'rgba(16, 185, 129, 0.10)', 'color' => 'rgb(5, 150, 105)'],
            ['label' => __('Available Balance'), 'value' => number_format((float) $profile->total_balance, 2), 'description' => __('Current balance available for withdrawal.'), 'icon' => 'fa-light fa-wallet', 'tone' => 'rgba(245, 158, 11, 0.12)', 'color' => 'rgb(217, 119, 6)'],
            ['label' => __('Approved Total'), 'value' => number_format((float) $profile->total_approved, 2), 'description' => __('Lifetime approved affiliate earnings.'), 'icon' => 'fa-light fa-sack-dollar', 'tone' => 'rgba(99, 102, 241, 0.10)', 'color' => 'rgb(79, 70, 229)'],
        ];
    @endphp

    <div class="space-y-8 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
        

        @if ($errors->has('affiliate_withdrawal'))
            <x-ui.alert :title="__('Unable to submit request')" :description="$errors->first('affiliate_withdrawal')" variant="danger" dismissible />
        @endif

        <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-accent);">
                        <i class="fa-light fa-hand-holding-dollar"></i>
                        {{ __('Affiliate program') }}
                    </span>
                    <div class="space-y-3">
                        <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Affiliate') }}</h1>
                        <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">{{ __('Share your referral link, track commissions, and request withdrawals from one place.') }}</p>
                    </div>
                </div>

                <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Affiliate wallet') }}</p>
                            <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((float) $profile->total_balance, 2) }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Available balance for withdrawal.') }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                            <i class="fa-light fa-wallet text-lg"></i>
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Clicks') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($profile->clicks) }}</p>
                        </div>
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Conversions') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($profile->conversions) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($metricCards as $metric)
                <x-ui.card class="overflow-hidden">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 space-y-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400 dark:text-slate-500">{{ $metric['label'] }}</p>
                            <p class="truncate text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $metric['value'] }}</p>
                            <p class="line-clamp-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $metric['description'] }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: {{ $metric['tone'] }}; color: {{ $metric['color'] }};">
                            <i class="{{ $metric['icon'] }} text-lg"></i>
                        </span>
                    </div>
                </x-ui.card>
            @endforeach
        </section>

        <div class="grid gap-5 xl:grid-cols-2 xl:items-stretch">
            <x-ui.card class="flex h-full flex-col space-y-5">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                        <i class="fa-light fa-link-simple"></i>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Referral assets') }}</h3>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Use this code or direct link anywhere you promote your referral offer.') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <x-ui.input :label="__('Referral code')" :value="$referralCode" readonly />
                    <x-ui.input :label="__('Referral link')" :value="$referralLink" readonly />
                </div>

                <div class="mt-auto rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.68); background-color: rgba(var(--theme-border-color-rgb), 0.03);">
                    <p class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                        <i class="fa-light fa-bullhorn" style="color: var(--theme-accent-color);"></i>
                        {{ __('Sharing note') }}
                    </p>
                    <p class="mt-2 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ __('Use the full link in social posts, bios, direct messages, and email campaigns so every referral visit is tracked correctly.') }}</p>
                </div>
            </x-ui.card>

            <x-ui.card class="flex h-full flex-col space-y-5">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(245, 158, 11, 0.12); color: rgb(217, 119, 6);">
                        <i class="fa-light fa-money-bill-transfer"></i>
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Request withdrawal') }}</h3>
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('Minimum withdrawal: :amount', ['amount' => number_format($minimumWithdrawal, 2)]) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.affiliate.withdraw') }}" class="flex h-full flex-col space-y-4">
                    @csrf
                    <x-ui.input name="amount" type="number" step="0.01" min="0.01" :label="__('Amount')" :value="old('amount')" :error="$errors->first('amount')" required />
                    <x-ui.input name="payment_method" :label="__('Payment method')" :value="old('payment_method')" :error="$errors->first('payment_method')" :placeholder="__('Bank transfer, PayPal, USDT...')" required />
                    <x-ui.textarea name="payment_details" :label="__('Payment details')" :error="$errors->first('payment_details')" rows="5" :placeholder="__('Account number, wallet address, account holder, and payout notes.')">{{ old('payment_details') }}</x-ui.textarea>
                    <div class="mt-auto pt-1">
                        <x-ui.button type="submit">
                            <i class="fa-light fa-paper-plane-top"></i>
                            {{ __('Submit request') }}
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="grid gap-5 xl:grid-cols-2 xl:items-stretch">
            <x-ui.datatable-shell class="h-full" :title="__('Recent commissions')" :info="__('Your latest affiliate commission records.')">
                <x-slot:toolbar>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl" style="background: rgba(16, 185, 129, 0.10); color: rgb(5, 150, 105);">
                        <i class="fa-light fa-badge-dollar"></i>
                    </span>
                </x-slot:toolbar>
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('User') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Payment') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Commission') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($commissions as $commission)
                            <x-ui.table-row>
                                <x-ui.table-cell><div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);"><i class="fa-light fa-user-check"></i></span><div class="space-y-1"><p class="font-medium" style="color: var(--theme-header-text-color);">{{ $commission->referredUser?->name ?: __('Unknown user') }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $commission->referredUser?->email ?: __('No email') }}</p></div></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm" style="color: var(--theme-header-text-color);">{{ $commission->paymentHistory?->transaction_id ?: __('N/A') }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ strtoupper((string) ($commission->paymentHistory?->currency ?: 'USD')) }} / {{ $commission->paymentHistory?->from ?: __('Unknown') }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((float) $commission->commission, 2) }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ number_format((float) $commission->commission_rate, 2) }}%</p></div></x-ui.table-cell>
                                <x-ui.table-cell><x-ui.badge :variant="$commission->statusVariant()">{{ $commission->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="4" class="py-10">
                                    <x-ui.empty icon="fa-light fa-badge-dollar" :title="__('No commissions yet.')" :description="__('Once referred users complete eligible payments, the records will show here.')" />
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>

            <x-ui.datatable-shell class="h-full" :title="__('Withdrawal history')" :info="__('Your latest withdrawal requests and payout status.')">
                <x-slot:toolbar>
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl" style="background: rgba(245, 158, 11, 0.12); color: rgb(217, 119, 6);">
                        <i class="fa-light fa-money-bill-transfer"></i>
                    </span>
                </x-slot:toolbar>
                <x-ui.table class="rounded-none border-0 shadow-none">
                    <x-ui.table-head>
                        <x-ui.table-cell head>{{ __('Request') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Method') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                        <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    </x-ui.table-head>
                    <x-ui.table-body>
                        @forelse ($withdrawals as $withdrawal)
                            <x-ui.table-row>
                                <x-ui.table-cell><div class="flex items-start gap-3"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(245, 158, 11, 0.12); color: rgb(217, 119, 6);"><i class="fa-light fa-receipt"></i></span><div class="space-y-1"><p class="font-medium" style="color: var(--theme-header-text-color);">{{ $withdrawal->id_secure }}</p><p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $withdrawal->createdAtFormatted() ?: __('N/A') }}</p></div></div></x-ui.table-cell>
                                <x-ui.table-cell><div class="space-y-1"><p class="text-sm" style="color: var(--theme-header-text-color);">{{ $withdrawal->payment_method }}</p><p class="line-clamp-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $withdrawal->payment_details }}</p></div></x-ui.table-cell>
                                <x-ui.table-cell><span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((float) $withdrawal->amount, 2) }}</span></x-ui.table-cell>
                                <x-ui.table-cell><x-ui.badge :variant="$withdrawal->statusVariant()">{{ $withdrawal->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="4" class="py-10">
                                    <x-ui.empty icon="fa-light fa-money-bill-transfer" :title="__('No withdrawals yet.')" :description="__('Your submitted payout requests will appear here.')" />
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.datatable-shell>
        </div>
    </div>
@endcomponent
