@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 2])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('Server Requirements') }}
        </h2>
        <p class="mt-1 text-center text-sm text-slate-500 dark:text-neutral-400">
            {{ __('Verify your server meets all requirements') }}
        </p>

        @include('install.partials.alerts')

        <div class="mt-6 space-y-5">
            @foreach($results['extensions'] as $type => $extension)
                {{-- Section header --}}
                <div>
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-neutral-800">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-neutral-200">
                                {{ mb_strtoupper($type) }}
                            </span>
                            @if($type == 'php')
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                    {{ config('install.php_version') }}+
                                </span>
                            @elseif($type == 'functions')
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                                    {{ __('Optional') }}
                                </span>
                            @endif
                        </div>

                        @if($type == 'php')
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500 dark:text-neutral-400">{{ PHP_VERSION }}</span>
                                @if(version_compare(PHP_VERSION, config('install.php_version')) >= 0)
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                        <svg class="h-3 w-3 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                                        <svg class="h-3 w-3 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Extension list --}}
                    <div class="mt-1 divide-y divide-slate-100 dark:divide-neutral-800">
                        @foreach($extension as $name => $enabled)
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <span class="text-sm text-slate-600 dark:text-neutral-300">{{ $name }}</span>
                                @if($enabled)
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                        <svg class="h-3 w-3 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </span>
                                @elseif($type == 'functions')
                                    {{-- Soft requirement: disabled is allowed, surface as a non-blocking warning --}}
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20" title="{{ __('Recommended — enable on your hosting for full functionality') }}">
                                        <svg class="h-3 w-3 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                                        <svg class="h-3 w-3 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Action --}}
<div class="mt-6 flex w-full justify-center">
    <a href="{{ route('install.permissions') }}"
       class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
        {{ __('Next') }}
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </a>
</div>
@endsection
