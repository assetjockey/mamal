<div>
    <style>
        .pbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; padding: .8rem 2rem; border-radius: .75rem;
            font-size: .8rem; font-weight: 600; line-height: 1;
            transition: background-color .18s ease, color .18s ease, opacity .18s ease;
        }
        .pbtn-solid { background:#18181b; color:#fff; }
        .pbtn-solid:hover { background:#27272a; }
        .dark .pbtn-solid { background:#fff; color:#18181b; }
        .dark .pbtn-solid:hover { background:#e4e4e7; }
        .plugin-ic-lg i {
            font-size: 3.25rem; line-height: 1;
            background: linear-gradient(135deg, #818CF8 0%, #4F46E5 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }
        .dark .plugin-ic-lg i {
            background: linear-gradient(135deg, #A5B4FC 0%, #6366F1 100%);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .plugin-ic-lg svg, .plugin-ic-lg img { width: 3.25rem; height: 3.25rem; }
    </style>

    <div class="flex justify-center">
        <div class="w-full max-w-2xl">

            {{-- Breadcrumbs --}}
            <div class="mb-6 flex justify-center">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item :href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item :href="route('admin.plugins')" separator="slash" class="text-xs">{{ __('Plugins') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Purchase complete') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            {{-- Success header --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-5 rounded-2xl flex items-center justify-center text-white"
                     style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                    <i class="fa-solid fa-circle-check text-3xl"></i>
                </div>
                <h1 class="text-2xl font-black mb-2">{{ __('Thank you for your purchase!') }}</h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                    {{ __('Payment for') }} <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $plugin['name'] ?? '' }}</span>
                    {{ __('was successful. Install it now to start using it.') }}
                </p>
            </div>

            {{-- Install card --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) mb-6 p-6 text-center">
                @php $banner = trim($plugin['banner'] ?? ''); @endphp
                <div class="plugin-ic-lg w-20 h-20 mx-auto mb-4 rounded-2xl flex items-center justify-center border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/[0.03]">
                    @if ($banner !== '')
                        {!! $banner !!}
                    @else
                        <i class="fa-solid fa-plug"></i>
                    @endif
                </div>

                <h2 class="text-lg font-bold mb-1">{{ $plugin['name'] ?? '' }}</h2>
                <p class="text-xs text-zinc-400 mb-5">
                    @if ($installed)
                        {{ __('This plugin is installed and ready to use.') }}
                    @else
                        {{ __('Click install to set it up automatically.') }}
                    @endif
                </p>

                @if ($installed)
                    <a href="{{ route('admin.plugins') }}" wire:navigate class="pbtn pbtn-solid">
                        <i class="fa-solid fa-arrow-right"></i> {{ __('Back to Plugins') }}
                    </a>
                @else
                    <button wire:click="install" wire:loading.attr="disabled" wire:target="install"
                            class="pbtn pbtn-solid disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="install"><i class="fa-solid fa-download"></i> {{ __('Install Plugin') }}</span>
                        <span wire:loading wire:target="install"><i class="fa-solid fa-spinner fa-spin"></i> {{ __('Installing…') }}</span>
                    </button>
                @endif
            </div>

            {{-- Details --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6">
                <h3 class="text-sm font-bold pb-3 mb-4 border-b border-zinc-100 dark:border-white/5">{{ __('Order details') }}</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @php
                        $rows = [
                            [__('Plugin'), $plugin['name'] ?? '—'],
                            [__('Version'), $plugin['version'] ?? '—'],
                            [__('Purchase date'), now()->format('M d, Y')],
                            [__('Installation'), __('One Click')],
                            [__('Free updates'), __('Lifetime')],
                            [__('Support'), __('Included')],
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
