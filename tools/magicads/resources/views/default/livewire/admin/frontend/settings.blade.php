<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Frontend Settings')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Frontend Settings') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Setup your frontend settings') }}</flux:subheading>
            </div>

            <div>

                <flux:fieldset>

                    <div class="flex gap-4 flex-col">
                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field variant="inline">
                                <flux:label class="font-extrabold"><strong>{{ __('Frontend Page') }}</strong></flux:label>
                                <flux:description>{{ __('When disabled, main landing page will not be available for anyone') }}</flux:description>
                                <flux:switch wire:model.boolean="frontend_page" />
                                <flux:error name="frontend_page" />
                            </flux:field>                          
                        </div> 

                        <div class="bold-label md:border-1 border-neutral-200 rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="mb-4">
                                <flux:label><strong>{{ __('Custom Landing Page URL') }}</strong></flux:label>
                            </div>                            
                            <flux:switch wire:model.boolean="custom_url_enabled" label="{{ __('Enable') }}" align="left" />
                            <div class="mt-3">
                                <flux:label>{{ __('Landing Page URL') }}
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                            <p>{{__('Set custom index url for all frontend pages. Ex: https://aws.amazon.com (Note: https:// - is required)')}}</p>
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                </flux:label>
                                <flux:input type="url" wire:model="custom_url" />
                                <flux:error name="custom_url" />
                            </div>                            
                        </div> 

                        <div class="md:border-1 border-(--default-border-color) rounded-xl md:p-10 mb-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <div class="mb-5">
                                <flux:label><strong>{{ __('Social Media Information') }}</strong></flux:label>
                            </div> 
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <flux:field class="mb-4">
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>{{ __('Twitter') }}</flux:label>
                                    <flux:input wire:model="twitter" placeholder="{{__('Enter your Twitter URL')}}"/>
                                    <flux:error name="twitter" />
                                </flux:field>
                                <flux:field class="mb-4">
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>{{ __('Facebook') }}</flux:label>
                                    <flux:input wire:model="facebook" placeholder="{{__('Enter your Facebook URL')}}"/>
                                    <flux:error name="facebook" />
                                </flux:field>
                                <flux:field class="mb-4">
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>{{ __('LinkedIn') }}</flux:label>
                                    <flux:input wire:model="linkedin" placeholder="{{__('Enter your LinkedIn URL')}}"/>
                                    <flux:error name="linkedin" />
                                </flux:field>
                                <flux:field class="mb-4">
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>{{ __('Instagram') }}</flux:label>
                                    <flux:input wire:model="instagram" placeholder="{{__('Enter your Instagram URL')}}"/>
                                    <flux:error name="instagram" />
                                </flux:field>
                                <flux:field>
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>{{ __('YouTube') }}</flux:label>
                                    <flux:input wire:model="youtube" placeholder="{{__('Enter your YouTube URL')}}"/>
                                    <flux:error name="youtube" />
                                </flux:field>
                                <flux:field>
                                    <flux:label><svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>{{ __('TikTok') }}</flux:label>
                                    <flux:input wire:model="tiktok" placeholder="{{__('Enter your TikTok URL')}}"/>
                                    <flux:error name="tiktok" />
                                </flux:field>
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

</div>

