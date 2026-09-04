<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('frontend.includes.head')
    @include('frontend.includes.styles')
    {!! head_code() !!}
</head>

<body>
    <header class="header v2">
        @include('frontend.includes.navbar')
        <div class="container d-flex align-items-center flex-column">
            <h2 class="page-title text-white text-center mb-0 mt-3">@yield('title')</h2>
        </div>
    </header>
    @yield('content')
    @include('frontend.includes.footer')
    @include('frontend.configurations.config')
    @include('frontend.configurations.widgets')
    @include('frontend.includes.scripts')
</body>

</html>
