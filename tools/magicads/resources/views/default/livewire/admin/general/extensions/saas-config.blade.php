<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-6/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="{{ route('admin.general') }}" separator="slash" class="text-xs">{{ __('General Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Plugins') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('SaaS Business') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('SaaS Business') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('SaaS Feature Configuration') }}</flux:subheading>
            </div>

            <div>
                <flux:fieldset>
                    <div class="flex gap-4 flex-col">
                        <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <h2 class="text-md font-bold mb-7">{{ __('General') }}</h2>

                            <div class="md:border border-(--default-border-color) md:p-5 rounded-xl dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <flux:field variant="inline">
                                    <flux:label>{{ __('Enable SaaS Business') }}</flux:label>
                                    <flux:description><small>{{ __('When enabled, billing, plans, subscriptions and referral features become available across the platform') }}</small></flux:description>
                                    <flux:switch wire:model.live="saas_feature" />
                                    <flux:error name="saas_feature" />
                                </flux:field>
                            </div>
                        </div>
                    </div>

                    <div class="flex w-full justify-center mt-4">
                        <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-(--default-primary-color) py-6 rounded-xl cursor-pointer">{{ __('Save') }}</flux:button>
                    </div>
                </flux:fieldset>
            </div>

        </div>
    </div>
</div>
