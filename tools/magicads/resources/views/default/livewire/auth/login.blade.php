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
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ __('Sign in') }}</h1>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Enter your email and password below to log in') }}</p>
            </div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @error('social')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        @if (! empty($socialProviders ?? []))
            <x-social-login :providers="$socialProviders" />
        @endif

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
