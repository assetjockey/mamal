<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-6/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="route('admin.support.tickets')" separator="slash" class="text-xs">{{__('Finance')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('View Ticket')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <div class="md:flex md:items-center md:justify-between md:mb-6">
                    <h1 class="font-bold md:text-2xl mb-2">{{ __('Ticket ID') }} - <span class="text-gray-500">{{ $ticket->ticket_id }}</span> </h1>
                    <flux:button icon="chevron-left" href="{{ route('admin.support.tickets') }}" variant="primary" class="hover:bg-blue-500 rounded-xl cursor-pointer">{{ __('Return') }}</flux:button>
                </div>
            </div>

            <div class="md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color) mb-9">

                    <div>
                        <h6 class="text-sm mb-3"><span class="font-bold">{{ __('Ticket Subject') }}</span>: <span>{{ $ticket->subject }}</span></h6>
                        <h6 class="text-sm mb-3"><span class="font-bold">{{ __('Ticket Status') }}</span>: <span>{{ ucfirst($ticket->status) }}</span></h6>
                        <h6 class="text-sm"><span class="font-bold">{{ __('Created By') }}</span>: <span>{{ ucfirst($ticket->user->name) }} ({{ $ticket->user->email }})</span></h6>
                    </div>
                
            </div>
                

            <div class="md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color) mb-9 max-h-[600px] overflow-y-auto" id="support-messages-container">
                @foreach ($messages as $message)
                    @if ($message->role != 'admin')
                        <!-- CUSTOMER MESSAGE -->
                        <div class="bg-white dark:bg-(--default-element-light-bg-color) rounded-lg p-4 mb-7 ml-auto md:max-w-[70%] border border-zinc-200">
                            <p class="font-bold text-xs mb-5 flex justify-between items-center">
                                <span><flux:icon.calendar-clock class="inline w-4 h-4 mr-2"/> {{ date_format($message->created_at, 'd M Y H:i A') }}</span>
                                <span>{{ __('Customer Message') }}</span>
                            </p>
                            <p class="text-sm mb-4">{!! nl2br(html_entity_decode($message->message))!!}</p>
                            @if ($message->attachment)
                                <p class="font-bold text-xs mb-1 mt-3">{{ __('Attachment') }}</p>
                                <a class="font-bold text-xs text-primary" href="{{ URL::asset($message->attachment) }}" target="_blank">{{ __('View Attached Image') }}</a>
                            @endif
                        </div>
                    @else
                        <!-- ADMIN RESPONSE -->
                        <div class="bg-(--default-element-light-bg-color) dark:bg-blue-900/20 rounded-lg p-4 mb-7 mr-auto md:max-w-[70%] border border-zinc-100">
                            <p class="font-bold text-xs mb-5 flex justify-between items-center">
                                <span><flux:icon.calendar-clock class="inline w-4 h-4 mr-2"/> {{ date_format($message->created_at, 'd M Y H:i A') }}</span>
                                <span>{{ __('Support Team Response') }}</span>
                            </p>
                            <p class="text-sm mb-4">{!! nl2br(html_entity_decode($message->message))!!}</p>
                            @if ($message->attachment)
                                <p class="font-bold text-xs mb-1 mt-3">{{ __('Attachment') }}</p>
                                <a class="font-bold text-xs text-primary" href="{{ URL::asset($message->attachment) }}" target="_blank">{{ __('View Attached Image') }}</a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div class="md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <!-- REPLY FORM -->
                <form wire:submit.prevent="submitResponse" enctype="multipart/form-data">
                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Ticket Status') }}</flux:label>
                        <flux:select wire:model="status">
                            <flux:select.option value="open">{{ __('Open') }}</flux:select.option>
                            <flux:select.option value="in_progress">{{ __('In Progress') }}</flux:select.option>
                            <flux:select.option value="resolved">{{ __('Resolved') }}</flux:select.option>
                            <flux:select.option value="closed">{{ __('Closed') }}</flux:select.option>
                        </flux:select>
                        <flux:error name="status" />
                    </flux:field>

                    <flux:field class="mb-6">
                        <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Response') }}</flux:label>
                        <flux:textarea wire:model="responseMessage" rows="6" placeholder="{{ __('Enter your reply message here...') }}" />
                        <flux:error name="responseMessage" />
                    </flux:field>

                    <flux:field class="mb-4">
                        <flux:label>{{ __('Attach File') }}</flux:label>
                        <flux:input type="file" wire:model="attachment" accept="image/jpeg,image/jpg,image/png" />
                        <flux:description class="text-xs">{{ __('JPG | JPEG | PNG | Max 5MB') }}</flux:description>
                        <flux:error name="attachment" />
                    </flux:field>

                    <div class="flex w-full justify-center mt-6 gap-3">                        
                        <flux:button type="submit" variant="primary" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{__('Reply')}}</flux:button>						
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

