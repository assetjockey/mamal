<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-5/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Backend Settings')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('SMTP Settings')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('SMTP Settings') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Manage your SMTP configuration for sending emails') }}</flux:subheading>
            </div>
            
            <form wire:submit.prevent="save"> 
                
                <div class="rounded-xl md:border border-(--default-border-color) md:pl-12 md:pr-12 md:pt-12 md:pb-6  dark:border-white/8 dark:bg-(--default-element-light-bg-color)">

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('SMTP Host') }}</flux:label>
                        <flux:input wire:model="host" placeholder="{{__('SMTP Host Name')}}"/>
                        <flux:error name="host" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('SMTP Port') }}</flux:label>
                        <flux:input wire:model="port" placeholder="{{__('SMTP Port')}}" type="number"/>
                        <flux:error name="port" />
                    </flux:field>
                    
                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('SMTP Username') }}</flux:label>
                        <flux:input wire:model="username" placeholder="{{__('SMTP Username')}}"/>
                        <flux:error name="username" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('SMTP Password') }}</flux:label>
                        <flux:input wire:model="password" placeholder="{{__('SMTP Password')}}" type="password"/>
                        <flux:error name="password" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Sender Email Address') }}</flux:label>
                        <flux:input wire:model="sender" placeholder="{{__('Sender Email Address')}}" type="email"/>
                        <flux:error name="sender" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Sender Full Name') }}</flux:label>
                        <flux:input wire:model="name" placeholder="{{__('Sender Full Name')}}"/>
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('SMTP Encryption') }}</flux:label>
                        <flux:select wire:model="encryption">
                            <flux:select.option value="tls">{{ __('TLS') }}</flux:select.option>
                            <flux:select.option value="ssl">{{ __('SSL') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="encryption" />
                    </flux:field>
                </div>

                <div class="mt-7 flex gap-4 w-full">
                    <flux:modal.trigger name="test-connection"><flux:button class="w-full py-6 rounded-xl cursor-pointer">{{__('Test Connection')}}</flux:button></flux:modal.trigger>
                    <flux:button wire:click="save" variant="primary" class="w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{__('Save')}}</flux:button>						
                </div>
            </form>
        </div>
    </div>

    <flux:modal name="test-connection" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{__('Test SMTP Connection')}}</flux:heading>
                <flux:text class="mt-2">{{__('Make sure you have changed your settings first.')}}</flux:text>
            </div>
            <flux:input label="{{__('Email Address')}}" wire:model="testEmail" placeholder="{{__('Enter valid email address')}}" type="email" />
            <flux:input label="{{__('Subject')}}" wire:model="testSubject" placeholder="{{__('Provide email subject')}}" type="text" />
            <flux:textarea label="{{__('Message')}}" wire:model="testMessage" placeholder="{{__('Provide test message')}}..."/>
            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" wire:click="checkConnection" class="w-full cursor-pointer rounded-xl hover:bg-blue-500" variant="primary">{{__('Send')}}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>

