<nav class="nav-bar">
    <div class="container-lg d-flex align-items-center">
        <a href="{{ route('home') }}" class="logo">
            <img src="{{ asset($settings['website_light_logo']) }}" alt="{{ $settings['website_name'] }}" />
            <img src="{{ asset($settings['website_dark_logo']) }}" alt="{{ $settings['website_name'] }}" />
        </a>
        <div class="nav-bar-actions ms-auto">
            <div class="nav-bar-menu">
                <div class="overlay"></div>
                <div class="nav-bar-links">
                    <div class="d-flex justify-content-end w-100 mb-3 d-lg-none">
                        <button class="btn-close"></button>
                    </div>
                    @foreach ($navbarMenuLinks as $navbarMenuLink)
                        <a class="nav-bar-link" {!! !$navbarMenuLink->type
                            ? 'href="' . $navbarMenuLink->link . '"'
                            : 'data-link="' . $navbarMenuLink->link . '"' !!}>
                            {{ $navbarMenuLink->name }}
                        </a>
                    @endforeach
                    <div class="drop-down languages" data-dropdown data-dropdown-position="top">
                        <div class="drop-down-btn">
                            <i class="fa-solid fa-globe me-2"></i>
                            <span>{{ getLangName() }}</span>
                            <i class="fa fa-angle-down ms-2"></i>
                        </div>
                        <div class="drop-down-menu">
                            @foreach ($languages as $language)
                                <a class="drop-down-item {{ getLang() == $language->code ? 'active' : '' }}"
                                    href="{{ langURL($language->code) }}">{{ $language->name }}</a>
                            @endforeach
                        </div>
                    </div>
                    @guest
                        @if ($settings['website_registration_status'])
                            <a class="nav-bar-link btn btn-outline-light btn-sm me-3" href="{{ route('register') }}">
                                {{ lang('Sign Up', 'user') }}
                            </a>
                        @endif
                        <a class="nav-bar-link btn btn-light btn-sm" href="{{ route('login') }}">
                            {{ lang('Sign In', 'user') }}
                        </a>
                    @endguest
                </div>
            </div>
            @auth
                <div class="drop-down user-menu ms-3" data-dropdown="" data-dropdown-position="top">
                    <div class="drop-down-btn">
                        <img src="{{ asset(userAuthInfo()->avatar) }}" alt="{{ userAuthInfo()->name }}"
                            class="user-img" />
                        <span class="user-name">{{ userAuthInfo()->name }}</span>
                        <i class="fa fa-angle-down ms-2"></i>
                    </div>
                    <div class="drop-down-menu">
                        <a class="drop-down-item" href="{{ route('user.dashboard') }}">
                            <i class="fa-solid fa-table-columns"></i>
                            {{ lang('Dashboard', 'user') }}
                        </a>
                        <a class="drop-down-item" href="{{ route('user.settings') }}">
                            <i class="fa fa-cog"></i>
                            {{ lang('Settings', 'user') }}
                        </a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="drop-down-item text-danger">
                            <i class="fa fa-power-off"></i>{{ lang('Logout', 'user') }}
                        </a>
                    </div>
                    <form id="logout-form" class="d-inline" action="{{ route('logout') }}" method="POST">
                        @csrf
                    </form>
                </div>
            @endauth
            <div class="nav-bar-menu-icon d-lg-none">
                <i class="fa fa-bars fa-lg"></i>
            </div>
        </div>
    </div>
</nav>
