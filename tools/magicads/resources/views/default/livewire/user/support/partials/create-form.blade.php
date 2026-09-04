<div class="rounded-2xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) p-6 md:p-8 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('Open a New Ticket') }}</h2>
            <p class="text-xs text-zinc-500 mt-1">{{ __('Tell us what is going on and we will help you out.') }}</p>
        </div>
        <button type="button" wire:click="cancelTicket" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 transition dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-300 dark:hover:text-white">
            <flux:icon.x-mark class="size-4" /> {{ __('Cancel') }}
        </button>
    </div>

    <form wire:submit.prevent="createTicket" enctype="multipart/form-data" class="space-y-6">
        <flux:field>
            <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Subject') }}</flux:label>
            <flux:input wire:model="subject" placeholder="{{ __('Brief summary of your issue') }}" />
            <flux:error name="subject" />
        </flux:field>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:field>
                <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Category') }}</flux:label>
                <flux:select wire:model="category">
                    <flux:select.option value="technical">{{ __('Technical') }}</flux:select.option>
                    <flux:select.option value="billing">{{ __('Billing') }}</flux:select.option>
                    <flux:select.option value="account">{{ __('Account') }}</flux:select.option>
                    <flux:select.option value="general">{{ __('General') }}</flux:select.option>
                    <flux:select.option value="request">{{ __('Feature Request') }}</flux:select.option>
                </flux:select>
                <flux:error name="category" />
            </flux:field>

            <flux:field>
                <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Priority') }}</flux:label>
                <flux:select wire:model="priority">
                    <flux:select.option value="low">{{ __('Low') }}</flux:select.option>
                    <flux:select.option value="medium">{{ __('Medium') }}</flux:select.option>
                    <flux:select.option value="high">{{ __('High') }}</flux:select.option>
                    <flux:select.option value="urgent">{{ __('Urgent') }}</flux:select.option>
                </flux:select>
                <flux:error name="priority" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label class="after:content-['*'] after:ml-1 after:text-red-500">{{ __('Message') }}</flux:label>
            <flux:textarea wire:model="message" rows="6" placeholder="{{ __('Describe your issue in detail...') }}" />
            <flux:error name="message" />
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

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" wire:click="cancelTicket" class="px-5 py-2.5 rounded-xl text-sm font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 transition dark:bg-(--default-element-light-bg-color) dark:border-white/8 dark:text-zinc-300">
                {{ __('Cancel') }}
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="createTicket" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-semibold shadow-sm shadow-indigo-500/25 hover:shadow-xl transition disabled:opacity-60 disabled:cursor-not-allowed" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                <flux:icon.paper-airplane class="size-4" wire:loading.remove wire:target="createTicket" />
                <flux:icon.arrow-path class="size-4 animate-spin" wire:loading wire:target="createTicket" />
                {{ __('Submit Ticket') }}
            </button>
        </div>
    </form>
</div>
