{{--
    Marketplace bundle + premium support cards.

    Shared by the plugin catalog and the checkout sidebar. Both lead to
    PluginController@purchasePackage which stashes the product in the session
    and forwards to the Stripe gateway. Styled with the approved brand gradient
    (recipe 1) framing a surface panel — never the banned purple→pink palettes.
--}}

{{-- Premium Support --}}
<div class="relative rounded-2xl p-px overflow-hidden"
     style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
    <div class="rounded-[15px] h-full bg-white dark:bg-(--default-element-bg-color) p-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-white"
                  style="background: linear-gradient(120deg, #F59E0B, #4F46E5);">
                <i class="fa-solid fa-headset text-sm"></i>
            </span>
            <h3 class="text-base font-black">{{ __('Premium VIP Support') }}</h3>
        </div>
        <div class="text-center my-5">
            <div class="text-4xl font-black text-zinc-900 dark:text-white">$299</div>
            <p class="text-[11px] text-zinc-400 mt-1">{{ __('Monthly cost · Price is in US dollar.') }}</p>
        </div>
        <ul class="space-y-2.5 mb-6 text-sm">
            @foreach ([
                __('Priority support in the ticket queue'),
                __('Support during weekends'),
                __('Maximum few hours of SLA time'),
                __('Access to extensions while on Premium Support'),
            ] as $feature)
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-[#D97706] mt-0.5"></i>
                    <span class="text-zinc-600 dark:text-zinc-300">{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('admin.plugins.purchase.package', 'support') }}"
           class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors">
            <i class="fa-solid fa-bolt"></i> {{ __('Buy Premium Support') }}
        </a>
    </div>
</div>

{{-- Premier Bundle --}}
<div class="relative rounded-2xl p-px overflow-hidden"
     style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
    <div class="rounded-[15px] h-full bg-white dark:bg-(--default-element-bg-color) p-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-white"
                  style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                <i class="fa-solid fa-box-open text-sm"></i>
            </span>
            <h3 class="text-base font-black">{{ __('Premier Package Bundle') }}</h3>
        </div>
        <div class="text-center my-5">
            <div class="text-4xl font-black text-zinc-900 dark:text-white">$999</div>
            <p class="text-[11px] text-zinc-400 mt-1">{{ __('One-time cost · Includes released and upcoming items.') }}</p>
        </div>
        <ul class="space-y-2.5 mb-6 text-sm">
            @foreach ([
                __('Full access to all paid themes'),
                __('Full access to all paid plugins'),
                __('Forever access to plugin updates'),
                __('Forever access to theme updates'),
            ] as $feature)
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-[#D97706] mt-0.5"></i>
                    <span class="text-zinc-600 dark:text-zinc-300">{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('admin.plugins.purchase.package', 'premier') }}"
           class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors">
            <i class="fa-solid fa-crown"></i> {{ __('Buy Premier Bundle') }}
        </a>
    </div>
</div>
