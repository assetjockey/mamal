<x-layouts::auth.split :title="$title ?? config('app.name')">
    @hasSection('content')
        @yield('content')
    @else
        {{ $slot }}
    @endif
</x-layouts::auth.plite>
