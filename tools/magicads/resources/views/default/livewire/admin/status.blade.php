<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-5/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('System Status')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('System Status') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Monitor your server performance status') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="space-y-4">
                        <div class="bold-label md:border-1 border-neutral-200 rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field variant="inline">
                                <flux:label class="font-extrabold"><strong>{{ __('User Registration') }}</strong></flux:label>
                                <flux:description>{{ __('When disabled, registration button will not be available for new users that are yet to register') }}</flux:description>
                                <flux:switch wire:model.live="newUser" />
                                <flux:error name="newUser" />
                            </flux:field>
                        </div>

                        <div class="bold-label md:border-1 border-neutral-200 rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field variant="inline">
                                <flux:label class="font-extrabold"><strong>{{ __('Email Verification') }}</strong></flux:label>
                                <flux:description>{{ __('When enabled, newly registered users will be required to verify their email addresses upon registration') }}</flux:description>
                                <flux:switch wire:model.live="emailVerification" />
                                <flux:error name="emailVerification" />
                            </flux:field>
                        </div>

                        <div class="bold-label md:border-1 border-neutral-200 rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field variant="inline">
                                <flux:label class="font-extrabold"><strong>{{ __('Subscribe During Registration') }}</strong></flux:label>
                                <flux:description>{{ __('When enabled, newly registered users will be required select a subscription plan upon registration') }}</flux:description>
                                <flux:switch wire:model.live="subscribeDuringRegistration" />
                                <flux:error name="subscribeDuringRegistration" />
                            </flux:field>
                        </div>
                    
                    </div>
                </flux:fieldset>

            </div>
            
        </div>
    </div>

</div>

