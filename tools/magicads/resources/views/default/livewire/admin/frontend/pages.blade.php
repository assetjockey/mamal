<div>
    <style>
        /* Self-contained prose styling for the page-content preview. */
        .pg-prose { color: #374151; font-size: 14px; line-height: 1.7; }
        .pg-prose h1 { font-size: 24px; font-weight: 800; color: #000; margin: 1.2em 0 .5em; line-height: 1.2; }
        .pg-prose h2 { font-size: 20px; font-weight: 700; color: #000; margin: 1.4em 0 .5em; line-height: 1.25; }
        .pg-prose h3 { font-size: 16px; font-weight: 600; color: #000; margin: 1.2em 0 .4em; }
        .pg-prose p  { margin: 0 0 1em; }
        .pg-prose ul, .pg-prose ol { margin: 0 0 1.1em; padding-left: 1.4em; }
        .pg-prose ul { list-style: disc; }
        .pg-prose ol { list-style: decimal; }
        .pg-prose li { margin-bottom: .35em; }
        .pg-prose a  { color: #4F46E5; text-decoration: underline; text-underline-offset: 2px; }
        .pg-prose strong { color: #000; font-weight: 700; }
        .pg-prose blockquote { border-left: 3px solid #4F46E5; padding-left: 1em; color: #4b5563; font-style: italic; margin: 1.2em 0; }
        .pg-prose hr { border: 0; border-top: 1px solid rgba(0,0,0,.08); margin: 1.6em 0; }
        .dark .pg-prose { color: #d4d4d8; }
        .dark .pg-prose h1, .dark .pg-prose h2, .dark .pg-prose h3, .dark .pg-prose strong { color: #fff; }
        .dark .pg-prose a { color: #818cf8; }
        .dark .pg-prose hr { border-top-color: rgba(255,255,255,.1); }
    </style>
    <div class="flex justify-center">
        <div class="w-full lg:w-8/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Pages') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Pages') }}</h1>
                <flux:subheading size="md">{{ __('Edit the content of your Privacy Policy and Terms of Service pages') }}</flux:subheading>
            </div>

            {{-- Tab switcher --}}
            <div class="mb-6 inline-flex rounded-xl border border-(--default-border-color) p-1 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <button type="button" wire:click="setTab('privacy')"
                        @class([
                            'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors cursor-pointer',
                            'bg-(--default-body-bg-color) text-zinc-900 shadow-sm dark:bg-(--default-element-bg-color) dark:text-white' => $tab === 'privacy',
                            'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'privacy',
                        ])>
                    <flux:icon.shield-check class="size-4" />
                    {{ __('Privacy Policy') }}
                </button>
                <button type="button" wire:click="setTab('terms')"
                        @class([
                            'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors cursor-pointer',
                            'bg-(--default-body-bg-color) text-zinc-900 shadow-sm dark:bg-(--default-element-bg-color) dark:text-white' => $tab === 'terms',
                            'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'terms',
                        ])>
                    <flux:icon.document-text class="size-4" />
                    {{ __('Terms of Service') }}
                </button>
            </div>

            <flux:fieldset>
                <div class="md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    @if ($tab === 'privacy')
                        <div class="mb-6 flex items-center justify-between gap-3">
                            <h2 class="text-md font-bold">{{ __('Privacy Policy Content') }}</h2>
                            <flux:button :href="route('privacy')" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square" class="cursor-pointer">{{ __('View page') }}</flux:button>
                        </div>

                        <flux:field>
                            <flux:label>{{ __('Content (HTML supported)') }}</flux:label>
                            <flux:textarea wire:model.live.debounce.500ms="privacy_content" rows="18"
                                           placeholder="{{ __('Write your privacy policy here. You can use HTML tags like <h2>, <p>, <ul>, <li>, <a>...') }}" />
                            <flux:description>{{ __('Leave empty to show the built-in default privacy policy. Headings, paragraphs and lists are styled automatically.') }}</flux:description>
                            <flux:error name="privacy_content" />
                        </flux:field>

                        @if (filled($privacy_content))
                            <div class="mt-7">
                                <span class="mb-2 block text-[11px] font-semibold uppercase tracking-wider text-zinc-400">{{ __('Live preview') }}</span>
                                <div class="pg-prose max-h-96 overflow-y-auto rounded-xl border border-(--default-border-color) bg-white p-6 dark:border-white/8 dark:bg-(--default-element-bg-color)">
                                    {!! $privacy_content !!}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mb-6 flex items-center justify-between gap-3">
                            <h2 class="text-md font-bold">{{ __('Terms of Service Content') }}</h2>
                            <flux:button :href="route('terms')" target="_blank" variant="ghost" size="sm" icon="arrow-top-right-on-square" class="cursor-pointer">{{ __('View page') }}</flux:button>
                        </div>

                        <flux:field>
                            <flux:label>{{ __('Content (HTML supported)') }}</flux:label>
                            <flux:textarea wire:model.live.debounce.500ms="terms_content" rows="18"
                                           placeholder="{{ __('Write your terms of service here. You can use HTML tags like <h2>, <p>, <ul>, <li>, <a>...') }}" />
                            <flux:description>{{ __('Leave empty to show the built-in default terms of service. Headings, paragraphs and lists are styled automatically.') }}</flux:description>
                            <flux:error name="terms_content" />
                        </flux:field>

                        @if (filled($terms_content))
                            <div class="mt-7">
                                <span class="mb-2 block text-[11px] font-semibold uppercase tracking-wider text-zinc-400">{{ __('Live preview') }}</span>
                                <div class="pg-prose max-h-96 overflow-y-auto rounded-xl border border-(--default-border-color) bg-white p-6 dark:border-white/8 dark:bg-(--default-element-bg-color)">
                                    {!! $terms_content !!}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="flex w-full justify-center mt-4">
                    <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">
                        {{ $tab === 'privacy' ? __('Save Privacy Policy') : __('Save Terms of Service') }}
                    </flux:button>
                </div>
            </flux:fieldset>
        </div>
    </div>
</div>
