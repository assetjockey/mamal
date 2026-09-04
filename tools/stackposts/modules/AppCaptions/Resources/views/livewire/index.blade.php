<div class="space-y-6 px-4 pb-8 pt-4 sm:px-5 xl:px-6" x-data="{ editorOpen: @entangle('editorOpen') }">
    <x-ui.card class="overflow-hidden border-none shadow-[0_30px_90px_-56px_rgba(15,23,42,0.42)]" style="background: linear-gradient(135deg, rgba(var(--theme-accent-rgb), 0.10), rgba(var(--theme-surface-overlay-rgb), 0.98) 42%, rgba(var(--theme-surface-overlay-rgb), 0.98));">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
            <div class="space-y-6">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em]" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: rgba(var(--theme-surface-overlay-rgb), 0.82); color: var(--theme-accent);">
                        <i class="fa-light fa-message-captions"></i>
                        {{ __('Caption library') }}
                    </span>
                    <div class="space-y-3">
                        <h1 class="text-3xl font-semibold tracking-[-0.05em] sm:text-4xl" style="color: var(--theme-header-text-color);">{{ __('Captions Workspace') }}</h1>
                        <p class="max-w-3xl text-sm leading-7 sm:text-[15px]" style="color: var(--theme-muted-text-color);">
                            {{ __('Store AI and manual caption blocks in one place, filter them quickly, and keep high-performing copy ready for publishing.') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-ui.button :href="route('portal.ai-studio')" variant="outline" wire:navigate>
                        <i class="fa-light fa-sparkles"></i>
                        {{ __('Open AI Studio') }}
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="openCreateEditor">
                        <i class="fa-light fa-plus"></i>
                        {{ $editingId ? __('New caption') : __('Create caption') }}
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-[1.35rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.72); background: rgba(var(--theme-surface-overlay-rgb), 0.88);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em]" style="color: var(--theme-muted-text-color);">{{ __('Caption inventory') }}</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($stats['total'] ?? 0)) }} {{ __('items') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Saved copy blocks available for reuse.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                        <i class="fa-light fa-quote-left text-lg"></i>
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('AI') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($stats['ai'] ?? 0)) }}</p>
                    </div>
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Manual') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($stats['manual'] ?? 0)) }}</p>
                    </div>
                    <div class="rounded-2xl border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.55); background: rgba(var(--theme-surface-base-rgb), 0.55);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ __('Active') }}</p>
                        <p class="mt-2 text-2xl font-semibold tracking-[-0.04em]" style="color: var(--theme-header-text-color);">{{ number_format((int) ($stats['active'] ?? 0)) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <div class="space-y-5">
        <section class="space-y-5">
            <x-ui.surface-card padding="lg" featured accent="primary">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-2xl sm:inline-flex" style="background: rgba(var(--theme-accent-rgb), 0.10); color: var(--theme-accent-color);">
                            <i class="fa-light fa-rectangle-list"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Caption Inventory') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge variant="primary">{{ count($captions) }} {{ __('shown') }}</x-ui.badge>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem_12rem_auto]">
                    <x-ui.input wire:model.live.debounce.300ms="search" :placeholder="__('Search name, caption, or notes')" />
                    <x-ui.select wire:model.live="sourceFilter">
                        <option value="">{{ __('All sources') }}</option>
                        <option value="manual">{{ __('Manual') }}</option>
                        <option value="ai">{{ __('AI') }}</option>
                    </x-ui.select>
                    <x-ui.select wire:model.live="statusFilter">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="draft">{{ __('Draft') }}</option>
                        <option value="archived">{{ __('Archived') }}</option>
                    </x-ui.select>
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" variant="ghost" wire:click="resetFilters">
                            <i class="fa-light fa-rotate-left"></i>
                            {{ __('Reset') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.surface-card>

            @if ($captions->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($captions as $caption)
                        @php
                            $sourceVariant = $caption->source_type === 'ai' ? 'primary' : 'neutral';
                            $statusVariant = match ($caption->status) {
                                'active' => 'success',
                                'draft' => 'neutral',
                                default => 'danger',
                            };
                        @endphp
                        <x-ui.surface-card padding="md" class="h-full">
                            <div class="flex h-full flex-col">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" style="background: {{ $caption->source_type === 'ai' ? 'rgba(var(--theme-accent-rgb), 0.10)' : 'rgba(100, 116, 139, 0.10)' }}; color: {{ $caption->source_type === 'ai' ? 'var(--theme-accent-color)' : 'rgb(71, 85, 105)' }};">
                                            <i class="fa-light {{ $caption->source_type === 'ai' ? 'fa-sparkles' : 'fa-pen-nib' }}"></i>
                                        </span>
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <x-ui.badge :variant="$sourceVariant">{{ strtoupper($caption->source_type) }}</x-ui.badge>
                                            <x-ui.badge :variant="$statusVariant">{{ strtoupper($caption->status) }}</x-ui.badge>
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium" style="color: var(--theme-muted-text-color);">{{ $caption->updated_at?->diffForHumans() }}</span>
                                </div>

                                <div class="mt-4">
                                    <p class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $caption->name }}</p>
                                    <p class="mt-3 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ \Illuminate\Support\Str::limit($caption->content, 220) }}</p>
                                </div>

                                @php
                                    $visibleTags = collect($caption->tags ?? [])
                                        ->map(fn ($tag) => trim((string) $tag))
                                        ->filter()
                                        ->reject(fn ($tag) => in_array(Str::lower($tag), ['manual', 'ai', 'misc', 'placeholder', 'short-text', 'short-content'], true))
                                        ->take(2)
                                        ->values();
                                @endphp

                                @if ($visibleTags->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($visibleTags as $tag)
                                            <x-ui.badge variant="neutral">{{ $tag }}</x-ui.badge>
                                        @endforeach
                                    </div>
                                @endif

                                @if (! empty($caption->notes))
                                    <p class="mt-4 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($caption->notes, 120) }}</p>
                                @endif

                                <div class="mt-5 flex items-center gap-2 pt-2" x-data="{ confirmDeleteOpen: false }">
                                    <x-ui.button type="button" size="sm" variant="outline" wire:click="editCaption({{ $caption->id }})">
                                        <i class="fa-light fa-pen-to-square"></i>
                                        {{ __('Edit') }}
                                    </x-ui.button>
                                    <x-ui.button type="button" size="sm" variant="danger" x-on:click="confirmDeleteOpen = true">
                                        <i class="fa-light fa-trash-can"></i>
                                        {{ __('Delete') }}
                                    </x-ui.button>

                                    <template x-teleport="body">
                                        <div
                                            x-cloak
                                            x-show="confirmDeleteOpen"
                                            class="fixed inset-0 z-[120] flex items-center justify-center p-6"
                                            x-on:keydown.escape.window="confirmDeleteOpen = false"
                                        >
                                            <div class="absolute inset-0 bg-white/55 backdrop-blur-[6px] dark:bg-slate-950/55" x-on:click="confirmDeleteOpen = false"></div>

                                            <div x-show="confirmDeleteOpen" x-transition.opacity.scale.90 class="relative w-full max-w-[26rem]">
                                                <div class="overflow-hidden rounded-[1.15rem] border shadow-[0_32px_80px_-34px_rgba(15,23,42,0.32)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                                                    <div class="flex items-start justify-between gap-4 border-b px-5 py-4 sm:px-6 sm:py-5" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                                                        <div class="min-w-0">
                                                            <h3 class="text-[1.05rem] font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Delete caption?') }}</h3>
                                                            <p class="mt-2 text-[15px] leading-7" style="color: var(--theme-muted-text-color);">{{ __('This will permanently remove ":name" from your caption library.', ['name' => $caption->name]) }}</p>
                                                        </div>

                                                        <button type="button" style="color: var(--theme-muted-text-color);" x-on:click="confirmDeleteOpen = false">
                                                            <i class="fa-light fa-xmark text-lg"></i>
                                                        </button>
                                                    </div>

                                                    <div class="px-5 py-4 sm:px-6 sm:py-5">
                                                        <p class="text-sm leading-7" style="color: var(--theme-muted-text-color);">
                                                            {{ __('You can keep editing or cancel now if this caption might still be reused later.') }}
                                                        </p>
                                                    </div>

                                                    <div class="border-t bg-slate-50/70 px-5 py-4 sm:px-6" style="border-color: color-mix(in srgb, var(--theme-border-color) 52%, transparent);">
                                                        <div class="flex items-center justify-end gap-3">
                                                            <x-ui.button type="button" variant="outline" x-on:click="confirmDeleteOpen = false">
                                                                <i class="fa-light fa-xmark"></i>
                                                                {{ __('Cancel') }}
                                                            </x-ui.button>
                                                            <x-ui.button type="button" variant="danger" wire:click="deleteCaption({{ $caption->id }})" x-on:click="confirmDeleteOpen = false">
                                                                <i class="fa-light fa-trash-can"></i>
                                                                {{ __('Delete caption') }}
                                                            </x-ui.button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </x-ui.surface-card>
                    @endforeach
                </div>
            @else
                <x-ui.empty
                    icon="fa-light fa-pen-field"
                    :title="__('No captions found')"
                    :description="__('Build a reusable caption library from AI-generated blocks or manual copy for faster publishing.')"
                >
                    <div class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background-color: color-mix(in srgb, var(--theme-surface-base) 94%, transparent); color: var(--theme-muted-text-color);">
                        <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Start building') }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Create, tag, reuse.') }}</span>
                    </div>
                </x-ui.empty>
            @endif
        </section>
    </div>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="editorOpen"
            class="fixed inset-0 z-[120] flex items-end justify-center p-0 sm:items-center sm:p-6"
            x-on:keydown.escape.window="$wire.resetEditor()"
        >
            <div class="absolute inset-0 bg-slate-950/45 backdrop-blur-[4px]" x-on:click="$wire.resetEditor()"></div>

            <div x-show="editorOpen" x-transition.opacity.scale.95 class="relative w-full max-w-[46rem]">
                <div class="max-h-[92vh] overflow-y-auto rounded-t-[1.4rem] border px-5 py-5 shadow-[0_-24px_70px_-30px_rgba(15,23,42,0.45)] sm:rounded-[1.4rem] sm:px-6 sm:py-6 sm:shadow-[0_32px_90px_-38px_rgba(15,23,42,0.42)]" style="border-color: color-mix(in srgb, var(--theme-border-color) 58%, transparent); background-color: var(--theme-surface-overlay);">
                    @include('appcaptions::livewire.partials.editor-form')
                </div>
            </div>
        </div>
    </template>
</div>
