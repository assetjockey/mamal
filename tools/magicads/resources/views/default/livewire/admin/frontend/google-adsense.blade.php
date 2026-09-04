<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-7/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('Frontend Settings') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Google AdSense') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Google AdSense') }}</h1>
                <flux:subheading size="md">{{ __('Connect AdSense and choose where ads appear on your public pages') }}</flux:subheading>
            </div>

            <flux:fieldset>
                <div class="flex gap-4 flex-col">

                    {{-- Account --}}
                    <div class="md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-7">{{ __('Account') }}</h2>

                        <div class="md:border border-(--default-border-color) md:p-5 rounded-xl mb-6 dark:border-white/8 dark:bg-(--default-element-bg-color)">
                            <flux:field variant="inline">
                                <flux:label>{{ __('Enable Google AdSense') }}</flux:label>
                                <flux:description><small>{{ __('Master switch. When off, no ad code is loaded on the frontend.') }}</small></flux:description>
                                <flux:switch wire:model.live="enabled" />
                                <flux:error name="enabled" />
                            </flux:field>
                        </div>

                        <flux:field class="mb-6">
                            <flux:label>{{ __('Publisher ID') }}</flux:label>
                            <flux:input wire:model="publisher_id" placeholder="ca-pub-1234567890123456" />
                            <flux:description>{{ __('Found in your AdSense account under Account → Settings. Format: ca-pub- followed by 16 digits.') }}</flux:description>
                            <flux:error name="publisher_id" />
                        </flux:field>

                        <div class="md:border border-(--default-border-color) md:p-5 rounded-xl dark:border-white/8 dark:bg-(--default-element-bg-color)">
                            <flux:field variant="inline">
                                <flux:label>{{ __('Auto Ads') }}</flux:label>
                                <flux:description><small>{{ __('Let Google automatically place ads across your pages. You can still configure the manual placements below.') }}</small></flux:description>
                                <flux:switch wire:model.live="auto_ads" />
                                <flux:error name="auto_ads" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- Placements --}}
                    <div class="md:border border-(--default-border-color) rounded-xl md:p-10 mb-3 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <h2 class="text-md font-bold mb-1">{{ __('Ad Placements') }}</h2>
                        <flux:subheading class="mb-7">{{ __('Paste the ad unit slot ID for each spot you want to use. Empty spots stay hidden on the frontend.') }}</flux:subheading>

                        <div class="flex flex-col gap-6">
                            @foreach ($placements as $key => $meta)
                                <flux:field>
                                    <div class="flex items-center justify-between gap-3">
                                        <flux:label>{{ __($meta['label']) }}</flux:label>
                                        <flux:badge color="zinc" size="sm">{{ $meta['size'] }}</flux:badge>
                                    </div>
                                    <flux:input wire:model="slot_{{ $key }}" placeholder="{{ __('Ad unit slot ID, e.g. 1234567890') }}" />
                                    <flux:error name="slot_{{ $key }}" />
                                </flux:field>
                            @endforeach
                        </div>

                        <div class="mt-7 flex items-start gap-2 rounded-lg bg-indigo-50/60 px-3 py-2.5 text-[12px] leading-relaxed text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <flux:icon.information-circle class="size-4 mt-px shrink-0" />
                            <span>{{ __('Create each ad unit in your AdSense dashboard (Ads → By ad unit), then copy its data-ad-slot value here. Only spots with a slot ID will render.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex w-full justify-center mt-4">
                    <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{ __('Save') }}</flux:button>
                </div>
            </flux:fieldset>
        </div>
    </div>
</div>
