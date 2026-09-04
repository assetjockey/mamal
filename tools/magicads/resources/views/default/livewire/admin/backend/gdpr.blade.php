<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Backend Settings')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('GDPR Settings')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('GDPR Settings') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Control your GDPR compliance settings') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="flex gap-4 flex-col">
                        <div class="md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="mb-5">
                                    <flux:switch class="mr-2" wire:model.boolean="enable_cookies" label="{{ __('Enable GDPR Cookies') }}" align="left" />
                                </div>
                                <div class="mb-5">
                                    <flux:switch class="mr-2" wire:model.boolean="disable_page_interaction" label="{{ __('Disable Page Interaction') }}" align="left" />
                                </div>
                                <div>
                                    <flux:switch class="mr-2" wire:model.boolean="hide_from_bots" label="{{ __('Hide from Bots') }}" align="left" />
                                </div>
                                <div>
                                    <flux:switch class="mr-2" wire:model.boolean="enable_dark_mode" label="{{ __('Enable Dark Mode') }}" align="left" />
                                </div>
                            </div>
                        </div>

                        <div class="md:border border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field class="mb-7">
                                <flux:label class="font-extrabold">{{ __('Cookie Valid Days') }}</flux:label>
                                <flux:input type="number" wire:model="cookie_valid_days"/>
                                <flux:error name="cookie_valid_days" />
                            </flux:field>  
                            <flux:field class="mb-7">
                                <flux:label class="font-extrabold">{{ __('Cookie Consent Modal Layout') }}</flux:label>
                                <flux:select wire:model="consent_modal_layout" placeholder="{{__('Choose modal layout')}}">
                                    <flux:select.option>box</flux:select.option>
                                    <flux:select.option>box inline</flux:select.option>
                                    <flux:select.option>box wide</flux:select.option>
                                    <flux:select.option>cloud</flux:select.option>
                                    <flux:select.option>cloud inline</flux:select.option>
                                    <flux:select.option>bar</flux:select.option>
                                    <flux:select.option>bar inline</flux:select.option>
                                </flux:select>
                            </flux:field>    
                            <flux:field class="mb-7">
                                <flux:label class="font-extrabold">{{ __('Cookie Consent Modal Position') }}</flux:label>
                                <flux:select wire:model="consent_modal_position" placeholder="{{__('Choose modal position')}}">
                                    <flux:select.option>top left</flux:select.option>
                                    <flux:select.option>top center</flux:select.option>
                                    <flux:select.option>top right</flux:select.option>
                                    <flux:select.option>middle left</flux:select.option>
                                    <flux:select.option>middle center</flux:select.option>
                                    <flux:select.option>middle right</flux:select.option>
                                    <flux:select.option>bottom left</flux:select.option>
                                    <flux:select.option>bottom center</flux:select.option>
                                    <flux:select.option>bottom right</flux:select.option>
                                </flux:select>
                            </flux:field>    
                            <flux:field class="mb-7">
                                <flux:label class="font-extrabold">{{ __('Preferences Modal Layout') }}</flux:label>
                                <flux:select wire:model="preferences_modal_layout" placeholder="{{__('Choose preferences modal layout')}}">
                                    <flux:select.option>box</flux:select.option>
                                    <flux:select.option>bar</flux:select.option>
                                    <flux:select.option>bar wide</flux:select.option>
                                </flux:select>
                            </flux:field>   
                             <flux:field>
                                <flux:label class="font-extrabold">{{ __('Preferences Modal Position') }}</flux:label>
                                <flux:select wire:model="preferences_modal_position" placeholder="{{__('Choose preferences modal position')}}">
                                    <flux:select.option>left</flux:select.option>
                                    <flux:select.option>right</flux:select.option>
                                </flux:select>
                            </flux:field>                  
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

