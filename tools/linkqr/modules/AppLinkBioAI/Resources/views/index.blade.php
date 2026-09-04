@php
    $hasDraft = $aiBioDraft !== [];
    $draftBlocks = collect((array) data_get($aiBioDraft, 'blocks', []));
    $creditAmount = (int) ($aiBioCreditPreview['amount'] ?? 0);
    $creditRemaining = (int) ($aiBioCreditPreview['remaining'] ?? 0);
    $creditText = trans_choice(':count credit|:count credits', $creditAmount, ['count' => number_format($creditAmount)]);
    $remainingText = ($aiBioCreditPreview['unlimited'] ?? false) ? __('Unlimited plan') : __(':credits left', ['credits' => number_format($creditRemaining)]);
    $promptIdeas = [
        __('Restaurant launch menu, booking, Google reviews, and WhatsApp orders.'),
        __('Coach selling online classes with consultation CTA and Instagram.'),
        __('Agency campaign page for lead form, portfolio, and case studies.'),
    ];
@endphp

<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.35rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background:
        radial-gradient(circle at 18% 0%, rgba(37,99,235,0.13), transparent 32%),
        radial-gradient(circle at 88% 8%, rgba(20,184,166,0.12), transparent 30%),
        linear-gradient(135deg, rgba(var(--theme-surface-base-rgb,255,255,255),1), rgba(var(--theme-surface-base-rgb,255,255,255),0.92));">
        <div class="grid gap-6 p-5 sm:p-7 xl:grid-cols-[minmax(0,1fr)_360px] xl:items-center">
            <div class="min-w-0">
                <span class="inline-flex h-8 items-center gap-2 rounded-full border px-3 text-[11px] font-semibold uppercase tracking-[0.14em]" style="border-color: rgba(var(--theme-accent-rgb),0.18); color: var(--theme-accent); background-color: rgba(var(--theme-accent-rgb),0.07);">
                    <i class="fa-light fa-sparkles"></i>
                    {{ __('Link Bio AI') }}
                </span>
                <h1 class="mt-5 max-w-3xl text-[2rem] font-semibold leading-[1.08] tracking-[-0.035em] sm:text-[2.65rem]" style="color: var(--theme-header-text-color);">{{ __('Draft a complete Bio page from one clear brief.') }}</h1>
                <p class="mt-4 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Generate title, headline, description, CTA blocks, product sections, FAQs, and link ideas before opening the editor.') }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ([__('Copy'), __('Sections'), __('Buttons'), __('Campaign ideas')] as $tag)
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.50);">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[1rem] border p-4 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.76); box-shadow: inset 0 1px 0 rgba(var(--theme-surface-soft-rgb,248,250,252),0.18);">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('AI credits') }}</p>
                        <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $creditText }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(37,99,235,0.12); color: var(--theme-accent);">
                        <i class="fa-light fa-coins"></i>
                    </span>
                </div>
                <div class="mt-4 rounded-xl border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $remainingText }}</p>
                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Each generation creates an editable draft, not a published page.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="rounded-[1.25rem] border shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
            <div class="border-b px-5 py-4 sm:px-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Brief composer') }}</p>
                        <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ __('Tell the assistant what to build') }}</h2>
                    </div>
                    <span class="rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">{{ __('8-1200 characters') }}</span>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <x-ui.textarea wire:model.defer="aiBioBrief" :label="__('Business, creator, or campaign brief')" :error="$errors->first('aiBioBrief')" rows="8" placeholder="{{ __('Example: Personal trainer in Hanoi, sells online coaching, wants visitors to book a consultation and follow Instagram.') }}"></x-ui.textarea>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Prompt starters') }}</p>
                    <div class="grid gap-2 lg:grid-cols-3">
                        @foreach ($promptIdeas as $idea)
                            <button type="button" wire:click="$set('aiBioBrief', @js($idea))" class="rounded-xl border p-3 text-left text-xs leading-5 transition hover:-translate-y-0.5 hover:shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.58);">
                                {{ $idea }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.select wire:model.defer="aiBioTone" :label="__('Tone')">
                        <option value="professional">{{ __('Professional') }}</option>
                        <option value="friendly">{{ __('Friendly') }}</option>
                        <option value="sales">{{ __('Sales') }}</option>
                        <option value="educational">{{ __('Educational') }}</option>
                        <option value="bold">{{ __('Bold') }}</option>
                        <option value="casual">{{ __('Casual') }}</option>
                    </x-ui.select>
                    <x-ai.language-field wire:model.defer="aiBioLanguage" name="aiBioLanguage" :value="$aiBioLanguage" :label="__('Language')" :preferred="[]" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.58);">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to draft') }}</p>
                        <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $creditText }} · {{ $remainingText }}</p>
                    </div>
                    <x-ui.button type="button" wire:click="generateAiBio" wire:loading.attr="disabled" wire:target="generateAiBio" class="{{ ($aiBioCreditPreview['enough'] ?? true) ? '' : 'pointer-events-none opacity-50' }}">
                        <i class="fa-light fa-sparkles" wire:loading.remove wire:target="generateAiBio"></i>
                        <i class="fa-light fa-spinner-third animate-spin" wire:loading wire:target="generateAiBio"></i>
                        <span wire:loading.remove wire:target="generateAiBio">{{ __('Generate draft') }}</span>
                        <span wire:loading wire:target="generateAiBio">{{ __('Generating...') }}</span>
                    </x-ui.button>
                </div>
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Draft preview') }}</p>
                        <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em]" style="color: var(--theme-header-text-color);">{{ $hasDraft ? data_get($aiBioDraft, 'title') : __('No draft yet') }}</h2>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: rgba(20,184,166,0.12); color: #0f766e;">
                        <i class="fa-light fa-mobile-screen-button"></i>
                    </span>
                </div>

                <div class="mt-5 rounded-[1.35rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(180deg, #111827, #0f172a);">
                    <div class="rounded-[1rem] p-4" style="background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.92); box-shadow: inset 0 1px 0 rgba(var(--theme-surface-soft-rgb,248,250,252),0.18);">
                        <div class="mx-auto h-16 w-16 rounded-full" style="background: linear-gradient(135deg, #2563eb, #14b8a6);"></div>
                        <p class="mt-4 text-center text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $hasDraft ? data_get($aiBioDraft, 'headline') : __('Your headline appears here') }}</p>
                        <p class="mt-2 text-center text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $hasDraft ? data_get($aiBioDraft, 'description') : __('Generate a draft to preview copy, sections, and buttons before creating the page.') }}</p>

                        <div class="mt-4 space-y-2">
                            @forelse ($draftBlocks->take(4) as $block)
                                <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.54);">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($block, 'title') ?: __(\Illuminate\Support\Str::headline((string) data_get($block, 'type', 'links'))) }}</p>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ count((array) data_get($block, 'items', [])) }} {{ __('items') }}</p>
                                </div>
                            @empty
                                @foreach ([__('Hero copy'), __('Primary CTA'), __('Social links')] as $placeholder)
                                    <div class="rounded-xl border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: rgba(var(--theme-surface-soft-rgb,248,250,252),0.54);">
                                        <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $placeholder }}</p>
                                        <div class="mt-2 h-2 w-2/3 rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.26);"></div>
                                    </div>
                                @endforeach
                            @endforelse
                        </div>
                    </div>
                </div>

                <x-ui.button type="button" wire:click="createFromAiBioDraft" wire:loading.attr="disabled" wire:target="createFromAiBioDraft" class="mt-5 w-full justify-center {{ $hasDraft && $canCreate ? '' : 'pointer-events-none opacity-50' }}">
                    <i class="fa-light fa-wand-magic-sparkles"></i>
                    {{ __('Create from draft') }}
                </x-ui.button>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-accent);">{{ __('Draft output') }}</p>
                <div class="mt-4 space-y-3">
                    @foreach ([__('Page title and headline'), __('Short profile description'), __('Editable Bio blocks'), __('Button and CTA ideas')] as $item)
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" style="background-color: rgba(22,163,74,0.10); color: #15803d;">
                                <i class="fa-light fa-check text-xs"></i>
                            </span>
                            <p class="text-sm" style="color: var(--theme-header-text-color);">{{ $item }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>
