<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('frontend.user.includes.head')
    @include('frontend.user.includes.styles')
</head>

<body>
    <div class="vr__dash vr__checkout__v2">
        <div class="vr__dash__body w-100 ms-0">
            <div class="vr__dash__navbar">
                <div class="container d-flex justify-content-between align-items-center">
                    <a class="vr__logo" href="{{ url('/') }}">
                        <img src="{{ asset($settings['website_light_logo']) }}"
                            alt="{{ $settings['website_name'] }}" />
                    </a>
                    <div class="vr__dash__navbar__actions">
                        <div class="vr__dash__navbar__action ms-3">
                            <div class="dropdown vr__language">
                                <button data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="vr__language__icon">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <div class="vr__language__title">
                                        {{ getLangName() }}
                                    </div>
                                    <div class="vr__language__arrow">
                                        <i class="fas fa-chevron-down fa-xs"></i>
                                    </div>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    @foreach ($languages as $language)
                                        <li>
                                            <a class="dropdown-item @if (app()->getLocale() == $language->code) active @endif"
                                                href="{{ langURL($language->code) }}">{{ $language->name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="vr__dash__navbar__action flex-shrink-0">
                            <div class="dropdown">
                                <div class="vr__dash__navbar__user" data-bs-toggle="dropdown">
                                    <img src="{{ asset(userAuthinfo()->avatar) }}"
                                        alt="{{ userAuthinfo()->firstname . ' ' . userAuthinfo()->lastname }}" />
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                    @if (userAuthInfo()->subscription)
                                        <li><a class="dropdown-item" href="{{ route('user.dashboard') }}">
                                                <i class="fas fa-th-large"></i>
                                                {{ lang('Dashboard', 'user') }}
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="{{ route('user.settings') }}"><i
                                                    class="fa fa-cog"></i>{{ lang('Settings', 'user') }}</a>
                                        </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button class="dropdown-item text-danger">
                                                <i class="fa fa-sign-out-alt"></i>{{ lang('Logout', 'user') }}
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="vr__dash__body__content">
                <div class="container-lg">
                    <div class="row justify-content-between align-items-center mb-4 g-3">
                        <div class="col-auto">
                            <h5 class="fs-5 mb-2">@yield('title')</h5>
                            @include('frontend.user.includes.breadcrumb')
                        </div>
                    </div>
                    @yield('content')
                </div>
            </div>
            <div class="vr__dash__footer mt-auto">
                <div class="container-lg">
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <p class="mb-0">&copy; <span data-year></span> {{ $settings['website_name'] }}
                                -
                                {{ lang('All rights reserved') }}.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('frontend.configurations.config')
    @include('frontend.configurations.widgets')
    @include('frontend.user.includes.scripts')
    @include('frontend.user.includes.toastr')
</body>

</html>
