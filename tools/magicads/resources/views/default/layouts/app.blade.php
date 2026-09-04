<x-layouts::app.sidebar :title="$title ?? config('app.name')">
    <flux:main class="bg-(--default-main-bg-color) rounded-xl border border-transparent dark:border-white/5 m-4 ml-0 shadow-sm dark:shadow-none lg:p-12 flex flex-col min-h-[calc(100dvh-2rem)]">
        <div class="flex-1">
            {{ $slot }}
        </div>

        @php
            $appName    = config('app.name', 'App');
            $appVersion = (string) config('app.version', '');
            $copyYear   = now()->format('Y');
        @endphp
        <footer class="mt-8 pt-4 flex justify-end items-center text-[11px] text-zinc-400 dark:text-zinc-500 select-none">
            <span class="font-medium tracking-wide">
                {{ $appName }}@if ($appVersion !== '') <span class="text-zinc-300 dark:text-zinc-600">{{ $appVersion }}</span>@endif
                <span class="mx-2 text-zinc-300 dark:text-zinc-600">|</span>
                {{ __('Copyright © :year', ['year' => $copyYear]) }}
            </span>
        </footer>
    </flux:main>
</x-layouts::app.sidebar>
