@extends('layouts.wrapper')

@section('body')
    <body class="d-flex flex-column">
        @include('shared.announcement')

        @section('header')
            @include('shared.header')
        @show

        <div class="d-flex flex-column flex-fill @auth content ms-lg-64 @endauth">
            @yield('content')

            @include('shared.modals.confirm')

            @section('footer')
                @include('shared.footer')
            @show
        </div>
    </body>
@endsection