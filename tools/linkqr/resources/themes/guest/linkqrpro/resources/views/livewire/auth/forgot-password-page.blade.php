<div class="flex flex-col gap-6">
    <div class="space-y-2 text-center">
        <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Account access') }}</span>
        <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Forgot password') }}</h1>
        <p class="text-sm leading-6 text-[#6d685f]">{{ __('Enter your email to receive a password reset link') }}</p>
    </div>

    @if (session('status') && session('status') !== __('Language switched successfully.'))
        <p class="text-center text-sm font-medium text-emerald-600">{{ session('status') }}</p>
    @endif

    <form wire:submit.prevent="sendResetLink" class="flex flex-col gap-6">
        <x-ui.input
            wire:model.defer="email"
            name="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
            :error="$errors->first('email')"
        />

        @if (function_exists('captcha_enabled') && captcha_enabled())
            @include(theme_view('livewire.auth.partials.captcha', 'guest'))
        @endif

        <x-ui.button type="submit" class="w-full !rounded-xl !bg-[#181714] !text-white" data-test="email-password-reset-link-button" wire:loading.attr="disabled" wire:target="sendResetLink">
            <span wire:loading.remove wire:target="sendResetLink">{{ __('Email password reset link') }}</span>
            <span wire:loading wire:target="sendResetLink">{{ __('Sending reset link...') }}</span>
        </x-ui.button>
    </form>

    <div class="space-x-1 text-center text-sm text-[#6d685f] rtl:space-x-reverse">
        <span>{{ __('Or, return to') }}</span>
        <a href="{{ route('login') }}" class="font-bold text-[#5f8dff] transition hover:text-[#181714]" wire:navigate>{{ __('log in') }}</a>
    </div>
</div>
