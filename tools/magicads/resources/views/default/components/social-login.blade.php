@props([
    'providers' => [],
    'label' => null,
])

@php
    // Provider slug => brand icon SVG path data (single-path, currentColor-ready
    // where noted). Kept inline so the component is self-contained.
    $icons = [
        'google' => '<svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>',
        'facebook' => '<svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'twitter' => '<svg class="h-[17px] w-[17px]" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'linkedin' => '<svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#0A66C2" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
    ];
@endphp

@if (! empty($providers))
    <div class="flex flex-col gap-4">
        <div class="flex items-center gap-3">
            <span class="h-px flex-1 bg-zinc-200 dark:bg-white/10"></span>
            <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                {{ $label ?? __('or continue with') }}
            </span>
            <span class="h-px flex-1 bg-zinc-200 dark:bg-white/10"></span>
        </div>

        <div class="grid {{ count($providers) > 1 ? 'grid-cols-1 sm:grid-cols-2' : 'grid-cols-1' }} gap-3">
            @foreach ($providers as $slug => $name)
                <a
                    href="{{ route('social.redirect', ['provider' => $slug]) }}"
                    class="group inline-flex items-center justify-center gap-2.5 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-none transition-all duration-200 hover:-translate-y-0.5 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/60 dark:border-white/10 dark:bg-white/5 dark:text-zinc-100 dark:hover:border-white/20 dark:hover:bg-white/10 dark:hover:shadow-lg dark:hover:shadow-black/40"
                    aria-label="{{ __('Continue with :provider', ['provider' => $name]) }}"
                >
                    <span class="flex h-5 w-5 shrink-0 items-center justify-center text-zinc-900 transition-transform duration-200 group-hover:scale-110 dark:text-white">
                        {!! $icons[$slug] ?? '' !!}
                    </span>
                    <span>{{ $name }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
