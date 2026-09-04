@php
    $socialProviders = collect([
        [
            'key' => 'google',
            'label' => __('Continue with Google'),
            'icon' => '<svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="#EA4335" d="M12 10.2v3.9h5.5c-.2 1.2-.9 2.3-1.9 3.1l3 2.3c1.8-1.6 2.9-4 2.9-6.8 0-.7-.1-1.4-.2-2H12z" /><path fill="#34A853" d="M12 22c2.6 0 4.8-.9 6.4-2.5l-3-2.3c-.8.6-1.9 1-3.4 1-2.6 0-4.8-1.7-5.6-4.1l-3.1 2.4C4.9 19.8 8.2 22 12 22z" /><path fill="#4A90E2" d="M6.4 14.1c-.2-.6-.3-1.3-.3-2.1s.1-1.4.3-2.1L3.3 7.5C2.5 9 2 10.4 2 12s.5 3 1.3 4.5l3.1-2.4z" /><path fill="#FBBC05" d="M12 5.8c1.4 0 2.6.5 3.6 1.4l2.7-2.7C16.8 3.1 14.6 2 12 2 8.2 2 4.9 4.2 3.3 7.5l3.1 2.4C7.2 7.5 9.4 5.8 12 5.8z" /></svg>',
            'enabled' => (string) get_option('auth_google_login_status', '0') === '1'
                && filled((string) get_option('auth_google_login_client_id', ''))
                && filled((string) get_option('auth_google_login_client_secret', '')),
        ],
        [
            'key' => 'facebook',
            'label' => __('Continue with Facebook'),
            'icon' => '<i class="fa-brands fa-square-facebook text-lg" style="color:#1877F2;"></i>',
            'enabled' => (string) get_option('auth_facebook_login_status', '0') === '1'
                && filled((string) get_option('auth_facebook_login_app_id', ''))
                && filled((string) get_option('auth_facebook_login_app_secret', '')),
        ],
        [
            'key' => 'x',
            'label' => __('Continue with X'),
            'icon' => '<i class="fa-brands fa-x-twitter text-lg" style="color:#111827;"></i>',
            'enabled' => (string) get_option('auth_x_login_status', '0') === '1'
                && filled((string) get_option('auth_x_login_client_id', ''))
                && filled((string) get_option('auth_x_login_client_secret', '')),
        ],
    ])->where('enabled')->values();
    $requestedUsername = trim((string) request()->query('username', ''));
    $registerUrl = $requestedUsername !== ''
        ? url('/register?username='.rawurlencode($requestedUsername))
        : url('/register');
@endphp

<div class="flex flex-col gap-6">
    <div class="space-y-2 text-center">
        <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Welcome back') }}</span>
        <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Log in to your workspace') }}</h1>
        <p class="text-sm leading-6 text-[#6d685f]">{{ __('Continue managing your Bio pages, QR campaigns, and click reports.') }}</p>
    </div>

    @if (session('status') && session('status') !== __('Language switched successfully.'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form x-data class="flex flex-col gap-6" x-on:submit.prevent="$wire.login()">
        <div class="space-y-2.5">
            <label for="login" class="block text-sm font-bold text-[#57534b]">{{ __('Username or email') }}</label>
            <input
                id="login"
                wire:model.defer="identifier"
                name="login"
                type="text"
                required
                autofocus
                autocomplete="username"
                placeholder="{{ __('Enter username or email') }}"
                class="flex h-12 w-full rounded-xl border border-[#ded7ca] bg-white px-4 text-sm font-semibold text-[#181714] outline-none transition placeholder:font-normal placeholder:text-[#aaa39a] focus:border-[#5f8dff] focus:ring-4 focus:ring-[#5f8dff]/10"
            >
            @error('identifier')
                <div class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</div>
            @enderror
        </div>

        <div class="relative">
            <div class="space-y-2.5">
            <label for="password" class="block text-sm font-bold text-[#57534b]">{{ __('Password') }}</label>
                <input
                    id="password"
                    wire:model.defer="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('Password') }}"
                    class="flex h-12 w-full rounded-xl border border-[#ded7ca] bg-white px-4 text-sm font-semibold text-[#181714] outline-none transition placeholder:font-normal placeholder:text-[#aaa39a] focus:border-[#5f8dff] focus:ring-4 focus:ring-[#5f8dff]/10"
                >
                @error('password')
                    <div class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</div>
                @enderror
            </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="absolute top-0 end-0 text-sm font-bold text-[#5f8dff] transition hover:text-[#181714]" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <label for="remember-1" class="group inline-flex cursor-pointer items-start gap-3">
            <input id="remember-1" type="checkbox" class="peer sr-only" wire:model.defer="remember" name="remember" value="1">

            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-[#ded7ca] bg-white text-white transition peer-checked:border-[#181714] peer-checked:bg-[#181714] peer-checked:[&_svg]:opacity-100">
                <svg viewBox="0 0 16 16" aria-hidden="true" class="h-3.5 w-3.5 opacity-0 transition-opacity duration-150" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2">
                    <path d="M3.5 8.5 6.5 11.5 12.5 4.5"></path>
                </svg>
            </span>

            <span class="min-w-0 pt-0.5">
                <span class="block text-sm font-semibold text-[#57534b]">{{ __('Remember me') }}</span>
            </span>
        </label>

        @if (function_exists('captcha_enabled') && captcha_enabled())
            @include(theme_view('livewire.auth.partials.captcha', 'guest'))
        @endif

        <div class="flex items-center justify-end">
            <button
                type="submit"
                class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#181714] px-5 text-sm font-extrabold text-white transition hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-[#181714]/10 disabled:pointer-events-none disabled:opacity-50"
                data-test="login-button"
                wire:loading.attr="disabled"
                wire:target="login"
            >
                <span wire:loading.remove wire:target="login">{{ __('Log in') }}</span>
                <span wire:loading wire:target="login">{{ __('Logging in...') }}</span>
            </button>
        </div>
    </form>

    @if ($socialProviders->isNotEmpty())
        <div class="space-y-4">
            <div class="flex items-center gap-3 text-xs uppercase tracking-[0.22em] text-[#8a867d]">
                <span class="h-px flex-1 bg-[#ded7ca]"></span>
                <span>{{ __('Or continue with') }}</span>
                <span class="h-px flex-1 bg-[#ded7ca]"></span>
            </div>

            <div class="grid w-full gap-3 grid-cols-1">
                @foreach ($socialProviders as $provider)
                    <a
                        href="{{ url('auth/login/'.$provider['key']) }}"
                        class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-[#ded7ca] bg-white px-4 py-3 text-sm font-bold text-[#181714] transition hover:bg-[#f4f1ea]"
                    >
                        {!! $provider['icon'] !!}
                        <span>{{ $provider['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if (auth_signup_enabled() && Route::has('register'))
        <div class="space-x-1 text-center text-sm text-[#6d685f] rtl:space-x-reverse">
            <span>{{ __("Don't have an account?") }}</span>
            <a href="{{ $registerUrl }}" class="font-bold text-[#5f8dff] transition hover:text-[#181714]" wire:navigate>{{ __('Sign up') }}</a>
        </div>
    @endif
</div>
