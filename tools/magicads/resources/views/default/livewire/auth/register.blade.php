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
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ __('Create account') }}</h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Enter your details below to create your account') }}</p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            {{-- Referral attribution: carry the referrer's code (from ?ref=) through
                 the POST so CreateNewUser can record who referred this signup. --}}
            <input type="hidden" name="ref" value="{{ old('ref', request()->query('ref')) }}" />
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        @if (! empty($socialProviders ?? []))
            <x-social-login :providers="$socialProviders" :label="__('or sign up with')" />
        @endif

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
