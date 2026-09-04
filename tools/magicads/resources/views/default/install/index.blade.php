@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 1])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('Install') }}
            <span class="bg-clip-text text-transparent" style="background-image: linear-gradient(120deg, #4F46E5, #0F172A 60%, #D97706);">{{ config('app.name') }}</span>
        </h2>

        @include('install.partials.alerts')

        <div class="mt-8 flex flex-col items-center text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-linear-to-br from-indigo-50 to-amber-50 dark:from-indigo-500/10 dark:to-amber-500/10">
                <svg class="h-10 w-10 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                </svg>
            </div>
            <p class="mt-4 text-sm text-slate-500 dark:text-neutral-400">
                {{ config('app.name') }} {{ __('Installation Wizard') }}
            </p>
            <p class="mt-1 text-xs text-slate-400 dark:text-neutral-500">
                {{ __('Follow the steps below to set up your application') }}
            </p>
        </div>
    </div>
</div>

{{-- Action --}}
<div class="mt-6 flex w-full justify-center">
    <a href="{{ route('install.requirements') }}"
       class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
        {{ __('Start Installation') }}
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </a>
</div>
@endsection
