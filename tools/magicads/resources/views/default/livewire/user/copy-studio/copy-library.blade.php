<div x-data="{ copiedId: null, copyText(txt, id) { navigator.clipboard.writeText(txt).then(() => { this.copiedId = id; setTimeout(() => this.copiedId = null, 1500); }); } }">
    <style>
        /* Custom thin scrollbar for in-card result regions */
        .copy-lib-scroll::-webkit-scrollbar { width: 6px; }
        .copy-lib-scroll::-webkit-scrollbar-track { background: transparent; }
        .copy-lib-scroll::-webkit-scrollbar-thumb {
            background: #d4d4d8; /* zinc-300 */
            border-radius: 9999px;
        }
        .copy-lib-scroll::-webkit-scrollbar-thumb:hover {
            background: #a1a1aa; /* zinc-400 */
        }
        .copy-lib-scroll {
            scrollbar-width: thin;
            scrollbar-color: #d4d4d8 transparent;
        }
        :is(.dark .copy-lib-scroll)::-webkit-scrollbar-thumb {
            background: #52525b; /* zinc-600 */
        }
        :is(.dark .copy-lib-scroll)::-webkit-scrollbar-thumb:hover {
            background: #71717a; /* zinc-500 */
        }
    </style>
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Copy Library') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <a href="{{ route('user.copy.studio') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-white/5 dark:hover:bg-white/10 dark:border-white/10 transition">
                    <flux:icon.plus class="size-3.5" /> {{ __('New copy') }}
                </a>
            </div>

            {{-- Hero --}}
            <div class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40 bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(79,70,229,0.22),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(245,158,11,0.14),transparent)]">
                <div class="absolute -top-24 -right-16 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none"></div>
                <div class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-indigo-400/60 to-transparent"></div>
                <div class="relative px-6 md:px-8 py-10 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.book-open class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight">{{ __('Copy Library') }}</h1>
                            <p class="text-xs text-zinc-400 mt-1">{{ __('Every ad copy you have generated. Reuse, favorite, or export anything.') }}</p>
                        </div>
                    </div>

                    {{-- Search + filters --}}
                    <div class="flex flex-col sm:flex-row items-stretch gap-2 w-full md:w-auto">
                        <div class="relative">
                            <flux:icon.magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-500" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search copies...') }}" class="pl-9 pr-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-hidden focus:border-indigo-400/50 focus:ring-2 focus:ring-indigo-400/20 backdrop-blur-sm transition" />
                        </div>
                        <select wire:model.live="platformFilter" class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-zinc-200 focus:outline-hidden focus:border-indigo-400/50 focus:ring-2 focus:ring-indigo-400/20 backdrop-blur-sm transition">
                            <option value="" class="bg-zinc-900 text-zinc-200">{{ __('All platforms') }}</option>
                            @foreach($platforms as $slug => $p)
                                <option value="{{ $slug }}" class="bg-zinc-900 text-zinc-200">{{ $p['label'] }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-zinc-200 cursor-pointer hover:bg-white/10 transition">
                            <input type="checkbox" wire:model.live="favoritesOnly" class="sr-only peer" />
                            <flux:icon.star class="size-3.5 text-zinc-500 peer-checked:fill-amber-400 peer-checked:text-amber-400" />
                            {{ __('Favorites') }}
                        </label>
                    </div>
                </div>
            </div>

            {{-- Grid --}}
            @if($copies->isEmpty())
                <div class="rounded-3xl border border-dashed border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-16 text-center">
                    <flux:icon.inbox class="size-10 mx-auto text-zinc-300 dark:text-neutral-600 mb-3" />
                    <h3 class="text-base font-bold text-zinc-800 dark:text-zinc-100">{{ __('Nothing here yet') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Generate your first ad copy to populate your library.') }}</p>
                    <a href="{{ route('user.copy.studio') }}" wire:navigate class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-xl transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <flux:icon.sparkles class="size-4" /> {{ __('Generate new copy') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($copies as $c)
                        @php
                            $platform     = config("ad-copy.platforms.{$c->platform}");
                            $platformFields = is_array($platform['fields'] ?? null) ? $platform['fields'] : [];
                            $allVariants  = is_array($c->variants) ? array_values($c->variants) : [];
                            if (empty($allVariants)) {
                                $allVariants = [[]];
                            }
                            $variantCount = count($allVariants);

                            // Per-variant "Copy all" payload: every field, label + value, in platform order.
                            $copyTexts = [];
                            foreach ($allVariants as $variant) {
                                $variant = is_array($variant) ? $variant : [];
                                $copyLines = [];
                                if ($c->title) {
                                    $copyLines[] = $c->title;
                                    $copyLines[] = str_repeat('─', max(8, min(40, mb_strlen($c->title))));
                                }
                                if (! empty($platformFields)) {
                                    foreach ($platformFields as $fs => $fm) {
                                        $raw = $variant[$fs] ?? null;
                                        $val = is_array($raw) ? implode("\n", $raw) : (string) ($raw ?? '');
                                        $val = trim($val);
                                        if ($val === '') {
                                            continue;
                                        }
                                        $copyLines[] = strtoupper($fm['label']) . ":\n" . $val;
                                    }
                                } else {
                                    // Fallback: if platform config missing, dump raw variant fields with their keys.
                                    foreach ($variant as $k => $v) {
                                        $val = is_array($v) ? implode("\n", $v) : (string) $v;
                                        if (trim($val) === '') continue;
                                        $copyLines[] = strtoupper(str_replace('_', ' ', (string) $k)) . ":\n" . trim($val);
                                    }
                                }
                                $copyTexts[] = implode("\n\n", $copyLines);
                            }
                        @endphp
                        @php $isFocus = $focus && (int) $focus === (int) $c->id; @endphp
                        <div wire:key="copy-{{ $c->id }}" id="copy-{{ $c->id }}" class="group relative flex scroll-mt-24"
                             x-data="{ menu: false, focused: @js($isFocus), activeVariant: 0, variantCount: {{ $variantCount }}, copyTexts: @js($copyTexts) }"
                             x-init="if (focused) { $nextTick(() => { $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(() => focused = false, 2200); }); }">
                            <div class="relative rounded-2xl border bg-white dark:bg-(--default-element-bg-color) overflow-hidden flex flex-col flex-1 transition-colors duration-300 hover:border-indigo-300 dark:hover:border-indigo-700/60"
                                 :class="focused ? 'border-indigo-400 ring-2 ring-indigo-400/60 dark:ring-indigo-500/50' : 'border-zinc-200 dark:border-white/8'">
                                {{-- Header --}}
                                    <div class="flex items-start justify-between p-4 border-b border-zinc-100 dark:border-white/6 shrink-0">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="relative w-9 h-9 rounded-xl bg-zinc-900 border border-zinc-800 ring-1 ring-{{ $c->platformTint() }}-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                                <flux:icon name="{{ $c->platformIcon() }}" class="size-4 text-{{ $c->platformTint() }}-400" />
                                            </span>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $c->platformLabel() }}</div>
                                                <div class="text-[10px] text-zinc-500">{{ count($c->variants ?? []) }} {{ __('variants') }} · {{ $c->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" wire:click="toggleFavorite({{ $c->id }})" class="w-7 h-7 rounded-lg flex items-center justify-center transition {{ $c->is_favorite ? 'text-amber-500' : 'text-zinc-400 hover:text-amber-500' }}" aria-label="{{ __('Favorite') }}">
                                                <flux:icon.star class="size-4 {{ $c->is_favorite ? 'fill-amber-500' : '' }}" />
                                            </button>
                                            <div class="relative" @click.stop>
                                                <button type="button" @click="menu = !menu" @click.away="menu = false" class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-50 dark:hover:bg-white/5 transition" aria-label="{{ __('Menu') }}">
                                                    <flux:icon.ellipsis-horizontal class="size-4" />
                                                </button>
                                                <div x-show="menu" x-cloak x-transition class="absolute right-0 mt-1.5 w-44 rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) shadow-xl z-20 overflow-hidden">
                                                    <a href="{{ route('user.copy.studio', ['reuse' => $c->id]) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/5 transition"><flux:icon.arrow-path class="size-3.5" /> {{ __('Reuse brief') }}</a>
                                                    <div class="h-px bg-zinc-100 dark:bg-(--default-element-light-bg-color)"></div>
                                                    <button wire:click="delete({{ $c->id }})" wire:confirm="{{ __('Delete this copy?') }}" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition"><flux:icon.trash class="size-3.5" /> {{ __('Delete') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Title (above the scroll region, so the fixed body stays equal-height) --}}
                                    @if($c->title)
                                        <div class="px-4 pt-3 pb-2 shrink-0">
                                            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 line-clamp-2 leading-snug">{{ $c->title }}</div>
                                        </div>
                                    @endif

                                    {{-- Variant switcher: only when more than one variant exists --}}
                                    @if($variantCount > 1)
                                        <div class="px-4 pt-1 pb-2 shrink-0">
                                            <div class="flex items-center gap-1 overflow-x-auto copy-lib-scroll -mx-0.5 px-0.5 py-0.5">
                                                @foreach($allVariants as $idx => $variant)
                                                    <button type="button"
                                                            @click="activeVariant = {{ $idx }}"
                                                            class="shrink-0 px-2.5 py-1 rounded-lg text-[10px] font-bold tabular-nums transition-colors border"
                                                            :class="activeVariant === {{ $idx }}
                                                                ? 'text-indigo-600 dark:text-indigo-300 border-indigo-500 dark:border-indigo-400 ring-1 ring-indigo-500/40 dark:ring-indigo-400/40'
                                                                : 'text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-white/10 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300 dark:hover:border-indigo-700/50'">
                                                        {{ __('Variant :n', ['n' => $idx + 1]) }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Result body: every field of the platform, internally scrollable, fixed height.
                                         One block per variant; only the active one is shown. --}}
                                    <div class="px-4 pt-2 pb-4 shrink-0">
                                        <div class="copy-lib-scroll relative h-[250px] overflow-y-auto pr-1.5 -mr-1.5">
                                            @foreach($allVariants as $idx => $variant)
                                                @php $variant = is_array($variant) ? $variant : []; @endphp
                                                <div x-show="activeVariant === {{ $idx }}" x-cloak class="space-y-2">
                                                    @if(! empty($platformFields))
                                                        @foreach($platformFields as $fs => $fm)
                                                            @php
                                                                $raw = $variant[$fs] ?? null;
                                                                $val = is_array($raw) ? implode("\n", $raw) : (string) ($raw ?? '');
                                                                $val = trim($val);
                                                                $len = mb_strlen($val);
                                                                $limit = (int) ($fm['limit'] ?? 0);
                                                                $over  = $limit > 0 && $len > $limit;
                                                                $empty = $val === '';
                                                            @endphp
                                                            <div class="rounded-lg border border-zinc-200/80 dark:border-white/6 bg-zinc-50/60 dark:bg-neutral-950/40 px-2.5 py-2 transition-colors hover:border-indigo-200 dark:hover:border-indigo-900/60">
                                                                <div class="flex items-center justify-between gap-2 mb-1">
                                                                    <span class="text-[9px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400 truncate">{{ $fm['label'] }}</span>
                                                                    @if($limit > 0)
                                                                        <span class="text-[9px] font-mono tabular-nums {{ $over ? 'text-rose-500' : 'text-zinc-400 dark:text-zinc-500' }}">{{ $len }}/{{ $limit }}</span>
                                                                    @endif
                                                                </div>
                                                                @if($empty)
                                                                    <p class="text-[11px] italic text-zinc-400 dark:text-zinc-600">{{ __('Not generated') }}</p>
                                                                @else
                                                                    <p class="text-[12px] leading-relaxed text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap break-words">{{ $val }}</p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @elseif(! empty($variant))
                                                        {{-- Fallback for orphaned platform configs --}}
                                                        @foreach($variant as $k => $v)
                                                            @php
                                                                $val = is_array($v) ? implode("\n", $v) : (string) $v;
                                                                $val = trim($val);
                                                            @endphp
                                                            @if($val !== '')
                                                                <div class="rounded-lg border border-zinc-200/80 dark:border-white/6 bg-zinc-50/60 dark:bg-neutral-950/40 px-2.5 py-2">
                                                                    <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400 mb-1">{{ str_replace('_', ' ', (string) $k) }}</div>
                                                                    <p class="text-[12px] leading-relaxed text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap break-words">{{ $val }}</p>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="h-full flex flex-col items-center justify-center text-center px-4">
                                                            <flux:icon.document class="size-6 text-zinc-300 dark:text-neutral-700 mb-2" />
                                                            <p class="text-[11px] text-zinc-400">{{ __('No content available') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Footer: pinned to the bottom for equal-height alignment --}}
                                    <div class="mt-auto flex items-center justify-between gap-2 px-4 py-3 bg-zinc-50/70 dark:bg-(--default-element-bg-color) border-t border-zinc-100 dark:border-white/6 shrink-0">
                                        <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                            @if($c->framework)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 border border-indigo-100 text-indigo-700 dark:bg-indigo-950/30 dark:border-indigo-900/40 dark:text-indigo-300">{{ config("ad-copy.frameworks.{$c->framework}.label", $c->framework) }}</span>
                                            @endif
                                            @if($c->tone)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 border border-amber-100 text-amber-700 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300">{{ Str::title($c->tone) }}</span>
                                            @endif
                                        </div>
                                        <button type="button"
                                                x-on:click="copyText(copyTexts[activeVariant], {{ $c->id }})"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-300 transition shrink-0">
                                            <template x-if="copiedId === {{ $c->id }}">
                                                <span class="inline-flex items-center gap-1 text-emerald-600"><flux:icon.check class="size-3" /> {{ __('Copied') }}</span>
                                            </template>
                                            <template x-if="copiedId !== {{ $c->id }}">
                                                <span class="inline-flex items-center gap-1"><flux:icon.document-duplicate class="size-3" />
                                                    <span x-text="variantCount > 1 ? '{{ __('Copy variant') }} ' + (activeVariant + 1) : '{{ __('Copy all') }}'"></span>
                                                </span>
                                            </template>
                                        </button>
                                    </div>
                                </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    <flux:pagination :paginator="$copies" />
                </div>
            @endif
        </div>
    </div>
</div>
