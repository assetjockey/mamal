<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-10/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="route('admin.support.tickets')" separator="slash" class="text-xs">{{__('Support')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Tickets')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Support Tickets') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Manage all customer support tickets') }}</flux:subheading>
            </div>

            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bold-label md:border-1 border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-500 mb-2">{{ __('Open Tickets') }}</div>
                                <div class="text-2xl font-extrabold">{{ $openCount }}</div>
                            </div>
                            <flux:icon.folder-open class="size-12 text-gray-500"/>
                        </div>
                    </div>

                    <div class="bold-label md:border-1 border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-500 mb-2">{{ __('Pending Tickets') }}</div>
                                <div class="text-2xl font-extrabold">{{ $pendingCount }}</div>
                            </div>
                            <flux:icon.clipboard-document-list class="size-12 text-amber-500"/>
                        </div>
                    </div>

                    <div class="bold-label md:border-1 border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-500 mb-2">{{ __('Resolved Tickets') }}</div>
                                <div class="text-2xl font-extrabold">{{ $resolvedCount }}</div>
                            </div>
                            <flux:icon.check-badge class="size-12 text-green-500"/>
                        </div>
                    </div>

                    <div class="bold-label md:border-1 border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-500 mb-2">{{ __('Closed Tickets') }}</div>
                                <div class="text-2xl font-extrabold">{{ $closedCount }}</div>
                            </div>
                            <flux:icon.x-circle class="size-12 text-red-500"/>
                        </div>
                    </div>


                </div>
            </div>

            <div class="mt-12">
                {{ $this->table }}
            </div>
            
        </div>
    </div>

</div>

