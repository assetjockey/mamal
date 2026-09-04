<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col gap-8">
            <a href="{{ route('home') }}" wire:navigate
                class="group inline-flex items-center gap-2 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ __('Back to home') }}
            </a>

            @php
                $authLogoSettings = \App\Models\GeneralSetting::first();
                $authCollapsedLogo = $authLogoSettings?->logo_frontend_collapsed
                    ? \Illuminate\Support\Facades\URL::asset($authLogoSettings->logo_frontend_collapsed)
                    : null;
            @endphp

            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3">
                @if($authCollapsedLogo)
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl">
                        <img src="{{ $authCollapsedLogo }}" alt="{{ config('app.name', 'Laravel') }}" class="h-11 w-11 object-contain" />
                    </span>
                @else
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                        <x-app-logo-icon class="h-6 fill-current" />
                    </span>
                @endif
                <span class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>

            <div class="mt-2">
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ __('Forgot password') }}</h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Enter your email to receive a password reset link') }}</p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email Address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Email password reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
