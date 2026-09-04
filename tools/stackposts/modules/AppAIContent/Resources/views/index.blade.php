<div
    class="mx-auto max-w-[1440px] space-y-4 px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{
        templatePanelOpen: false,
        init() {
            window.__appShellSuppressLoading = true;
        },
    }"
    x-on:open-ai-templates.window="templatePanelOpen = true"
    x-on:close-ai-templates.window="templatePanelOpen = false"
    x-on:livewire:navigating.window="window.__appShellSuppressLoading = false"
>
    <x-ui.ai-hero
        :eyebrow="__('AI Studio')"
        :title="__('AI Content')"
        :description="__('Build reusable social copy from templates, context, and output controls.')"
        icon="fa-light fa-wand-magic-sparkles"
        :panel-label="__('Caption generator')"
        :panel-title="__('Template-driven')"
        :panel-description="__('Reusable captions, platform settings, and output controls.')"
        :metrics="[
            ['label' => __('Library'), 'value' => count($categoryOptions).' '.__('categories')],
            ['label' => __('Platforms'), 'value' => count($selectedPlatforms).' '.__('selected')],
            ['label' => __('Credits'), 'value' => ($creditPreview['amount'] ?? 0).' '.__('per run')],
        ]"
    />

    <section class="grid overflow-hidden rounded-[1rem] border xl:grid-cols-[21rem_minmax(0,1fr)] 2xl:grid-cols-[21rem_minmax(0,1fr)_18.5rem]" style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base); box-shadow: 0 18px 50px -42px rgba(15,23,42,0.45);">
        <div class="hidden xl:block">
            @include('appaicontent::partials.template-sidebar')
        </div>

        <div class="min-w-0 xl:border-l" style="border-color: rgba(var(--theme-border-color-rgb), 0.65);">
            @include('appaicontent::partials.builder-panel')
            @include('appaicontent::partials.results-panel')
        </div>

        @include('appaicontent::partials.support-sidebar')
    </section>

    <div
        x-cloak
        x-show="templatePanelOpen"
        x-transition.opacity
        class="fixed inset-0 z-[9999] xl:hidden"
        x-on:keydown.escape.window="templatePanelOpen = false"
    >
        <div class="absolute inset-0 bg-slate-950/40" x-on:click="templatePanelOpen = false"></div>
        <div
            class="absolute inset-y-0 left-0 flex w-full max-w-[23rem] flex-col border-r shadow-2xl"
            style="border-color: rgba(var(--theme-border-color-rgb), 0.65); background: var(--theme-surface-base);"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <div class="flex items-center justify-between border-b px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb), 0.65);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Templates') }}</p>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-[0.7rem] border text-sm"
                    style="border-color: rgba(var(--theme-border-color-rgb), 0.65); color: var(--theme-muted-text-color); background: var(--theme-surface-base);"
                    x-on:click="templatePanelOpen = false"
                    aria-label="{{ __('Close') }}"
                >
                    <i class="fa-light fa-xmark"></i>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto">
                @include('appaicontent::partials.template-sidebar')
            </div>
        </div>
    </div>
</div>
