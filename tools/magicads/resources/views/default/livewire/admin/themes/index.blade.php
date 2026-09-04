<div>
    <style>
        [x-cloak] { display: none !important; }
        /* Shared clean button language — solid black on white (light),
           solid white on black (dark). No gradients. */
        .tbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; width: 100%; padding: .7rem 1rem; border-radius: .75rem;
            font-size: .75rem; font-weight: 600; line-height: 1;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease, opacity .18s ease;
        }
        .tbtn-solid { background:#18181b; color:#fff; }
        .tbtn-solid:hover { background:#27272a; }
        .dark .tbtn-solid { background:#fff; color:#18181b; }
        .dark .tbtn-solid:hover { background:#e4e4e7; }
        .tbtn-muted { background:#f4f4f5; color:#a1a1aa; cursor:not-allowed; }
        .dark .tbtn-muted { background:rgba(255,255,255,.05); color:#71717a; }
        .tbtn-outline { background:transparent; color:#3f3f46; border:1px solid #e4e4e7; }
        .tbtn-outline:hover { background:#fafafa; border-color:#d4d4d8; }
        .dark .tbtn-outline { color:#d4d4d8; border-color:rgba(255,255,255,.12); }
        .dark .tbtn-outline:hover { background:rgba(255,255,255,.04); }
    </style>

    <div
        class="flex justify-center"
        x-data="{
            activeTab: 'all',
            search: '',
            meta: @js($themeMeta),
            matches(slug) {
                const m = this.meta.find(t => t.slug === slug);
                if (!m) return false;
                if (this.activeTab !== 'all' && m.type !== this.activeTab) return false;
                const q = this.search.trim().toLowerCase();
                if (q !== '' && !m.name.includes(q)) return false;
                return true;
            },
            get visibleCount() {
                return this.meta.filter(t => this.matches(t.slug)).length;
            }
        }"
    >
        <div class="w-full lg:w-10/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex justify-center">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Themes') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            {{-- Heading --}}
            <div class="mb-9 text-center">
                <h1 class="font-bold text-2xl">{{ __('Themes') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Select and install your preferred theme with one click') }}</flux:subheading>
            </div>

            {{-- Search --}}
            <div class="max-w-200 mt-7 mb-4 mx-auto">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                    <input
                        type="text"
                        x-model="search"
                        class="w-full text-xs h-11 leading-none pl-11 pr-5 bg-white dark:bg-(--default-element-bg-color) dark:text-zinc-100 rounded-full border border-(--default-border-color) focus:outline-none focus:ring-2 focus:ring-zinc-900/10 dark:focus:ring-white/15 focus:border-zinc-300 dark:focus:border-white/20 transition"
                        id="theme-search-input"
                        placeholder="{{ __('Search themes by name...') }}"
                    >
                </div>
            </div>

            {{-- Tabs --}}
            <div class="max-w-200 mt-4 mb-8 mx-auto">
                <div class="p-1.5 rounded-full border border-(--default-border-color) flex gap-1">
                    @php
                        $tabs = ['all' => __('All'), 'frontend' => __('Frontend'), 'dashboard' => __('Dashboard')];
                    @endphp
                    @foreach ($tabs as $key => $label)
                        <button
                            type="button"
                            @click="activeTab = '{{ $key }}'"
                            class="flex-1 text-xs px-4 py-2 rounded-full font-semibold transition-all duration-200"
                            :class="activeTab === '{{ $key }}'
                                ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
                                : 'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/5'"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($themes as $theme)
                    @php
                        $isDefault = ($theme['slug'] ?? '') === 'default';
                        $record = $extensions->firstWhere('slug', $theme['slug']);
                        $localVersion = $record?->version;
                        $hasUpdate = $record && (float) $localVersion < (float) ($theme['version'] ?? 0);
                        $isActive = $record && ! $isDefault
                            && ($record->slug === $settings->dashboard_theme || $record->slug === $settings->frontend_theme);
                        $defaultActive = $isDefault
                            && $settings->dashboard_theme === 'default'
                            && $settings->frontend_theme === 'default';
                    @endphp

                    <div x-show="matches('{{ $theme['slug'] }}')" x-cloak
                         class="group flex flex-col rounded-2xl overflow-hidden border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) transition-all duration-200 hover:border-zinc-300 dark:hover:border-white/20 hover:shadow-[0_8px_30px_-12px_rgba(0,0,0,0.18)] dark:hover:shadow-none"
                         id="{{ $theme['slug'] }}-card">

                        {{-- Banner --}}
                        <figure class="relative m-0 overflow-hidden aspect-16/10 bg-zinc-100 dark:bg-white/5">
                            <img src="{{ $theme['banner'] ?? '' }}" alt="{{ $theme['name'] ?? '' }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover block transition-transform duration-500 group-hover:scale-105">

                            {{-- Type chip --}}
                            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 text-[10px] font-semibold px-2.5 py-1 rounded-full bg-white/90 dark:bg-zinc-900/80 text-zinc-700 dark:text-zinc-200 backdrop-blur-sm border border-black/5 dark:border-white/10">
                                <i class="fa-solid fa-objects-column text-[9px]"></i>
                                {{ $isDefault ? __('Free') : ucfirst($theme['type'] ?? '') }}
                            </span>

                            {{-- Update chip --}}
                            @if ($hasUpdate)
                                <span class="absolute top-3 right-3 inline-block text-[10px] font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                    {{ __('Update Available') }}
                                </span>
                            @endif

                            {{-- Hover preview --}}
                            @if (!empty($theme['demo_url']))
                                <figcaption class="absolute inset-0 flex items-center justify-center bg-zinc-950/55 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ $theme['demo_url'] }}" target="_blank"
                                       class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-900 bg-white px-4 py-2 rounded-full hover:bg-zinc-100 transition-colors">
                                        <i class="fa-solid fa-eye"></i> {{ __('Live Preview') }}
                                    </a>
                                </figcaption>
                            @endif
                        </figure>

                        {{-- Body --}}
                        <div class="p-5 flex flex-col grow">

                            <div class="mb-2 flex items-center gap-2">
                                <h6 class="text-[15px] font-black leading-tight">
                                    {{ $theme['name'] ?? '' }} {{ __('Theme') }}
                                </h6>
                                @unless ($isDefault)
                                    <span class="text-zinc-400 text-[11px] font-medium">v{{ $theme['version'] ?? '1.0' }}</span>
                                @endunless
                            </div>

                            <p class="text-[13px] text-zinc-500 dark:text-zinc-400 leading-relaxed grow mb-4">
                                {{ $theme['short_description'] ?? '' }}
                            </p>

                            {{-- Meta row --}}
                            <div class="flex items-center gap-3 text-[11px] text-zinc-400 mb-4">
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-star text-amber-500"></i> 5.0</span>
                                <span class="inline-flex items-center gap-1"><i class="fa-solid fa-bolt"></i> {{ __('One Click') }}</span>
                                @if ($isActive || $defaultActive)
                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 ml-auto font-medium">
                                        <i class="fa-solid fa-circle-check"></i> {{ __('Active') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Action --}}
                            <div class="mt-auto">
                                @if ($record && $record->purchased && ! $record->installed)
                                    {{-- Purchased, needs install --}}
                                    <button wire:click="installTheme('{{ $theme['slug'] }}')" wire:loading.attr="disabled"
                                            wire:target="installTheme('{{ $theme['slug'] }}')" class="tbtn tbtn-solid">
                                        <span wire:loading.remove wire:target="installTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-download"></i> {{ __('Install Theme') }}</span>
                                        <span wire:loading wire:target="installTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @elseif ($hasUpdate)
                                    {{-- Update available --}}
                                    <button wire:click="installTheme('{{ $theme['slug'] }}')" wire:loading.attr="disabled"
                                            wire:target="installTheme('{{ $theme['slug'] }}')" class="tbtn tbtn-solid">
                                        <span wire:loading.remove wire:target="installTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ __('Update Theme') }}</span>
                                        <span wire:loading wire:target="installTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Updating…') }}</span>
                                    </button>
                                @elseif ($isDefault)
                                    {{-- Default theme --}}
                                    @if ($defaultActive)
                                        <button disabled class="tbtn tbtn-muted">
                                            <i class="fa-solid fa-circle-check"></i> {{ __('Activated') }}
                                        </button>
                                    @else
                                        <button wire:click="activateTheme('default')" wire:loading.attr="disabled"
                                                wire:target="activateTheme('default')" class="tbtn tbtn-outline">
                                            <span wire:loading.remove wire:target="activateTheme('default')"><i class="fa-solid fa-power-off"></i> {{ __('Activate Theme') }}</span>
                                            <span wire:loading wire:target="activateTheme('default')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Activating…') }}</span>
                                        </button>
                                    @endif
                                @elseif ($record && $record->purchased && $record->installed)
                                    {{-- Owned & installed --}}
                                    @if ($isActive)
                                        <button disabled class="tbtn tbtn-muted">
                                            <i class="fa-solid fa-circle-check"></i> {{ __('Activated') }}
                                        </button>
                                    @else
                                        <button wire:click="activateTheme('{{ $theme['slug'] }}')" wire:loading.attr="disabled"
                                                wire:target="activateTheme('{{ $theme['slug'] }}')" class="tbtn tbtn-outline">
                                            <span wire:loading.remove wire:target="activateTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-power-off"></i> {{ __('Activate Theme') }}</span>
                                            <span wire:loading wire:target="activateTheme('{{ $theme['slug'] }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Activating…') }}</span>
                                        </button>
                                    @endif
                                @else
                                    {{-- Not purchased --}}
                                    <a href="{{ route('admin.themes.checkout', $theme['slug']) }}" wire:navigate class="tbtn tbtn-solid">
                                        <i class="fa-solid fa-cart-shopping"></i> {{ __('Buy Now') }}
                                        <span class="opacity-60">· ${{ $theme['price'] ?? '0' }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Empty state --}}
                <div x-show="visibleCount === 0" x-cloak class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4 bg-zinc-100 dark:bg-white/5">
                        <i class="fa-solid fa-palette text-2xl text-zinc-300 dark:text-zinc-600"></i>
                    </div>
                    <p class="text-zinc-400 text-sm">{{ __('No themes found.') }}</p>
                </div>
            </div>

        </div>
    </div>
</div>
