@if (! empty($publicTemplates))
    @php
        $visuals = [
            'split_showcase' => ['bg' => '#f8fafc', 'ink' => '#0f172a', 'accent' => '#2563eb', 'soft' => '#dbeafe', 'mode' => 'split'],
            'mobile_stack' => ['bg' => '#0f172a', 'ink' => '#f8fafc', 'accent' => '#38bdf8', 'soft' => '#1e293b', 'mode' => 'phone'],
            'catalog_grid' => ['bg' => '#f3f7fb', 'ink' => '#1f2937', 'accent' => '#0f766e', 'soft' => '#d8ece8', 'mode' => 'grid'],
            'editorial' => ['bg' => '#f7f4ef', 'ink' => '#111827', 'accent' => '#334155', 'soft' => '#e7dfd2', 'mode' => 'editorial'],
            'compact_banner' => ['bg' => '#eef4ff', 'ink' => '#172554', 'accent' => '#2563eb', 'soft' => '#dbeafe', 'mode' => 'banner'],
            'sidebar_profile' => ['bg' => '#eef7f4', 'ink' => '#064e3b', 'accent' => '#047857', 'soft' => '#d7eee6', 'mode' => 'sidebar'],
            'menu_board' => ['bg' => '#111827', 'ink' => '#ffffff', 'accent' => '#eab308', 'soft' => '#374151', 'mode' => 'menu'],
            'lead_capture' => ['bg' => '#f8f3f7', 'ink' => '#4a2440', 'accent' => '#be3c78', 'soft' => '#ead7e4', 'mode' => 'form'],
            'event_pass' => ['bg' => '#f2f6fb', 'ink' => '#1e3a8a', 'accent' => '#3157a4', 'soft' => '#dce8f7', 'mode' => 'ticket'],
            'file_card' => ['bg' => '#f8fafc', 'ink' => '#334155', 'accent' => '#64748b', 'soft' => '#e2e8f0', 'mode' => 'file'],
            'review_spotlight' => ['bg' => '#f8f5ef', 'ink' => '#4b3826', 'accent' => '#b7791f', 'soft' => '#eadfcf', 'mode' => 'review'],
            'resume_sheet' => ['bg' => '#f7f8fa', 'ink' => '#111827', 'accent' => '#0f172a', 'soft' => '#d1d5db', 'mode' => 'resume'],
            'booking_card' => ['bg' => '#eef8fb', 'ink' => '#164e63', 'accent' => '#0891b2', 'soft' => '#cfeef5', 'mode' => 'booking'],
            'app_download' => ['bg' => '#0f172a', 'ink' => '#ffffff', 'accent' => '#2dd4bf', 'soft' => '#334155', 'mode' => 'app'],
            'payment_card' => ['bg' => '#f4f2fb', 'ink' => '#312e81', 'accent' => '#4f46e5', 'soft' => '#ddd6fe', 'mode' => 'payment'],
            'link_hub' => ['bg' => '#f2f7fb', 'ink' => '#172554', 'accent' => '#2563eb', 'soft' => '#dbeafe', 'mode' => 'links'],
        ];
    @endphp

    <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Public page view') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose the visual layout visitors see after scanning this QR.') }}</p>
            </div>
            <x-ui.badge variant="info">{{ count($publicTemplates) }} {{ __('views') }}</x-ui.badge>
        </div>

        <div class="mt-4 grid max-h-[34rem] grid-cols-[repeat(auto-fit,minmax(10.25rem,1fr))] gap-4 overflow-y-auto pr-2">
            @foreach ($publicTemplates as $templateKey => $template)
                @php
                    $visual = $visuals[$templateKey] ?? $visuals['split_showcase'];
                    $isSelected = $publicTemplate === $templateKey;
                @endphp

                <label class="group/card flex min-h-[13.75rem] cursor-pointer flex-col overflow-hidden rounded-xl border transition hover:-translate-y-[1px]" style="{{ $isSelected ? 'border-color: '.$visual['accent'].'; background-color: color-mix(in srgb, var(--theme-surface-base) 86%, '.$visual['accent'].' 14%); box-shadow: 0 16px 30px -24px '.$visual['accent'].';' : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);' }}">
                    <input type="radio" class="sr-only" wire:model.live="publicTemplate" value="{{ $templateKey }}">
                    <div class="h-28 border-b p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.42); background: color-mix(in srgb, {{ $visual['bg'] }} 38%, var(--theme-surface-soft));">
                        @switch($visual['mode'])
                            @case('phone')
                                <div class="mx-auto h-full max-w-[4.4rem] rounded-xl p-2 shadow-sm" style="background: {{ $visual['ink'] }};">
                                    <span class="block h-5 rounded-md" style="background: {{ $visual['accent'] }};"></span>
                                    <span class="mt-2 block h-1.5 rounded" style="background: rgba(255,255,255,.85);"></span>
                                    <span class="mt-1 block h-1.5 rounded" style="background: rgba(255,255,255,.55);"></span>
                                    <span class="mt-1 block h-1.5 rounded" style="background: rgba(255,255,255,.55);"></span>
                                </div>
                                @break
                            @case('grid')
                                <div class="grid h-full grid-cols-2 gap-2">
                                    <span class="rounded-lg shadow-sm" style="background: var(--theme-surface-base);"></span>
                                    <span class="rounded-lg shadow-sm" style="background: {{ $visual['accent'] }};"></span>
                                    <span class="rounded-lg shadow-sm" style="background: {{ $visual['soft'] }};"></span>
                                    <span class="rounded-lg shadow-sm" style="background: var(--theme-surface-base);"></span>
                                </div>
                                @break
                            @case('editorial')
                            @case('file')
                            @case('resume')
                                <div class="h-full rounded-lg p-3 shadow-sm" style="background: var(--theme-surface-base);">
                                    <span class="block h-2.5 w-2/3 rounded" style="background: var(--theme-header-text-color);"></span>
                                    <span class="mt-3 block h-1.5 rounded" style="background: rgba(var(--theme-border-color-rgb),0.72);"></span>
                                    <span class="mt-1.5 block h-1.5 w-5/6 rounded" style="background: rgba(var(--theme-border-color-rgb),0.58);"></span>
                                    <span class="mt-3 block h-5 rounded-md" style="background: {{ $visual['accent'] }};"></span>
                                </div>
                                @break
                            @case('menu')
                                <div class="h-full p-3" style="background: {{ $visual['bg'] }}; border: 1px solid {{ $visual['soft'] }};">
                                    <span class="block h-2.5 w-1/2" style="background: {{ $visual['accent'] }};"></span>
                                    <span class="mt-4 block h-1.5 rounded bg-white/80"></span>
                                    <span class="mt-2 block h-1.5 rounded bg-white/55"></span>
                                    <span class="mt-3 block h-2 w-1/3" style="background: {{ $visual['accent'] }};"></span>
                                </div>
                                @break
                            @case('ticket')
                                <div class="h-full rounded-lg p-3 shadow-sm" style="background: var(--theme-surface-base);">
                                    <span class="block h-3 rounded" style="background: {{ $visual['accent'] }};"></span>
                                    <span class="mt-3 block h-2 rounded" style="background: rgba(var(--theme-border-color-rgb),0.72);"></span>
                                    <span class="mt-2 block h-2 w-2/3 rounded" style="background: rgba(var(--theme-border-color-rgb),0.58);"></span>
                                </div>
                                @break
                            @case('links')
                                <div class="space-y-2">
                                    <span class="block h-6 rounded-lg shadow-sm" style="background: var(--theme-surface-base);"></span>
                                    <span class="block h-6 rounded-lg shadow-sm" style="background: {{ $visual['accent'] }};"></span>
                                    <span class="block h-6 rounded-lg shadow-sm" style="background: var(--theme-surface-base);"></span>
                                </div>
                                @break
                            @case('payment')
                                <div class="h-full rounded-lg p-3 text-white shadow-sm" style="background: linear-gradient(135deg, {{ $visual['accent'] }}, #0f172a);">
                                    <span class="block h-3 w-1/3 rounded bg-white/80"></span>
                                    <span class="mt-5 block h-2 rounded bg-white/70"></span>
                                    <span class="mt-2 block h-2 w-2/3 rounded bg-white/45"></span>
                                </div>
                                @break
                            @default
                                <div class="grid h-full grid-cols-[1fr_0.8fr] gap-2 rounded-lg p-2 shadow-sm" style="background: var(--theme-surface-base);">
                                    <div class="space-y-1.5">
                                        <span class="block h-2.5 rounded" style="background: {{ $visual['ink'] }};"></span>
                                        <span class="block h-1.5 rounded" style="background: rgba(var(--theme-border-color-rgb),0.68);"></span>
                                        <span class="block h-5 w-2/3 rounded-md" style="background: {{ $visual['accent'] }};"></span>
                                    </div>
                                    <div class="rounded-lg" style="background: {{ $visual['accent'] }};"></div>
                                </div>
                        @endswitch
                    </div>
                    <div class="flex flex-1 items-start gap-2 px-3 py-2.5">
                        <i class="fa-light {{ $template['icon'] ?? 'fa-browser' }} mt-0.5 text-xs" style="color: {{ $visual['accent'] }};"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $template['label'] }}</span>
                            <span class="mt-0.5 block line-clamp-2 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $template['description'] }}</span>
                        </span>
                        @if ($isSelected)
                            <i class="fa-light fa-check mt-1 text-sm" style="color: {{ $visual['accent'] }};"></i>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
    </section>
@endif
