<div>
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar --}}
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Brand Kit') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('user.brands.create') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 dark:text-zinc-300 dark:hover:text-white dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:border-zinc-700 transition">
                        <flux:icon.plus class="size-4 relative" />
                        <span class="relative">{{ __('Create Brand') }}</span>
                    </a>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Hero Banner — Brands Command Deck           --}}
            {{-- ========================================== --}}
            <div data-brand-hero class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 dark:border-white/6 shadow-sm shadow-neutral-950/40">
                <style>
                    /* Light mode: keep the original command-deck radial wash. */
                    [data-brand-hero] {
                        background-color: #09090b; /* zinc-950 */
                        background-image:
                            radial-gradient(ellipse 80% 50% at 10% -10%, rgba(79,70,229,0.22), transparent),
                            radial-gradient(ellipse 80% 50% at 110% 110%, rgba(245,158,11,0.14), transparent);
                    }
                    /* Dark mode: match the user dashboard hero gradient exactly. */
                    .dark [data-brand-hero] {
                        background-color: #000000;
                        background-image: linear-gradient(to bottom right, #0b0b11, #070709, #000000);
                    }
                </style>
                <div class="absolute -top-24 -right-16 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none"></div>
                <div class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-indigo-400/60 to-transparent"></div>
                
                <div class="relative px-6 md:px-8 py-10 flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.sparkles class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">    
                            <h1 class="text-xl md:text-2xl font-extrabold text-white leading-tight tracking-tight">{{ __('Your Brands') }}</h1>
                            <p class="text-xs text-zinc-400 mt-1 max-w-md">
                                {{ __('Every brand you add becomes context for the AI — the more you define, the more on-brand every ad gets.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="relative flex-1 md:flex-none md:w-64">
                            <flux:icon.magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-500" />
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search brands...') }}" class="w-full pl-9 pr-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-zinc-200 placeholder:text-zinc-500 focus:outline-hidden focus:border-indigo-400/50 focus:ring-2 focus:ring-indigo-400/20 backdrop-blur-sm transition" />
                        </div>                        
                    </div>
                </div>
            </div>

            @if($brands->isEmpty() && ! $search)
                {{-- Empty state --}}
                <div class="relative overflow-hidden rounded-3xl border border-dashed border-zinc-200 dark:border-white/8 dark:from-neutral-900 dark:to-neutral-950 p-16 text-center">
                    <div class="absolute inset-0 pointer-events-none opacity-30" style="background-image: radial-gradient(circle at 30% 20%, rgba(99,102,241,0.15), transparent 40%), radial-gradient(circle at 70% 80%, rgba(139,92,246,0.10), transparent 40%);"></div>
                    <div class="relative">
                        <div class="inline-flex w-16 h-16 rounded-2xl bg-linear-to-br from-indigo-500 to-violet-600 p-px mb-4 shadow-sm shadow-indigo-500/25">
                            <div class="w-full h-full rounded-[15px] bg-white dark:bg-(--default-element-bg-color) flex items-center justify-center">
                                <flux:icon.sparkles class="size-8 text-indigo-500" />
                            </div>
                        </div>
                        <h2 class="text-lg font-bold text-zinc-800 dark:text-zinc-100 mb-1">{{ __('Create your first brand') }}</h2>
                        <p class="text-sm text-zinc-500 max-w-md mx-auto mb-6">{{ __('A brand captures your company identity — logo, colors, voice, values. The AI will use it to craft ads that feel unmistakably yours.') }}</p>
                        <a href="{{ route('user.brands.create') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-xl transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                            <flux:icon.plus class="size-4" /> {{ __('Create new Brand') }}
                        </a>
                    </div>
                </div>
            @elseif($brands->isEmpty())
                <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-light-bg-color) p-10 text-center">
                    <flux:icon.inbox class="size-10 mx-auto text-zinc-300 dark:text-neutral-600 mb-3" />
                    <p class="text-sm text-zinc-500">{{ __('No brands match your search.') }}</p>
                </div>
            @else
                {{-- Brand cards grid — wide glass frame + clean white surface --}}
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    @foreach($brands as $b)
                        @php
                            $primary   = $b->primary_color   ?? '#6366f1';
                            $secondary = $b->secondary_color ?? '#8b5cf6';
                            $accent    = $b->accent_color    ?? '#38bdf8';
                            $initial   = mb_strtoupper(mb_substr($b->name, 0, 1));
                            $logoUrl   = $b->logo_path ?: null;
                        @endphp
                        <div wire:key="brand-{{ $b->id }}" class="group relative" x-data="{ menu: false }">
                            <div class="relative rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden transition-colors duration-300 hover:border-indigo-300 dark:hover:border-indigo-700/60">

                                {{-- Default star badge (top-left) --}}
                                @if($b->is_default)
                                    <div class="absolute top-3 left-3 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-widest bg-amber-50 border border-amber-200 text-amber-700 dark:bg-amber-950/40 dark:border-amber-900/40 dark:text-amber-300">
                                        <flux:icon.star class="size-2.5 fill-amber-400 text-amber-400" />
                                        {{ __('Default') }}
                                    </div>
                                @endif

                                {{-- 3-dot menu (top-right) — sibling of the card link, not nested --}}
                                <div class="absolute top-3 right-3 z-10" @click.stop>
                                    <button type="button" @click="menu = !menu" @click.away="menu = false" class="w-7 h-7 rounded-lg bg-white hover:bg-zinc-50 border border-zinc-200 text-zinc-500 hover:text-zinc-700 flex items-center justify-center transition dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:bg-white/10" aria-label="{{ __('Brand menu') }}">
                                        <flux:icon.ellipsis-horizontal class="size-4" />
                                    </button>
                                    <div x-show="menu" x-cloak x-transition class="absolute right-0 mt-1.5 w-48 rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) shadow-xl z-20 overflow-hidden">
                                        <a href="{{ route('user.brands.edit', $b->id) }}" wire:navigate class="flex items-center gap-2 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                                            <flux:icon.pencil-square class="size-3.5" /> {{ __('Edit') }}
                                        </a>
                                        @if(! $b->is_default)
                                            <button type="button" wire:click="setDefault({{ $b->id }})" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                                                <flux:icon.star class="size-3.5" /> {{ __('Set as default') }}
                                            </button>
                                        @endif
                                        <button type="button" wire:click="duplicate({{ $b->id }})" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-white/5 transition">
                                            <flux:icon.document-duplicate class="size-3.5" /> {{ __('Duplicate') }}
                                        </button>
                                        <div class="h-px bg-zinc-100 dark:bg-(--default-element-light-bg-color)"></div>
                                        <button type="button" wire:click="delete({{ $b->id }})" wire:confirm="{{ __('Delete this brand? This cannot be undone.') }}" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition">
                                            <flux:icon.trash class="size-3.5" /> {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Main clickable area — a single anchor, no nested interactive elements --}}
                                <a href="{{ route('user.brands.edit', $b->id) }}" wire:navigate class="block relative">

                                    {{-- Logo zone — centered circle logo --}}
                                    <div class="relative flex items-center justify-center px-4 py-6">

                                        {{-- Circle logo container — fixed size for uniformity --}}
                                        <div class="relative w-[104px] h-[104px] rounded-full overflow-hidden border border-zinc-200 dark:border-white/8 shrink-0">
                                            @if($logoUrl)
                                                <img src="{{ URL::asset($logoUrl) }}" alt="{{ $b->name }}"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                                {{-- Graceful fallback if image fails to load --}}
                                                <div class="hidden items-center justify-center w-full h-full text-2xl font-black text-white"
                                                     style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                                                    {{ $initial }}
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center w-full h-full text-2xl font-black text-white"
                                                     style="background: linear-gradient(135deg, {{ $primary }}, {{ $secondary }});">
                                                    {{ $initial }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Title + meta section --}}
                                    <div class="px-5 py-4 border-t border-zinc-100 dark:border-white/6 text-center">
                                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $b->name }}</h3>
                                        @if($b->industry)
                                            <p class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mt-1">{{ $b->industry }}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $brands->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
