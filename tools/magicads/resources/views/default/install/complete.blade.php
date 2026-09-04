@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 6])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('Installation Complete') }}
        </h2>

        @include('install.partials.alerts')

        <div class="mt-8 flex flex-col items-center text-center">
            @if ($activated)
                {{-- Success state --}}
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-linear-to-br from-emerald-50 to-green-50 dark:from-emerald-500/10 dark:to-green-500/10">
                    <svg class="h-10 w-10 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>
                <p class="mt-4 text-lg font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ __('Application Successfully Activated') }}!
                </p>
            @else
                {{-- Error state --}}
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-linear-to-br from-red-50 to-orange-50 dark:from-red-500/10 dark:to-orange-500/10">
                    <svg class="h-10 w-10 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <p class="mt-4 text-lg font-semibold text-red-600 dark:text-red-400">
                    {{ __('Application was NOT Activated') }}!
                </p>
            @endif

            @if ($createDefaultAdmin)
                <div class="mt-6 w-full rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-neutral-700 dark:bg-neutral-800">
                    <p class="text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Default admin credentials') }}
                    </p>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between rounded-lg bg-white px-4 py-2.5 dark:bg-neutral-900">
                            <span class="text-xs uppercase tracking-wider text-slate-400 dark:text-neutral-500">{{ __('Email') }}</span>
                            <span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">admin@example.com</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-white px-4 py-2.5 dark:bg-neutral-900">
                            <span class="text-xs uppercase tracking-wider text-slate-400 dark:text-neutral-500">{{ __('Password') }}</span>
                            <span class="font-mono text-sm font-semibold text-slate-900 dark:text-white">admin12345</span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-amber-600 dark:text-amber-400">
                        <svg class="mr-1 inline h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        {{ __('Please change these credentials after your first login') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Action --}}
<div class="mt-6 flex w-full justify-center">
    <a href="{{ url('/') }}"
       class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
        {{ __('Finish Installation') }}
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </a>
</div>
@endsection
