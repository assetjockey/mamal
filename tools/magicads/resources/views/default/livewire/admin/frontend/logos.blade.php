<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Frontend Settings')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Logos')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Logos') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Control all application logos easily') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="bold-label md:border-1 bg-(--default-body-bg-color) border-(--default-body-bg-color) rounded-xl md:p-5 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <flux:label><strong>{{ __('Frontend Logos') }}</strong></flux:label>
                    </div>    

                    <div class="space-y-4">
                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Frontend Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_frontend" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_frontend)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_frontend->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_frontend_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_frontend_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div>  

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Frontend Collapsed Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_frontend_collapsed" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_frontend_collapsed)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_frontend_collapsed->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_frontend_collapsed_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_frontend_collapsed_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Favicon') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_favicon" accept="image/x-icon,image/vnd.microsoft.icon"/>
                                    </flux:field>
                                </div>
                                @if($logo_favicon)
                                    <div class="flex-shrink-0">
                                        <span class="text-sm text-green-600">✓ {{ $logo_favicon->getClientOriginalName() }}</span>
                                    </div>
                                @elseif($logo_favicon_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_favicon_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 
                    </div>

                    <div class="mt-12 bold-label md:border-1 bg-(--default-body-bg-color) border-(--default-body-bg-color) rounded-xl md:p-5 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <flux:label><strong>{{ __('Dashboard Logos') }}</strong></flux:label>
                    </div>   

                    <div class="space-y-4">
                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Dashboard Light Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_dashboard_light" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_dashboard_light)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_dashboard_light->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_dashboard_light_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_dashboard_light_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Dashboard Dark Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_dashboard_dark" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_dashboard_dark)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_dashboard_dark->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_dashboard_dark_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_dashboard_dark_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Dashboard Collapsed Light Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_dashboard_collapsed_light" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_dashboard_collapsed_light)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_dashboard_collapsed_light->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_dashboard_collapsed_light_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_dashboard_collapsed_light_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-7 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="flex items-start gap-4">
                                <div class="flex-1">
                                    <flux:field >
                                        <div class="mb-3">
                                            <flux:label>{{ __('Dashboard Collapsed Dark Logo') }}</flux:label>
                                        </div>
                                        <flux:input type="file" wire:model="logo_dashboard_collapsed_dark" accept="image/*"/>
                                    </flux:field>
                                </div>
                                @if($logo_dashboard_collapsed_dark)
                                    <div class="flex-shrink-0">
                                        <img src="{{ $logo_dashboard_collapsed_dark->temporaryUrl() }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @elseif($logo_dashboard_collapsed_dark_path)
                                    <div class="flex-shrink-0">
                                        <img src="{{ URL::asset($logo_dashboard_collapsed_dark_path) }}" alt="Logo Preview" class="h-20 object-cover rounded-lg border border-gray-300">
                                    </div>
                                @endif
                            </div>
                        </div> 
                    </div>

                    <div class="flex w-full justify-center mt-9">
                        <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{__('Save')}}</flux:button>						
                    </div>
                </flux:fieldset>

            </div>
            
        </div>
    </div>

</div>

