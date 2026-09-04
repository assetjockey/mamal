@guest
    <div id="header" class="header bg-base-0 position-sticky top-0 end-0 start-0 w-full box-border z-1025 shadow">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light px-0 py-4">
                <a href="{{ route('home') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                    <div class="h-10 w-auto">
                        <img src="{{ asset('uploads/brand/' . (config('settings.dark_mode') == 1 ? config('settings.logo_dark') : config('settings.logo'))) }}" alt="{{ config('settings.title') }}" width="auto" height="40" data-theme-dark="{{ asset('uploads/brand/' . config('settings.logo_dark')) }}" data-theme-light="{{ asset('uploads/brand/' . config('settings.logo')) }}" data-theme-target="src" class="h-full border-0 max-h-10 object-contain max-w-48">
                    </div>
                </a>
                <button class="text-secondary d-flex d-lg-none align-items-center navbar-toggler border-0 p-0" type="button" data-toggle="collapse" data-target="#header-navbar" aria-controls="header-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    @include('icons.menu', ['class' => 'w-5 h-5 fill-current'])
                </button>

                <div class="collapse navbar-collapse" id="header-navbar">
                    <ul class="navbar-nav pt-2 p-lg-0 ms-auto">
                        @if(enabledPaymentProcessors())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pricing') }}" role="button">{{ __('Pricing') }}</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}" role="button">{{ __('Login') }}</a>
                        </li>

                        @if(config('settings.registration'))
                            <li class="nav-item d-flex align-items-center">
                                <a class="btn btn-outline-primary" href="{{ route('register') }}" role="button">{{ __('Register') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </div>
@else
    <div id="header" class="header bg-base-0 position-sticky top-0 end-0 start-0 w-full box-border z-1025 shadow d-lg-none">
        <div class="container-fluid">
            <nav class="navbar navbar-light px-0 py-4">
                <a href="{{ route('dashboard') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand p-0">
                    <div class="h-10 w-auto">
                        <img src="{{ asset('uploads/brand/' . (config('settings.dark_mode') == 1 ? config('settings.logo_dark') : config('settings.logo'))) }}" alt="{{ config('settings.title') }}" width="auto" height="40" data-theme-dark="{{ asset('uploads/brand/' . config('settings.logo_dark')) }}" data-theme-light="{{ asset('uploads/brand/' . config('settings.logo')) }}" data-theme-target="src" class="h-full border-0 max-h-10 object-contain max-w-48">
                    </div>
                </a>
                <button class="text-secondary d-flex d-lg-none align-items-center slide-menu-toggle navbar-toggler border-0 p-0" type="button" aria-label="{{ __('Toggle navigation') }}">
                    @include('icons.menu', ['class' => 'w-5 h-5 fill-current'])
                </button>
            </nav>
        </div>
    </div>

    <nav class="slide-menu position-fixed top-0 bottom-0 w-64 start-0 shadow bg-base-0 navbar navbar-light p-0 d-flex flex-column z-1030" id="slide-menu">
        <div class="min-h-0 flex-grow-1 d-flex flex-column w-full">
            <div>
                <div class="ps-6 py-4 d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('settings.title') }}" class="navbar-brand m-0 p-0">
                        <div class="h-10 w-auto">
                            <img src="{{ asset('uploads/brand/' . (config('settings.dark_mode') == 1 ? config('settings.logo_dark') : config('settings.logo'))) }}" alt="{{ config('settings.title') }}" width="auto" height="40" data-theme-dark="{{ asset('uploads/brand/' . config('settings.logo_dark')) }}" data-theme-light="{{ asset('uploads/brand/' . config('settings.logo')) }}" data-theme-target="src" class="h-full border-0 max-h-10 object-contain max-w-48">
                        </div>
                    </a>
                    <div class="close slide-menu-toggle cursor-pointer d-lg-none d-flex align-items-center ms-auto px-6 py-2">
                        @include('icons.close', ['class' => 'fill-current w-4 h-4'])
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="py-4 ps-6 pe-0 fw-medium text-muted text-uppercase flex-grow-1">{{ __('Menu') }}</div>

                @if(Auth::user()->isAdmin())
                    @if (request()->is('admin/*'))
                        <a class="px-6 py-2 text-decoration-none text-secondary" href="{{ route('dashboard') }}" data-tooltip="true" title="{{ __('User') }}" role="button"><span class="d-flex align-items-center">@include('icons.account-circle', ['class' => 'w-4 h-4 fill-current'])</span></a>
                    @else
                        <a class="px-6 py-2 text-decoration-none text-secondary" href="{{ route('admin.dashboard') }}" data-tooltip="true" title="{{ __('Admin') }}" role="button"><span class="d-flex align-items-center">@include('icons.supervised-user-circle', ['class' => 'w-4 h-4 fill-current'])</span></a>
                    @endif
                @endif
            </div>

            <div class="min-h-0 flex-grow-1 overflow-auto sidebar">
                @yield('menu')
            </div>

            <a href="{{ route('account.plan') }}" class="text-decoration-none py-2 px-2 my-2 mx-4">
                <div class="row no-gutters">
                    <div class="col">
                        <div class="small text-muted">
                            {{ __(':number of :total links used.', ['number' => shortenNumber(Auth::user()->linksCount), 'total' => (Auth::user()->active_plan->features->links < 0 ? '∞' : shortenNumber(Auth::user()->active_plan->features->links))]) }}
                        </div>
                    </div>
                </div>

                <div class="progress w-full my-2 h-1.25">
                    <div class="progress-bar rounded" role="progressbar" style="width: {{ (Auth::user()->active_plan->features->links == 0 ? 100 : ((Auth::user()->linksCount / Auth::user()->active_plan->features->links) * 100)) }}%"></div>
                </div>
            </a>

            <div class="sidebar sidebar-footer">
                <div class="py-4 ps-6 pe-0 d-flex align-items-center" aria-expanded="true">
                    <a href="{{ route('account') }}" class="d-flex align-items-center overflow-hidden text-secondary text-decoration-none flex-grow-1">
                        <img src="{{ Auth::user()->avatarUrl }}" class="flex-shrink-0 rounded-circle w-10 h-10 me-4" alt="">

                        <div class="d-flex flex-column text-truncate">
                            <div class="fw-medium text-inverse text-truncate">
                                {{ Auth::user()->name }}
                            </div>

                            <div class="small fw-medium">
                                {{ __('Account') }}
                            </div>
                        </div>
                    </a>

                    <a class="py-2 px-6 d-flex flex-shrink-0 align-items-center text-secondary" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" data-tooltip="true" title="{{ __('Logout') }}">@include('icons.logout', ['class' => 'fill-current w-4 h-4'])</a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endguest