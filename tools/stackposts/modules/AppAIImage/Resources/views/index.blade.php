<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Image')"
        :description="__('Describe the image, choose a style, then generate one saved asset for Files.')"
        icon="fa-light fa-image"
        :panel-label="__('Image generator')"
        :panel-title="__('Saved to Files')"
        :panel-description="__('Create one production-ready visual asset per run.')"
        :metrics="[
            ['label' => __('Style'), 'value' => ucfirst((string) $style)],
            ['label' => __('Ratio'), 'value' => $ratio],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per image')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-wand-magic-sparkles text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create image') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Keep the brief short and specific: subject, setting, composition, lighting, and what to avoid.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('Auto-saved to Files') }}
                </span>
            </div>

            <div class="mt-5">
                <x-ui.textarea
                    wire:model.defer="prompt"
                    :label="__('Prompt')"
                    rows="9"
                    placeholder="{{ __('Example: Clean product photo of a premium coffee cup on a marble counter, soft morning light, shallow depth of field, no text, no watermark.') }}"
                >{{ $prompt }}</x-ui.textarea>
                @error('prompt')
                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <x-ui.select wire:model.live="style" :label="__('Style')">
                    @foreach ($styleOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="ratio" :label="__('Aspect ratio')">
                    @foreach ($ratioOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                    <i class="fa-light fa-image"></i>
                    <span wire:loading.remove wire:target="generate">{{ __('Generate image') }}</span>
                    <span wire:loading wire:target="generate">{{ __('Generating...') }}</span>
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
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Preview') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Latest generated image') : __('Generated image appears here') }}</p>
                </div>
                @if ($result)
                    <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        {{ strtoupper((string) ($result['status'] ?? 'generated')) }}
                    </span>
                @endif
            </div>

            @if ($result)
                <div
                    class="relative mt-4 aspect-square overflow-hidden rounded-[0.95rem] border"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: var(--theme-surface-base);"
                    x-data="{ imageLoaded: false, imageError: false }"
                >
                    <div class="absolute inset-0 flex items-center justify-center px-6 text-center" style="background: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%);">
                        <div class="max-w-sm">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-image text-lg"></i>
                            </span>
                            <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">
                                <span x-show="!imageError">{{ __('Loading image') }}</span>
                                <span x-show="imageError">{{ __('Preview unavailable') }}</span>
                            </p>
                            <p class="mt-2 text-sm leading-6 break-all" style="color: var(--theme-muted-text-color);">
                                {{ $result['file_name'] ?? __('Generated image') }}
                            </p>
                        </div>
                    </div>

                    @if (!empty($result['preview_url']))
                        <img
                            src="{{ $result['preview_url'] }}"
                            alt="{{ $result['file_name'] ?? 'AI image' }}"
                            class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-200"
                            x-bind:class="{ 'opacity-100': imageLoaded && !imageError }"
                            x-on:load="imageLoaded = true"
                            x-on:error="imageError = true"
                        >
                    @endif
                </div>

                <div class="mt-4 grid gap-2 text-sm sm:grid-cols-3">
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Size') }}</p>
                        <p class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ $result['file_size'] ?? '' }}</p>
                    </div>
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Dimensions') }}</p>
                        <p class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ $result['dimensions'] ?: __('Saved') }}</p>
                    </div>
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Provider') }}</p>
                        <p class="mt-1 truncate font-semibold" style="color: var(--theme-header-text-color);">{{ strtoupper((string) ($result['provider'] ?? '')) }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-ui.button :href="$result['download_url'] ?? null" target="_blank" rel="noopener">
                        <i class="fa-light fa-download"></i>
                        {{ __('Download') }}
                    </x-ui.button>
                    <x-ui.button :href="$result['files_url'] ?? null" variant="outline" wire:navigate>
                        <i class="fa-light fa-folder-open"></i>
                        {{ __('Open Files') }}
                    </x-ui.button>
                </div>
            @else
                <div class="mt-4 flex aspect-square min-h-[22rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-image-polaroid text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No image yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Write a prompt and generate. The image will be saved to Files automatically.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($recentJobs->isNotEmpty() || $promptHistory->isNotEmpty())
        <section class="grid gap-5 lg:grid-cols-2">
            @if ($recentJobs->isNotEmpty())
                <div class="rounded-[1rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent images') }}</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($recentJobs as $job)
                            <button type="button" wire:click="loadJob({{ $job->id }})" class="rounded-[0.8rem] border px-3 py-3 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]" style="border-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.34)' : 'rgba(var(--theme-border-color-rgb), 0.42)' }}; background-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.07)' : 'color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%)' }};">
                                <p class="text-sm font-semibold capitalize" style="color: var(--theme-header-text-color);">{{ $job->style }} &middot; {{ $job->ratio }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($job->prompt, 82) }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($promptHistory->isNotEmpty())
                <div class="rounded-[1rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Prompt history') }}</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($promptHistory as $history)
                            <button type="button" wire:click="loadPromptHistory({{ $history->id }})" class="rounded-[0.8rem] border px-3 py-3 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%);">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Image prompt') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 82) }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif
</div>
