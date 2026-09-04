<x-settings.shell
    icon="lock-closed"
    :eyebrow="__('Security')"
    :heading="__('Password')"
    :subheading="__('Ensure your account is using a long, random password to stay secure')"
>
    <flux:heading class="sr-only">{{ __('Password Settings') }}</flux:heading>

    <form method="POST" wire:submit="updatePassword" class="space-y-6">
        <flux:input
            wire:model="current_password"
            :label="__('Current password')"
            type="password"
            required
            autocomplete="current-password"
            icon="lock-closed"
        />
        <flux:input
            wire:model="password"
            :label="__('New password')"
            type="password"
            required
            autocomplete="new-password"
            icon="key"
        />
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm Password')"
            type="password"
            required
            autocomplete="new-password"
            icon="key"
        />

        <div class="flex items-center justify-end gap-4 border-t border-zinc-200 pt-5 dark:border-white/8">
            <x-action-message class="text-[12px] font-semibold text-emerald-600 dark:text-emerald-400" on="password-updated">
                <span class="inline-flex items-center gap-1"><flux:icon.check-circle class="size-4" /> {{ __('Saved') }}</span>
            </x-action-message>

            <flux:button variant="primary" type="submit">
                <span wire:loading.remove wire:target="updatePassword">{{ __('Save changes') }}</span>
                <span wire:loading wire:target="updatePassword" class="inline-flex items-center gap-2">
                    <flux:icon.arrow-path class="size-4 animate-spin" /> {{ __('Saving…') }}
                </span>
            </flux:button>
        </div>
    </form>
</x-settings.shell>
