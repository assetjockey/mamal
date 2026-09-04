<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Review')"
        :description="__('Paste a draft and get publish-readiness feedback, risks, and concrete fixes.')"
        icon="fa-light fa-shield-check"
        :panel-label="__('Quality pass')"
        :panel-title="__('Pre-publish review')"
        :panel-description="__('Check hooks, clarity, risk, CTA quality, and next edits.')"
        :metrics="[
            ['label' => __('Type'), 'value' => __('Pre-publish')],
            ['label' => __('Language'), 'value' => $languageLabel],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per review')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-clipboard-check text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Draft under review') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('AI checks hook strength, clarity, risks, CTA quality, and gives specific next edits.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('Quality pass') }}
                </span>
            </div>

            <div class="mt-5">
                <x-ui.textarea
                    wire:model.defer="reviewDraft"
                    :label="__('Draft')"
                    :error="$errors->first('reviewDraft')"
                    rows="10"
                    placeholder="{{ __('Paste the draft to review...') }}"
                >{{ $reviewDraft }}</x-ui.textarea>
            </div>

            <div class="mt-4">
                <x-ui.language-select
                    wire:model.live="language"
                    :label="__('Output language')"
                    :preferred="['vi', 'en']"
                />
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                <x-ui.button type="button" wire:click="review" wire:loading.attr="disabled" wire:target="review" :disabled="!($creditPreview['enough'] ?? true)">
                    <i class="fa-light fa-shield-check"></i>
                    <span wire:loading.remove wire:target="review">{{ __('Review draft') }}</span>
                    <span wire:loading wire:target="review">{{ __('Reviewing...') }}</span>
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
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Review result') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Publish-readiness feedback') : __('Result appears here after review') }}</p>
                </div>
                @if ($result)
                    <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        {{ $result['score'] ?? 0 }}/100
                    </span>
                @endif
            </div>

            @if ($result)
                <div class="mt-4 rounded-[0.9rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: var(--theme-surface-base);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $result['verdict'] ?? __('Review complete') }}</p>
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.18);">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ max(6, min(100, (int) ($result['score'] ?? 0))) }}%; background: linear-gradient(90deg, rgba(var(--theme-accent-rgb), 0.55), var(--theme-accent));"></div>
                    </div>
                    @if (!empty($result['final_tip']))
                        <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $result['final_tip'] }}</p>
                    @endif
                </div>

                <div class="mt-4 grid gap-3">
                    @foreach (['strengths' => __('Strengths'), 'risks' => __('Risks'), 'fixes' => __('Fixes')] as $key => $label)
                        @if (!empty($result[$key]))
                            <div class="rounded-[0.9rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: var(--theme-surface-base);">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-accent);">{{ $label }}</p>
                                @foreach ($result[$key] as $line)
                                    <p class="mt-3 text-sm leading-7" style="color: var(--theme-header-text-color);">{{ $line }}</p>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="mt-4 flex min-h-[24rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-shield text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No review result yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Paste a draft and run review to get score, risks, and fixes.') }}</p>
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
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Review prompt') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 82) }}</p>
                    </button>
                @endforeach
            </div>
        </section>
    @endif
</div>
