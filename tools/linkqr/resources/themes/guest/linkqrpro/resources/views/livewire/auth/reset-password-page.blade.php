<div class="flex flex-col gap-6">
    <div class="space-y-2 text-center">
        <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Account access') }}</span>
        <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Reset password') }}</h1>
        <p class="text-sm leading-6 text-[#6d685f]">{{ __('Please enter your new password below') }}</p>
    </div>

    @if (session('status') && session('status') !== __('Language switched successfully.'))
        <p class="text-center text-sm font-medium text-emerald-600">{{ session('status') }}</p>
    @endif

    <form wire:submit.prevent="resetPassword" class="flex flex-col gap-6">
        <input type="hidden" wire:model="token">

        <x-ui.input
            wire:model.defer="email"
            name="email"
            :label="__('Email')"
            type="email"
            required
            autocomplete="email"
            :error="$errors->first('email')"
        />

        <x-ui.input
            wire:model.defer="password"
            name="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            :error="$errors->first('password')"
        />

        <x-ui.input
            wire:model.defer="password_confirmation"
            name="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            :error="$errors->first('password_confirmation')"
        />

        @if (function_exists('captcha_enabled') && captcha_enabled())
            @include(theme_view('livewire.auth.partials.captcha', 'guest'))
        @endif

        <div class="flex items-center justify-end">
            <x-ui.button type="submit" class="w-full !rounded-xl !bg-[#181714] !text-white" data-test="reset-password-button" wire:loading.attr="disabled" wire:target="resetPassword">
                <span wire:loading.remove wire:target="resetPassword">{{ __('Reset password') }}</span>
                <span wire:loading wire:target="resetPassword">{{ __('Resetting password...') }}</span>
            </x-ui.button>
        </div>
    </form>
</div>
