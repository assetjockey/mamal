<aside class="min-w-0" style="background: color-mix(in srgb, var(--theme-surface-base) 98%, var(--theme-body-bg) 2%);">
    <div class="space-y-4 p-4">
        @if ((string) $selectedCategoryId === '')
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Categories') }}</p>
                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Choose a category to open templates.') }}</p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" style="background: rgba(var(--theme-accent-rgb), 0.09); color: var(--theme-accent);">{{ count($categoryOptions) }}</span>
            </div>

            <x-ui.input
                wire:model.live.debounce.300ms="categorySearch"
                :label="__('Search categories')"
                :placeholder="__('Search categories...')"
            />

            @if (count($categoryOptions) > 0)
                <div class="mt-3 max-h-[calc(100vh-21rem)] min-h-[22rem] space-y-2 overflow-y-auto pr-1">
                    @foreach ($categoryOptions as $category)
                        @php($colors = $category->colorClasses())
                        <button
                            type="button"
                            wire:click="selectCategory({{ $category->id }})"
                            class="flex w-full items-center gap-3 rounded-[0.7rem] border px-3 py-2.5 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]"
                            style="border-color: rgba(var(--theme-border-color-rgb), 0.44); background: var(--theme-surface-base);"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem] ring-1 {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['ring'] }}">
                                <i class="{{ $category->icon ?: 'fa-light fa-folder-tree' }}"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $category->name }}</span>
                                <span class="mt-1 block text-xs" style="color: var(--theme-muted-text-color);">{{ number_format((int) $category->templates_count) }} {{ __('templates') }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            @else
                @if (trim((string) $categorySearch) !== '')
                    <div class="mt-4 rounded-[0.75rem] border px-4 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb), 0.42); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);">
                        {{ __('No categories match your search.') }}
                    </div>
                @else
                    <div class="mt-4">
                        <x-ui.empty
                            icon="fa-light fa-folder-open"
                            :title="__('No AI template categories yet')"
                            :description="__('Create template categories first to build a reusable caption prompt library.')"
                        />
                    </div>
                @endif
            @endif
        @else
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="min-w-0 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('AI Templates') }}</p>
                    <x-ui.button type="button" size="sm" variant="outline" wire:click="backToCategories">
                        {{ __('Back') }}
                    </x-ui.button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @php($selectedCategory = collect($categoryOptions)->firstWhere('id', (int) $selectedCategoryId))
                    @if ($selectedCategory)
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium" style="border-color: rgba(var(--theme-border-color-rgb), 0.5); background-color: color-mix(in srgb, var(--theme-surface-base) 95%, transparent); color: var(--theme-muted-text-color);">
                            <i class="{{ $selectedCategory->icon ?: 'fa-light fa-folder-tree' }}"></i>
                            <span>{{ $selectedCategory->name }}</span>
                        </span>
                    @endif
                    @if ($selectedTemplate)
                        <x-ui.button type="button" size="sm" variant="outline" wire:click="clearTemplate">
                            {{ __('Clear') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            <div class="pt-1">
                <x-ui.input
                    wire:model.live.debounce.300ms="templateSearch"
                    :label="__('Search')"
                    :placeholder="__('Search prompts...')"
                />
            </div>

            @if ($templates->isNotEmpty())
                <div class="mt-3 max-h-[calc(100vh-24rem)] min-h-[22rem] space-y-2 overflow-y-auto pr-1">
                    @foreach ($templates as $template)
                        <button
                            type="button"
                            x-on:click="
                                window.dispatchEvent(new CustomEvent('caption-template-selected', {
                                    detail: {
                                        content: @js($template->cleanContent()),
                                    }
                                }));
                                window.dispatchEvent(new CustomEvent('close-ai-templates'));
                                $wire.selectTemplate({{ $template->id }});
                            "
                            class="w-full rounded-[0.7rem] border px-3 py-2.5 text-left transition hover:border-[color:rgba(var(--theme-accent-rgb),0.35)]"
                            style="
                                border-color: {{ (int) $selectedTemplateId === (int) $template->id ? 'rgba(var(--theme-accent-rgb), 0.42)' : 'rgba(var(--theme-border-color-rgb), 0.46)' }};
                                background: {{ (int) $selectedTemplateId === (int) $template->id ? 'rgba(var(--theme-accent-rgb), 0.08)' : 'var(--theme-surface-base)' }};
                            "
                        >
                            <p class="text-sm font-medium leading-6" style="color: var(--theme-header-text-color);">{{ $template->contentPreview(128) }}</p>
                            <p class="mt-2 text-xs" style="color: var(--theme-muted-text-color);">{{ $template->category?->name ?: __('Uncategorized') }}</p>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="mt-4">
                    <x-ui.empty
                        icon="fa-light fa-magnifying-glass"
                        :title="__('No templates found')"
                        :description="__('Try another search or go back to choose a different category.')"
                    />
                </div>
            @endif
        @endif
    </div>
</aside>
