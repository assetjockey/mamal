<x-ui.dashboard-module
    class="overflow-hidden rounded-[1.75rem] border p-5 shadow-[0_30px_90px_-62px_rgba(15,23,42,0.5)] sm:p-6"
    style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 0% 10%, rgba(var(--theme-accent-rgb),0.18), transparent 26%),
        radial-gradient(circle at 44% 0%, rgba(168,85,247,0.16), transparent 26%),
        radial-gradient(circle at 100% 14%, rgba(20,184,166,0.14), transparent 26%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.94), rgba(var(--theme-surface-soft-rgb,248,250,252),0.84));"
    :eyebrow="__('AI Studio')"
    :title="null"
    :description="null"
>
    <div class="max-w-full overflow-hidden">
        <div class="flex min-w-0 flex-col gap-5">
            <div class="min-w-0 rounded-[1.25rem] border px-4 py-4 shadow-sm sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background:
                radial-gradient(circle at top left, rgba(var(--theme-accent-rgb),0.16), transparent 34%),
                linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),0.86), rgba(var(--theme-surface-soft-rgb,248,250,252),0.72));">
                <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-accent-rgb),0.16); background: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                <i class="fa-light fa-sparkles mr-1.5 text-[10px]"></i>{{ __('AI toolkit') }}
                            </span>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]" style="background: rgba(var(--theme-success-color-rgb),0.1); color: var(--theme-success-color);">
                                {{ __('Ready to create') }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-[1.8rem] font-semibold tracking-[-0.05em]" style="color: var(--theme-header-text-color);">{{ __('Jump into the right AI workflow faster') }}</h3>
                        <p class="mt-2 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep image, caption, video, and repurpose tools close together so ideation starts here and publishing stays separate.') }}</p>
                    </div>

                    <div class="flex w-full min-w-0 flex-wrap items-center gap-3 lg:w-auto lg:shrink-0">
                        <x-ui.button :href="$item['route'] ?? route('portal.ai-studio')" size="sm" class="w-full justify-center px-3 text-center whitespace-normal sm:w-auto" wire:navigate>{{ __('Open AI Studio') }}</x-ui.button>
                    </div>
                </div>
            </div>

            <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <div class="min-w-0 space-y-4">
                <div class="grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($tools as $index => $tool)
                        @php($tone = ['#2563eb', '#7c3aed', '#0f766e', '#d97706'][$index % 4])
                        <a href="{{ $tool['route'] }}" wire:navigate class="min-w-0 max-w-full rounded-[1.1rem] border p-4 shadow-sm transition hover:-translate-y-[1px]" style="border-color: {{ $tone }}2e; background: linear-gradient(135deg, {{ $tone }}14, rgba(var(--theme-surface-base-rgb,255,255,255),0.84));">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem]" style="background: {{ $tone }}18; color: {{ $tone }};">
                                <i class="{{ $tool['icon'] }}"></i>
                            </span>
                            <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $tool['description'] }}</p>
                            <span class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">
                                {{ __('Open tool') }}
                                <i class="fa-light fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <x-ui.card class="min-w-0 space-y-4" style="border-color: rgba(168,85,247,0.24); background: linear-gradient(135deg, rgba(168,85,247,0.10), rgba(var(--theme-surface-base-rgb,255,255,255),0.84)); box-shadow: 0 22px 44px -40px rgba(15,23,42,0.16);">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Related tools') }}</p>
                    <p class="mt-2 text-lg font-semibold tracking-[-0.03em]" style="color: var(--theme-header-text-color);">{{ __('AI adjacent workflows') }}</p>
                    <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Shortcuts for the workflows that usually follow AI ideation and generation.') }}</p>
                </div>

                <div class="grid min-w-0 gap-3">
                    @foreach ($supportTools as $tool)
                        <a href="{{ $tool['route'] }}" wire:navigate class="flex min-w-0 items-center justify-between gap-3 rounded-[1rem] border px-4 py-3 transition hover:-translate-y-[1px]" style="border-color: rgba(var(--theme-border-color-rgb),0.56); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76);">
                            <span class="min-w-0 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $tool['label'] }}</span>
                            <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">
                                {{ __('Open') }}
                                <i class="fa-light fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
            </div>
        </div>
    </div>
</x-ui.dashboard-module>
