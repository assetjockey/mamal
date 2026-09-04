<div class="flex justify-center overflow-x-hidden" x-data="financeDashboard()" x-init="init()">
    <div class="w-full lg:w-10/12 min-w-0">
        <div class="mb-6">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Dashboard')}}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </div>

        <div class="mb-9">
            <h1 class="font-extrabold md:text-2xl">{{ __('Welcome Admin') }}</h1>
        </div>

        <div class="flex flex-wrap rounded-xl border border-(--default-border-color) mb-10">
            <div class="w-full p-2 md:p-8">
                <div class="">
                    <div class="p-4 md:p-2 md:pl-4 mb-3">
                        <h4 class="text-[20px] font-bold mt-0 leading-[1.3rem]">{{ __("What's New Today") }}</h4>
                       <span class="inline-flex items-center gap-1 text-[10px] text-[#728096]">
                            <x-heroicon-o-calendar-days class="w-3.5 h-3.5" />
                            {{ now()->format('l, F j, Y H:i A') }}
                        </span>
                    </div>
                    <div class="px-4 pt-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-5 m-1 mb-5">          

                            @if (\App\Services\HelperService::extensionSaaS())
                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/finance/dashboard') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['revenue']) }}</h3>
                                    <flux:icon.badge-dollar-sign class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Revenue') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Today's revenue") }}"></i></h6>
                                </div>
                            </div>
                            @endif

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/accounts/list') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['new_users']) }}</h3>
                                    <flux:icon.user-round-plus class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('New Users') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Today's new users") }}"></i></h6>
                                </div>
                            </div>

                            @if (\App\Services\HelperService::extensionSaaS())
                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/finance/subscriptions') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['subscribers']) }}</h3>
                                    <flux:icon.user-round-check class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Subscribers') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Today's new subscribers") }}"></i></h6>
                                </div>
                            </div>

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/finance/transactions') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['transactions']) }}</h3>
                                    <flux:icon.banknote-arrow-up class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Transactions') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Today's new transactions") }}"></i></h6>
                                </div>
                            </div>
                            @endif

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/support/tickets') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['tickets']) }}</h3>
                                    <flux:icon.headset class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Tickets') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Today's new support tickets") }}"></i></h6>
                                </div>
                            </div>

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/accounts/activity') }}'">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['online_users']) }}</h3>
                                    <flux:icon.monitor-check class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Online Users') }} <i class="ml-1 text-[#728096] text-[13px] font-normal fa-solid fa-circle-info" data-tippy-content="{{ __("Currently online users") }}"></i></h6>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-5 m-1 mt-3">

                            <div class="md:col-span-3 lg:col-span-3 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box cursor-pointer" onclick="window.location.href='{{ url('app/admin/update') }}'">
                                <div class="p-10 flex flex-col justify-center h-full">
                                    <h3 class="text-[16px] font-semibold mb-2"><span id="current-update-status">{{config('app.name')}} {{ __('has the latest version installed') }}</span><span id="new-update-status" class="hidden">{{__('There is a new update available for')}} {{config('app.name')}}</span></h3>
                                    <h6 class="text-[#728096] text-[14px] mb-0 flex items-center gap-1"><flux:icon.package-check class="size-5 text-gray-400"/> {{ __('Version') }} <span id="version-number">{{config('app.version')}}</span></h6>
                                </div>
                            </div>

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['tokens_used']) }}</h3>
                                    <flux:icon.wand-sparkles class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Credits Used') }}</h6>
                                </div>
                            </div>

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['media_used']) }}</h3>
                                    <flux:icon.image-plus class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Image Created') }}</h6>
                                </div>
                            </div>

                            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) dashboard-tool-box">
                                <div class="p-4">
                                    <h3 class="text-[25px] mb-7 font-bold">{{ number_format($today['contents']) }}</h3>
                                    <flux:icon.clapperboard class="size-6 text-gray-400 mb-2"/>
                                    <h6 class="text-[#728096] text-[14px]">{{ __('Video Created') }}</h6>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (\App\Services\HelperService::extensionSaaS())
        <div class="w-full">
            <div class="p-4 md:p-2 md:pl-4 mb-3">
                <h4 class="text-[20px] font-extrabold ">{{ __("Finance Metrics") }}</h4>
            </div>
        </div>
        <div class="flex flex-wrap rounded-xl border border-(--default-border-color) mb-10">
            <div class="w-full">
                <div class="p-2 md:p-8">
                    <div class="flex flex-wrap mb-6">
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 my-auto border-r border-(--default-border-color)">
                            <div class="text-center">
                                <h6 class="text-gray-500 text-[12px] mb-2 font-bold">{{ __('Total Revenue') }}</h6>
                                <h3 class="text-[20px] mb-2 font-bold text-(--default-primary-color)">{{ $currencySymbol }} {{ number_format((float)$total['total_income'][0]['data'],2) }}</h3>
                                <h6 class="text-gray-500 text-[10px]">{{ __('Lifetime') }} <span class="font-bold">{{ __('earnings') }}</span></h6>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 my-auto border-r border-(--default-border-color)">
                            <div class="text-center">
                                <h6 class="text-gray-500 text-[12px] mb-2 font-bold">{{ __('Total Spending') }}</h6>
                                <h3 class="text-[20px] mb-2 font-bold text-red-500">${{ number_format((float)$total['total_spending'], 2) }}</h3>
                                <h6 class="text-gray-500 text-[10px]">{{ __('Estimated') }} <span class="font-bold">{{ __('AI service costs') }}</span></h6>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 p-5 border-r border-(--default-border-color)">
                            <div class="text-center">
                                <h6 class="text-[12px] text-gray-500 font-bold">{{ __('Total Active Subscribers') }}</h6>
                                <h6 class="mb-0 text-[20px] font-bold">{{ number_format($total['total_subscribers']) }}</h6>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 p-5 border-r border-(--default-border-color)">
                            <div class="text-center">
                                <h6 class="text-[12px] text-gray-500 font-bold">{{ __('Referral Earnings') }}</h6>
                                <h6 class="mb-0 text-[20px] font-bold">{{ $currencySymbol }}{{ number_format((float)$total['referral_earnings'][0]['data'], 2) }}</h6>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 p-5">
                            <div class="text-center">
                                <h6 class="text-[12px] text-gray-500 font-bold">{{ __('Referral Payouts') }}</h6>
                                <h6 class="mb-0 text-[20px] font-bold">{{ $currencySymbol }}{{ number_format((float)$total['referral_payouts'][0]['data'], 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 mt-4">
                        <div class="flex flex-col gap-6 md:w-48 shrink-0 justify-center">
                            <div>
                                <p class="text-[11px] text-gray-400 mb-1">{{ __('Current Month Earning') }}</p>
                                <h3 class="text-[22px] font-bold">{{ $currencySymbol }}{{ number_format((float)$monthlyFinance['earning'], 2) }}</h3>
                                <span class="text-[11px] font-medium {{ $monthlyFinance['earning_change'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                    {{ $monthlyFinance['earning_change'] >= 0 ? '+' : '' }}{{ $monthlyFinance['earning_change'] }}%
                                    <span class="text-gray-400">{{ __('vs last month') }}</span>
                                </span>
                            </div>
                            <div>
                                <p class="text-[11px] text-gray-400 mb-1">{{ __('Current Month Spending') }}</p>
                                <h3 class="text-[22px] font-bold">${{ number_format((float)$monthlyFinance['spending'], 2) }}</h3>
                                <span class="text-[11px] font-medium {{ $monthlyFinance['spending_change'] >= 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $monthlyFinance['spending_change'] >= 0 ? '+' : '' }}{{ $monthlyFinance['spending_change'] }}%
                                    <span class="text-gray-400">{{ __('vs last month') }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div id="revenueChart" class="mt-2 min-h-[300px]"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if (\App\Services\HelperService::extensionSaaS())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-10">
            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) h-[500px]">
                <div class="pl-5 pt-4 pb-4">
                    <div class="mt-3">
                         <h5 class="text-sm font-bold flex"><x-heroicon-o-currency-dollar class="w-5 h-5 mr-2 mb-5" />{{ __('Revenue Source') }}</h5>
                    </div>
                </div>
                <div class="p-4">
                    <div class="relative mt-4">
                        <div id="revenuePlan" class="h-[330px]"></div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) h-[500px]">
                <div class="pl-5 pt-4 pb-4">
                    <div class="mt-3">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-users class="w-5 h-5 mr-2 mb-5" />{{ __('User Distribution') }}</h5>
                    </div>
                </div>
                <div class="p-4">
                    <div class="relative mt-4 flex items-center justify-center">
                        <canvas id="userDoughnut" class="h-[330px]"></canvas>
                    </div>
                </div>
            </div>
        </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-10">
            <div class="pb-5 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="pt-4 pb-4 px-5">
                    <div class="mt-3 flex items-center justify-between">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-credit-card class="w-5 h-5 mr-2 mb-5" />{{ __('Latest Transactions') }}</h5>
                        @if (\App\Services\HelperService::extensionSaaS())
                            <a href="{{ route('admin.finance.orders') }}" wire:navigate class="text-xs text-zinc-500 font-small hover:text-(--default-primary-color)">{{ __('View All') }} →</a>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[500px]">
                        <div class="w-full px-6">
                            <div class="grid grid-cols-4 gap-2">
                                <div class="font-semibold text-gray-400 text-xs">{{ __('Plan') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs">{{ __('Price') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs mr-4">{{ __('Status') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs mr-5">{{ __('Date') }}</div>
                            </div>
                        </div>

                        <div class="pt-2 h-[400px] overflow-y-auto px-5">
                            <div class="flex flex-col gap-2">
                                @foreach ($transactions as $data)
                                    <div class="w-full">
                                        <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition"
                                            onclick="window.location.href='{{ url('app/admin/finance/transaction/'.$data->id.'/show') }}'">
                                            <div class="pt-2 pb-2 px-4 grid grid-cols-4 gap-2 items-center">
                                                <div>
                                                    <p class="font-semibold text-xs mb-0">{{ $data->plan_name }}</p>
                                                    <p class="text-gray-400 text-[10px] mb-0">{{ ucfirst($data->frequency) }} {{ __('Plan') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs mb-0 text-gray-400">{{ config('currencies.' . $data->currency . '.symbol', '') }}{{ number_format($data->price) }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs mb-0 text-gray-400">{{ __(ucfirst($data->status)) }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[10px] mb-0 text-gray-400">
                                                        {{ date_format($data->created_at, 'd M Y') }}<br>
                                                        <span>{{ date_format($data->created_at, 'H:i A') }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-5 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="pt-4 pb-4 px-5">
                    <div class="mt-3 flex items-center justify-between">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-credit-card class="w-5 h-5 mr-2 mb-5" />{{ __('Pending Approvals') }}</h5>
                        @if (\App\Services\HelperService::extensionSaaS())
                            <a href="{{ route('admin.finance.orders') }}" wire:navigate class="text-xs text-zinc-500 font-small hover:text-(--default-primary-color)">{{ __('View All') }} →</a>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[500px]">
                        <div class="w-full px-6">
                            <div class="grid grid-cols-5 gap-2">
                                <div class="font-semibold text-gray-400 text-xs">{{ __('Plan') }}</div>
                                <div class="font-semibold text-gray-400 text-xs">{{ __('User') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs">{{ __('Price') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs">{{ __('Gateway') }}</div>
                                <div class="text-right font-semibold text-gray-400 text-xs">{{ __('Status') }}</div>
                            </div>
                        </div>

                        <div class="pt-2 h-[400px] overflow-y-auto px-5">
                            <div class="flex flex-col gap-2">
                                @foreach ($approvals as $data)
                                    <div class="w-full">
                                        <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition"
                                            onclick="window.location.href='{{ url('app/admin/finance/transaction/'.$data->id.'/show') }}'">
                                            <div class="pt-2 pb-2 px-4 grid grid-cols-5 gap-2 items-center">
                                                <div>
                                                    <p class="font-semibold text-xs mb-0">{{ $data->plan_name }}</p>
                                                    <p class="text-gray-400 text-[10px] mb-0">{{ ucfirst($data->frequency) }} {{ __('Plan') }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-xs mb-0">{{ $data->user?->name }}</p>
                                                    <p class="text-gray-400 text-[10px] mb-0">{{ $data->user?->email }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs mb-0 text-gray-400">{{ config('currencies.' . $data->currency . '.symbol', '') }}{{ number_format($data->price) }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs mb-0 text-gray-400">{{ $data->gateway }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs mb-0 text-gray-400">{{ __(ucfirst($data->status)) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
        @endif


        <div class="w-full">
            <div class="p-4 md:p-2 md:pl-4 mb-3">
                <h4 class="text-[20px] font-extrabold ">{{ __("User Metrics") }}</h4>
            </div>
        </div>

        <div class="flex flex-wrap rounded-xl border border-(--default-border-color) mb-10">
            <div class="w-full">
                <div class="p-2 md:p-6">
                    <div class="flex flex-wrap">
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 py-4 px-8 border-r border-(--default-border-color)">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-gray-500 text-[12px] mb-2 font-bold">{{ __('Total Users') }}</div>
                                    <div class="text-2xl font-extrabold">{{ number_format($userMetrics['total_users']) }}</div>
                                </div>
                                <flux:icon.user-group class="size-12"/>
                            </div>
                        </div>
                        @if (\App\Services\HelperService::extensionSaaS())
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 py-4 px-8 border-r border-(--default-border-color)">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="text-gray-500 text-[12px] mb-2 font-bold">{{ __('Total Subscribers') }}</h6>
                                <h3 class="text-[20px] mb-2 font-bold">{{ number_format($userMetrics['total_subscribers']) }}</h3>
                                </div>
                                <flux:icon.user-star class="size-12 text-blue-500"/>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 py-4 px-8 border-r border-(--default-border-color)">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="text-[12px] text-gray-500 mb-2 font-bold">{{ __('Total Referred') }}</h6>
                                    <h6 class="mb-0 text-[20px] font-bold">{{ number_format($userMetrics['total_referred']) }}</h6>
                                </div>
                                <flux:icon.user-plus class="size-12"/>
                            </div>
                        </div>
                        @endif
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 py-4 px-8 border-r border-(--default-border-color)">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="text-[12px] text-gray-500 mb-2 font-bold">{{ __('Online Users') }}</h6>
                                    <h6 class="mb-0 text-[20px] font-bold">{{ number_format($userMetrics['online_users']) }}</h6>
                                </div>
                                <flux:icon.user-check class="size-12 text-yellow-500"/>
                            </div>
                        </div>
                        <div class="flex-1 basis-full md:basis-1/2 lg:basis-0 py-4 px-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h6 class="text-[12px] text-gray-500 mb-2 font-bold">{{ __('Visitors Today') }}</h6>
                                <h6 class="mb-0 text-[20px] font-bold">{{ number_format($userMetrics['visitors_today']) }}</h6>
                                </div>
                                <flux:icon.user-search class="size-12"/>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex flex-wrap rounded-xl border border-(--default-border-color) mb-10">
            <div class="w-full">
                <div class="p-2 md:p-8">
                    <div class="">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-globe-americas class="w-5 h-5 mr-2 mb-5" />{{ __('Top Visitor Countries') }}</h5>
                        <div class="flex flex-wrap gap-3">

                            <div class="w-full md:flex-1 md:basis-0 min-w-0 mt-3">
                                <div class="" style="max-height: 457px;">
                                    <div class="p-4 overflow-y-scroll" style="max-height: 457px;">
                                        <div class="flex flex-wrap">
                                            <div class="w-full">
                                                <div>
                                                    @if ($google_analytics_dashboard)
                                                        <div id="ga-preloader-3" style="position: absolute; left: 48%; top: 40%;"></div>
                                                        <ul id="countryList"></ul>
                                                    @elseif ($topCountries->isNotEmpty())
                                                        @php($countryMax = max($topCountries->values()->all()))
                                                        <ul class="flex flex-col gap-3">
                                                            @foreach ($topCountries as $country => $count)
                                                                <li class="flex items-center gap-3">
                                                                    <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300 w-28 truncate" title="{{ $country ?: __('Unknown') }}">{{ $country ?: __('Unknown') }}</span>
                                                                    <div class="flex-1 h-1.5 rounded-full bg-zinc-100 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                                                        <div class="h-full rounded-full" style="width: {{ $countryMax > 0 ? max(4, round(($count / $countryMax) * 100)) : 0 }}%; background: linear-gradient(120deg, #4F46E5, #0F172A);"></div>
                                                                    </div>
                                                                    <span class="text-[11px] font-semibold text-zinc-500 w-8 text-right">{{ number_format($count) }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <h6 class="text-center text-xs text-gray-400 flex justify-center">{{ __('No visitor data yet') }}</h6>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Center Panel: Map --}}
                            <div class="w-full md:flex-1 md:basis-0 min-w-0 my-auto">
                                <div class="mt-3">
                                    @if ($google_maps)
                                        <div id="countries-analytics-chart" class="h-[330px]"></div>
                                    @else
                                        <div class="text-center">
                                            <p class="text-xs mt-6">{{ __('Google Maps is Disabled') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right Panel: GA Stats Cards --}}
                            <div class="w-full md:flex-1 md:basis-0 min-w-0 mt-3">

                                @foreach ([
                                    ['title' => 'Average Session Duration', 'id' => 'google_average_session'],
                                    ['title' => 'Bounce Rate',               'id' => 'google_bounce_rate'],
                                    ['title' => 'Sessions',                  'id' => 'google_sessions'],
                                    ['title' => 'Views per Session',         'id' => 'google_session_views'],
                                    ] as $stat)
                                    <div class="w-full mb-3">
                                        <div class="rounded-xl text-center border border-(--default-border-color)">
                                            <div class="pt-3 pb-1 flex justify-center">
                                                <div class="mt-2 mb-0 text-center">
                                                    <h3 class="text-sm font-semibold mb-0 text-center">{{ __($stat['title']) }}</h3>
                                                    <span class="text-[10px] text-gray-400">({{ __('Last 30 Days') }})</span>
                                                </div>
                                            </div>
                                            <div class="p-5 pt-2">
                                                <div>
                                                    @if ($google_analytics_dashboard)
                                                        <div class="ga-preloader" style="position: absolute; left: 48%; top: -50%;"></div>
                                                        <h6 class="text-gray-400" id="{{ $stat['id'] }}"></h6>
                                                    @else
                                                        <h6 class="text-center text-xs text-gray-400 flex justify-center">{{ __('Google Analytics is not configured yet') }}</h6>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- User Traffic + Users and Sessions (Google Analytics) --}}
        @if ($google_analytics_dashboard)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-10">
                <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                    <div class="px-5 pt-5 pb-2">
                        <h5 class="text-sm font-bold flex items-center"><x-heroicon-o-chart-pie class="w-5 h-5 mr-2" />{{ __('User Traffic') }}</h5>
                    </div>
                    <div class="p-4">
                        <div class="relative h-[300px] flex items-center justify-center">
                            <canvas id="trafficDoughnut"></canvas>
                        </div>
                        <div id="trafficLegend" class="flex flex-wrap justify-center gap-x-4 gap-y-1 mt-3 text-[10px] text-zinc-500"></div>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                    <div class="px-5 pt-5 pb-2">
                        <h5 class="text-sm font-bold flex items-center"><x-heroicon-o-globe-alt class="w-5 h-5 mr-2" />{{ __('Users And Sessions') }}</h5>
                    </div>
                    <div class="p-4">
                        <div class="h-[300px]">
                            <canvas id="usersSessionsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="w-full">
            <div class="p-4 md:p-2 md:pl-4 mb-3">
                <h4 class="text-[20px] font-extrabold ">{{ __("Platform Metrics") }}</h4>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-10">

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Credits Used') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['credits']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['credits']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.wand-sparkles class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['credits']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['credits']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Image Generated') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['images']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['images']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.image-plus class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['images']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['images']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Video Generated') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['videos']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['videos']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.clapperboard class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['videos']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['videos']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-10">

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Support Tickets Created') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['tickets']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['tickets']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.headset class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['tickets']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['tickets']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Words Generated') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['words']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['words']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.text-wrap class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['words']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['words']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12px] font-bold mb-3">{{ __('Copy Created') }} <span class="text-gray-400">({{ __(now()->format('M')) }})</span></p>
                            <h2 class="text-[20px] font-bold mb-1">{{ number_format($platformMetrics['copies']['current_month']) }}</h2>
                            <span class="text-gray-400 text-[11px]">{{ $platformMetrics['copies']['change'] }}% {{ __('this month') }}</span>
                        </div>
                        <flux:icon.layers class="size-10 text-gray-400"/>
                    </div>
                    <div class="flex justify-between mt-4 pt-3 border-t border-(--default-border-color)">
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Last Month') }}</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['copies']['last_month']) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[11px] mr-1">{{ __('Total') }} ({{ date('Y') }})</span>
                            <span class="text-[11px] font-semibold">{{ number_format($platformMetrics['copies']['yearly']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Credits Usage + Top Models --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-10">
            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="pb-2 pt-5">
                    <h5 class="text-sm font-bold flex mb-3 pl-5"><x-heroicon-o-sparkles class="w-5 h-5 mr-2" />{{ __('Credits Usage') }} <span class="text-gray-400 ml-1">({{ __('FY') }} {{ date('Y') }})</span></h5>
                </div>
                <div class="p-4">
                    <canvas id="chart-credits-usage" class="h-[330px]"></canvas>
                </div>
            </div>

            <div class="rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <h5 class="text-sm font-bold flex items-center"><x-heroicon-o-cpu-chip class="w-5 h-5 mr-2" />{{ __('Top Used Models') }}</h5>
                    @php($modelsTotal = collect($topModels['all'] ?? [])->sum('count'))
                    @if ($modelsTotal > 0)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2.5 py-1 rounded-full text-zinc-600 dark:text-zinc-300" style="background: transparent;">
                            <x-heroicon-s-bolt class="w-3 h-3" />{{ number_format($modelsTotal) }} {{ __('generations') }}
                        </span>
                    @endif
                </div>

                @if (!empty($topModels['all']))
                    @php($modelsMax = max(array_column($topModels['all'], 'count')))
                    <div class="px-4 pb-4 flex flex-col gap-2.5">
                        @foreach ($topModels['all'] as $i => $model)
                            @php($kindStyle = match($model['kind']) {
                                'image' => ['icon' => 'heroicon-m-photo', 'chip' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300', 'label' => __('Image')],
                                'video' => ['icon' => 'heroicon-m-film', 'chip' => 'bg-violet-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300', 'label' => __('Video')],
                                'text'  => ['icon' => 'heroicon-m-document-text', 'chip' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300', 'label' => __('Copy')],
                                default => ['icon' => 'heroicon-m-cpu-chip', 'chip' => 'bg-zinc-100 text-zinc-600 dark:bg-(--default-element-light-bg-color) dark:text-zinc-300', 'label' => __('Model')],
                            })
                            <div class="group relative flex items-center gap-3 rounded-xl border border-(--default-border-color) px-3 py-2.5 hover:shadow-sm transition">
                                {{-- Icon + label --}}
                                <div class="flex items-center gap-2.5 min-w-0 w-44 shrink-0">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-(--default-element-light-bg-color) text-(--default-primary-color) shrink-0">
                                        <x-dynamic-component :component="$kindStyle['icon']" class="w-4 h-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold truncate" title="{{ $model['label'] }}">{{ $model['label'] }}</p>
                                        <p class="text-[10px] text-gray-400 truncate">{{ $model['vendor'] ?? $model['model_id'] }}</p>
                                    </div>
                                </div>

                                {{-- Bar --}}
                                <div class="flex-1 min-w-0 hidden sm:block">
                                    <div class="h-2.5 rounded-full bg-zinc-100 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-700"
                                            style="width: {{ $modelsMax > 0 ? max(6, round(($model['count'] / $modelsMax) * 100)) : 0 }}%; background: linear-gradient(120deg, #4F46E5, #6366F1);"></div>
                                    </div>
                                </div>

                                {{-- Category chip --}}
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-semibold {{ $kindStyle['chip'] }} shrink-0">{{ $kindStyle['label'] }}</span>

                                {{-- Count + percent --}}
                                <div class="text-right w-16 shrink-0">
                                    <p class="text-xs font-bold leading-tight">{{ number_format($model['count']) }}</p>
                                    <p class="text-[10px] text-gray-400 leading-tight">{{ $model['percent'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <x-heroicon-o-cpu-chip class="w-10 h-10 text-gray-300 mb-2" />
                        <p class="text-xs text-gray-400">{{ __('No model usage yet') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Support Tickets + Recent Activities --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-10">
            <div class="pb-5 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="pt-4 pb-4 px-5">
                    <div class="mt-3 flex items-center justify-between">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-lifebuoy class="w-5 h-5 mr-2" />{{ __('Support Tickets') }}</h5>
                        <a href="{{ route('admin.support.tickets') }}" wire:navigate class="text-xs text-zinc-500 hover:text-(--default-primary-color)">{{ __('View All') }} →</a>
                    </div>
                </div>
                <div class="px-5">
                    @forelse ($tickets as $ticket)
                        <a href="{{ route('admin.support.tickets.view', $ticket->ticket_id) }}" wire:navigate
                            class="flex items-center gap-3 py-3 border-b border-(--default-border-color) last:border-0 hover:bg-gray-50 dark:hover:bg-white/5 transition rounded-lg px-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold truncate">{{ ucfirst($ticket->subject) }}</p>
                                <p class="text-[10px] text-gray-400 truncate">
                                    #{{ $ticket->ticket_id }}
                                    @if ($ticket->category) · {{ ucfirst($ticket->category) }} @endif
                                    @if ($ticket->user) · {{ $ticket->user->name }} @endif
                                </p>
                            </div>
                            @php($statusStyles = [
                                'open' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300',
                                'in_progress' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-300',
                                'pending' => 'bg-zinc-100 text-zinc-700',
                                'resolved' => 'bg-emerald-100 text-emerald-800',
                                'closed' => 'bg-rose-100 text-rose-800',
                            ])
                            <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-medium rounded-lg {{ $statusStyles[$ticket->status] ?? 'bg-zinc-100 text-zinc-700' }}">
                                {{ __(ucfirst(str_replace('_', ' ', $ticket->status))) }}
                            </span>
                            <span class="text-[10px] text-gray-400 w-20 text-right shrink-0">{{ $ticket->updated_at?->diffForHumans(null, true) }}</span>
                        </a>
                    @empty
                        <p class="text-center text-xs text-gray-400 py-12">{{ __('No open tickets — all caught up.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="pb-5 rounded-xl border border-(--default-border-color) bg-white dark:bg-(--default-element-bg-color)">
                <div class="pt-4 pb-4 px-5">
                    <div class="mt-3 flex items-center justify-between">
                        <h5 class="text-sm font-bold flex"><x-heroicon-o-bell-alert class="w-5 h-5 mr-2" />{{ __('Recent Activities') }}</h5>
                        <a href="{{ route('admin.notifications.system') }}" wire:navigate class="text-xs text-zinc-500 hover:text-(--default-primary-color)">{{ __('View All') }} →</a>
                    </div>
                </div>
                <div class="px-5">
                    @php($activityStyles = [
                        'payment' => ['dot' => 'bg-emerald-500', 'icon' => 'heroicon-o-banknotes'],
                        'user' => ['dot' => 'bg-indigo-500', 'icon' => 'heroicon-o-user-plus'],
                        'payout' => ['dot' => 'bg-amber-500', 'icon' => 'heroicon-o-arrow-up-right'],
                        'general' => ['dot' => 'bg-zinc-400', 'icon' => 'heroicon-o-bell'],
                    ])
                    @forelse ($activities as $activity)
                        @php($style = $activityStyles[$activity['kind']] ?? $activityStyles['general'])
                        <div class="flex items-start gap-3 py-3 border-b border-(--default-border-color) last:border-0">
                            <span class="mt-1 w-2 h-2 rounded-full shrink-0 {{ $style['dot'] }}"></span>
                            <div class="flex-1 min-w-0">
                                @if ($activity['url'])
                                    <a href="{{ $activity['url'] }}" class="text-xs font-semibold hover:text-(--default-primary-color)">{{ __($activity['title']) }}</a>
                                @else
                                    <p class="text-xs font-semibold">{{ __($activity['title']) }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400 truncate">{{ $activity['message'] }}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 shrink-0">{{ $activity['time']?->diffForHumans(null, true) }}</span>
                        </div>
                    @empty
                        <p class="text-center text-xs text-gray-400 py-12">{{ __('No recent activity.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    function financeDashboard() {
        return {
            charts: [],
            init() {
                this.waitForApex(() => this.renderCharts());
            },
            waitForApex(cb, attempts = 0) {
                if (typeof ApexCharts !== 'undefined') return cb();
                if (attempts > 50) return console.error('ApexCharts failed to load');
                setTimeout(() => this.waitForApex(cb, attempts + 1), 100);
            },
            destroyCharts() {
                this.charts.forEach(c => { try { c.destroy(); } catch(e) {} });
                this.charts = [];
            },
            mount(selector, opts) {
                const el = document.querySelector(selector);
                if (!el) return;
                const chart = new ApexCharts(el, opts);
                chart.render();
                this.charts.push(chart);
            },
            renderCharts() {
                this.destroyCharts();
                const isDark = document.documentElement.classList.contains('dark');
                const C = {
                    text: '#a1a1aa',
                    grid: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
                    bg: isDark ? '#18181b' : '#ffffff',
                    // Brand palette — see .kiro/steering/brand-palette.md. Use these
                    // for brand-expressive series (revenue, categorical breakdowns).
                    brand: '#4F46E5',        // primary (indigo-600)
                    brandDeep: '#0F172A',    // secondary (slate-900)
                    brandAccent: '#F59E0B',  // accent UI (amber-500) — fills only, never text
                    brandHover: '#6366F1',   // derived: indigo-500
                    brandOnDark: '#818CF8',  // derived: indigo-400
                    accentStrong: '#B45309', // derived: amber-700
                    // Semantic-only — never used as brand identity.
                    emerald: '#10b981',      // success
                    amber: '#f59e0b',        // warning
                    rose: '#f43f5e',         // destructive / critical
                    zinc300: isDark ? '#3f3f46' : '#d4d4d8',
                    track: isDark ? '#27272a' : '#f4f4f5',
                    value: isDark ? '#fafafa' : '#18181b',
                };
                const base = {
                    chart: { toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
                    grid: { borderColor: C.grid, strokeDashArray: 3, padding: { left: 8, right: 8 } },
                    xaxis: { labels: { style: { colors: C.text, fontSize: '10px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                    yaxis: { labels: { style: { colors: C.text, fontSize: '10px' } } },
                    tooltip: { theme: isDark ? 'dark' : 'light', style: { fontSize: '11px' } },
                    legend: { labels: { colors: C.text }, fontSize: '11px' },
                    dataLabels: { enabled: false },
                };
                const cs = @js($currencySymbol);
                const fmt = v => cs + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : Number(v).toFixed(0));

                // 1. Revenue Overview
                const rev = @json($revenueChartData);
                this.mount('#revenueChart', {
                    ...base,
                    chart: { ...base.chart, type: 'line', height: 300 },
                    series: [
                        { name: 'Revenue', type: 'area', data: rev.revenue },
                        { name: 'Cost', type: 'column', data: rev.orders }
                    ],
                    colors: [isDark ? C.brandOnDark : C.brand, C.brandAccent],
                    fill: {
                        type: ['gradient', 'solid'],
                        gradient: {
                            shade: isDark ? 'dark' : 'light',
                            type: 'vertical',
                            shadeIntensity: 0.2,
                            inverseColors: false,
                            opacityFrom: 0.55,
                            opacityTo: 0.0,
                            stops: [0, 100],
                        }
                    },
                    stroke: { width: [1.5, 0], curve: 'smooth' },
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
                    xaxis: { ...base.xaxis, categories: rev.labels },
                    yaxis: [
                        { ...base.yaxis, title: { text: 'Revenue', style: { color: C.text, fontSize: '10px' } }, labels: { formatter: fmt }, tickAmount: 4 },
                    ],
                });

                // 2. Revenue by Plan (donut)
                const rbp = @json($revenueByPlanType);
                const rbpLabels = Object.keys(rbp);
                const rbpValues = Object.values(rbp).map(Number);
                if (rbpValues.length > 0) {
                    this.mount('#revenuePlan', {
                        chart: { type: 'donut', height: 330, background: 'transparent', toolbar: { show: false } },
                        series: rbpValues,
                        labels: rbpLabels,
                        colors: [C.brand, C.brandDeep, C.brandAccent, C.brandHover, C.accentStrong, C.brandOnDark],
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true,
                            name: { fontSize: '12px', color: C.text },
                            value: { fontSize: '14px', fontWeight: 700, color: C.value, formatter: v => cs + Number(v).toLocaleString() },
                            total: { show: true, label: 'Total', color: C.text, formatter: w => cs + w.globals.seriesTotals.reduce((a,b) => a+b, 0).toLocaleString() }
                        } } } },
                        stroke: { width: 2, colors: [C.bg] },
                        dataLabels: { enabled: false },
                        legend: { position: 'bottom', labels: { colors: C.text }, fontSize: '10px' },
                        tooltip: { theme: isDark ? 'dark' : 'light' },
                    });
                }
                
                // 3. User Distribution (Chart.js doughnut)
                const ud = @json($userDistribution);
                const udLabels = Object.keys(ud);
                const udValues = Object.values(ud).map(Number);
                const udCanvas = document.getElementById('userDoughnut');
                if (udCanvas && udValues.length > 0) {
                    new Chart(udCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: udLabels,
                            datasets: [{
                                data: udValues,
                                backgroundColor: [C.brand, C.brandDeep, C.brandAccent],
                                borderColor: C.bg,
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '10%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: C.text, font: { size: 11 }, padding: 30 }
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#27272a' : '#fff',
                                    titleColor: C.value,
                                    bodyColor: C.text,
                                    borderColor: C.zinc300,
                                    borderWidth: 1,
                                }
                            }
                        }
                    });
                }

                // 4. Shared month labels + stacked-bar options (used by Credits Usage)
                const monthLabels = ['{{ __('Jan') }}','{{ __('Feb') }}','{{ __('Mar') }}','{{ __('Apr') }}','{{ __('May') }}','{{ __('Jun') }}','{{ __('Jul') }}','{{ __('Aug') }}','{{ __('Sep') }}','{{ __('Oct') }}','{{ __('Nov') }}','{{ __('Dec') }}'];
                const stackedBarOpts = (stepHint) => ({
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: { stacked: true, beginAtZero: true, ticks: { color: C.text, font: { size: 10 } }, grid: { color: C.grid } },
                        x: { stacked: true, ticks: { color: C.text, font: { size: 10 } }, grid: { color: C.grid } },
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: C.text, boxWidth: 10, font: { size: 11 }, padding: 16 } },
                        tooltip: {
                            backgroundColor: isDark ? '#27272a' : '#fff',
                            titleColor: C.value, bodyColor: C.text, borderColor: C.zinc300, borderWidth: 1,
                        },
                    },
                });

                // 5. Credits Usage (stacked bar, Chart.js) — media + copy
                const cu = @json($creditsUsageChart);
                const cuCanvas = document.getElementById('chart-credits-usage');
                if (cuCanvas) {
                    new Chart(cuCanvas, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                { label: '{{ __('Media Credits') }}', data: cu.media, backgroundColor: C.brand, barPercentage: 0.7, borderRadius: { topLeft: 0, topRight: 0 } },
                                { label: '{{ __('Copy Credits') }}', data: cu.copy, backgroundColor: C.brandAccent, barPercentage: 0.7, borderRadius: { topLeft: 12, topRight: 12 } },
                            ],
                        },
                        options: stackedBarOpts(),
                    });
                }

                // 6. Top Used Models is rendered server-side as a styled
                //    leaderboard (no JS chart needed).
            }
        };
    }
    </script>

    {{-- Google Analytics: lazy-loaded over AJAX so slow GA round-trips never
         block the dashboard render. Powers the country list, GeoChart map,
         User Traffic doughnut, Users & Sessions chart, and the GA stat cards. --}}
    @if ($google_analytics_dashboard)
        @if ($google_maps && $google_maps_key)
            <script src="https://www.gstatic.com/charts/loader.js"></script>
        @endif
        <script>
        (function () {
            const brand = { indigo: '#4F46E5', slate: '#0F172A', indigoSoft: '#6366F1', amber: '#F59E0B', amberSoft: '#FCD34D', onDark: '#818CF8' };
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = '#a1a1aa';
            const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
            let mapData = null;

            function gaLoading(show) {
                document.querySelectorAll('.ga-preloader, #ga-preloader-3').forEach(el => {
                    el.innerHTML = show
                        ? '<span class="inline-block w-4 h-4 border-2 border-zinc-300 border-t-(--default-primary-color) rounded-full animate-spin"></span>'
                        : '';
                });
            }

            function waitForChart(cb, n = 0) {
                if (typeof Chart !== 'undefined') return cb();
                if (n > 60) return;
                setTimeout(() => waitForChart(cb, n + 1), 100);
            }

            function setText(id, value) {
                const el = document.getElementById(id);
                if (el) el.innerHTML = value;
            }

            function renderTraffic(labels, values) {
                const canvas = document.getElementById('trafficDoughnut');
                if (!canvas || !labels.length) return;
                const palette = [brand.slate, brand.indigo, brand.indigoSoft, brand.amber, brand.onDark, brand.amberSoft];
                new Chart(canvas, {
                    type: 'doughnut',
                    data: { labels, datasets: [{ data: values, backgroundColor: palette, borderColor: isDark ? '#18181b' : '#fff', borderWidth: 2, hoverOffset: 6 }] },
                    options: {
                        cutout: '68%', maintainAspectRatio: false, responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: isDark ? '#27272a' : '#fff', titleColor: isDark ? '#fafafa' : '#18181b', bodyColor: textColor, borderColor: gridColor, borderWidth: 1 },
                        },
                    },
                });
                const legend = document.getElementById('trafficLegend');
                if (legend) {
                    legend.innerHTML = labels.map((l, i) =>
                        `<span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full" style="background:${palette[i % palette.length]}"></span>${l}</span>`
                    ).join('');
                }
            }

            function renderUsersSessions(usersObj, sessionsObj) {
                const canvas = document.getElementById('usersSessionsChart');
                if (!canvas) return;
                const labels = Object.values(usersObj.days || {});
                const users = Object.values(usersObj.users || {}).map(Number);
                const sessions = Object.values(sessionsObj.sessions || {}).map(Number);
                const ctx = canvas.getContext('2d');
                const usersFill = ctx.createLinearGradient(0, 0, 0, 300);
                usersFill.addColorStop(0, 'rgba(79,70,229,0.35)');
                usersFill.addColorStop(1, 'rgba(79,70,229,0)');
                const sessFill = ctx.createLinearGradient(0, 0, 0, 300);
                sessFill.addColorStop(0, 'rgba(245,158,11,0.30)');
                sessFill.addColorStop(1, 'rgba(245,158,11,0)');
                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: '{{ __('Sessions') }}', data: sessions, borderColor: brand.amber, backgroundColor: sessFill, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, fill: true, tension: 0.4 },
                            { label: '{{ __('Users') }}', data: users, borderColor: brand.indigo, backgroundColor: usersFill, borderWidth: 2, pointRadius: 0, pointHoverRadius: 4, fill: true, tension: 0.4 },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false, responsive: true,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            y: { beginAtZero: true, ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                            x: { ticks: { color: textColor, font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }, grid: { display: false } },
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: textColor, boxWidth: 10, font: { size: 11 }, padding: 16 } },
                            tooltip: { backgroundColor: isDark ? '#27272a' : '#fff', titleColor: isDark ? '#fafafa' : '#18181b', bodyColor: textColor, borderColor: gridColor, borderWidth: 1 },
                        },
                    },
                });
            }

            @if ($google_maps && $google_maps_key)
            function drawMap() {
                if (!mapData || typeof google === 'undefined' || !google.visualization) return;
                const rows = [['Country', '{{ __('Users') }}']];
                Object.entries(mapData).forEach(([k, v]) => rows.push([k, parseInt(v)]));
                if (rows.length < 2) return;
                const data = google.visualization.arrayToDataTable(rows);
                const chart = new google.visualization.GeoChart(document.getElementById('countries-analytics-chart'));
                chart.draw(data, {
                    colorAxis: { colors: ['#C7D2FE', '#4F46E5'] },
                    backgroundColor: 'transparent',
                    datalessRegionColor: isDark ? 'rgba(255,255,255,0.06)' : '#f1f5f9',
                    defaultColor: '#4F46E5',
                });
            }
            if (typeof google !== 'undefined' && google.charts) {
                google.charts.load('current', { packages: ['geochart'], mapsApiKey: '{{ $google_maps_key }}' });
                google.charts.setOnLoadCallback(() => drawMap());
            }
            @endif

            function loadAnalytics() {
                gaLoading(true);
                fetch('{{ route('admin.dashboard.analytics') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                })
                .then(r => r.json())
                .then(data => {
                    gaLoading(false);
                    if (!data || data.status !== 200) return;

                    setText('google_session_views', data.google_session_views);
                    setText('google_sessions', data.google_sessions);
                    setText('google_bounce_rate', (parseFloat(data.google_bounce_rate) * 100).toFixed(2) + '%');
                    setText('google_average_session', data.google_average_session);

                    const cl = document.getElementById('countryList');
                    if (cl) cl.innerHTML = data.google_countries || '';

                    waitForChart(() => {
                        try { renderTraffic(Object.values(JSON.parse(data.traffic_label)), Object.values(JSON.parse(data.traffic_data)).map(Number)); } catch (e) {}
                        try { renderUsersSessions(JSON.parse(data.google_users), JSON.parse(data.google_user_sessions)); } catch (e) {}
                    });

                    @if ($google_maps && $google_maps_key)
                    mapData = data.map_countries || {};
                    drawMap();
                    @endif
                })
                .catch(() => gaLoading(false));
            }

            if (document.readyState !== 'loading') loadAnalytics();
            else document.addEventListener('DOMContentLoaded', loadAnalytics);
        })();
        </script>
    @endif
@endpush
