<div>
    <div class="rounded-2xl border border-zinc-200 bg-white p-6 md:p-8 dark:border-white/8 dark:bg-(--default-element-light-bg-color)"
         style="--db-brand-1: #4F46E5; --db-brand-2: #0F172A; --db-brand-3: #F59E0B; --db-brand-3-text: #D97706;">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-1">
            <div class="flex items-center gap-2">
                <flux:icon.book-open class="size-4 text-zinc-400" />
                <h2 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Prompt Library') }}</h2>
            </div>
            <button type="button" wire:click="create"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold text-white shadow-sm transition active:scale-95"
                    style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                <flux:icon.plus class="size-4" />
                {{ __('New Prompt') }}
            </button>
        </div>
        <p class="text-[11px] text-zinc-400 mb-6">{{ __('Curated prompts you create here are available to every user inside the Image and Video studios.') }}</p>

        {{-- Tabs (image / video) --}}
        <div class="flex items-center gap-1 mb-4 p-1 rounded-xl bg-zinc-100 dark:bg-white/5 w-fit">
            <button type="button" wire:click="setTab('image')"
                    @class([
                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                        'bg-white text-indigo-700 shadow-sm dark:bg-neutral-900 dark:text-indigo-300' => $tab === 'image',
                        'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'image',
                    ])>
                <flux:icon.photo class="size-4" />
                {{ __('Image Studio') }}
                <span class="ml-0.5 opacity-60">{{ $imageCount }}</span>
            </button>
            <button type="button" wire:click="setTab('video')"
                    @class([
                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                        'bg-white text-indigo-700 shadow-sm dark:bg-neutral-900 dark:text-indigo-300' => $tab === 'video',
                        'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'video',
                    ])>
                <flux:icon.film class="size-4" />
                {{ __('Video Studio') }}
                <span class="ml-0.5 opacity-60">{{ $videoCount }}</span>
            </button>
        </div>

        {{-- Search --}}
        <div class="relative mb-4">
            <flux:icon.magnifying-glass class="size-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="{{ __('Search prompts...') }}"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8" />
        </div>

        {{-- List --}}
        <div class="space-y-2">
            @forelse ($prompts as $prompt)
                <div wire:key="admin-prompt-{{ $prompt->id }}"
                     class="group flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-3.5 transition-all hover:border-indigo-300 hover:shadow-sm dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/50">
                    <div class="min-w-0 flex-1">
                        <h4 class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $prompt->title }}</h4>
                        <p class="text-[12px] text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2 mt-0.5">{{ $prompt->body }}</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button" wire:click="edit({{ $prompt->id }})"
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-indigo-600 hover:bg-indigo-50 transition dark:hover:text-indigo-300 dark:hover:bg-indigo-950/30"
                                title="{{ __('Edit') }}">
                            <flux:icon.pencil-square class="size-4" />
                        </button>
                        <button type="button" wire:click="delete({{ $prompt->id }})"
                                wire:confirm="{{ __('Delete this prompt? It will be removed from every user\'s library. This cannot be undone.') }}"
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 transition dark:hover:text-rose-300 dark:hover:bg-rose-950/30"
                                title="{{ __('Delete') }}">
                            <flux:icon.trash class="size-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center text-center py-12 px-4">
                    <span class="w-12 h-12 rounded-2xl bg-zinc-100 flex items-center justify-center mb-3 dark:bg-white/5">
                        <flux:icon.book-open class="size-6 text-zinc-400" />
                    </span>
                    <p class="text-sm font-bold text-zinc-700 dark:text-zinc-200">{{ __('No prompts yet') }}</p>
                    <p class="text-[12px] text-zinc-500 dark:text-zinc-400 mt-1 max-w-xs">{{ __('Create curated prompts that all users can pick from in the studio.') }}</p>
                </div>
            @endforelse
        </div>

        @if ($prompts->hasPages())
            <div class="mt-4">{{ $prompts->links() }}</div>
        @endif
    </div>

    {{-- ============================ Create / Edit modal ============================ --}}
    <flux:modal wire:model="showModal" class="max-w-lg w-full">
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $promptId ? __('Edit Prompt') : __('New Prompt') }}
                </h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('This prompt will be available to all users.') }}</p>
            </div>

            <flux:field>
                <flux:label>{{ __('Studio') }}</flux:label>
                <div class="flex items-center gap-2 mt-1">
                    <label class="flex-1">
                        <input type="radio" wire:model="type" value="image" class="peer sr-only" />
                        <div class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border text-xs font-bold transition border-zinc-200 text-zinc-500 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 dark:border-white/8 dark:peer-checked:bg-indigo-950/30 dark:peer-checked:text-indigo-300">
                            <flux:icon.photo class="size-4" /> {{ __('Image') }}
                        </div>
                    </label>
                    <label class="flex-1">
                        <input type="radio" wire:model="type" value="video" class="peer sr-only" />
                        <div class="cursor-pointer flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border text-xs font-bold transition border-zinc-200 text-zinc-500 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 dark:border-white/8 dark:peer-checked:bg-indigo-950/30 dark:peer-checked:text-indigo-300">
                            <flux:icon.film class="size-4" /> {{ __('Video') }}
                        </div>
                    </label>
                </div>
                <flux:error name="type" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Title') }}</flux:label>
                <flux:input wire:model="title" maxlength="160" placeholder="{{ __('e.g. Cinematic product hero shot') }}" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Prompt') }}</flux:label>
                <flux:textarea wire:model="body" rows="6" maxlength="4000" placeholder="{{ __('Write the full reusable prompt text...') }}" />
                <flux:error name="body" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="save" variant="primary">{{ $promptId ? __('Update') : __('Create') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
