<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('frontend.user.includes.head')
    @include('frontend.user.includes.styles')
</head>

<body>
    <div class="vr__page @yield('bg')">
        <nav class="vr__nav__bar">
            <div class="vr__nav__container h-100">
                <div class="row align-items-center h-100">
                    <div class="col-auto">
                        <a class="logo" href="{{ route('home') }}">
                            <img src="{{ asset($settings['website_light_logo']) }}"
                                alt="{{ $settings['website_name'] }}" />
                        </a>
                    </div>
                    <div class="col-auto ms-auto">
                        <div class="vr__signs">
                            @auth
                                <form class="d-inline" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="btn vr__sign__link"><i
                                            class="fas fa-sign-out-alt me-2"></i>{{ lang('Logout', 'user') }}</button>
                                </form>
                            @else
                                @if (request()->routeIs('register'))
                                    <a class="vr__sign__link"
                                        href="{{ route('login') }}">{{ lang('Sign In', 'user') }}</a>
                                @elseif(request()->routeIs('login'))
                                    @if ($settings['website_registration_status'])
                                        <a class="vr__sign__link"
                                            href="{{ route('register') }}">{{ lang('Sign Up', 'user') }}</a>
                                    @endif
                                @else
                                    <a class="vr__sign__link"
                                        href="{{ route('login') }}">{{ lang('Sign In', 'user') }}</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="vr__form__aria">
            @yield('content')
        </div>
        <div class="vr__footer">
            <div class="vr__nav__container">
                <div class="row justify-content-between align-items-center g-3">
                    <div class="col-auto">
                        <div class="vr__copyright">
                            <p class="mb-0 small">&copy; <span data-year></span> {{ $settings['website_name'] }}
                                -
                                {{ lang('All rights reserved') }}.</p>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="vr__lang">
                            <div class="vr__lang__icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <select class="vr__change__language form-select">
                                @foreach ($languages as $language)
                                    <option data-link="{{ langURL($language->code) }}"
                                        value="{{ $language->code }}"
                                        @if (app()->getLocale() == $language->code) selected @endif>
                                        {{ $language->name }}</option>
                                @endforeach
                            </select>
                            <div class="select-icon">
                                <i class="fas fa-caret-down"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('frontend.configurations.config')
    @include('frontend.configurations.widgets')
    @include('frontend.user.includes.scripts')
    {!! google_captcha() !!}
    @include('frontend.user.includes.toastr')
</body>

</html>
