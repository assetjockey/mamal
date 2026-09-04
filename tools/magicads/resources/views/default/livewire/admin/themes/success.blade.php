<div>
    <style>
        .tbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; padding: .8rem 2rem; border-radius: .75rem;
            font-size: .8rem; font-weight: 600; line-height: 1;
            transition: background-color .18s ease, color .18s ease, opacity .18s ease;
        }
        .tbtn-solid { background:#18181b; color:#fff; }
        .tbtn-solid:hover { background:#27272a; }
        .dark .tbtn-solid { background:#fff; color:#18181b; }
        .dark .tbtn-solid:hover { background:#e4e4e7; }
    </style>

    <div class="flex justify-center">
        <div class="w-full max-w-2xl">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex justify-center">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.themes')" separator="slash" class="text-xs">{{ __('Themes') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Purchase complete') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            {{-- Success header --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-5 rounded-2xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/20">
                    <i class="fa-solid fa-circle-check text-3xl text-emerald-500 dark:text-emerald-400"></i>
                </div>
                <h1 class="text-2xl font-black mb-2">{{ __('Thank you for your purchase!') }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                    {{ __('Payment for') }} <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $theme['name'] ?? '' }} {{ __('Theme') }}</span>
                    {{ __('was successful. Install it now to start using it.') }}
                </p>
            </div>

            {{-- Install card --}}
            <div class="rounded-2xl overflow-hidden border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) mb-6">
                @if (!empty($theme['banner']))
                    <img src="{{ $theme['banner'] }}" alt="{{ $theme['name'] ?? '' }}" class="w-full h-auto block">
                @endif

                <div class="p-6 text-center">
                    <h2 class="text-lg font-bold mb-1">{{ $theme['name'] ?? '' }} {{ __('Theme') }}</h2>
                    <p class="text-xs text-zinc-400 mb-5">
                        @if ($installed)
                            {{ __('This theme is installed and active.') }}
                        @else
                            {{ __('Click install to set it up automatically.') }}
                        @endif
                    </p>

                    @if ($installed)
                        <a href="{{ route('admin.themes') }}" wire:navigate class="tbtn tbtn-solid">
                            <i class="fa-solid fa-arrow-right"></i> {{ __('Back to Themes') }}
                        </a>
                    @else
                        <button wire:click="install" wire:loading.attr="disabled" wire:target="install"
                                class="tbtn tbtn-solid disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="install"><i class="fa-solid fa-download"></i> {{ __('Install Theme') }}</span>
                            <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                <h3 class="text-sm font-bold pb-3 mb-4 border-b border-zinc-100 dark:border-white/5">{{ __('Order details') }}</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @php
                        $rows = [
                            [__('Theme'), $theme['name'] ?? '—'],
                            [__('Type'), ucfirst($theme['type'] ?? '—') . ' ' . __('Theme')],
                            [__('Version'), $theme['version'] ?? '—'],
                            [__('Purchase date'), now()->format('M d, Y')],
                            [__('Installation'), __('One Click')],
                            [__('Free updates'), __('Lifetime')],
                        ];
                    @endphp
                    @foreach ($rows as $row)
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-zinc-400 mb-1">{{ $row[0] }}</dt>
                            <dd class="font-semibold">{{ $row[1] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

        </div>
    </div>
</div>
