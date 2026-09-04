@extends('layouts.wrapper')

@section('body')
    <body class="d-flex flex-column">
        <div class="d-flex flex-column flex-fill">
            @yield('content')
        </div>

        @include('shared.footer', ['footer' => ['class' => 'footer py-4 border-top bg-base-1', 'menu' => ['class' => 'text-inverse', 'links' => [], 'socials' => []], 'copyright' => ['text' => __('Link by :service', ['service' => '<a href="' . config('app.url') . '" class="text-inverse">' . config('settings.title') . '</a>'])], 'cookie_law' => ['removed' => true]]])
    </body>
@endsection
