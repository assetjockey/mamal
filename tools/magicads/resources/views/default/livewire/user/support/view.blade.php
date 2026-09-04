<div>
    <div class="flex justify-center">
        <div class="w-full lg:w-8/12 xl:w-7/12">

            {{-- Top toolbar --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Help') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="{{ route('user.support') }}" separator="slash" class="text-xs">{{ __('Support') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Ticket') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <a href="{{ route('user.support') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 transition dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-300 dark:hover:text-white">
                    <flux:icon.chevron-left class="size-4" /> {{ __('Back to tickets') }}
                </a>
            </div>

            {{-- Ticket header card --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h1 class="text-lg md:text-xl font-extrabold text-zinc-900 dark:text-white">{{ $ticket->subject }}</h1>
                            <x-support.priority-badge :priority="$ticket->priority" />
                        </div>
                        <div class="flex items-center gap-2 flex-wrap text-[11px] text-zinc-400">
                            <span class="font-mono">{{ $ticket->ticket_id }}</span>
                            <span>&middot;</span>
                            <span class="capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
                            <span>&middot;</span>
                            <span>{{ __('Opened') }} {{ $ticket->created_at?->format('M j, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <x-support.status-badge :status="$ticket->status" />
                    </div>
                </div>
            </div>

            {{-- Conversation thread --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-5 md:p-7 mb-6 max-h-[600px] overflow-y-auto" id="support-messages-container">
                @forelse ($messages as $message)
                    @if ($message->role === 'admin')
                        {{-- SUPPORT TEAM RESPONSE (left) --}}
                        <div class="bg-(--default-element-light-bg-color) dark:bg-indigo-950/20 rounded-xl p-4 mb-6 mr-auto md:max-w-[78%] border border-zinc-100">
                            <p class="font-bold text-xs mb-3 flex justify-between items-center gap-3 text-zinc-700 dark:text-zinc-300">
                                <span class="inline-flex items-center gap-1.5"><flux:icon.lifebuoy class="size-4 text-indigo-500" /> {{ __('Support Team') }}</span>
                                <span class="text-zinc-400 font-medium">{{ $message->created_at?->format('d M Y, h:i A') }}</span>
                            </p>
                            <p class="text-sm text-zinc-700 dark:text-zinc-200">{!! nl2br(html_entity_decode($message->message)) !!}</p>
                            @if ($message->attachment)
                                <a class="inline-flex items-center gap-1.5 font-semibold text-xs text-indigo-600 dark:text-indigo-400 mt-3 hover:underline" href="{{ URL::asset($message->attachment) }}" target="_blank">
                                    <flux:icon.paper-clip class="size-3.5" /> {{ __('View Attached Image') }}
                                </a>
                            @endif
                        </div>
                    @else
                        {{-- USER MESSAGE (right) --}}
                        <div class="rounded-xl p-4 mb-6 ml-auto md:max-w-[78%] border border-zinc-200">
                            <p class="font-bold text-xs mb-3 flex justify-between items-center gap-3 ">
                                <span class="inline-flex items-center gap-1.5"><flux:icon.user-circle class="size-4" /> {{ __('You') }}</span>
                                <span class=" font-medium">{{ $message->created_at?->format('d M Y, h:i A') }}</span>
                            </p>
                            <p class="text-sm ">{!! nl2br(html_entity_decode($message->message)) !!}</p>
                            @if ($message->attachment)
                                <a class="inline-flex items-center gap-1.5 font-semibold text-xs text-primary mt-3 hover:underline" href="{{ URL::asset($message->attachment) }}" target="_blank">
                                    <flux:icon.paper-clip class="size-3.5" /> {{ __('View Attached Image') }}
                                </a>
                            @endif
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-zinc-500 text-center py-6">{{ __('No messages yet.') }}</p>
                @endforelse
            </div>

            {{-- Reply form --}}
            @if ($ticket->status === 'closed')
                <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-zinc-50 dark:bg-white/[.04] p-6 text-center">
                    <flux:icon.lock-closed class="size-7 mx-auto text-zinc-400 mb-2" />
                    <p class="text-sm text-zinc-500">{{ __('This ticket is closed. Open a new ticket if you still need help.') }}</p>
                    <a href="{{ route('user.support.create') }}" wire:navigate class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-xl transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                        <flux:icon.plus class="size-4" /> {{ __('New Ticket') }}
                    </a>
                </div>
            @else
                <div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 md:p-7">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white mb-4">{{ __('Add a Reply') }}</h3>
                    <form wire:submit.prevent="submitReply" enctype="multipart/form-data" class="space-y-5">
                        <flux:field>
                            <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Message') }}</flux:label>
                            <flux:textarea wire:model="replyMessage" rows="5" placeholder="{{ __('Type your reply here...') }}" />
                            <flux:error name="replyMessage" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Attach File') }}</flux:label>
                            <flux:input type="file" wire:model="attachment" accept="image/jpeg,image/jpg,image/png" />
                            <flux:description class="text-xs">{{ __('JPG | JPEG | PNG | Max 5MB') }}</flux:description>
                            <flux:error name="attachment" />

                            <div wire:loading wire:target="attachment" class="text-xs text-zinc-500 mt-2">{{ __('Uploading...') }}</div>
                            @if ($attachment)
                                <img src="{{ $attachment->temporaryUrl() }}" alt="{{ __('Attachment preview') }}" class="mt-3 max-h-40 rounded-lg border border-zinc-200 dark:border-white/8" />
                            @endif
                        </flux:field>

                        <div class="flex w-full justify-center mt-6 gap-3">
                            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submitReply" class="md:w-1/2 w-full hover:bg-blue-500 py-6 rounded-xl cursor-pointer">{{ __('Send Reply') }}</flux:button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
