@component(theme_view('layouts.auth', 'guest'), ['title' => __('Confirm password')])
    <div class="flex flex-col gap-6">
        <div class="space-y-2 text-center">
            <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Secure area') }}</span>
            <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Confirm password') }}</h1>
            <p class="text-sm leading-6 text-[#6d685f]">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>
        </div>

        @if (session('status'))
            <p class="text-center text-sm font-medium text-emerald-600">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-ui.input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                :error="$errors->first('password')"
            />

            <x-ui.button type="submit" class="w-full !rounded-xl !bg-[#181714] !text-white" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </x-ui.button>
        </form>
    </div>
@endcomponent
