@php
    $creditRemainingLabel = $credits['unlimited']
        ? __('Unlimited')
        : number_format((int) ($credits['remaining'] ?? 0));
    $usagePercent = (int) ($credits['usage_percent'] ?? 0);
    $displayName = $user?->name ?: $user?->username ?: __('there');
@endphp

<div class="relative min-w-0 overflow-hidden rounded-[1.75rem] border shadow-[0_30px_90px_-60px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.22); background:
    radial-gradient(circle at 8% 12%, rgba(37,99,235,0.28), transparent 26%),
    radial-gradient(circle at 44% 0%, rgba(20,184,166,0.24), transparent 28%),
    radial-gradient(circle at 100% 20%, rgba(245,158,11,0.30), transparent 30%),
    linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.96), rgba(var(--theme-surface-soft-rgb,248,250,252),0.90));">
    <div class="pointer-events-none absolute inset-0 opacity-[0.18]" style="background-image: linear-gradient(rgba(37,99,235,0.24) 1px, transparent 1px), linear-gradient(90deg, rgba(37,99,235,0.24) 1px, transparent 1px); background-size: 36px 36px;"></div>

    <div class="relative grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(22rem,0.75fr)] xl:p-7">
        <section class="relative overflow-hidden rounded-[1.35rem] border p-5 shadow-sm backdrop-blur sm:p-6" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.84);">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-accent-rgb,37,99,235),0.20); background-color: rgba(var(--theme-accent-rgb,37,99,235),0.10); color: var(--theme-accent);">
                        <i class="fa-light fa-calendar-days"></i>
                        {{ $todayLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold" style="background-color: rgba(16,185,129,0.12); color: #047857;">
                        <span class="h-2 w-2 rounded-full bg-current"></span>
                        {{ __('Portal ready') }}
                    </span>
                </div>

                <div class="rounded-[1rem] border px-4 py-3 text-left shadow-sm sm:text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Plan status') }}</p>
                    <p class="mt-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $planName }}</p>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $planExpiryLabel }}</p>
                </div>
            </div>

            <div class="mt-12 max-w-3xl">
                <h1 class="text-[2.45rem] font-semibold leading-[1.02] tracking-[-0.075em] sm:text-[3.65rem]" style="color: var(--theme-header-text-color);">
                    {{ __('Welcome, :name', ['name' => $displayName]) }}
                </h1>
                <p class="mt-4 max-w-2xl text-[15px] leading-7" style="color: var(--theme-muted-text-color);">
                    {{ __('A quick, colorful command center for plan health, credits, QR campaigns, Bio pages, and publishing momentum.') }}
                </p>
            </div>

            <div class="mt-8 grid gap-3 md:grid-cols-3">
                @foreach ([
                    ['label' => __('Current plan'), 'value' => $planName, 'note' => __('Active package'), 'icon' => 'fa-gem', 'color' => '#2563eb'],
                    ['label' => __('Access'), 'value' => $user?->isInPlanTrial() ? __('Trial') : __('Live'), 'note' => $user?->isInPlanTrial() ? __('Trial workspace') : $planExpiryLabel, 'icon' => 'fa-signal-stream', 'color' => '#0f766e'],
                    ['label' => __('Workspace'), 'value' => __('Ready'), 'note' => __('Publish and measure'), 'icon' => 'fa-rocket-launch', 'color' => '#d97706'],
                ] as $item)
                    <div class="rounded-[1rem] border p-4" style="border-color: {{ $item['color'] }}33; background: linear-gradient(135deg, {{ $item['color'] }}18, rgba(var(--theme-surface-base-rgb,255,255,255),0.82));">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ $item['label'] }}</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.75rem]" style="background-color: {{ $item['color'] }}16; color: {{ $item['color'] }};">
                                <i class="fa-light {{ $item['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-3 truncate text-lg font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $item['value'] }}</p>
                        <p class="mt-1 truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $item['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="relative overflow-hidden rounded-[1.35rem] border p-5 shadow-sm" style="border-color: rgba(245,158,11,0.24); background:
            radial-gradient(circle at 88% 0%, rgba(251,191,36,0.32), transparent 28%),
            linear-gradient(160deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.88), rgba(245,158,11,0.10));">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem]" style="background: linear-gradient(135deg, rgba(245,158,11,0.18), rgba(249,115,22,0.12)); color: #d97706;">
                        <i class="fa-light fa-gem text-lg"></i>
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.22em]" style="color: var(--theme-muted-text-color);">{{ __('Your credits') }}</p>
                        <p class="mt-2 text-[3rem] font-semibold leading-none tracking-[-0.08em]" style="color: var(--theme-header-text-color);">{{ $creditRemainingLabel }}</p>
                    </div>
                </div>
                <span class="rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(245,158,11,0.14); color: #c2410c;">{{ $credits['unlimited'] ? __('Unlimited') : __('Balance') }}</span>
            </div>

            <p class="mt-5 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                {{ $credits['unlimited']
                    ? __('Unlimited generation access across AI writing, media, and automation.')
                    : __('Credits ready for image generation, AI writing, and automation runs.') }}
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Used') }}</p>
                    <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) $credits['used']) }}</p>
                </div>
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(16,185,129,0.24); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Available') }}</p>
                    <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: #047857;">{{ $creditRemainingLabel }}</p>
                </div>
            </div>

            <div class="mt-5 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.5); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.78);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                        <p class="mt-1 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $usagePercent }}%</p>
                    </div>
                    <div class="text-right text-xs leading-5" style="color: var(--theme-muted-text-color);">
                        <p>{{ __('Used: :count', ['count' => number_format((int) $credits['used'])]) }}</p>
                        <p>{{ $credits['unlimited'] ? __('Unlimited available') : __(':count left', ['count' => $creditRemainingLabel]) }}</p>
                    </div>
                </div>
                <div class="mt-4 h-3 overflow-hidden rounded-full" style="background-color: rgba(245,158,11,0.16);">
                    <div class="h-full rounded-full" style="width: {{ max(4, $usagePercent) }}%; background: linear-gradient(90deg, #f97316, #f59e0b, #2563eb);"></div>
                </div>
            </div>
        </aside>
    </div>
</div>
