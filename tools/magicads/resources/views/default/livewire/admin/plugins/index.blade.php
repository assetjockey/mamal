<div>
    <style>
        [x-cloak] { display: none !important; }

        /* Plugin button language — matches the Themes screens: solid black on
           white (light) / solid white on black (dark). No brand gradients. */
        .pbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; width: 100%; padding: .7rem 1rem; border-radius: .75rem;
            font-size: .75rem; font-weight: 600; line-height: 1;
            transition: background-color .18s ease, color .18s ease, border-color .18s ease, opacity .18s ease;
        }
        .pbtn-solid { background:#18181b; color:#fff; }
        .pbtn-solid:hover { background:#27272a; }
        .dark .pbtn-solid { background:#fff; color:#18181b; }
        .dark .pbtn-solid:hover { background:#e4e4e7; }
        .pbtn-outline { background:transparent; color:#3f3f46; border:1px solid #e4e4e7; }
        .pbtn-outline:hover { background:#fafafa; border-color:#d4d4d8; }
        .dark .pbtn-outline { color:#d4d4d8; border-color:rgba(255,255,255,.12); }
        .dark .pbtn-outline:hover { background:rgba(255,255,255,.04); }
        .pbtn-muted { background:#f4f4f5; color:#a1a1aa; cursor:not-allowed; }
        .dark .pbtn-muted { background:rgba(255,255,255,.05); color:#71717a; }
        .pbtn-danger { background:transparent; color:#e11d48; border:1px solid #fecdd3; }
        .pbtn-danger:hover { background:#fff1f2; border-color:#fda4af; }
        .dark .pbtn-danger { color:#fb7185; border-color:rgba(251,113,133,.3); }
        .dark .pbtn-danger:hover { background:rgba(251,113,133,.08); }

        /* Plugin icon — the marketplace `banner` carries a FontAwesome glyph.
           We paint the glyph itself with a brand gradient via background-clip:text
           (no solid box behind it). Light uses the text-safe amber (#D97706);
           dark switches to primary-on-dark + UI amber so both stops stay legible. */
        .plugin-ic i {
            font-size: 3.25rem; line-height: 1;
            background: linear-gradient(135deg, #818CF8 0%, #4F46E5 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }
        .dark .plugin-ic i {
            background: linear-gradient(135deg, #A5B4FC 0%, #6366F1 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .plugin-ic svg, .plugin-ic img { width: 3rem; height: 3rem; }

        /* Featured card gradient-border fill — white (light) / surface (dark) so
           the brand gradient frames a panel that matches the page. */
        [data-plugins-grid] { --plugin-card-bg: #ffffff; }
        .dark [data-plugins-grid] { --plugin-card-bg: #18181b; }
    </style>

    <div
        data-plugins-grid
        class="flex justify-center"
        x-data="{
            activeTab: 'all',
            search: '',
            meta: @js($pluginMeta),
            get installed() { return $wire.installedSlugs },
            matches(slug) {
                const m = this.meta.find(p => p.slug === slug);
                if (!m) return false;
                if (this.activeTab === 'installed' && !this.installed.includes(slug)) return false;
                if (this.activeTab === 'free' && !m.free) return false;
                if (this.activeTab === 'paid' && m.free) return false;
                const q = this.search.trim().toLowerCase();
                if (q !== '' && !m.name.includes(q) && !m.desc.includes(q) && !m.tags.includes(q)) return false;
                return true;
            },
            get visibleCount() {
                return this.meta.filter(p => this.matches(p.slug)).length;
            }
        }"
    >
        <div class="w-full lg:w-10/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex justify-center">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Plugins') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            {{-- Heading --}}
            <div class="mb-9 text-center">
                <h1 class="font-bold text-2xl">{{ __('Plugins') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Select and install your preferred plugins with one click') }}</flux:subheading>
            </div>

            {{-- Search --}}
            <div class="max-w-200 mt-7 mb-4 mx-auto">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                    <input
                        type="text"
                        x-model="search"
                        class="w-full text-xs h-11 leading-none pl-11 pr-5 bg-white dark:bg-(--default-element-bg-color) dark:text-zinc-100 rounded-full border border-(--default-border-color) focus:outline-none focus:ring-2 focus:ring-zinc-900/10 dark:focus:ring-white/15 focus:border-zinc-300 dark:focus:border-white/20 transition"
                        id="extension-search-input"
                        placeholder="{{ __('Search your plugins...') }}"
                    >
                </div>
            </div>

            {{-- Tabs --}}
            <div class="max-w-200 mt-4 mb-8 mx-auto">
                <div class="p-1.5 rounded-full border border-(--default-border-color) flex gap-1">
                    @php
                        $tabs = ['all' => __('All'), 'installed' => __('Installed'), 'paid' => __('Paid'), 'free' => __('Free')];
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="templates-panel">
                @foreach ($extensions as $extension)
                    @php
                        $slug = $extension['slug'] ?? '';
                        $banner = trim($extension['banner'] ?? '');
                        $localVersion = $detailVersions[$slug] ?? null;
                        $isInstalled = in_array($slug, $installedSlugs, true);
                        $isPurchased = in_array($slug, $purchasedSlugs, true);
                        $isFree = (bool) ($extension['is_free'] ?? false);
                        $freeForExtended = (bool) ($extension['free_for_extended'] ?? false);
                        $extendedInstall = $freeForExtended && $isExtendedLicense && ! $isFree;
                        $hasUpdate = $localVersion !== null && (float) $localVersion < (float) ($extension['version'] ?? 0);
                        $price = $extension['price'] ?? '0';
                    @endphp

                    <div x-show="matches('{{ $slug }}')" x-cloak
                         @class([
                            'group flex flex-col rounded-2xl bg-white dark:bg-(--default-element-bg-color) transition-all duration-200 hover:shadow-[0_8px_30px_-12px_rgba(0,0,0,0.18)] dark:hover:shadow-none',
                            'border border-zinc-200 dark:border-white/10 hover:border-transparent dark:hover:border-transparent' => !($extension['is_featured'] ?? false),
                            'border border-transparent' => $extension['is_featured'] ?? false,
                         ])
                         @if ($extension['is_featured'] ?? false)
                            style="background: linear-gradient(var(--plugin-card-bg), var(--plugin-card-bg)) padding-box, linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B) border-box;"
                         @endif
                         id="{{ $slug }}-card">

                        <div class="p-8 flex flex-col grow">

                            {{-- Icon + status --}}
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <a href="{{ route('admin.plugins.checkout', $slug) }}" wire:navigate
                                   class="plugin-ic shrink-0 flex items-center justify-center">
                                    @if ($banner !== '')
                                        {!! $banner !!}
                                    @else
                                        <i class="fa-solid fa-plug"></i>
                                    @endif
                                </a>

                                <div class="flex flex-col items-end gap-1.5">
                                    @if ($hasUpdate)
                                        <span class="inline-block text-[10px] font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                            {{ __('Update Available') }}
                                        </span>
                                    @elseif ($isInstalled)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                                            <i class="fa-solid fa-circle-check"></i> {{ __('Installed') }}
                                        </span>
                                    @endif
                                    @if ($isFree)
                                        <span class="inline-block text-[10px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">{{ __('Free') }}</span>
                                    @elseif ($extendedInstall)
                                        <span class="inline-block text-[10px] font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">{{ __('Free for Extended') }}</span>
                                    @endif
                                    @if ($extension['is_new'] ?? false)
                                        <span class="inline-block text-[10px] font-semibold px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">{{ __('New') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Name + rating --}}
                            <div class="mb-2 flex items-center gap-2">
                                <h6 class="text-[15px] font-black leading-tight">
                                    {{ $extension['name'] ?? '' }}
                                </h6>
                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-500 ml-auto shrink-0">
                                    <i class="fa-solid fa-star"></i> 5.0
                                </span>
                            </div>

                            <p class="text-[13px] text-zinc-500 dark:text-zinc-400 leading-relaxed grow mb-4">
                                {{ $extension['short_description'] ?? '' }}
                            </p>

                            {{-- Tags --}}
                            @if (!empty($extension['tags']))
                                <div class="flex flex-wrap gap-x-3 gap-y-1 mb-4">
                                    @foreach (array_slice(explode(',', $extension['tags']), 0, 3) as $tag)
                                        <span class="text-[11px] text-zinc-400">
                                            <i class="fa-solid fa-circle text-[4px] mr-1 align-middle"></i>{{ trim($tag) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Action --}}
                            <div class="mt-3" wire:key="cta-{{ $slug }}">
                                @if ($hasUpdate)
                                    <button wire:click="install('{{ $slug }}')" wire:loading.attr="disabled"
                                            wire:target="install('{{ $slug }}')" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install('{{ $slug }}')"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ __('Update') }}</span>
                                        <span wire:loading wire:target="install('{{ $slug }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Updating…') }}</span>
                                    </button>
                                @elseif ($isInstalled)
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.plugins.checkout', $slug) }}" wire:navigate class="pbtn pbtn-outline flex-1">
                                            <i class="fa-solid fa-sliders"></i> {{ __('Manage') }}
                                        </a>
                                        <button wire:click="uninstall('{{ $slug }}')" wire:loading.attr="disabled"
                                                wire:target="uninstall('{{ $slug }}')"
                                                wire:confirm="{{ __('Are you sure you want to uninstall this plugin?') }}"
                                                class="pbtn pbtn-danger" style="width:auto; padding-left:.9rem; padding-right:.9rem;">
                                            <span wire:loading.remove wire:target="uninstall('{{ $slug }}')"><i class="fa-solid fa-trash-can"></i></span>
                                            <span wire:loading wire:target="uninstall('{{ $slug }}')"><i class="fa-solid fa-spinner fa-spin"></i></span>
                                        </button>
                                    </div>
                                @elseif ($isPurchased || $isFree)
                                    <button wire:click="install('{{ $slug }}')" wire:loading.attr="disabled"
                                            wire:target="install('{{ $slug }}')" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install('{{ $slug }}')"><i class="fa-solid fa-download"></i> {{ __('Install') }}</span>
                                        <span wire:loading wire:target="install('{{ $slug }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @elseif ($extendedInstall)
                                    <button wire:click="install('{{ $slug }}')" wire:loading.attr="disabled"
                                            wire:target="install('{{ $slug }}')" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install('{{ $slug }}')"><i class="fa-solid fa-download"></i> {{ __('Install') }} <span class="opacity-70">· {{ __('Extended') }}</span></span>
                                        <span wire:loading wire:target="install('{{ $slug }}')"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @else
                                    <a href="{{ route('admin.plugins.checkout', $slug) }}" wire:navigate class="pbtn pbtn-solid">
                                        <i class="fa-solid fa-cart-shopping"></i> {{ __('Buy Now') }}
                                        <span class="opacity-70">· ${{ $price }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Empty state --}}
                <div x-show="visibleCount === 0" x-cloak class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <div class="plugin-ic mb-4"><i class="fa-solid fa-plug"></i></div>
                    <p class="text-zinc-400 text-sm">{{ __('No plugins found.') }}</p>
                </div>
            </div>


        </div>
    </div>
</div>
