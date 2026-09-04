<div>
    <style>
        .pbtn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: .5rem; padding: .8rem 2rem; border-radius: .75rem;
            font-size: .8rem; font-weight: 600; line-height: 1;
            transition: opacity .18s ease;
        }
    </style>

    <div class="flex justify-center">
        <div class="w-full max-w-xl">

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
                    {{ __('Payment for') }} <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $name }}</span>
                    {{ __('was successful.') }}
                </p>
            </div>

            {{-- Info card --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-8 text-center">
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6 leading-relaxed">
                    @if ($slug === 'support')
                        {{ __('Your premium support subscription is now active. Our team is ready to help — reach out any time through the support portal.') }}
                    @elseif ($slug === 'premier')
                        {{ __('The Premier bundle is unlocked. Every paid plugin and theme — released and upcoming — is now available to install from your catalog.') }}
                    @else
                        {{ __('For further instructions and details please contact our support team.') }}
                    @endif
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    @if ($slug === 'premier')
                        <a href="{{ route('admin.plugins') }}" wire:navigate
                           class="pbtn text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            <i class="fa-solid fa-grip"></i> {{ __('Browse Plugins') }}
                        </a>
                    @endif
                    <a href="https://berkine.ticksy.com/" target="_blank"
                       class="pbtn text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                        <i class="fa-solid fa-headset"></i> {{ __('Contact Support') }}
                    </a>
                </div>
            </div>

            {{-- Order details --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-(--default-element-bg-color) p-6 mt-6">
                <h3 class="text-sm font-bold pb-3 mb-4 border-b border-zinc-100 dark:border-white/5">{{ __('Order details') }}</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @php
                        $rows = [
                            [__('Product'), $name],
                            [__('Purchase date'), now()->format('M d, Y')],
                            [__('Billing'), $slug === 'support' ? __('Monthly') : __('One-time')],
                            [__('Status'), __('Active')],
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
