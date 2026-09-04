<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-linear-to-br from-slate-100 via-slate-50 to-slate-200 antialiased dark:from-neutral-950 dark:via-neutral-900 dark:to-neutral-950">
        <div class="flex min-h-svh flex-col items-center justify-center p-4 sm:p-6 md:p-10">
            <div class="w-full max-w-xl flex flex-col items-center">
                {{-- Logo --}}
                <div class="mb-8">
                    @php
                        $installLogo = 'uploads/logo/logo_frontend_collapsed.png';
                        $installLogoExists = file_exists(public_path($installLogo));
                    @endphp
                    @if($installLogoExists)
                        <img src="{{ asset($installLogo) }}" alt="{{ config('app.name') }}" class="h-12 w-auto object-contain" />
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl shadow-lg shadow-indigo-600/30"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                            <x-app-logo-icon class="size-7 fill-current text-white" />
                        </span>
                    @endif
                </div>

                @yield('content')
            </div>
        </div>
        @fluxScripts
    </body>
</html>
