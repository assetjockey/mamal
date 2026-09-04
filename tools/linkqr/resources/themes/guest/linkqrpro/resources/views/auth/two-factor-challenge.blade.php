@component(theme_view('layouts.auth', 'guest'), ['title' => __('Two-factor authentication')])
    <div class="flex flex-col gap-6">
        <div
            class="relative w-full h-auto"
            x-cloak
            x-data="{
                showRecoveryInput: @js($errors->has('recovery_code')),
                code: '',
                recovery_code: '',
                toggleInput() {
                    this.showRecoveryInput = !this.showRecoveryInput;
                    this.code = '';
                    this.recovery_code = '';
                    $dispatch('clear-2fa-auth-code');
                    $nextTick(() => {
                        this.showRecoveryInput
                            ? this.$refs.recovery_code?.focus()
                            : $dispatch('focus-2fa-auth-code');
                    });
                },
            }"
        >
            <div x-show="!showRecoveryInput">
                <div class="space-y-2 text-center">
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Two-factor') }}</span>
                    <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Authentication code') }}</h1>
                    <p class="text-sm leading-6 text-[#6d685f]">{{ __('Enter the authentication code provided by your authenticator application.') }}</p>
                </div>
            </div>

            <div x-show="showRecoveryInput">
                <div class="space-y-2 text-center">
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Two-factor') }}</span>
                    <h1 class="pt-3 font-serif text-4xl leading-tight tracking-[-0.03em] text-[#181714]">{{ __('Recovery code') }}</h1>
                    <p class="text-sm leading-6 text-[#6d685f]">{{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="space-y-5 text-center">
                    <div x-show="!showRecoveryInput">
                        <div class="my-5">
                            <x-ui.input
                                type="text"
                                name="code"
                                x-model="code"
                                maxlength="6"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                :label="__('Authentication code')"
                                :error="$errors->first('code')"
                            />
                        </div>
                    </div>

                    <div x-show="showRecoveryInput">
                        <div class="my-5">
                            <x-ui.input
                                type="text"
                                name="recovery_code"
                                x-ref="recovery_code"
                                x-bind:required="showRecoveryInput"
                                autocomplete="one-time-code"
                                x-model="recovery_code"
                                :label="__('Recovery code')"
                                :error="$errors->first('recovery_code')"
                            />
                        </div>
                    </div>

                    <x-ui.button type="submit" class="w-full !rounded-xl !bg-[#181714] !text-white">
                        {{ __('Continue') }}
                    </x-ui.button>
                </div>

                <div class="mt-5 space-x-0.5 text-center text-sm leading-5 text-[#6d685f]">
                    <span>{{ __('or you can') }}</span>
                    <div class="inline cursor-pointer font-bold text-[#5f8dff] underline transition hover:text-[#181714]">
                        <span x-show="!showRecoveryInput" @click="toggleInput()">{{ __('login using a recovery code') }}</span>
                        <span x-show="showRecoveryInput" @click="toggleInput()">{{ __('login using an authentication code') }}</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endcomponent
