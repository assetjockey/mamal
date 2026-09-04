<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Frontend Settings')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('SEO Manager')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('SEO Manager') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Enhance your SEO settings across the website') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="flex gap-4 flex-col">
                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="bold-label md:border-1 bg-(--default-body-bg-color) border-(--default-body-bg-color) rounded-xl md:p-4 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <flux:label><strong>{{ __('SEO for Main Landing Page') }}</strong></flux:label>
                            </div>
                            <div class="flex-col gap-5">
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Home Page Title') }}</flux:label>
                                    <flux:input wire:model="home_title" placeholder="{{__('Enter your landing page title')}}"/>
                                    <flux:error name="home_title" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Home Page Description') }}</flux:label>
                                    <flux:input wire:model="home_description" placeholder="{{__('Enter your landing page description')}}" type="textarea" rows="4"/>
                                    <flux:error name="home_description" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Home Page Keywords') }}</flux:label>
                                    <flux:input wire:model="home_keywords" placeholder="{{__('Enter your landing page keywords')}}"/>
                                    <flux:error name="home_keywords" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Home Page Author') }}</flux:label>
                                    <flux:input wire:model="home_author" placeholder="{{__('Enter your landing page author name')}}"/>
                                    <flux:error name="home_author" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Home Page Canonical URL') }}</flux:label>
                                    <flux:input type="url" wire:model="home_url" placeholder="{{__('Enter your landing page canonical URL')}}"/>
                                    <flux:error name="home_url" />
                                </flux:field>
                            </div>                           
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="bold-label md:border-1 bg-(--default-body-bg-color) border-(--default-body-bg-color) rounded-xl md:p-4 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <flux:label><strong>{{ __('SEO for Login Page') }}</strong></flux:label>
                            </div>
                            <div class="flex-col gap-5">
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Login Page Title') }}</flux:label>
                                    <flux:input wire:model="login_title" placeholder="{{__('Enter your login page title')}}"/>
                                    <flux:error name="login_title" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Login Page Description') }}</flux:label>
                                    <flux:input wire:model="login_description" placeholder="{{__('Enter your login page description')}}" type="textarea" rows="4"/>
                                    <flux:error name="login_description" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Login Page Keywords') }}</flux:label>
                                    <flux:input wire:model="login_keywords" placeholder="{{__('Enter your login page keywords')}}"/>
                                    <flux:error name="login_keywords" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Login Page Author') }}</flux:label>
                                    <flux:input wire:model="login_author" placeholder="{{__('Enter your login page author name')}}"/>
                                    <flux:error name="login_author" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Login Page Canonical URL') }}</flux:label>
                                    <flux:input type="url" wire:model="login_url" placeholder="{{__('Enter your login page canonical URL')}}"/>
                                    <flux:error name="login_url" />
                                </flux:field>
                            </div>                           
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="bold-label md:border-1 bg-(--default-body-bg-color) border-(--default-body-bg-color) rounded-xl md:p-4 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                                <flux:label><strong>{{ __('SEO for Registration Page') }}</strong></flux:label>
                            </div>
                            <div class="flex-col gap-5">
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Registration Page Title') }}</flux:label>
                                    <flux:input wire:model="registration_title" placeholder="{{__('Enter your registration page title')}}"/>
                                    <flux:error name="registration_title" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Registration Page Description') }}</flux:label>
                                    <flux:input wire:model="registration_description" placeholder="{{__('Enter your registration page description')}}" type="textarea" rows="4"/>
                                    <flux:error name="registration_description" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Registration Page Keywords') }}</flux:label>
                                    <flux:input wire:model="registration_keywords" placeholder="{{__('Enter your registration page keywords')}}"/>
                                    <flux:error name="registration_keywords" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Registration Page Author') }}</flux:label>
                                    <flux:input wire:model="registration_author" placeholder="{{__('Enter your registration page author name')}}"/>
                                    <flux:error name="registration_author" />
                                </flux:field>
                                <flux:field class="mb-6">
                                    <flux:label>{{ __('Registration Page Canonical URL') }}</flux:label>
                                    <flux:input type="url" wire:model="registration_url" placeholder="{{__('Enter your registration page canonical URL')}}"/>
                                    <flux:error name="registration_url" />
                                </flux:field>
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

