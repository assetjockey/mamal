@component(theme_view('layouts.auth', 'guest'), ['title' => __('Email verification')])
    <div class="mt-4 flex flex-col gap-6">
        <div class="space-y-2 text-center">
            <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Email verification') }}</span>
            <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Check your inbox') }}</h1>
        </div>
        <p class="text-center text-sm leading-6 text-[#6d685f]">
            {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <p class="text-center text-sm font-medium text-emerald-600">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        @endif

        <div class="flex flex-col items-center justify-between space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui.button type="submit" class="w-full !rounded-xl !bg-[#181714] !text-white">
                    {{ __('Resend verification email') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button variant="ghost" type="submit" class="cursor-pointer text-sm !text-[#6d685f]" data-test="logout-button">
                    {{ __('Log out') }}
                </x-ui.button>
            </form>
        </div>
    </div>
@endcomponent
