@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 3])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('Permissions') }}
        </h2>
        <p class="mt-1 text-center text-sm text-slate-500 dark:text-neutral-400">
            {{ __('Ensure the following directories are writable') }}
        </p>

        @include('install.partials.alerts')

        <div class="mt-6 space-y-5">
            @foreach($results['permissions'] as $type => $files)
                <div>
                    {{-- Section header --}}
                    <div class="flex items-center rounded-xl bg-slate-50 px-4 py-3 dark:bg-neutral-800">
                        <span class="text-sm font-semibold text-slate-700 dark:text-neutral-200">{{ __($type) }}</span>
                    </div>

                    {{-- File list --}}
                    <div class="mt-1 divide-y divide-slate-100 dark:divide-neutral-800">
                        @foreach($files as $file => $writable)
                            <div class="flex items-center justify-between px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    <span class="text-sm text-slate-600 dark:text-neutral-300">{{ $file }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-mono text-slate-500 dark:bg-neutral-800 dark:text-neutral-400">775</span>
                                    @if($writable)
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
    <a href="{{ route('install.database') }}"
       class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
        {{ __('Next') }}
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
        </svg>
    </a>
</div>
@endsection
