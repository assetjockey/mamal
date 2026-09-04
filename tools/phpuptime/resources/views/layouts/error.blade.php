@extends('layouts.wrapper')

@section('body')
    <body class="d-flex flex-column">
        @if(parse_url(config('app.url'), PHP_URL_HOST) == request()->getHost())
            @include('shared.header')
        @endif

        <div class="d-flex flex-column flex-fill @auth content @endauth">
            @yield('content')

            @include('shared.footer', ['footer' => ['menu' => ['removed' => true], 'copyright' => (parse_url(config('app.url'), PHP_URL_HOST) !== request()->getHost() ? ['removed' => true] : [])]])
        </div>
    </body>
@endsection
