@extends('layouts.wrapper')

@section('body')
    <body class="d-flex flex-column">
        @include('shared.announcement')

        @include('shared.header')

        <div class="d-flex flex-column flex-fill @auth content ms-lg-64 @endauth">
            @yield('content')

            @include('shared.footer', ['footer' => ['menu' => ['removed' => true]]])
        </div>
    </body>
@endsection