@extends('layouts.install')

@section('content')
{{-- Step Indicator --}}
@include('install.partials.stepper', ['currentStep' => 4])

{{-- Card --}}
<div class="w-full rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-neutral-800 dark:bg-neutral-900 dark:shadow-none">
    <div class="px-6 py-8 sm:px-10 sm:py-10">

        <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ __('Database Configuration') }}
        </h2>
        <p class="mt-1 text-center text-sm text-slate-500 dark:text-neutral-400">
            {{ __('Enter your database connection details') }}
        </p>

        @include('install.partials.alerts')

        <form action="{{ route('install.database.store') }}" method="POST" class="mt-6">
            @csrf

            <div class="space-y-5">
                {{-- Hostname & Port --}}
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="hostname" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                            {{ __('Hostname') }}
                        </label>
                        <input type="text"
                               id="hostname"
                               name="hostname"
                               value="{{ old('hostname') }}"
                               placeholder="localhost"
                               autocomplete="off"
                               required
                               @class([
                                   'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('hostname'),
                                   'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('hostname'),
                               ])>
                        @error('hostname')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="port" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                            {{ __('Port') }}
                        </label>
                        <input type="text"
                               id="port"
                               name="port"
                               value="{{ old('port') }}"
                               placeholder="3306"
                               autocomplete="off"
                               required
                               @class([
                                   'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                                   'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('port'),
                                   'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('port'),
                               ])>
                        @error('port')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Database Name --}}
                <div>
                    <label for="database" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Database Name') }}
                    </label>
                    <input type="text"
                           id="database"
                           name="database"
                           value="{{ old('database') }}"
                           placeholder="{{ __('Enter database name') }}"
                           autocomplete="off"
                           required
                           @class([
                               'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                               'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('database'),
                               'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('database'),
                           ])>
                    @error('database')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DB User --}}
                <div>
                    <label for="user" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Database User') }}
                    </label>
                    <input type="text"
                           id="user"
                           name="user"
                           value="{{ old('user') }}"
                           placeholder="{{ __('Enter database user') }}"
                           autocomplete="off"
                           required
                           @class([
                               'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                               'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('user'),
                               'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('user'),
                           ])>
                    @error('user')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- DB Password --}}
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-neutral-300">
                        {{ __('Database Password') }}
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           value="{{ old('password') }}"
                           placeholder="{{ __('Enter database password') }}"
                           autocomplete="off"
                           @class([
                               'w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition-colors focus:outline-none focus:ring-2 dark:bg-neutral-800 dark:text-white dark:placeholder-neutral-500',
                               'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('password'),
                               'border-slate-300 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-neutral-700 dark:focus:border-indigo-500' => !$errors->has('password'),
                           ])>
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Action --}}
            <div class="mt-8 flex justify-center">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-indigo-700/35 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900" style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                    {{ __('Next') }}
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
