<div>
    <style>
        [x-cloak] { display: none !important; }
        .tbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; width: 100%; padding: .8rem 1rem; border-radius: .75rem;
            font-size: .8rem; font-weight: 600; line-height: 1;
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

    <div class="flex justify-center">
        <div class="w-full lg:w-10/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex justify-center">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.themes')" separator="slash" class="text-xs">{{ __('Themes') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $theme['name'] ?? __('Theme') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-8">
                <a href="{{ route('admin.themes') }}" wire:navigate class="inline-flex items-center gap-2 text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors">
                    <i class="fa-solid fa-arrow-left-long"></i>
                    {{ __('View all themes') }}
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                {{-- LEFT: Theme detail --}}
                <div class="lg:col-span-8 space-y-6">

                    {{-- Hero card --}}
                    <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color)">
                        <figure class="relative m-0 group">
                            <img src="{{ $theme['banner'] ?? '' }}" alt="{{ $theme['name'] ?? '' }}" class="w-full h-auto block">
                            @if (!empty($theme['demo_url']))
                                <figcaption class="absolute inset-0 flex items-center justify-center bg-zinc-950/55 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <a href="{{ $theme['demo_url'] }}" target="_blank"
                                       class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-900 bg-white px-5 py-2.5 rounded-full hover:bg-zinc-100 transition-colors">
                                        <i class="fa-solid fa-eye"></i> {{ __('Live Preview') }}
                                    </a>
                                </figcaption>
                            @endif
                        </figure>

                        <div class="p-6">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-zinc-100 dark:bg-white/5 text-zinc-700 dark:text-zinc-200">
                                    <i class="fa-solid fa-objects-column text-[10px]"></i>
                                    {{ ucfirst($theme['type'] ?? '') }} {{ __('Theme') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                    <i class="fa-solid fa-badge-check"></i> {{ __('Verified') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                                    <i class="fa-solid fa-star"></i> 5.0
                                </span>
                            </div>

                            <h1 class="text-2xl font-black mb-1">{{ $theme['name'] ?? '' }} {{ __('Theme') }}</h1>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                                {{ $theme['short_description'] ?? '' }}
                            </p>
                        </div>
                    </div>

                    {{-- Quick stats --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $stats = [
                                ['icon' => 'fa-code-branch', 'label' => __('Version'), 'value' => 'v' . ($theme['version'] ?? '1.0')],
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
                    @if (!empty($theme['main_description']))
                        <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                            <h2 class="text-base font-bold mb-3">{{ __('About') }} {{ $theme['name'] ?? '' }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ $theme['main_description'] }}</p>
                        </div>
                    @endif

                    {{-- Feature tags --}}
                    @if (!empty($tags))
                        <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                            <h2 class="text-base font-bold mb-4">{{ __("What's included") }}</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                                @foreach ($tags as $tag)
                                    <div class="flex items-start gap-2.5 text-sm">
                                        <i class="fa-solid fa-circle-check text-zinc-900 dark:text-zinc-100 mt-0.5"></i>
                                        <span class="text-zinc-600 dark:text-zinc-300">{{ $tag }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- FAQ --}}
                    <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6"
                         x-data="{ open: 1 }">
                        <h2 class="text-base font-bold mb-4">{{ __('Got questions?') }}</h2>
                        <div class="divide-y divide-zinc-100 dark:divide-white/5">
                            @php
                                $faqs = [
                                    ['q' => __('How do I install the theme?'), 'a' => __('After your purchase is confirmed, an Install button appears. One click downloads, extracts and activates your new theme within seconds — no manual upload needed.')],
                                    ['q' => __('How do I switch between themes?'), 'a' => __('If you own multiple themes, just hit Activate on any purchased theme. It is set as your active frontend or dashboard theme automatically based on its type.')],
                                    ['q' => __('Do I get free updates?'), 'a' => __('Yes. Every purchase includes lifetime updates. Whenever a new version ships, an Update button appears on the theme card.')],
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
                                <div class="text-4xl font-black text-zinc-900 dark:text-white">${{ $theme['price'] ?? '0' }}</div>
                                <p class="text-[11px] text-zinc-400 mt-2">{{ __('One-time payment · USD · Tax included') }}</p>
                            </div>

                            <div class="p-6">
                                @if ($this->purchased && $this->installed)
                                    @if ($this->upgradable)
                                        <button wire:click="install" wire:loading.attr="disabled" wire:target="install" class="tbtn tbtn-solid">
                                            <span wire:loading.remove wire:target="install"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ __('Update Theme') }}</span>
                                            <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Updating…') }}</span>
                                        </button>
                                    @else
                                        <button wire:click="activate" wire:loading.attr="disabled" wire:target="activate" class="tbtn tbtn-outline">
                                            <span wire:loading.remove wire:target="activate"><i class="fa-solid fa-circle-check"></i> {{ __('Activate Theme') }}</span>
                                            <span wire:loading wire:target="activate"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Activating…') }}</span>
                                        </button>
                                    @endif
                                @elseif ($this->purchased && ! $this->installed)
                                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install" class="tbtn tbtn-solid">
                                        <span wire:loading.remove wire:target="install"><i class="fa-solid fa-download"></i> {{ __('Install Theme') }}</span>
                                        <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                                    </button>
                                @else
                                    <button wire:click="buy" wire:loading.attr="disabled" wire:target="buy" class="tbtn tbtn-solid">
                                        <span wire:loading.remove wire:target="buy"><i class="fa-solid fa-cart-shopping"></i> {{ __('Buy Theme') }}</span>
                                        <span wire:loading wire:target="buy"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Redirecting…') }}</span>
                                    </button>
                                @endif

                                <div class="flex items-center justify-center gap-4 mt-4 text-[11px] text-zinc-400">
                                    <span class="inline-flex items-center gap-1"><i class="fa-solid fa-lock"></i> {{ __('Secure') }}</span>
                                    <span class="inline-flex items-center gap-1"><i class="fa-solid fa-rotate-left"></i> {{ __('Instant access') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Details grid --}}
                        <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                            <h3 class="text-sm font-bold pb-3 mb-4 border-b border-zinc-100 dark:border-white/5">{{ __('Details') }}</h3>
                            <dl class="space-y-3 text-sm">
                                @php
                                    $details = [
                                        [__('Released'), $theme['released_date'] ?? '—'],
                                        [__('Last update'), $theme['updated_date'] ?? '—'],
                                        [__('Version'), $theme['version'] ?? '—'],
                                        [__('Type'), ucfirst($theme['type'] ?? '—')],
                                        [__('Installation'), __('One Click')],
                                        [__('Free updates'), __('Lifetime')],
                                    ];
                                @endphp
                                @foreach ($details as $row)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-zinc-400">{{ $row[0] }}</dt>
                                        <dd class="font-semibold text-right">{{ $row[1] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
