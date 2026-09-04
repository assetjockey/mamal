<div>
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Help') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Support') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-1.5">
                    @if(! $creating)
                        <button type="button" wire:click="startTicket" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 transition dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-300 dark:hover:text-white">
                            <flux:icon.plus class="size-4 relative" />
                            <span class="relative">{{ __('New Ticket') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Hero Banner — Support Desk                  --}}
            {{-- ========================================== --}}
            <div class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40 bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(79,70,229,0.22),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(245,158,11,0.14),transparent)]">
                <div class="absolute -top-24 -right-16 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none"></div>
                <div class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-indigo-400/60 to-transparent"></div>

                <div class="relative px-6 md:px-8 py-10 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.lifebuoy class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl md:text-2xl font-extrabold text-white leading-tight tracking-tight">{{ __('Help & Support') }}</h1>
                            <p class="text-xs text-zinc-400 mt-1 max-w-md">
                                {{ __('Open a ticket and our team will get back to you. Track every conversation right here.') }}
                            </p>
                        </div>
                    </div>

                    @unless($creating)
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <div class="relative flex-1 md:flex-none md:w-64">
                                <flux:icon.magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-500" />
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search tickets...') }}" class="w-full pl-9 pr-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-hidden focus:border-indigo-400/50 focus:ring-2 focus:ring-indigo-400/20 backdrop-blur-sm transition" />
                            </div>
                        </div>
                    @endunless
                </div>
            </div>

            @if($creating)
                @include('livewire.user.support.partials.create-form')
            @else
                {{-- Stats --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-semibold text-zinc-500 mb-1">{{ __('All Tickets') }}</div>
                                <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $totalCount }}</div>
                            </div>
                            <flux:icon.inbox-stack class="size-9 text-zinc-400" />
                        </div>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-semibold text-zinc-500 mb-1">{{ __('Open') }}</div>
                                <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $openCount }}</div>
                            </div>
                            <flux:icon.folder-open class="size-9 text-indigo-500" />
                        </div>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-semibold text-zinc-500 mb-1">{{ __('In Progress') }}</div>
                                <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $inProgressCount }}</div>
                            </div>
                            <flux:icon.clock class="size-9 text-amber-500" />
                        </div>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-semibold text-zinc-500 mb-1">{{ __('Resolved') }}</div>
                                <div class="text-2xl font-extrabold text-zinc-900 dark:text-white">{{ $resolvedCount }}</div>
                            </div>
                            <flux:icon.check-badge class="size-9 text-emerald-500" />
                        </div>
                    </div>
                </div>

                {{-- Status filter pills --}}
                <div class="flex flex-wrap items-center gap-2 mb-5">
                    @php
                        $filters = [
                            '' => __('All'),
                            'open' => __('Open'),
                            'in_progress' => __('In Progress'),
                            'resolved' => __('Resolved'),
                            'closed' => __('Closed'),
                        ];
                    @endphp
                    @foreach($filters as $value => $label)
                        <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                            @class([
                                'px-3 py-1.5 rounded-full text-xs font-medium border transition',
                                'bg-indigo-600 text-white border-indigo-600' => $statusFilter === $value,
                                'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300 dark:border-white/8' => $statusFilter !== $value,
                            ])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Ticket list --}}
                @if($tickets->isEmpty())
                    <div class="relative overflow-hidden rounded-3xl border border-dashed border-zinc-200 dark:border-white/8 p-16 text-center">
                        <div class="absolute inset-0 pointer-events-none opacity-30" style="background-image: radial-gradient(circle at 30% 20%, rgba(79,70,229,0.15), transparent 40%), radial-gradient(circle at 70% 80%, rgba(245,158,11,0.10), transparent 40%);"></div>
                        <div class="relative">
                            <div class="inline-flex w-16 h-16 rounded-2xl p-px mb-4 shadow-sm shadow-indigo-500/25" style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                                <div class="w-full h-full rounded-[15px] bg-white dark:bg-(--default-element-bg-color) flex items-center justify-center">
                                    <flux:icon.chat-bubble-left-right class="size-8 text-indigo-500" />
                                </div>
                            </div>
                            <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-100 mb-1">
                                {{ ($search !== '' || $statusFilter !== '') ? __('No tickets match your filters') : __('No tickets yet') }}
                            </h2>
                            <p class="text-sm text-zinc-500 max-w-md mx-auto mb-6">{{ __('Need a hand? Open a ticket and our support team will reply as soon as possible.') }}</p>
                            <flux:button type="button" wire:click="startTicket" variant="primary" icon="plus" class="hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{ __('Create a Ticket') }}</flux:button>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden divide-y divide-zinc-100 dark:divide-white/6">
                        @foreach($tickets as $ticket)
                            <a href="{{ route('user.support.view', $ticket->ticket_id) }}" wire:navigate wire:key="ticket-{{ $ticket->id }}"
                               class="flex items-center gap-4 px-5 py-4 hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                                <div class="shrink-0">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-zinc-400 border border-zinc-400 dark:bg-white dark:text-zinc-900 dark:border-white/8">
                                        <flux:icon.ticket class="size-5" />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $ticket->subject }}</span>
                                        <x-support.priority-badge :priority="$ticket->priority" />
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-zinc-400">
                                        <span class="font-mono">{{ $ticket->ticket_id }}</span>
                                        <span>&middot;</span>
                                        <span class="capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
                                        <span>&middot;</span>
                                        <span>{{ $ticket->created_at?->format('M j, Y') }}</span>
                                        <span>&middot;</span>
                                        <span class="inline-flex items-center gap-1"><flux:icon.chat-bubble-left-ellipsis class="size-3" /> {{ $ticket->messages_count }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center gap-3">
                                    <x-support.status-badge :status="$ticket->status" />
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 dark:text-neutral-600" />
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $tickets->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
