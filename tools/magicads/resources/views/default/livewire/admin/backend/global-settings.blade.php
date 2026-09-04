<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-7/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Backend Settings')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Global Settings')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Global Settings') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Setup global website details and tools') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="flex gap-4 flex-col">
                        <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <flux:field class="mb-6">
                                    <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Website Name') }}</flux:label>
                                    <flux:input wire:model="website_name" placeholder="{{__('Enter your website name')}}" value="{{ config('app.name') }}"/>
                                    <flux:error name="website_name" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Website URL') }}</flux:label>
                                    <flux:input wire:model="website_url" placeholder="{{__('Enter your website URL')}}" value="{{ config('app.url') }}"/>
                                    <flux:error name="website_url" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Default Main Theme') }}</flux:label>
                                    <flux:select wire:model="default_theme">
                                        <flux:select.option value="light">{{ __('Light Theme') }}</flux:select.option>
                                        <flux:select.option value="dark">{{ __('Dark Theme') }}</flux:select.option>
                                    </flux:select>                                
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Time Zone') }}</flux:label>
                                    <flux:select wire:model="time_zone">
                                        @foreach(config('timezones') as $value => $label)
                                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                        @endforeach
                                    </flux:select>                                
                                </flux:field>
                            </div>                           
                        </div> 

                        <div class="mbold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:heading class="mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <strong>{{ __('Google reCaptcha v3') }}</strong>
                            </flux:heading>
                            <flux:switch wire:model.boolean="google_recaptcha" label="{{ __('Enable Google reCaptcha v3') }}" align="left" />
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <flux:input class="!focus:border-(--default-border-color)" wire:model="google_recaptcha_site_key" label="{{ __('Google reCaptcha Site Key') }}"/>
                                <flux:input wire:model="google_recaptcha_secret_key" label="{{ __('Google reCaptcha Secret Key') }}"/>
                            </div>                           
                        </div> 

                        <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:heading class="mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <strong>{{ __('Google Analytics for Homepage') }}</strong>
                            </flux:heading>
                            <flux:switch wire:model.boolean="google_analytics_homepage" label="{{ __('Enable Google Analytics for Homepage') }}" align="left" />
                            <div class="mt-5 grid grid-cols-1">
                                <flux:input wire:model="google_analytics_tracking_id" label="{{ __('Google Analytics Tracking ID') }}"/>
                            </div>
                            <flux:separator variant="subtle"  class="mt-7"/>
                            <div class="mt-7">
                                <flux:heading class="mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    <strong>{{ __('Google Analytics for Admin Dashboard') }}</strong>
                                </flux:heading>
                                <flux:switch wire:model.boolean="google_analytics_dashboard" label="{{ __('Enable Google Analytics for Admin Dashboard') }}" align="left" />
                                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <flux:input wire:model="google_analytics_property_id" label="{{ __('Google Analytics Property ID') }}"/>
                                    <flux:input wire:model="google_analytics_service_credentials" label="{{ __('Google Service Account Credentials') }}"/>
                                </div>
                            </div>                            
                        </div>    
                        
                        <div class="mbold-label md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:heading class="mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <strong>{{ __('Google Maps') }}</strong>
                            </flux:heading>
                            <flux:switch wire:model.boolean="google_maps" label="{{ __('Enable Google Maps') }}" align="left" />
                            <div class="mt-5 grid grid-cols-1">
                                <flux:input wire:model="google_maps_api_key" label="{{ __('Google Maps API Key') }}"/>
                            </div>                           
                        </div> 
                    </div>

                    <div class="flex w-full justify-center mt-4">
                        <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{__('Save')}}</flux:button>						
                    </div>
                </flux:fieldset>

            </div>
            
        </div>
    </div>

</div>

