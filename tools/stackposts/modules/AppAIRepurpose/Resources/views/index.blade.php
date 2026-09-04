<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('Repurpose')"
        :description="__('Paste one source and turn it into platform-ready captions.')"
        icon="fa-light fa-code-branch"
        :panel-label="__('Cross-format rewrite')"
        :panel-title="__('One source, many outputs')"
        :panel-description="__('Adapt long-form material into channel-ready content.')"
        :metrics="[
            ['label' => __('Tone'), 'value' => ucfirst((string) $tone)],
            ['label' => __('Language'), 'value' => $languageLabel],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per run')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-repeat text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Source content') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use a blog excerpt, sales message, campaign note, or long-form draft.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('Cross-format rewrite') }}
                </span>
            </div>

            <div class="mt-5">
                <x-ui.textarea
                    wire:model.defer="sourceContent"
                    :label="__('Source')"
                    :error="$errors->first('sourceContent')"
                    rows="10"
                    placeholder="{{ __('Paste the original content you want to adapt...') }}"
                >{{ $sourceContent }}</x-ui.textarea>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <x-ui.select wire:model.live="tone" :label="__('Tone')">
                    @foreach ($toneOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.language-select
                    wire:model.live="language"
                    :label="__('Language')"
                    :preferred="['vi', 'en']"
                />
            </div>

            <div class="mt-4">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Target platforms') }}</p>
                @if (!empty($platformOptions))
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($platformOptions as $platform)
                            <label class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-header-text-color);">
                                <input type="checkbox" wire:model.live="selectedPlatforms" value="{{ $platform['value'] }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $platform['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="mt-3 rounded-[0.85rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                        {{ __('Connect social channels first to choose output destinations.') }}
                    </div>
                @endif
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                <x-ui.button type="button" wire:click="repurpose" wire:loading.attr="disabled" wire:target="repurpose" :disabled="!($creditPreview['enough'] ?? true)">
                    <i class="fa-light fa-code-branch"></i>
                    <span wire:loading.remove wire:target="repurpose">{{ __('Repurpose content') }}</span>
                    <span wire:loading wire:target="repurpose">{{ __('Repurposing...') }}</span>
                </x-ui.button>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
                    <span>{{ __(':credits credits', ['credits' => $creditPreview['amount'] ?? 0]) }}</span>
                    <span>&bull;</span>
                    <span>{{ ($creditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => $creditPreview['remaining'] ?? 0]) }}</span>
                </div>
            </div>

            @if (!($creditPreview['enough'] ?? true))
                <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ __('Not enough credits remaining for this action.') }}</p>
                @include(theme_view('partials.credit-topup-cta', 'app'))
            @endif
        </div>

        <div class="min-w-0 border-t p-5 lg:border-l lg:border-t-0 lg:p-6" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: color-mix(in srgb, var(--theme-surface-base) 98%, var(--theme-body-bg) 2%);">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Outputs') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Repurposed content cards') : __('Generated outputs appear here') }}</p>
                </div>
                @if ($result)
                    <x-ui.button type="button" size="sm" variant="outline" wire:click="saveAllResults" wire:loading.attr="disabled" wire:target="saveAllResults" :disabled="empty($result['items'] ?? [])">
                        <i class="fa-light fa-books"></i>
                        <span wire:loading.remove wire:target="saveAllResults">{{ __('Save all') }}</span>
                        <span wire:loading wire:target="saveAllResults">{{ __('Saving...') }}</span>
                    </x-ui.button>
                @endif
            </div>

            @if ($result)
                <div class="mt-4 grid gap-3">
                    @foreach (($result['items'] ?? []) as $item)
                        <div class="rounded-[0.9rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $item['title'] ?: strtoupper((string) $item['target']) }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ strtoupper((string) $item['target']) }} &middot; {{ $item['format'] }}</p>
                                </div>
                                @if (in_array($loop->index, $savedResultIndexes ?? [], true))
                                    <x-ui.badge variant="success">{{ __('Saved') }}</x-ui.badge>
                                @endif
                            </div>

                            <p class="mt-3 text-sm leading-7 whitespace-pre-line" style="color: var(--theme-header-text-color);">{{ $item['caption'] }}</p>
                            @if (!empty($item['notes']))
                                <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $item['notes'] }}</p>
                            @endif

                            <div class="mt-4">
                                <x-ui.button
                                    type="button"
                                    size="sm"
                                    variant="{{ in_array($loop->index, $savedResultIndexes ?? [], true) ? 'outline' : 'secondary' }}"
                                    wire:click="saveResultCaption({{ $loop->index }})"
                                    wire:loading.attr="disabled"
                                    wire:target="saveResultCaption({{ $loop->index }})"
                                    :disabled="in_array($loop->index, $savedResultIndexes ?? [], true)"
                                >
                                    <i class="fa-light fa-book-bookmark"></i>
                                    <span wire:loading.remove wire:target="saveResultCaption({{ $loop->index }})">
                                        {{ in_array($loop->index, $savedResultIndexes ?? [], true) ? __('Saved to Captions') : __('Save to Captions') }}
                                    </span>
                                    <span wire:loading wire:target="saveResultCaption({{ $loop->index }})">{{ __('Saving...') }}</span>
                                </x-ui.button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-4 flex min-h-[24rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-repeat text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No repurpose output yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Paste source content and run repurpose to create reusable cards.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($promptHistory->isNotEmpty())
        <section class="rounded-[1rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base);">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($promptHistory as $history)
                    <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="rounded-[0.8rem] border px-3 py-3 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%);">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Repurpose prompt') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 82) }}</p>
                    </button>
                @endforeach
            </div>
        </section>
    @endif
</div>
