@php
    $metrics = $metrics ?? [];
    $routes = $routes ?? [];
    $activity = collect($charts['activity'] ?? []);
    $topClients = collect($topClients ?? []);
    $topLinks = collect($topLinks ?? []);
    $maxActivity = max(1, (int) $activity->max('clicks'));
    $linkTotal = max(1, (int) ($metrics['links'] ?? 0));
    $activeRate = round(((int) ($metrics['active_links'] ?? 0) / $linkTotal) * 100);
    $blockedRate = round(((int) ($metrics['blocked_links'] ?? 0) / $linkTotal) * 100);

    $cards = [
        ['label' => __('Links'), 'value' => $metrics['links'] ?? 0, 'note' => __(':count active', ['count' => number_format((int) ($metrics['active_links'] ?? 0))]), 'icon' => 'fa-link-simple', 'color' => '#2563eb'],
        ['label' => __('Clients'), 'value' => $metrics['clients'] ?? 0, 'note' => __('Workspaces using short links'), 'icon' => 'fa-users', 'color' => '#0f766e'],
        ['label' => __('Clicks'), 'value' => $metrics['clicks'] ?? 0, 'note' => __(':count last 7 days', ['count' => number_format((int) ($metrics['clicks_7d'] ?? 0))]), 'icon' => 'fa-arrow-pointer', 'color' => '#7c3aed'],
        ['label' => __('Reports'), 'value' => $metrics['open_reports'] ?? 0, 'note' => __('Open abuse reports'), 'icon' => 'fa-flag', 'color' => '#f59e0b'],
    ];
@endphp

<x-ui.dashboard-module
    :eyebrow="__('Marketing Tools')"
    :title="__('Short Links platform health')"
    :description="__('Monitor creation, client adoption, click volume, moderation risk, and leading short-link campaigns.')"
>
    <x-slot:actions>
        @if (! empty($routes['analytics']))
            <x-ui.button :href="$routes['analytics']" size="sm" wire:navigate>{{ __('Open analytics') }}</x-ui.button>
        @endif
        @if (! empty($routes['manage']))
            <x-ui.button :href="$routes['manage']" variant="outline" size="sm" wire:navigate>{{ __('Manage links') }}</x-ui.button>
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

        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.52);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Last 7 days') }}</p>
                        <h3 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Click activity') }}</h3>
                    </div>
                    <span class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ number_format((int) $activity->sum('clicks')) }} {{ __('clicks') }}</span>
                </div>
                <div class="mt-5 grid h-40 grid-cols-7 items-end gap-2">
                    @foreach ($activity as $row)
                        @php
                            $clicks = (int) ($row['clicks'] ?? 0);
                            $height = max(4, round(($clicks / $maxActivity) * 100));
                        @endphp
                        <div class="flex h-full min-w-0 flex-col justify-end gap-2">
                            <div class="flex h-full items-end justify-center rounded-[0.75rem] px-2 py-2" style="background-color: rgba(var(--theme-border-color-rgb),0.08);">
                                <span class="w-full rounded-full" title="{{ $clicks }} {{ __('clicks') }}" style="height: {{ $height }}%; background: linear-gradient(180deg,#2563eb,#14b8a6);"></span>
                            </div>
                            <span class="truncate text-center text-[10px] font-semibold" style="color: var(--theme-muted-text-color);">{{ $row['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.58);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Health') }}</p>
                <div class="mt-4 space-y-4">
                    @foreach ([['label' => __('Active rate'), 'value' => $activeRate, 'color' => '#0f766e'], ['label' => __('Blocked rate'), 'value' => $blockedRate, 'color' => '#dc2626']] as $health)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $health['label'] }}</span>
                                <span style="color: var(--theme-muted-text-color);">{{ $health['value'] }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.22);">
                                <div class="h-full rounded-full" style="width: {{ max(3, min(100, (int) $health['value'])) }}%; background: {{ $health['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-2">
            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.56);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Top clients') }}</p>
                <div class="mt-3 space-y-2">
                    @forelse ($topClients as $client)
                        <div class="rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.54);">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $client['name'] }}</p>
                                <span class="text-xs font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $client['clicks']) }}</span>
                            </div>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $client['email'] }} - {{ number_format((int) $client['links']) }} {{ __('links') }}</p>
                        </div>
                    @empty
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No client activity yet.') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.52);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Top links') }}</p>
                <div class="mt-3 space-y-2">
                    @forelse ($topLinks as $link)
                        <div class="rounded-[0.85rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.54);">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                                <span class="text-xs font-semibold" style="color: var(--theme-accent);">{{ number_format((int) $link->clicks_count) }}</span>
                            </div>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->short_code }} - {{ str($link->status)->title() }}</p>
                        </div>
                    @empty
                        <p class="text-sm" style="color: var(--theme-muted-text-color);">{{ __('No short links yet.') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-ui.dashboard-module>
