@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 5])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('License Activation') }}
        </h2>
        <p class="mt-1 text-center text-sm text-slate-500 dark:text-neutral-400">
            {{ __('Enter your purchase code to activate') }}
        </p>

        @include('install.partials.alerts')

        <form action="{{ route('install.activation.activate') }}" method="POST" class="mt-6">
            @csrf

            <div class="space-y-5">
                {{-- License Code --}}
                <div>
                    <label for="license" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Activation Code') }}
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                        </div>
                        <input type="text"
                               id="license"
                               name="license"
                               value="{{ old('license') }}"
                               placeholder="{{ __('Enter your license code') }}"
                               autocomplete="off"
                               required
                               @class([
                                   'w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('license'),
                                   'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('license'),
                               ])>
                    </div>
                    @error('license')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Envato Username --}}
                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Envato Username') }}
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <input type="text"
                               id="username"
                               name="username"
                               value="{{ old('username') }}"
                               placeholder="{{ __('Enter your Envato username') }}"
                               autocomplete="off"
                               required
                               @class([
                                   'w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('username'),
                                   'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('username'),
                               ])>
                    </div>
                    @error('username')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Action --}}
            <div class="mt-8 flex justify-center">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                    {{ __('Activate') }}
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
