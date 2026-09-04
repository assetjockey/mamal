<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-9/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Accounts')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Activity Monitoring')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold md:text-2xl">{{ __('Activity Monitoring') }}</h1>
                <flux:subheading size="sm" class="mb-6 md:text-sm">{{ __('Realtime user activity status overview') }}</flux:subheading>
            </div>

            <div class="mt-12">
                {{ $this->table }}
            </div>
            
        </div>
    </div>

</div>

