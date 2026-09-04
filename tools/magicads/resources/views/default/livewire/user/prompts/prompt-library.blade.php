@php
    // Brand palette (locked, see .kiro/steering/brand-palette.md)
    $brandPrimary    = '#4F46E5'; // db-brand-1
    $brandSecondary  = '#0F172A'; // db-brand-2
    $brandAccent     = '#F59E0B'; // db-brand-3 (UI only — never text)
    $brandAccentText = '#D97706'; // db-brand-3-text
@endphp

<div>
    <flux:modal name="prompt-library" :closable="false" class="max-w-3xl w-full" @prompt-selected.window="$dispatch('modal-close', { name: 'prompt-library' })">
        <div class="flex flex-col max-h-[82vh]"
             @open-prompt-library.window="$wire.setTab($event.detail.context)"
             style="--db-brand-1: {{ $brandPrimary }}; --db-brand-2: {{ $brandSecondary }}; --db-brand-3: {{ $brandAccent }}; --db-brand-3-text: {{ $brandAccentText }};">

            {{-- ============================ Header ============================ --}}
            <div class="relative shrink-0 overflow-hidden rounded-xl p-5 mb-4 text-white border border-zinc-800/90 bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(79,70,229,0.22),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(245,158,11,0.14),transparent)]">
                <flux:modal.close>
                    <button type="button" aria-label="{{ __('Close') }}"
                            class="absolute top-3 right-3 z-10 inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/10 text-white ring-1 ring-white/20 backdrop-blur hover:bg-white/20 hover:ring-white/40 transition active:scale-95">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </flux:modal.close>
                <div class="relative flex items-start gap-3">
                    <span class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center shrink-0 ring-1 ring-white/20">
                        <flux:icon.book-open class="size-5 text-white" />
                    </span>
                    <div class="min-w-0 pr-10">
                        <h3 class="text-base font-bold leading-tight">{{ __('Prompt Library') }}</h3>
                        <p class="text-[12px] text-white/80 mt-0.5">{{ __('Browse curated prompts, save your own, and reuse your favorites.') }}</p>
                    </div>
                </div>
            </div>

            {{-- ============================ Studio type tabs ============================ --}}
            <div class="flex items-center gap-1 mb-3 p-1 rounded-xl bg-zinc-100 dark:bg-white/5 w-fit">
                <button type="button" wire:click="setTab('image')"
                        @class([
                            'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                            'bg-white text-indigo-700 shadow-sm dark:bg-neutral-900 dark:text-indigo-300' => $tab === 'image',
                            'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'image',
                        ])>
                    <flux:icon.photo class="size-4" />
                    {{ __('Image Prompts') }}
                </button>
                <button type="button" wire:click="setTab('video')"
                        @class([
                            'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all',
                            'bg-white text-indigo-700 shadow-sm dark:bg-neutral-900 dark:text-indigo-300' => $tab === 'video',
                            'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== 'video',
                        ])>
                    <flux:icon.film class="size-4" />
                    {{ __('Video Prompts') }}
                </button>
            </div>

            {{-- ============================ Toolbar: search + filters + add ============================ --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                <div class="relative flex-1">
                    <flux:icon.magnifying-glass class="size-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('Search prompts...') }}"
                           class="w-full pl-9 pr-3 py-2 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8" />
                </div>

                <div class="flex items-center gap-1 p-1 rounded-lg bg-zinc-100 dark:bg-white/5">
                    @foreach (['all' => __('All'), 'mine' => __('Mine'), 'favorites' => __('Favorites')] as $key => $label)
                        <button type="button" wire:click="setFilter('{{ $key }}')"
                                @class([
                                    'px-2.5 py-1 rounded-md text-[11px] font-bold transition-all',
                                    'bg-white text-indigo-700 shadow-sm dark:bg-neutral-900 dark:text-indigo-300' => $filter === $key,
                                    'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $filter !== $key,
                                ])>
                            {{ $label }}
                            @if ($key === 'mine' && $myCount > 0)
                                <span class="ml-0.5 opacity-60">{{ $myCount }}</span>
                            @elseif ($key === 'favorites' && $favCount > 0)
                                <span class="ml-0.5 opacity-60">{{ $favCount }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <button type="button" wire:click="toggleCreate"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold text-white bg-zinc-900 hover:bg-indigo-600 shadow-sm transition active:scale-95 dark:bg-neutral-950 dark:hover:bg-neutral-900 dark:border dark:border-white/8">
                    <flux:icon.plus class="size-4" />
                    {{ __('New Prompt') }}
                </button>
            </div>

            {{-- ============================ Inline create form ============================ --}}
            @if ($showCreate)
                <div class="mb-3 rounded-xl border border-indigo-200 bg-indigo-50/60 p-4 dark:border-indigo-900/40 dark:bg-indigo-950/20">
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon.pencil-square class="size-4 text-indigo-600 dark:text-indigo-400" />
                        <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-100">
                            {{ $tab === 'video' ? __('New video prompt') : __('New image prompt') }}
                        </h4>
                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ __('(private to you)') }}</span>
                    </div>

                    <input type="text" wire:model="newTitle" maxlength="160"
                           placeholder="{{ __('Title — e.g. Cinematic product hero') }}"
                           class="w-full mb-2 px-3 py-2 rounded-lg border border-zinc-200 bg-white text-sm text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8" />
                    <flux:error name="newTitle" />

                    <textarea wire:model="newBody" rows="4" maxlength="4000"
                              placeholder="{{ __('Write your reusable prompt text here...') }}"
                              class="w-full mt-2 px-3 py-2 rounded-lg border border-zinc-200 bg-white text-sm leading-relaxed text-zinc-800 placeholder:text-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition resize-y min-h-[90px] dark:bg-(--default-element-bg-color) dark:text-zinc-200 dark:border-white/8"></textarea>
                    <flux:error name="newBody" />

                    <div class="flex justify-end gap-2 mt-3">
                        <button type="button" wire:click="toggleCreate"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 transition dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-white/5">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="saveNew"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white shadow-sm transition active:scale-95"
                                style="background: linear-gradient(120deg, var(--db-brand-1), var(--db-brand-2));">
                            <flux:icon.check class="size-4" />
                            {{ __('Save Prompt') }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- ============================ Prompt list ============================ --}}
            <div class="flex-1 overflow-y-auto -mx-1 px-1 space-y-2 min-h-[200px]">
                @forelse ($prompts as $prompt)
                    @php $isFav = in_array($prompt->id, $favoriteIds, true); @endphp
                    <div wire:key="prompt-{{ $prompt->id }}"
                         x-data="{ expanded: false }"
                         class="group rounded-xl border border-zinc-200 bg-white p-3.5 transition-all hover:border-indigo-300 hover:shadow-sm dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/50">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                    <h4 class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $prompt->title }}</h4>
                                    @if ($prompt->is_global)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full text-[9px] font-bold uppercase tracking-wider text-white"
                                              style="background: var(--db-brand-1);">
                                            <flux:icon.sparkles class="size-2.5" />
                                            {{ __('Curated') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-px rounded-full bg-zinc-100 text-[9px] font-bold uppercase tracking-wider text-zinc-500 dark:bg-white/10 dark:text-zinc-400">
                                            {{ __('Yours') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[12px] text-zinc-500 dark:text-zinc-400 leading-relaxed whitespace-pre-line wrap-break-word"
                                   :class="expanded ? '' : 'line-clamp-2'">{{ $prompt->body }}</p>
                                <button type="button" x-on:click="expanded = !expanded"
                                        class="mt-1 inline-flex items-center gap-0.5 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
                                    <span x-text="expanded ? '{{ __('Show less') }}' : '{{ __('Read more') }}'"></span>
                                    <flux:icon.chevron-down class="size-3 transition-transform" ::class="expanded ? 'rotate-180' : ''" />
                                </button>
                            </div>

                            {{-- favorite toggle --}}
                            <button type="button" wire:click="toggleFavorite({{ $prompt->id }})"
                                    title="{{ $isFav ? __('Remove from favorites') : __('Add to favorites') }}"
                                    class="shrink-0 p-1.5 rounded-lg transition hover:bg-amber-50 dark:hover:bg-amber-950/30">
                                @if ($isFav)
                                    <flux:icon.star variant="solid" class="size-4 text-amber-500" />
                                @else
                                    <flux:icon.star class="size-4 text-zinc-300 group-hover:text-amber-400 dark:text-zinc-600" />
                                @endif
                            </button>
                        </div>

                        <div class="flex items-center justify-end gap-1.5 mt-2.5 pt-2.5 border-t border-zinc-100 dark:border-white/5">
                            @if (! $prompt->is_global && $prompt->user_id === auth()->id())
                                <button type="button" wire:click="delete({{ $prompt->id }})"
                                        wire:confirm="{{ __('Delete this prompt? This cannot be undone.') }}"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-semibold text-zinc-500 hover:text-rose-600 hover:bg-rose-50 transition dark:text-zinc-400 dark:hover:text-rose-300 dark:hover:bg-rose-950/30">
                                    <flux:icon.trash class="size-3.5" />
                                    {{ __('Delete') }}
                                </button>
                            @endif
                            <button type="button" wire:click="use({{ $prompt->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-bold border border-zinc-200 text-zinc-700 hover:border-indigo-600 hover:text-indigo-700 transition active:scale-95 dark:border-white/8 dark:text-zinc-200 dark:hover:border-indigo-500 dark:hover:text-indigo-300">
                                <flux:icon.arrow-down-on-square class="size-3.5" />
                                {{ __('Use this prompt') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center text-center py-12 px-4">
                        <span class="w-12 h-12 rounded-2xl bg-zinc-100 flex items-center justify-center mb-3 dark:bg-white/5">
                            <flux:icon.book-open class="size-6 text-zinc-400" />
                        </span>
                        <p class="text-sm font-bold text-zinc-700 dark:text-zinc-200">
                            @if ($filter === 'favorites')
                                {{ __('No favorites yet') }}
                            @elseif ($filter === 'mine')
                                {{ __('You haven\'t created any prompts yet') }}
                            @else
                                {{ __('No prompts found') }}
                            @endif
                        </p>
                        <p class="text-[12px] text-zinc-500 dark:text-zinc-400 mt-1 max-w-xs">
                            {{ __('Create your own reusable prompt with the New Prompt button above.') }}
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- ============================ Footer ============================ --}}
            <div class="flex justify-end pt-4 mt-2 border-t border-zinc-100 dark:border-white/5">
                <flux:modal.close>
                    <button type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white bg-zinc-900 hover:bg-indigo-600 shadow-sm transition active:scale-95 dark:bg-neutral-950 dark:hover:bg-neutral-900 dark:border dark:border-white/8">
                        {{ __('Close') }}
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
