<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('backend.includes.head')
    @include('backend.includes.styles')
</head>

<body>
    @include('backend.includes.sidebar')
    <div class="vironeer-page-content">
        @include('backend.includes.header')
        <div class="container">
            <div class="vironeer-page-body">
                <div class="py-4 g-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-lg">
                            @include('backend.includes.breadcrumb')
                        </div>
                        <div class="col-12 col-lg-auto">
                            @hasSection('back')
                                <a href="@yield('back')" class="btn btn-secondary"><i
                                        class="fas fa-arrow-left me-2"></i>{{ __('Back') }}</a>
                            @endif
                            @hasSection('access')
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        @yield('access')
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.settings.index') }}">{{ __('Settings') }}</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('pages.index') }}">{{ __('Pages') }}</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('languages.index') }}">{{ __('Languages') }}</a></li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.settings.gateways.index') }}">{{ __('Payment Gateways') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.notifications.index') }}">{{ __('Notifications') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
        </div>
        @include('backend.includes.footer')
    </div>
    @include('backend.includes.scripts')
</body>

</html>
