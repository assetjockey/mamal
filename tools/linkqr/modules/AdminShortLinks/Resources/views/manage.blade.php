<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-5">
        <section class="overflow-hidden rounded-[1.35rem] border shadow-[0_30px_90px_-70px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-5 border-b p-5 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-shield-check"></i>
                        {{ __('Admin management') }}
                    </span>
                    <h1 class="mt-4 text-[2rem] font-semibold leading-tight tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ __('Manage short links') }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Review client links, moderate reported destinations, and keep redirect quality clean.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-ui.button :href="route('admin.short-links.index')" variant="outline" wire:navigate>
                        <i class="fa-light fa-chart-line"></i>
                        {{ __('Analytics') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="grid gap-3 border-b p-5 sm:grid-cols-2 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                @foreach ([
                    ['label' => __('Links'), 'value' => $metrics['links'], 'icon' => 'fa-link-simple', 'color' => '#2563eb'],
                    ['label' => __('Active'), 'value' => $metrics['active'], 'icon' => 'fa-circle-check', 'color' => '#059669'],
                    ['label' => __('Blocked'), 'value' => $metrics['blocked'], 'icon' => 'fa-ban', 'color' => '#dc2626'],
                    ['label' => __('Reported'), 'value' => $metrics['reported'], 'icon' => 'fa-flag', 'color' => '#f59e0b'],
                ] as $stat)
                    <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                <p class="mt-2 text-3xl font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $stat['value']) }}</p>
                            </div>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};"><i class="fa-light {{ $stat['icon'] }}"></i></span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-3 border-b p-5 lg:flex-row lg:items-center lg:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="grid gap-3 sm:grid-cols-[minmax(16rem,1fr)_12rem_8rem] lg:w-[48rem]">
                    <div class="relative">
                        <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                        <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search links') }}" class="h-11 w-full rounded-[0.8rem] border pl-9 pr-3 text-sm outline-none" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                    </div>
                    <x-ui.select wire:model.live="status" :label="false">
                        <option value="all">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="paused">{{ __('Paused') }}</option>
                        <option value="blocked">{{ __('Blocked') }}</option>
                        <option value="reported">{{ __('Reported') }}</option>
                    </x-ui.select>
                    <x-ui.select wire:model.live="perPage" :label="false">
                        <option value="15">15 / p</option>
                        <option value="25">25 / p</option>
                        <option value="50">50 / p</option>
                    </x-ui.select>
                </div>
            </div>

            <div class="hidden grid-cols-[minmax(0,1fr)_12rem_10rem_16rem] border-b px-5 py-3 text-xs font-semibold uppercase tracking-[0.14em] lg:grid" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">
                <div>{{ __('Short link') }}</div>
                <div>{{ __('Client') }}</div>
                <div>{{ __('Clicks') }}</div>
                <div class="text-right">{{ __('Actions') }}</div>
            </div>

            <div class="divide-y" style="--tw-divide-color: rgba(var(--theme-border-color-rgb),0.52);">
                @forelse ($links as $link)
                    @php($owner = $owners[(int) $link->owner_user_id] ?? null)
                    <div class="grid gap-4 p-5 lg:grid-cols-[minmax(0,1fr)_12rem_10rem_16rem] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                                <x-ui.badge :variant="$link->moderation_status === 'blocked' ? 'danger' : ($link->moderation_status === 'pending' ? 'warning' : 'success')">{{ str($link->moderation_status ?: 'approved')->title() }}</x-ui.badge>
                                <x-ui.badge :variant="$link->status === 'active' ? 'success' : 'neutral'">{{ str($link->status)->title() }}</x-ui.badge>
                            </div>
                            <p class="mt-2 break-all font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $link->shortUrl() }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->destination_url }}</p>
                        </div>
                        <div class="min-w-0 text-sm">
                            <p class="truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $owner?->name ?: __('Unknown') }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $owner?->email }}</p>
                        </div>
                        <div class="text-sm" style="color: var(--theme-muted-text-color);">
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $link->clicks_count) }}</span>
                            {{ __('clicks') }}
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                            @if ($link->moderation_status === 'blocked')
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="restore({{ $link->id }})">
                                    <i class="fa-light fa-rotate-left"></i>
                                    {{ __('Restore') }}
                                </x-ui.button>
                            @else
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="approve({{ $link->id }})">
                                    <i class="fa-light fa-check"></i>
                                    {{ __('Approve') }}
                                </x-ui.button>
                                <x-ui.button type="button" size="sm" variant="outline" wire:click="block({{ $link->id }})" class="hover:!border-red-200 hover:!text-red-600">
                                    <i class="fa-light fa-ban"></i>
                                    {{ __('Block') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm" style="color: var(--theme-muted-text-color);">{{ __('No short links found.') }}</div>
                @endforelse
            </div>

            @if ($links->hasPages())
                <div class="border-t p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    {{ $links->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
