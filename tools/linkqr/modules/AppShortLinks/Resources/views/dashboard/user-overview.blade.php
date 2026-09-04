@php
    $metrics = $metrics ?? [];
    $routes = $routes ?? [];
    $limits = $limits ?? [];
    $activity = collect($charts['activity'] ?? []);
    $topLinks = collect($topLinks ?? []);
    $maxActivity = max(1, (int) $activity->max('clicks'));
    $limitLabel = fn ($limit) => ($limit['limit'] ?? null) === null
        ? number_format((int) ($limit['used'] ?? 0)).' / '.__('Unlimited')
        : number_format((int) ($limit['used'] ?? 0)).' / '.number_format((int) $limit['limit']);
    $limitPercent = fn ($limit) => ($limit['limit'] ?? null) === null || (int) $limit['limit'] <= 0
        ? 0
        : min(100, (int) round(((int) ($limit['used'] ?? 0) / (int) $limit['limit']) * 100));

    $cards = [
        ['label' => __('Short links'), 'value' => $metrics['links'] ?? 0, 'note' => __(':count active', ['count' => number_format((int) ($metrics['active_links'] ?? 0))]), 'icon' => 'fa-link-simple', 'color' => '#2563eb'],
        ['label' => __('Clicks'), 'value' => $metrics['clicks'] ?? 0, 'note' => __(':count last 7 days', ['count' => number_format((int) ($metrics['clicks_7d'] ?? 0))]), 'icon' => 'fa-arrow-pointer', 'color' => '#7c3aed'],
        ['label' => __('Blocked'), 'value' => $metrics['blocked_links'] ?? 0, 'note' => __('Moderation status'), 'icon' => 'fa-ban', 'color' => '#dc2626'],
        ['label' => __('Monthly clicks'), 'value' => $metrics['monthly_clicks'] ?? 0, 'note' => __('Current billing cycle'), 'icon' => 'fa-calendar-lines', 'color' => '#0f766e'],
    ];
@endphp

<x-ui.dashboard-module
    class="overflow-hidden rounded-[1.75rem] border p-5 shadow-[0_30px_90px_-62px_rgba(15,23,42,0.5)] sm:p-6"
    style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 0% 8%, rgba(37,99,235,0.14), transparent 25%),
        radial-gradient(circle at 42% 0%, rgba(20,184,166,0.12), transparent 26%),
        radial-gradient(circle at 100% 16%, rgba(124,58,237,0.12), transparent 26%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.94), rgba(var(--theme-surface-soft-rgb,248,250,252),0.86));"
    :eyebrow="__('Short Links')"
    :title="__('Link performance dashboard')"
    :description="__('Track branded links, monthly quota usage, click activity, and your top campaigns from one workspace snapshot.')"
>
    <x-slot:actions>
        @if (! empty($routes['links']))
            <x-ui.button :href="$routes['links']" size="sm" wire:navigate>
                <i class="fa-light fa-link-simple"></i>
                {{ __('Links') }}
            </x-ui.button>
        @endif
        @if (! empty($routes['analytics']))
            <x-ui.button :href="$routes['analytics']" variant="outline" size="sm" wire:navigate>{{ __('Analytics') }}</x-ui.button>
        @endif
    </x-slot:actions>

    <div class="space-y-4">
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

        <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(37,99,235,0.18); background:
            linear-gradient(135deg, rgba(37,99,235,0.10), rgba(20,184,166,0.07)),
            rgba(var(--theme-surface-base-rgb,255,255,255),0.86);">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)]">
                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Click activity') }}</p>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Last 7 days') }}</p>
                        </div>
                        <span class="text-xs font-semibold" style="color: var(--theme-muted-text-color);">{{ number_format((int) $activity->sum('clicks')) }} {{ __('clicks') }}</span>
                    </div>

                    <div class="mt-6 grid h-52 grid-cols-7 items-end gap-3 rounded-[1rem] border px-4 pb-3 pt-5" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.70);">
                        @foreach ($activity as $point)
                            @php
                                $height = max(8, (int) round(((int) ($point['clicks'] ?? 0) / $maxActivity) * 100));
                            @endphp
                            <div class="flex h-full min-w-0 flex-col justify-end gap-2">
                                <div class="flex h-full items-end justify-center">
                                    <span class="w-6 rounded-t-full" style="height: {{ $height }}%; background: linear-gradient(180deg, #2563eb, #14b8a6);"></span>
                                </div>
                                <p class="truncate text-center text-[11px] font-medium" style="color: var(--theme-muted-text-color);">{{ $point['label'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid content-start gap-3">
                    @foreach ([['label' => __('Links quota'), 'limit' => $limits['links'] ?? ['used' => 0, 'limit' => null], 'color' => '#2563eb'], ['label' => __('Monthly clicks'), 'limit' => $limits['monthly_clicks'] ?? ['used' => 0, 'limit' => null], 'color' => '#7c3aed']] as $quota)
                        @php($percent = $limitPercent($quota['limit']))
                        <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $quota['label'] }}</p>
                                <p class="text-sm font-semibold" style="color: var(--theme-muted-text-color);">{{ $limitLabel($quota['limit']) }}</p>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full" style="background: var(--theme-surface-subtle);">
                                <div class="h-full rounded-full" style="width: {{ $percent === 0 ? 100 : $percent }}%; background: {{ $quota['limit']['limit'] ?? null ? $quota['color'] : 'linear-gradient(90deg,#2563eb,#14b8a6)' }}; opacity: {{ ($quota['limit']['limit'] ?? null) ? '1' : '.55' }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.90);">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Top short links') }}</p>
                @if (! empty($routes['links']))
                    <a href="{{ $routes['links'] }}" wire:navigate class="text-xs font-semibold" style="color: var(--theme-accent);">{{ __('View all') }}</a>
                @endif
            </div>
            <div class="mt-4 grid gap-3">
                @forelse ($topLinks as $link)
                    <div class="grid gap-3 rounded-[0.95rem] border px-4 py-3 sm:grid-cols-[minmax(0,1fr)_8rem_6rem]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->short_code }} - {{ $link->destination_url }}</p>
                        </div>
                        <div class="flex items-center sm:justify-end">
                            <x-ui.badge :variant="$link->status === 'active' ? 'success' : 'neutral'">{{ str($link->status)->title() }}</x-ui.badge>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $link->clicks_count) }}</p>
                            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('clicks') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[0.95rem] border px-4 py-6 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.58); color: var(--theme-muted-text-color);">{{ __('No short links yet. Create a branded link to start tracking performance.') }}</div>
                @endforelse
            </div>
        </section>
    </div>
</x-ui.dashboard-module>
