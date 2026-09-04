<div class="mx-auto max-w-[1320px] space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('Best Time')"
        :description="__('Select channels and find the strongest posting windows from workspace behavior.')"
        icon="fa-light fa-clock"
        :panel-label="__('Recommendation mode')"
        :panel-title="__('History-based')"
        :panel-description="__('Compare timing patterns across selected channels.')"
        :metrics="[
            ['label' => __('Selected'), 'value' => count($selectedAccountIds).' '.__('channels')],
            ['label' => __('Mode'), 'value' => __('History')],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per run')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="min-w-0 p-5 lg:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-[0.65rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-share-nodes text-sm"></i>
                        </span>
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Channel set') }}</p>
                    </div>
                    <p class="mt-2 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose one or more connected channels to compare timing patterns.') }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 96%, var(--theme-body-bg) 4%); color: var(--theme-muted-text-color);">
                    {{ __('History-based') }}
                </span>
            </div>

            @error('selectedAccountIds')
                <p class="mt-3 text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p>
            @enderror

            @if ($accounts->isNotEmpty())
                <div class="mt-5">
                    <x-ui.channel-selector
                        name="selectedAccountIds"
                        wire-model="selectedAccountIds"
                        :options="$channelOptions"
                        :network-options="$channelNetworks"
                        :selected="collect($selectedAccountIds)->map(fn ($id) => (string) $id)->all()"
                        :label="__('Channel')"
                        :error="$errors->first('selectedAccountIds') ?: $errors->first('selectedAccountIds.*')"
                        :placeholder="__('Choose one or more accounts')"
                        :empty-label="__('No matching channels found.')"
                        :multiple="true"
                        :live="true"
                        :sync-on-close="false"
                        :show-network-filters="true"
                        :connect-href="route('portal.channels')"
                        :connect-label="__('Connect a channel')"
                    />
                </div>
            @else
                <x-ui.empty class="mt-5" icon="fa-light fa-share-nodes" :title="__('No active channels')" :description="__('Connect channels first, then return here to analyze posting windows.')">
                    <x-ui.button class="mt-4" :href="route('portal.channels')" variant="outline" wire:navigate>{{ __('Open Channels') }}</x-ui.button>
                </x-ui.empty>
            @endif

            <div class="mt-5 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb), 0.5);">
                <x-ui.button type="button" wire:click="suggest" wire:loading.attr="disabled" wire:target="suggest" :disabled="!($creditPreview['enough'] ?? true)">
                    <i class="fa-light fa-clock"></i>
                    <span wire:loading.remove wire:target="suggest">{{ __('Suggest times') }}</span>
                    <span wire:loading wire:target="suggest">{{ __('Analyzing...') }}</span>
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
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Recommended windows') }}</p>
                    <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $result ? __('Ranked posting windows') : __('Suggestions appear here after analysis') }}</p>
                </div>
                @if ($result)
                    <span class="rounded-full border px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.16em]" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">
                        {{ count($result) }} {{ __('slots') }}
                    </span>
                @endif
            </div>

            @if ($result)
                <div class="mt-4 grid gap-3">
                    @foreach ($result as $slot)
                        <div class="rounded-[0.9rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb), 0.52); background: var(--theme-surface-base);">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $slot['label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Confidence') }}</p>
                                </div>
                                <span class="rounded-full border px-3 py-1.5 text-xs font-semibold" style="border-color: rgba(var(--theme-accent-rgb), 0.24); background-color: rgba(var(--theme-accent-rgb), 0.08); color: var(--theme-accent);">{{ $slot['confidence'] }}%</span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb), 0.18);">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ max(6, min(100, (int) ($slot['confidence'] ?? 0))) }}%; background: linear-gradient(90deg, rgba(var(--theme-accent-rgb), 0.55), var(--theme-accent));"></div>
                            </div>
                            @foreach (($slot['reasons'] ?? []) as $reason)
                                <p class="mt-3 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ $reason }}</p>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-4 flex min-h-[24rem] items-center justify-center rounded-[0.95rem] border border-dashed px-6 text-center" style="border-color: rgba(var(--theme-border-color-rgb), 0.58); background: var(--theme-surface-base);">
                    <div class="max-w-sm">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-[0.9rem]" style="background: rgba(var(--theme-accent-rgb), 0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-timer text-lg"></i>
                        </span>
                        <p class="mt-4 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No timing recommendations yet') }}</p>
                        <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Choose channels and run a suggestion pass to see ranked publishing windows.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
