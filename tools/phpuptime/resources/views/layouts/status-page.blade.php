@extends('layouts.wrapper')

@section('body')
    <body class="d-flex flex-column" id="status-page">
    <div id="header" class="header bg-base-0 position-sticky top-0 right-0 left-0 w-100 box-sizing-border-box z-1025 shadow">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light px-0 py-3">
                <a href="{{ $statusPage->url }}" aria-label="{{ config('settings.title') }}" class="navbar-brand text-dark font-weight-bold p-0">
                    <div class="height-10 d-flex align-items-center width-auto">
                        @if ($statusPage->logo)
                            <img src="{{ $statusPage->logoUrl }}" alt="{{ $statusPage->name }}" width="auto" height="40" class="h-100 border-0 max-height-10 object-fit-contain max-width-48">
                        @else
                            {{ $statusPage->name }}
                        @endif
                    </div>
                </a>

                @if ($statusPage->website_url || $statusPage->contact_url)
                    <button class="text-secondary d-flex d-lg-none align-items-center navbar-toggler border-0 p-0" type="button" data-toggle="collapse" data-target="#header-navbar" aria-controls="header-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        @include('icons.menu', ['class' => 'width-5 height-5 fill-current'])
                    </button>

                    <div class="collapse navbar-collapse" id="header-navbar">
                        <ul class="navbar-nav pt-2 p-lg-0 {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                            @if ($statusPage->website_url)
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ $statusPage->website_url }}" rel="nofollow noreferrer noopener">{{ __('Website') }}</a>
                                </li>
                            @endif

                            @if ($statusPage->contact_url)
                                <li class="nav-item d-flex align-items-center">
                                    <a href="{{ $statusPage->contact_url }}" class="btn btn-outline-dark" rel="nofollow noreferrer noopener" role="button">{{ __('Contact') }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </nav>
        </div>
    </div>

    <div class="bg-base-1 flex-fill">
        <div class="container h-100 py-6">
            @yield('content')
        </div>
    </div>

    @include('shared.footer', ['footer' => ['menu' => ['class' => 'text-dark', 'links' => [], 'socials' => []], 'copyright' => ['name' => $statusPage->name], 'cookie_law' => ['removed' => true]]])
    </body>
@endsection
