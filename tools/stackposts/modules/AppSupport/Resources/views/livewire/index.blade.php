<div class="mx-auto max-w-[88rem] space-y-6">
    @if ($statusMessage)
        <x-ui.alert :title="__('Saved')" :description="$statusMessage" variant="success" dismissible />
    @endif

    @php
        $supportTotal = max(1, (int) ($summary['total'] ?? 0));
        $supportMetrics = [
            [
                'label' => __('Total'),
                'value' => number_format($summary['total']),
                'description' => __('All support tickets created from your account.'),
                'progress' => $summary['total'] > 0 ? 100 : 0,
                'icon' => 'fa-light fa-folders',
                'tone' => 'rgba(59, 130, 246, 0.10)',
                'color' => 'rgb(37, 99, 235)',
            ],
            [
                'label' => __('Open'),
                'value' => number_format($summary['open']),
                'description' => __('Tickets waiting for action or a final answer.'),
                'progress' => $summary['total'] > 0 ? (int) round(($summary['open'] / $supportTotal) * 100) : 0,
                'icon' => 'fa-light fa-door-open',
                'tone' => 'rgba(99, 102, 241, 0.10)',
                'color' => 'rgb(79, 70, 229)',
            ],
            [
                'label' => __('Resolved'),
                'value' => number_format($summary['resolved']),
                'description' => __('Tickets already completed by the support team.'),
                'progress' => $summary['total'] > 0 ? (int) round(($summary['resolved'] / $supportTotal) * 100) : 0,
                'icon' => 'fa-light fa-circle-check',
                'tone' => 'rgba(16, 185, 129, 0.10)',
                'color' => 'rgb(5, 150, 105)',
            ],
            [
                'label' => __('Unread'),
                'value' => number_format($summary['unread']),
                'description' => __('Threads with new admin replies not opened yet.'),
                'progress' => $summary['total'] > 0 ? (int) round(($summary['unread'] / $supportTotal) * 100) : 0,
                'icon' => 'fa-light fa-bell',
                'tone' => 'rgba(245, 158, 11, 0.12)',
                'color' => 'rgb(217, 119, 6)',
            ],
        ];
    @endphp

    <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
            <div class="space-y-6">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-accent);">
                        <i class="fa-light fa-headset"></i>
                        {{ __('User portal') }}
                    </span>
                    <div class="space-y-3">
                        <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Support') }}</h1>
                        <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">{{ __('Open support tickets, review replies from the admin team, and keep your conversation history in one place.') }}</p>
                    </div>
                </div>

                <x-ui.modal width="lg" :title="__('New support ticket')" :description="__('Describe your issue clearly so the team can respond with the right context.')">
                    <x-slot:trigger>
                        <x-ui.button type="button">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Create ticket') }}
                        </x-ui.button>
                    </x-slot:trigger>

                    <form id="portal-support-create-form" wire:submit="createTicket" class="space-y-5">
                        <x-ui.select wire:model.defer="cateId" name="cateId" :label="__('Category')" :error="$errors->first('cateId')">
                            <option value="">{{ __('Select category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </x-ui.select>

                        <x-ui.input wire:model.defer="title" name="title" :label="__('Subject')" :error="$errors->first('title')" :placeholder="__('Brief summary of the issue')" required />
                        <x-ui.textarea wire:model.defer="content" name="content" :label="__('Details')" :error="$errors->first('content')" rows="6" :placeholder="__('Explain what happened, what you expected, and any steps to reproduce the issue.')" />
                    </form>

                    <x-slot:footer>
                        <x-ui.button type="button" variant="outline" x-on:click="open = false">
                            <i class="fa-light fa-xmark"></i>
                            {{ __('Cancel') }}
                        </x-ui.button>
                        <x-ui.button type="submit" form="portal-support-create-form">
                            <i class="fa-light fa-paper-plane-top"></i>
                            {{ __('Create ticket') }}
                        </x-ui.button>
                    </x-slot:footer>
                </x-ui.modal>
            </div>

            <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Support desk') }}</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($summary['total']) }} {{ __('tickets') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Conversation history and ticket status in one place.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                        <i class="fa-light fa-comments-question-check text-lg"></i>
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Open') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($summary['open']) }}</p>
                    </div>
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Resolved') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($summary['resolved']) }}</p>
                    </div>
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Unread') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format($summary['unread']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($supportMetrics as $metric)
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
                <div class="mt-5 flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb), 0.16);">
                        <div class="h-full rounded-full" style="width: {{ $metric['progress'] }}%; background: {{ $metric['color'] }};"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $metric['progress'] }}%</span>
                </div>
            </x-ui.card>
        @endforeach
    </section>

    <form method="GET" action="{{ route('portal.support.index') }}" class="space-y-4">
        <x-ui.filter-panel fields-class="xl:grid-cols-[minmax(0,1.35fr)_240px_auto]">
            <x-slot:fields>
                <x-ui.input name="q" :label="__('Search')" :value="$filters['q']" :placeholder="__('Ticket id, subject, content...')" />
                <x-ui.select name="status" :label="__('Status')">
                    <option value="all" @selected($filters['status'] === 'all')>{{ __('All') }}</option>
                    <option value="1" @selected((string) $filters['status'] === '1')>{{ __('Open') }}</option>
                    <option value="2" @selected((string) $filters['status'] === '2')>{{ __('Resolved') }}</option>
                    <option value="0" @selected((string) $filters['status'] === '0')>{{ __('Closed') }}</option>
                </x-ui.select>
            </x-slot:fields>
            <x-slot:actions>
                <x-ui.button type="submit">
                    <i class="fa-light fa-filter"></i>
                    {{ __('Filter') }}
                </x-ui.button>
                <x-ui.button href="{{ route('portal.support.index') }}" variant="outline" wire:navigate>
                    <i class="fa-light fa-rotate-left"></i>
                    {{ __('Reset') }}
                </x-ui.button>
            </x-slot:actions>
            <x-slot:chips>
                @if ($filters['status'] !== 'all')
                    <x-ui.badge variant="neutral">{{ __('Status') }}: {{ $filters['status'] === '1' ? __('Open') : ($filters['status'] === '2' ? __('Resolved') : __('Closed')) }}</x-ui.badge>
                @endif
            </x-slot:chips>
        </x-ui.filter-panel>
    </form>

    <x-ui.datatable-shell
        :title="__('Your tickets')"
        :info="__('Support requests opened from this account.')"
        header-class="py-4"
        eyebrow-class="text-[10px] tracking-[0.2em]"
        title-class="mt-1 text-[1.15rem] tracking-[-0.025em]"
        description-class="mt-1 leading-6"
    >
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                    <i class="fa-light fa-ticket"></i>
                </span>
                <x-ui.badge variant="primary">{{ number_format($summary['total']) }} {{ __('tickets') }}</x-ui.badge>
                <x-ui.badge variant="success">{{ number_format($summary['open']) }} {{ __('open') }}</x-ui.badge>
                @if ($summary['unread'] > 0)
                    <x-ui.badge variant="danger">{{ number_format($summary['unread']) }} {{ __('new updates') }}</x-ui.badge>
                @endif
            </div>
        </x-slot:toolbar>

        <x-ui.table class="rounded-none border-0 shadow-none">
            <x-ui.table-head>
                <x-ui.table-cell head>{{ __('Ticket') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Queue') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Updated') }}</x-ui.table-cell>
                <x-ui.table-cell head>{{ __('Actions') }}</x-ui.table-cell>
            </x-ui.table-head>
            <x-ui.table-body>
                @forelse ($tickets as $ticket)
                    <x-ui.table-row>
                        <x-ui.table-cell>
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                                        <i class="fa-light fa-ticket-simple"></i>
                                    </span>
                                    <p class="text-[1rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $ticket->title }}</p>
                                    @if ($ticket->user_read)
                                        <x-ui.badge variant="danger" class="px-2 py-0.5 text-[10px] tracking-[0.16em]">{{ __('Unread') }}</x-ui.badge>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: var(--theme-muted-text-color);">
                                    <span class="font-mono">{{ $ticket->id_secure }}</span>
                                    <span class="inline-flex h-1 w-1 rounded-full" style="background-color: color-mix(in srgb, var(--theme-muted-text-color) 38%, transparent);"></span>
                                    <span>{{ $ticket->comments_count }} {{ __('replies') }}</span>
                                </div>

                                <p class="max-w-[42rem] text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $ticket->excerpt() }}</p>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: color-mix(in srgb, {{ $ticket->category?->color ?: 'var(--theme-accent)' }} 18%, var(--theme-border-color)); color: {{ $ticket->category?->color ?: 'var(--theme-accent)' }}; background-color: color-mix(in srgb, {{ $ticket->category?->color ?: 'var(--theme-accent)' }} 8%, transparent);">
                                    @if ($ticket->category?->icon)
                                        <i class="{{ $ticket->category->icon }}"></i>
                                    @endif
                                    {{ $ticket->category?->name ?: __('Unassigned') }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: color-mix(in srgb, {{ $ticket->type?->color ?: 'var(--theme-border-color)' }} 18%, var(--theme-border-color)); color: {{ $ticket->type?->color ?: 'var(--theme-muted-text-color)' }}; background-color: color-mix(in srgb, {{ $ticket->type?->color ?: 'var(--theme-border-color)' }} 7%, transparent);">
                                    @if ($ticket->type?->icon)
                                        <i class="{{ $ticket->type->icon }}"></i>
                                    @endif
                                    {{ $ticket->type?->name ?: __('No type') }}
                                </span>
                                <x-ui.badge :variant="$ticket->statusVariant()">{{ $ticket->statusLabel() }}</x-ui.badge>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="space-y-1">
                                <p class="text-sm font-medium" style="color: var(--theme-header-text-color);">{{ $ticket->changedAtFormatted() ?: __('N/A') }}</p>
                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ $ticket->changedFromHuman() ?: __('No activity') }}</p>
                            </div>
                        </x-ui.table-cell>
                        <x-ui.table-cell>
                            <div class="flex flex-wrap items-center gap-2 justify-end">
                                <x-ui.button href="{{ route('portal.support.show', $ticket) }}" variant="outline" size="sm" wire:navigate>
                                    <i class="fa-light fa-eye"></i>
                                    {{ __('View') }}
                                </x-ui.button>
                                @if ((int) $ticket->status === 1)
                                    <form wire:submit="resolveTicket({{ $ticket->id }})">
                                        <x-ui.button type="submit" size="sm">
                                            <i class="fa-light fa-circle-check"></i>
                                            {{ __('Resolve') }}
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </x-ui.table-cell>
                    </x-ui.table-row>
                @empty
                    <x-ui.table-row>
                        <x-ui.table-cell colspan="4" class="py-10"><x-ui.empty icon="fa-light fa-headset" :title="__('No support tickets found.')" :description="__('Create your first ticket to start a support thread with the admin team.')" /></x-ui.table-cell>
                    </x-ui.table-row>
                @endforelse
            </x-ui.table-body>
        </x-ui.table>

        @if ($tickets->hasPages())
            <div class="border-t px-6 py-4" style="border-color: var(--theme-border-color);">{{ $tickets->links() }}</div>
        @endif
    </x-ui.datatable-shell>
</div>
