<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Video')"
        :description="__('Describe the motion, choose duration and format, then start one render job.')"
        icon="fa-light fa-video"
        :panel-label="__('Async render')"
        :panel-title="__('Saved to Files')"
        :panel-description="__('Render status is tracked until the final MP4 is ready.')"
        :metrics="[
            ['label' => __('Duration'), 'value' => $duration.'s'],
            ['label' => __('Format'), 'value' => str($format)->replace('-', ' ')->title()],
            ['label' => __('Credits'), 'value' => $selectedDurationCredits.' '.__('per video')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-film text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Create video') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Write the subject, motion, camera direction, mood, and final frame. The render runs asynchronously.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('Saved to Files when complete') }}
                </span>
            </div>

            <div class="mt-5">
                <x-ui.textarea
                    wire:model.defer="prompt"
                    :label="__('Prompt')"
                    rows="9"
                    placeholder="{{ __('Example: A product launch teaser with slow camera push-in, warm studio lighting, smooth transitions, and a final clean hero shot. No text overlays.') }}"
                >{{ $prompt }}</x-ui.textarea>
                @error('prompt')
                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                @enderror
                @error('duration')
                    <p class="mt-2 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <x-ui.select wire:model.live="duration" :label="__('Duration')">
                    @foreach ($durationOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }} - {{ $option['credits'] }} {{ __('credits') }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model.live="format" :label="__('Format')">
                    @foreach ($formatOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="mt-5 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                <x-ui.button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate" :disabled="!($creditPreview['enough'] ?? true)">
                    <i class="fa-light fa-video"></i>
                    <span wire:loading.remove wire:target="generate">{{ __('Generate video') }}</span>
                    <span wire:loading wire:target="generate">{{ __('Starting...') }}</span>
                </x-ui.button>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-2 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    <i class="fa-light fa-coins text-xs" style="color: var(--theme-accent);"></i>
                    <span>{{ __(':credits credits', ['credits' => $selectedDurationCredits]) }}</span>
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
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Render status') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Current video job') : __('Started jobs appear here') }}</p>
                </div>
                @if ($result)
                    <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        {{ strtoupper((string) ($result['status'] ?? 'queued')) }}
                    </span>
                @endif
            </div>

            @if ($result)
                @if (($result['status'] ?? '') === 'completed' && !empty($result['preview_url']))
                    <div class="mt-4 overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background: var(--theme-surface-base);">
                        <video controls preload="metadata" class="block aspect-[9/16] max-h-[32rem] w-full object-contain" src="{{ $result['preview_url'] }}"></video>
                    </div>
                @else
                    <div class="mt-4 flex aspect-video min-h-[18rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                        <div class="max-w-sm">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                                <i class="fa-light fa-film text-lg"></i>
                            </span>
                            <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Render in progress') }}</p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Refresh the job when you want to check if the MP4 is ready.') }}</p>
                        </div>
                    </div>
                @endif

                <div class="mt-4 grid gap-2 text-sm sm:grid-cols-3">
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Progress') }}</p>
                        <p class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ (int) ($result['progress'] ?? 0) }}%</p>
                    </div>
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Duration') }}</p>
                        <p class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ $result['duration'] ?? $duration }}s</p>
                    </div>
                    <div class="rounded-[0.75rem] border px-3 py-2.5" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Provider') }}</p>
                        <p class="mt-1 truncate font-semibold" style="color: var(--theme-header-text-color);">{{ strtoupper((string) ($result['provider'] ?? '')) }}</p>
                    </div>
                </div>

                @if (!empty($result['id']))
                    <div class="mt-3 rounded-[0.75rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background: var(--theme-surface-base);">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Job ID') }}</p>
                        <p class="mt-1 break-all font-semibold" style="color: var(--theme-header-text-color);">{{ $result['id'] }}</p>
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    @if (($result['status'] ?? '') !== 'completed')
                        <x-ui.button type="button" variant="outline" wire:click="refreshResult" wire:loading.attr="disabled" wire:target="refreshResult">
                            <i class="fa-light fa-rotate"></i>
                            <span wire:loading.remove wire:target="refreshResult">{{ __('Refresh status') }}</span>
                            <span wire:loading wire:target="refreshResult">{{ __('Refreshing...') }}</span>
                        </x-ui.button>
                    @endif

                    @if (($result['status'] ?? '') === 'completed' && !empty($result['download_url']))
                        <x-ui.button :href="$result['download_url'] ?? null" target="_blank" rel="noopener">
                            <i class="fa-light fa-download"></i>
                            {{ __('Download MP4') }}
                        </x-ui.button>
                    @endif

                    @if (($result['status'] ?? '') === 'completed' && !empty($result['files_url']))
                        <x-ui.button :href="$result['files_url'] ?? null" variant="outline" wire:navigate>
                            <i class="fa-light fa-folder-open"></i>
                            {{ __('Open Files') }}
                        </x-ui.button>
                    @endif

                    @if (($result['status'] ?? '') === 'completed')
                        <x-ui.button type="button" variant="danger" wire:click="deleteCompletedJob" wire:loading.attr="disabled" wire:target="deleteCompletedJob">
                            <i class="fa-light fa-trash"></i>
                            <span wire:loading.remove wire:target="deleteCompletedJob">{{ __('Delete job') }}</span>
                            <span wire:loading wire:target="deleteCompletedJob">{{ __('Deleting...') }}</span>
                        </x-ui.button>
                    @endif
                </div>
            @else
                <div class="mt-4 flex aspect-video min-h-[18rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-video text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No render job yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Start a render job, then refresh until the video is ready.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if ($recentJobs->isNotEmpty() || $promptHistory->isNotEmpty())
        <section class="grid gap-5 lg:grid-cols-2">
            @if ($recentJobs->isNotEmpty())
                <div class="rounded-[1rem] border p-4 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recent video jobs') }}</p>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($recentJobs as $job)
                            <button type="button" wire:click="loadJob({{ $job->id }})" class="rounded-[0.8rem] border px-3 py-3 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]" style="border-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.34)' : 'rgba(var(--theme-border-color-rgb), 0.42)' }}; background-color: {{ $jobId === $job->id ? 'rgba(var(--theme-accent-rgb), 0.07)' : 'color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%)' }};">
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ str($job->format)->replace('-', ' ')->title() }} &middot; {{ $job->duration }}s</p>
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
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $history->title ?: __('Video prompt') }}</p>
                                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ \Illuminate\Support\Str::limit($history->prompt, 82) }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif
</div>
