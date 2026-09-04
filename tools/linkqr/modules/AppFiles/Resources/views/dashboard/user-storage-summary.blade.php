@php
    $storageBytes = (int) ($metrics['storage_bytes'] ?? 0);
    $limitMb = (int) ($metrics['limit_mb'] ?? 0);
    $limitBytes = $limitMb > 0 ? $limitMb * 1024 * 1024 : 0;
    $usagePercent = $limitBytes > 0 ? min(100, (int) round(($storageBytes / $limitBytes) * 100)) : null;
    $averageFileBytes = (int) ($metrics['average_file_bytes'] ?? 0);

    $formatBytes = static function (int $bytes): string {
        return $bytes >= 1073741824
            ? number_format($bytes / 1073741824, 2).' GB'
            : ($bytes >= 1048576
                ? number_format($bytes / 1048576, 2).' MB'
                : ($bytes >= 1024
                    ? number_format($bytes / 1024, 2).' KB'
                    : $bytes.' B'));
    };

    $storageLabel = $formatBytes($storageBytes);
    $averageFileLabel = $formatBytes($averageFileBytes);

    $limitLabel = $limitMb > 0 ? number_format($limitMb).' MB' : __('No storage limit');
    $categoryPalette = [
        'image' => 'rgba(var(--theme-accent-rgb), 0.88)',
        'video' => 'rgba(16,185,129,0.92)',
        'audio' => 'rgba(245,158,11,0.92)',
        'document' => 'rgba(99,102,241,0.88)',
        'spreadsheet' => 'rgba(14,165,233,0.9)',
        'pdf' => 'rgba(239,68,68,0.88)',
        'archive' => 'rgba(168,85,247,0.9)',
        'other' => 'rgba(100,116,139,0.88)',
    ];
    $categoryLabels = [
        'image' => __('Images'),
        'video' => __('Videos'),
        'audio' => __('Audio'),
        'document' => __('Documents'),
        'spreadsheet' => __('Sheets'),
        'pdf' => __('PDF'),
        'archive' => __('Archives'),
        'other' => __('Other'),
    ];
    $totalCategorizedBytes = collect($storageByCategory ?? [])->sum();
    $dominantCategory = collect($storageByCategory ?? [])->sortDesc()->keys()->first();
    $dominantCategoryBytes = $dominantCategory ? (int) (($storageByCategory[$dominantCategory] ?? 0)) : 0;
    $dominantCategoryShare = $totalCategorizedBytes > 0 && $dominantCategoryBytes > 0
        ? (int) round(($dominantCategoryBytes / $totalCategorizedBytes) * 100)
        : 0;
    $dominantCategoryLabel = $dominantCategory ? ($categoryLabels[$dominantCategory] ?? ucfirst((string) $dominantCategory)) : __('No assets yet');
    $dominantCategoryTitle = match ($dominantCategory) {
        'image' => __('Image-heavy library'),
        'video' => __('Video-heavy library'),
        'audio' => __('Audio-heavy library'),
        'document', 'spreadsheet', 'pdf' => __('Document-led library'),
        'archive' => __('Archive-heavy library'),
        'other' => __('Mixed asset library'),
        default => __('No assets yet'),
    };
@endphp

<x-ui.dashboard-module
    class="overflow-hidden rounded-[1.75rem] border p-5 shadow-[0_30px_90px_-62px_rgba(15,23,42,0.5)] sm:p-6"
    style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 0% 10%, rgba(14,165,233,0.16), transparent 26%),
        radial-gradient(circle at 44% 0%, rgba(20,184,166,0.16), transparent 26%),
        radial-gradient(circle at 100% 14%, rgba(var(--theme-accent-rgb),0.13), transparent 26%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.94), rgba(var(--theme-surface-soft-rgb,248,250,252),0.84));"
    :eyebrow="__('Files')"
    :title="null"
    :description="null"
>
    <div class="grid gap-4">
        <div class="overflow-hidden">
            <div class="flex flex-col gap-5">
                <div class="rounded-[1.25rem] border px-5 py-4 shadow-sm sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
                    radial-gradient(circle at top left, rgba(14,165,233,0.16), transparent 34%),
                    linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.86), rgba(var(--theme-surface-soft-rgb,248,250,252),0.72));">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                    <i class="fa-light fa-folders mr-1.5 text-[10px]"></i>{{ __('Storage and asset health') }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                    {{ __('Library snapshot') }}
                                </span>
                            </div>
                            <h3 class="mt-3 text-[1.8rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('See storage usage, asset mix, and library pressure quickly') }}</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Review total footprint, image-heavy usage, and current file-library balance before opening the full manager.') }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-ui.button :href="$item['route'] ?? route('portal.files.index')" size="sm" wire:navigate>{{ __('Open files') }}</x-ui.button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
        <div class="grid gap-4 xl:grid-cols-[1.16fr_0.84fr]">
            <x-ui.card class="space-y-5" style="border-color: rgba(14,165,233,0.22); background: linear-gradient(135deg, rgba(14,165,233,0.10), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 22px 46px -42px rgba(15,23,42,0.16);">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Storage usage') }}</p>
                        <p class="mt-2 text-[2.2rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ $storageLabel }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            {{ $usagePercent !== null ? __(':used of :limit used across your file library.', ['used' => $storageLabel, 'limit' => $limitLabel]) : __('This plan does not currently enforce a storage ceiling.') }}
                        </p>
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-3 text-right" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background: color-mix(in srgb, var(--theme-surface-overlay) 82%, transparent); box-shadow: inset 0 1px 0 color-mix(in srgb, var(--theme-surface-overlay) 72%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Usage') }}</p>
                        <p class="mt-2 text-[1.8rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $usagePercent ?? 0 }}%</p>
                    </div>
                </div>

                <div class="h-3 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.35);">
                    <div
                        class="h-full rounded-full transition-all"
                        style="width: {{ $usagePercent ?? 18 }}%; background: linear-gradient(90deg, rgba(var(--theme-accent-rgb),0.95), rgba(16,185,129,0.92));"
                    ></div>
                </div>

                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(37,99,235,0.20); background: linear-gradient(135deg, rgba(37,99,235,0.09), rgba(var(--theme-surface-base-rgb,255,255,255),0.78));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('All entries') }}</p>
                        <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['total'] ?? 0)) }}</p>
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(16,185,129,0.20); background: linear-gradient(135deg, rgba(16,185,129,0.09), rgba(var(--theme-surface-base-rgb,255,255,255),0.78));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Images') }}</p>
                        <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['images'] ?? 0)) }}</p>
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(124,58,237,0.20); background: linear-gradient(135deg, rgba(124,58,237,0.09), rgba(var(--theme-surface-base-rgb,255,255,255),0.78));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Folders') }}</p>
                        <p class="mt-2 text-[1.7rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['folders'] ?? 0)) }}</p>
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-4" style="border-color: rgba(245,158,11,0.22); background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(var(--theme-surface-base-rgb,255,255,255),0.78));">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Avg file size') }}</p>
                        <p class="mt-2 text-[1.35rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $averageFileLabel }}</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-4" style="border-color: rgba(20,184,166,0.22); background: linear-gradient(135deg, rgba(20,184,166,0.10), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 22px 46px -42px rgba(15,23,42,0.14);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Asset mix') }}</p>
                        <p class="mt-2 text-[1.3rem] font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">
                            {{ $dominantCategoryTitle }}
                        </p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">
                            @if ($dominantCategory)
                                {{ __(':type currently accounts for about :share% of the occupied storage in your library.', ['type' => Str::lower($dominantCategoryLabel), 'share' => $dominantCategoryShare]) }}
                            @else
                                {{ __('A quick split of the file types currently consuming space in your library.') }}
                            @endif
                        </p>

                        @if ($dominantCategory)
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-accent-rgb),0.14); background: rgba(var(--theme-accent-rgb),0.05); color: var(--theme-header-text-color);">
                                    <span class="h-2 w-2 rounded-full" style="background: {{ $categoryPalette[$dominantCategory] ?? $categoryPalette['other'] }};"></span>
                                    {{ $dominantCategoryLabel }}
                                </span>
                                <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: color-mix(in srgb, var(--theme-surface-overlay) 78%, transparent); color: var(--theme-muted-text-color);">
                                    {{ $formatBytes($dominantCategoryBytes) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[var(--theme-card-radius,1.15rem)] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.52); background: color-mix(in srgb, var(--theme-surface-overlay) 84%, transparent);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Capacity') }}</p>
                        <p class="mt-2 text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $limitLabel }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse (collect($storageByCategory ?? [])->sortDesc()->take(4) as $category => $bytes)
                        @php
                            $categoryShare = $totalCategorizedBytes > 0 ? max(3, (int) round(($bytes / $totalCategorizedBytes) * 100)) : 0;
                        @endphp
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $categoryPalette[$category] ?? $categoryPalette['other'] }};"></span>
                                    <span class="font-medium" style="color: var(--theme-header-text-color);">{{ $categoryLabels[$category] ?? ucfirst((string) $category) }}</span>
                                </div>
                                <span style="color: var(--theme-muted-text-color);">{{ $formatBytes((int) $bytes) }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full" style="background: rgba(var(--theme-border-color-rgb),0.28);">
                                <div class="h-full rounded-full" style="width: {{ $categoryShare }}%; background: {{ $categoryPalette[$category] ?? $categoryPalette['other'] }};"></div>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty
                            icon="fa-light fa-folder-open"
                            :title="__('No files stored yet')"
                            :description="__('Type distribution will appear here once assets are uploaded into the library.')"
                        />
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.card class="space-y-2" style="border-color: rgba(37,99,235,0.20); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 18px 38px -36px rgba(15,23,42,0.12);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('File assets') }}</p>
                <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['files'] ?? 0)) }}</p>
                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Uploaded assets excluding folders.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2" style="border-color: rgba(16,185,129,0.20); background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 18px 38px -36px rgba(15,23,42,0.12);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Non-image assets') }}</p>
                <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($metrics['other_assets'] ?? 0)) }}</p>
                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Documents, videos, archives, and other stored files.') }}</p>
            </x-ui.card>

            <x-ui.card class="space-y-2" style="border-color: rgba(245,158,11,0.22); background: linear-gradient(135deg, rgba(245,158,11,0.09), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 18px 38px -36px rgba(15,23,42,0.12);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Storage footprint') }}</p>
                <p class="mt-2 text-[1.65rem] font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ $storageLabel }}</p>
                <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Current occupied space across all stored assets.') }}</p>
            </x-ui.card>
        </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
