<div>
    <style>
        [x-cloak] { display: none !important; }
        /* Matches the Themes checkout: solid black (light) / solid white (dark).
           No brand gradients on buttons. */
        .pbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; width: 100%; padding: .8rem 1rem; border-radius: .75rem;
            font-size: .8rem; font-weight: 600; line-height: 1;
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

        .plugin-ic-lg i {
            font-size: 4rem; line-height: 1;
            background: linear-gradient(135deg, #818CF8 0%, #4F46E5 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }
        .dark .plugin-ic-lg i {
            background: linear-gradient(135deg, #A5B4FC 0%, #6366F1 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .plugin-ic-lg svg, .plugin-ic-lg img { width: 4rem; height: 4rem; }
    </style>

    <div class="flex justify-center">
        <div class="w-full lg:w-10/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.plugins')" separator="slash" class="text-xs">{{ __('Plugins') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $plugin['name'] ?? __('Plugin') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-8">
                <a href="{{ route('admin.plugins') }}" wire:navigate class="inline-flex items-center gap-2 text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    <i class="fa-solid fa-arrow-left-long"></i>
                    {{ __('View all plugins') }}
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- LEFT: Plugin detail --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- Hero card --}}
                    @php $banner = trim($plugin['banner'] ?? ''); @endphp
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                        <div class="flex items-start gap-5">
                            {{-- Large gradient glyph --}}
                            <div class="plugin-ic-lg shrink-0 w-24 h-24 rounded-2xl flex items-center justify-center border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/[0.03]">
                                @if ($banner !== '')
                                    {!! $banner !!}
                                @else
                                    <i class="fa-solid fa-plug"></i>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-zinc-100 dark:bg-white/5 text-zinc-700 dark:text-zinc-200">
                                        <i class="fa-solid fa-plug text-[10px]"></i>
                                        {{ __('Plugin') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                        <i class="fa-solid fa-badge-check"></i> {{ __('Verified') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                                        <i class="fa-solid fa-star"></i> 5.0
                                    </span>
                                    @if ($this->installed)
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                            <i class="fa-solid fa-circle-check"></i> {{ __('Installed') }}
                                        </span>
                                    @endif
                                </div>

                                <h1 class="text-2xl font-black mb-1">{{ $plugin['name'] ?? '' }}</h1>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                                    {{ $plugin['short_description'] ?? '' }}
                                </p>
                            </div>
                        </div>

                        @if (!empty($plugin['demo_url']))
                            <div class="mt-5">
                                <a href="{{ $plugin['demo_url'] }}" target="_blank"
                                   class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('View Documentation') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Quick stats --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $stats = [
                                ['icon' => 'fa-code-branch', 'label' => __('Version'), 'value' => 'v' . ($plugin['version'] ?? '1.0')],
                                ['icon' => 'fa-bolt', 'label' => __('Install'), 'value' => __('One Click')],
                                ['icon' => 'fa-arrows-rotate', 'label' => __('Updates'), 'value' => __('Lifetime')],
                                ['icon' => 'fa-headset', 'label' => __('Support'), 'value' => __('Included')],
                            ];
                        @endphp
                        @foreach ($stats as $stat)
                            <div class="rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-4 text-center">
                                <i class="fa-solid {{ $stat['icon'] }} text-zinc-700 dark:text-zinc-300 mb-2"></i>
                                <div class="text-[10px] uppercase tracking-wide text-zinc-400 mb-0.5">{{ $stat['label'] }}</div>
                                <div class="text-sm font-bold">{{ $stat['value'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    {{-- About --}}
                    @if (!empty($plugin['main_description']))
                        <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                            <h2 class="text-base font-bold mb-3">{{ __('About') }} {{ $plugin['name'] ?? '' }}</h2>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed prose prose-sm dark:prose-invert max-w-none">{!! $plugin['main_description'] !!}</div>
                        </div>
                    @endif

                    {{-- FAQ --}}
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6"
                         x-data="{ open: 1 }">
                        <h2 class="text-base font-bold mb-4">{{ __('Got questions?') }}</h2>
                        <div class="divide-y divide-zinc-100 dark:divide-white/5">
                            @php
                                $faqs = [
                                    ['q' => __('How do I install the plugin?'), 'a' => __('After your purchase is confirmed, an Install button appears. One click downloads, extracts and wires up the plugin within seconds — no manual upload needed.')],
                                    ['q' => __('How do I remove a plugin?'), 'a' => __('Open the plugin from your catalog and hit Uninstall. Every file the plugin added is removed and the plugin is cleanly disabled.')],
                                    ['q' => __('Do I get free updates?'), 'a' => __('Yes. Every purchase includes lifetime updates. Whenever a new version ships, an Update button appears on the plugin card.')],
                                ];
                            @endphp
                            @foreach ($faqs as $i => $faq)
                                <div class="py-3">
                                    <button type="button" class="w-full flex items-center justify-between text-left gap-4"
                                            @click="open === {{ $i + 1 }} ? open = null : open = {{ $i + 1 }}">
                                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $faq['q'] }}</span>
                                        <i class="fa-solid text-xs text-zinc-400 transition-transform"
                                           :class="open === {{ $i + 1 }} ? 'fa-minus' : 'fa-plus'"></i>
                                    </button>
                                    <div x-show="open === {{ $i + 1 }}" x-collapse x-cloak>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed pt-2.5">{{ $faq['a'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Purchase panel --}}
                <div class="lg:col-span-4">
                    <div class="lg:sticky lg:top-6 space-y-6">

                        {{-- Price / action card --}}
                        <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color)">
                            <div class="px-6 py-6 text-center border-b border-zinc-100 dark:border-white/5">
                                <p class="text-[11px] uppercase tracking-wider text-zinc-400 mb-2">{{ __('For a limited time only') }}</p>
                                @if ($plugin['is_free'] ?? false)
                                    <div class="text-4xl font-black text-zinc-900 dark:text-white">{{ __('Free') }}</div>
                                    <p class="text-[11px] text-zinc-400 mt-2">{{ __('No payment required') }}</p>
                                @elseif (!is_null($plugin['discount_price'] ?? null))
                                    <div class="text-4xl font-black text-zinc-900 dark:text-white">
                                        ${{ $plugin['discount_price'] }}
                                        <span class="text-lg text-zinc-400 line-through font-bold">${{ $plugin['price_placeholder'] ?? $plugin['price'] ?? '0' }}</span>
                                    </div>
                                    <p class="text-[11px] text-zinc-400 mt-2">{{ __('One-time payment · USD · Tax included') }}</p>
                                @else
                                    <div class="text-4xl font-black text-zinc-900 dark:text-white">${{ $plugin['price'] ?? '0' }}</div>
                                    <p class="text-[11px] text-zinc-400 mt-2">{{ __('One-time payment · USD · Tax included') }}</p>
                                @endif
                            </div>

                            <div class="p-6">
                                @if (! $approved)
                                    <button disabled class="pbtn pbtn-muted">
                                        <i class="fa-solid fa-triangle-exclamation"></i> {{ __('Requires app version') }} v{{ $approvedVersion }}
                                    </button>
                                @elseif ($this->onlyForExtended && ! $this->purchased)
                                    <button disabled class="pbtn pbtn-muted">
                                        <i class="fa-solid fa-lock"></i> {{ __('Extended License Required') }}
                                    </button>
                                @elseif ($this->installed && $this->upgradable)
                                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ __('Update Plugin') }}</span>
                                        <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Updating…') }}</span>
                                    </button>
                                    <button wire:click="uninstall" wire:loading.attr="disabled" wire:target="uninstall"
                                            wire:confirm="{{ __('Are you sure you want to uninstall this plugin?') }}" class="pbtn pbtn-danger mt-2.5">
                                        <span wire:loading.remove wire:target="uninstall"><i class="fa-solid fa-trash-can"></i> {{ __('Uninstall') }}</span>
                                        <span wire:loading wire:target="uninstall"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Uninstalling…') }}</span>
                                    </button>
                                @elseif ($this->installed)
                                    <button disabled class="pbtn pbtn-muted">
                                        <i class="fa-solid fa-circle-check"></i> {{ __('Installed') }}
                                    </button>
                                    <button wire:click="uninstall" wire:loading.attr="disabled" wire:target="uninstall"
                                            wire:confirm="{{ __('Are you sure you want to uninstall this plugin?') }}" class="pbtn pbtn-danger mt-2.5">
                                        <span wire:loading.remove wire:target="uninstall"><i class="fa-solid fa-trash-can"></i> {{ __('Uninstall') }}</span>
                                        <span wire:loading wire:target="uninstall"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Uninstalling…') }}</span>
                                    </button>
                                @elseif ($this->purchased)
                                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install"><i class="fa-solid fa-download"></i> {{ __('Install Plugin') }}</span>
                                        <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @elseif ($this->extendedInstall)
                                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="install"><i class="fa-solid fa-download"></i> {{ __('Install Plugin') }} <span class="opacity-70">· {{ __('Extended') }}</span></span>
                                        <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @else
                                    <button wire:click="buy" wire:loading.attr="disabled" wire:target="buy" class="pbtn pbtn-solid">
                                        <span wire:loading.remove wire:target="buy"><i class="fa-solid fa-cart-shopping"></i> {{ __('Buy Plugin') }}</span>
                                        <span wire:loading wire:target="buy"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Redirecting…') }}</span>
                                    </button>
                                @endif

                                <div class="flex items-center justify-center gap-4 mt-4 text-[11px] text-zinc-400">
                                    <span class="inline-flex items-center gap-1"><i class="fa-solid fa-lock"></i> {{ __('Secure') }}</span>
                                    <span class="inline-flex items-center gap-1"><i class="fa-solid fa-bolt"></i> {{ __('Instant access') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Details grid — each fact in its own box --}}
                        <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                            <h3 class="text-sm font-bold pb-3 mb-4 border-b border-zinc-100 dark:border-white/5">{{ __('Details') }}</h3>
                            @php
                                $details = [
                                    ['icon' => 'fa-calendar-day', 'label' => __('Released'), 'value' => $plugin['released_date'] ?? '—'],
                                    ['icon' => 'fa-arrows-rotate', 'label' => __('Last update'), 'value' => $plugin['updated_date'] ?? '—'],
                                    ['icon' => 'fa-code-branch', 'label' => __('Version'), 'value' => $plugin['version'] ?? '—'],
                                    ['icon' => 'fa-bolt', 'label' => __('Installation'), 'value' => __('One Click')],
                                    ['icon' => 'fa-key', 'label' => __('License required'), 'value' => $plugin['license_required'] ?? __('Regular')],
                                    ['icon' => 'fa-infinity', 'label' => __('Free updates'), 'value' => __('Lifetime')],
                                ];
                            @endphp
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ($details as $row)
                                    <div class="rounded-xl border border-zinc-200 dark:border-white/10 bg-zinc-50/60 dark:bg-white/[0.03] p-3">
                                        <div class="flex items-center gap-1.5 text-[10px] uppercase tracking-wide text-zinc-400 mb-1.5">
                                            <i class="fa-solid {{ $row['icon'] }} text-zinc-400"></i>
                                            <span>{{ $row['label'] }}</span>
                                        </div>
                                        <div class="text-sm font-bold text-zinc-700 dark:text-zinc-200 truncate" title="{{ $row['value'] }}">{{ $row['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

              
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
