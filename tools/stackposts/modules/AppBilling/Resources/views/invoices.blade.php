@component(theme_view('layouts.app', 'app'), ['title' => __('Invoices')])
    @php
        $invoiceRows = $invoices->getCollection();
        $paidRows = $invoiceRows->where('status', 1);
        $pageTotal = (float) $paidRows->sum('amount');
        $latestInvoice = $invoiceRows->first();
        $currencySymbol = $latestInvoice?->currency ?: 'USD';
    @endphp

    <div class="space-y-6">
        <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-muted-text-color);">
                            <i class="fa-light fa-file-invoice-dollar"></i>
                            {{ __('Invoice workspace') }}
                        </span>
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Invoices') }}</h1>
                                <x-ui.badge variant="neutral">{{ trans_choice(':count record|:count records', $invoices->total(), ['count' => number_format($invoices->total())]) }}</x-ui.badge>
                            </div>
                            <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                                {{ __('Browse invoice references, transaction ids, payment amounts, billing statuses, and PDF downloads from one place.') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button href="{{ route('portal.billing') }}" variant="outline" wire:navigate>
                            <i class="fa-light fa-arrow-left"></i>
                            {{ __('Back to billing') }}
                        </x-ui.button>
                    </div>
                </div>

                <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Latest invoice') }}</p>
                            <h2 class="mt-3 text-xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $latestInvoice?->id_secure ?: __('No invoice yet') }}</h2>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $latestInvoice?->createdAtFormatted('Y-m-d H:i') ?: __('No billing activity recorded') }}</p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                            <i class="fa-light fa-receipt text-lg"></i>
                        </span>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Paid on page') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($paidRows->count()) }}</p>
                        </div>
                        <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Page total') }}</p>
                            <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $currencySymbol.' '.number_format($pageTotal, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.datatable-shell :title="__('Invoice history')" :info="__('All recorded payment history rows belonging to the current account.')">
            <x-slot:toolbar>
                <x-ui.button href="{{ route('portal.billing') }}" variant="outline" wire:navigate>
                    <i class="fa-light fa-arrow-left"></i>
                    {{ __('Back to billing') }}
                </x-ui.button>
            </x-slot:toolbar>
            <x-ui.table class="rounded-none border-0 shadow-none">
                <x-ui.table-head>
                    <x-ui.table-cell head>{{ __('Invoice') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Transaction') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Plan') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Gateway') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Amount') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Status') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Created') }}</x-ui.table-cell>
                    <x-ui.table-cell head>{{ __('Actions') }}</x-ui.table-cell>
                </x-ui.table-head>
                <x-ui.table-body>
                    @forelse ($invoices as $invoice)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-receipt"></i>
                                    </span>
                                    <div class="min-w-0 space-y-1">
                                        <p class="truncate font-semibold uppercase" style="color: var(--theme-header-text-color);">{{ $invoice->id_secure ?: __('N/A') }}</p>
                                        <p class="text-xs" style="color: var(--theme-muted-text-color);">#{{ $invoice->id }}</p>
                                    </div>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell><span class="font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $invoice->transaction_id ?: __('N/A') }}</span></x-ui.table-cell>
                            <x-ui.table-cell>{{ $invoice->plan?->name ?: __('N/A') }}</x-ui.table-cell>
                            <x-ui.table-cell>{{ $invoice->from ?: __('N/A') }}</x-ui.table-cell>
                            <x-ui.table-cell>{{ ($invoice->currency ?: 'USD').' '.number_format((float) $invoice->amount, 2) }}</x-ui.table-cell>
                            <x-ui.table-cell><x-ui.badge :variant="$invoice->statusVariant()">{{ $invoice->statusLabel() }}</x-ui.badge></x-ui.table-cell>
                            <x-ui.table-cell>{{ $invoice->createdAtFormatted('Y-m-d H:i') ?: __('N/A') }}</x-ui.table-cell>
                            <x-ui.table-cell>
                                <x-ui.button href="{{ route('portal.invoices.download', $invoice) }}" variant="outline" size="sm">
                                    <i class="fa-light fa-download"></i>
                                    {{ __('Download PDF') }}
                                </x-ui.button>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row>
                            <x-ui.table-cell colspan="8" class="py-10">
                                <x-ui.empty icon="fa-light fa-file-invoice" :title="__('No invoices recorded yet.')" :description="__('Payment records and downloadable invoices will appear here after checkout activity.')" />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>

            @if ($invoices->hasPages())
                <div class="border-t px-6 py-4" style="border-color: var(--theme-border-color);">{{ $invoices->links() }}</div>
            @endif
        </x-ui.datatable-shell>
    </div>
@endcomponent
