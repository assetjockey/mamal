<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('Semantic Search')"
        :description="__('Find related captions and posts by meaning, not exact keywords.')"
        icon="fa-light fa-brain-circuit"
        :panel-label="__('Lookup mode')"
        :panel-title="__('Meaning-based')"
        :panel-description="__('Recover related workspace content by intent and similarity.')"
        :metrics="[
            ['label' => __('Mode'), 'value' => __('Meaning')],
            ['label' => __('Results'), 'value' => __('Top 12')],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per search')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-magnifying-glass text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Search workspace') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Describe the idea, topic, campaign, or intent you want to recover from saved content.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('Workspace lookup') }}
                </span>
            </div>

            <form wire:submit.prevent="search" class="mt-5 space-y-4">
                <x-ui.input
                    wire:model.defer="searchQuery"
                    :label="__('Search intent')"
                    :error="$errors->first('searchQuery')"
                    :placeholder="__('Launch offer, product education, customer win, reminder...')"
                />

                <div class="flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="search" :disabled="!($creditPreview['enough'] ?? true)">
                        <i class="fa-light fa-magnifying-glass"></i>
                        <span wire:loading.remove wire:target="search">{{ __('Search') }}</span>
                        <span wire:loading wire:target="search">{{ __('Searching...') }}</span>
                    </x-ui.button>

                    <div class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
                        <span>{{ __(':credits credits', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
                        <span>&bull;</span>
                        <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                    </div>
                </div>
            </form>

            @if (!($creditPreview['enough'] ?? true))
                <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
                @include(theme_view('partials.credit-topup-cta', 'app'))
            @endif

            <div wire:loading.flex wire:target="search" class="mt-4 items-center gap-3 rounded-[0.9rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-accent-rgb), 0.16); background-color: rgba(var(--theme-accent-rgb), 0.06); color: var(--theme-muted-text-color);">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full" style="background-color: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                    <i class="fa-light fa-loader animate-spin"></i>
                </span>
                <div>
                    <p class="font-medium" style="color: var(--theme-header-text-color);">{{ __('Ranking related content') }}</p>
                    <p class="text-xs sm:text-sm">{{ __('AI is comparing your intent with captions and publishing posts.') }}</p>
                </div>
            </div>

            <div class="mt-5 grid gap-2 sm:grid-cols-3">
                @foreach ([__('Intent'), __('Captions'), __('Posts')] as $step)
                    <div class="rounded-[0.75rem] border px-3 py-2.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.48); background: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                        {{ $step }}
                    </div>
                @endforeach
            </div>
        </div>

        <div class="min-w-0 border-t p-5 lg:border-l lg:border-t-0 lg:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: color-mix(in srgb, var(--theme-surface-base) 98%, var(--theme-body-bg) 2%);">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Semantic matches') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Ranked by relevance score') : __('Matches appear here after search') }}</p>
                </div>
                @if ($result)
                    <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        {{ count($result) }} {{ __('matches') }}
                    </span>
                @endif
            </div>

            @if ($result)
                <div class="mt-4 grid max-h-[35rem] gap-3 overflow-y-auto pr-1">
                    @foreach ($result as $item)
                        @php
                            $type = (string) ($item['type'] ?? 'content');
                            $score = (int) ($item['score'] ?? 0);
                            $tags = array_slice((array) ($item['tags'] ?? []), 0, 4);
                            $meta = (array) ($item['meta'] ?? []);
                        @endphp
                        <div class="rounded-[0.9rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['title'] ?? __('Untitled content') }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs" style="color: var(--theme-muted-text-color);">
                                        <span class="rounded-full border px-2.5 py-1 font-semibold uppercase tracking-[0.12em]" style="border-color: rgba(var(--theme-accent-rgb), 0.22); background-color: rgba(var(--theme-accent-rgb), 0.07); color: var(--theme-accent);">{{ $type }}</span>
                                        @foreach (array_filter($meta) as $value)
                                            <span>{{ $value }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">{{ $score }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-7" style="color: var(--theme-muted-text-color);">{{ $item['excerpt'] ?? '' }}</p>
                            @if ($tags)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($tags as $tag)
                                        <span class="rounded-full px-2.5 py-1 text-xs" style="background: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-4 flex min-h-[24rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-compass text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No semantic matches yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Search a topic or intent to see related captions and posts ranked here.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
