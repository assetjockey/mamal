<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('frontend.user.includes.head')
    @include('frontend.user.includes.styles')
</head>

<body>
    <div class="vr__dash">
        <aside class="vr__dash__sidebar">
            <div class="vr__overlay"></div>
            <div class="vr__dash__sidebar__content">
                <div class="vr__dash__sidebar__header">
                    <a class="logo" href="{{ url('/') }}">
                        <img src="{{ asset($settings['website_light_logo']) }}" alt="{{ $settings['website_name'] }}" />
                    </a>
                </div>
                <div class="vr__dash__sidebar__body">
                    <a href="{{ route('user.dashboard') }}" @if (request()->segment(3) == 'dashboard') class="active" @endif>
                        <i class="fas fa-th-large fa-lg"></i> {{ lang('Dashboard', 'user') }}
                    </a>
                    <a href="{{ route('user.transfers.index') }}"
                        @if (request()->segment(3) == 'transfers') class="active" @endif>
                        <i class="far fa-paper-plane fa-lg"></i> {{ lang('My Transfers', 'user') }}
                    </a>
                    <a href="{{ route('user.subscription') }}" @if (request()->segment(3) == 'subscription') class="active" @endif>
                        <i class="far fa-gem fa-lg"></i> {{ lang('My Subscription', 'user') }}
                    </a>
                    @if ($settings['website_tickets_status'])
                        <a href="{{ route('user.tickets') }}" @if (request()->segment(3) == 'tickets') class="active" @endif>
                            <i class="far fa-life-ring fa-lg"></i> {{ lang('My Tickets', 'user') }}
                            @if ($repliedTicketsCount)
                                <span class="vr__counter">{{ $repliedTicketsCount }}</span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('user.settings') }}" @if (request()->segment(3) == 'settings') class="active" @endif>
                        <i class="fa fa-cog fa-lg"></i> {{ lang('Settings', 'user') }}
                    </a>
                </div>
                <div class="vr__dash__sidebar__footer">
                    <form class="d-inline" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-secondary btn-lg w-100">
                            <i class="fas fa-sign-out-alt me-2"></i> {{ lang('Logout', 'user') }}
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        <div class="vr__dash__body">
            @if (subscription()->is_canceled)
                <div class="vr__alert bg-danger">
                    <p class="mb-0">
                        <i class="far fa-times-circle me-2"></i>
                        <strong>{{ lang('Your subscription has been canceled, please contact us for more information', 'alerts') }}</strong>
                    </p>
                </div>
            @endif
            @if (!subscription()->plan->is_free && subscription()->is_expired && !subscription()->is_canceled)
                <div class="vr__alert bg-danger">
                    <p class="mb-0">
                        <i class="fas fa-stopwatch me-2"></i>
                        <strong>{{ lang('Your subscription has been expired, Please renew it to continue using the service.', 'user') }}</strong>
                    </p>
                </div>
            @endif
            @if (!subscription()->plan->is_free &&
                subscription()->remining_days < 6 &&
                !subscription()->is_expired &&
                !subscription()->is_canceled)
                <div class="vr__alert bg-warning">
                    <p class="mb-0 text-dark">
                        <i class="fas fa-stopwatch me-2"></i>
                        <strong>{{ lang('Your subscription is about expired, Renew it to avoid deleting your files.', 'user') }}</strong>
                    </p>
                </div>
            @endif
            <nav class="vr__dash__navbar py-15">
                <div class="vr__sidebar__toggle">
                    <i class="fas fa-bars fa-lg"></i>
                </div>
                <a class="logo" href="{{ url('/') }}">
                    <img src="{{ asset($settings['website_favicon']) }}" alt="{{ $settings['website_name'] }}" />
                </a>
                @hasSection('search')
                    <div class="vr__dash__search ms-4 me-4">
                        <div class="vr__dash__search__input">
                            <form action="{{ url()->current() }}" method="GET">
                                <input class="form-control" type="text" name="search"
                                    placeholder="{{ lang('Type to search...', 'user') }}"
                                    @if (request()->input('search')) value="{{ request()->input('search') }}" @endif>
                            </form>
                        </div>
                    </div>
                @endif
                <div class="vr__dash__navbar__actions">
                    <div class="vr__dash__navbar__action">
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
                    @hasSection('search')
                        <div class="vr__dash__navbar__action search">
                            <a class="vr__search__button">
                                <i class="fa fa-search"></i>
                            </a>
                        </div>
                    @endif
                    <div class="vr__dash__navbar__action">
                        <div class="dropdown">
                            <a class="noti__btn" data-bs-toggle="dropdown">
                                <i class="far fa-bell"></i>
                                <div class="noti__count">{{ $unreadUserNotifications }}</div>
                            </a>
                            <div class="dropdown-menu noti">
                                <div class="dropdown-menu-header">
                                    <h6 class="mb-0">{{ lang('Notifications', 'user') }}
                                        ({{ $unreadUserNotificationsAll }})</h6>
                                    @if ($unreadUserNotifications)
                                        <a href="{{ route('user.notifications.readall') }}"
                                            class="vr__confirm__action vr__link__color">{{ lang('Make All as Read', 'user') }}</a>
                                    @else
                                        <span class="text-muted">{{ lang('Make All as Read', 'user') }}</span>
                                    @endif
                                </div>
                                <div class="dropdown-menu-body">
                                    @forelse ($userNotifications as $userNotification)
                                        @if ($userNotification->link)
                                            <a class="dropdown-item @if (!$userNotification->status) unread @endif"
                                                href="{{ route('user.notifications.view', hashid($userNotification->id)) }}">
                                            @else
                                                <div
                                                    class="dropdown-item @if (!$userNotification->status) unread @endif">
                                        @endif
                                        <div class="dropdown-item-icon">
                                            <img src="{{ $userNotification->image }}">
                                        </div>
                                        <div class="dropdown-item-info">
                                            <p class="dropdown-item-title">
                                                {{ $userNotification->title }}</p>
                                            <span
                                                class="dropdown-item-text">{{ $userNotification->created_at->diffforhumans() }}</span>
                                        </div>
                                        @if ($userNotification->link)
                                            </a>
                                        @else
                                </div>
                                @endif
                            @empty
                                <div class="empty text-center">
                                    <small class="text-muted mb-0">{{ lang('No notifications found', 'user') }}</small>
                                </div>
                                @endforelse
                            </div>
                            <div class="dropdown-menu-footer">
                                <a class="dropdown-item" href="{{ route('user.notifications') }}">
                                    {{ lang('View All', 'user') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="vr__dash__navbar__action">
                    <div class="dropdown">
                        <div class="vr__dash__navbar__user" data-bs-toggle="dropdown">
                            <img src="{{ asset(userAuthinfo()->avatar) }}"
                                alt="{{ userAuthinfo()->firstname . ' ' . userAuthinfo()->lastname }}" />
                        </div>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="{{ route('user.settings') }}"><i
                                        class="fa fa-edit"></i>{{ lang('Account Details', 'user') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.settings.password') }}"><i
                                        class="fas fa-sync-alt"></i>{{ lang('Change Password', 'user') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.settings.2fa') }}"><i
                                        class="fas fa-fingerprint"></i>{{ lang('2FA Authentication', 'user') }}</a>
                            </li>
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
        </nav>
        <div class="vr__dash__body__content">
            <div class="vr__dash__container">
                <div class="vr__dash__title mb-4">
                    <div class="row justify-content-between align-items-center g-3">
                        <div class="col-auto">
                            <h5 class="fs-4 mb-2">@yield('title')</h5>
                            @include('frontend.user.includes.breadcrumb')
                        </div>
                        <div class="col-auto">
                            @hasSection('status_dropdown')
                                <div class="dropdown me-2 d-inline">
                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ lang('Status', 'tickets') }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li><a class="dropdown-item"
                                                href="{{ route('user.tickets') }}">{{ lang('All', 'tickets') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('user.tickets.status', 'opened') }}">{{ lang('Opened', 'tickets') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('user.tickets.status', 'answered') }}">{{ lang('Answered', 'tickets') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('user.tickets.status', 'replied') }}">{{ lang('Replied', 'tickets') }}</a>
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('user.tickets.status', 'closed') }}">{{ lang('Closed', 'tickets') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                            @hasSection('back')
                                <a href="@yield('back')" class="btn btn-primary me-2"><i
                                        class="fas fa-arrow-left me-2"></i>{{ lang('Back', 'user') }}</a>
                            @endif
                            @hasSection('link')
                                <a href="@yield('link')" class="btn btn-secondary me-2"><i
                                        class="fa fa-plus"></i></a>
                            @endif
                            @if (request()->routeIs('user.transfers.show') && !isExpiry($transfer->expiry_at) && $transfer->status)
                                <button class="btn btn-secondary" data-bs-toggle="modal"
                                    data-bs-target="#transferSettingsModal"><i
                                        class="fa fa-cog me-2"></i>{{ lang('Transfer settings', 'user') }}</button>
                            @endif
                            @if (request()->routeIs('user.subscription') || request()->routeIs('user.transfers.index'))
                                <a href="{{ url('/') }}" class="btn btn-primary"><i
                                        class="far fa-paper-plane me-2"></i>{{ lang('Start Transfer', 'user') }}</a>
                            @endif
                            @if (request()->routeIs('user.notifications'))
                                @if ($unreadUserNotifications)
                                    <a class="vr__confirm__action btn btn-outline-success"
                                        href="{{ route('user.notifications.readall') }}">{{ lang('Make All as Read', 'user') }}</a>
                                @else
                                    <button class="btn btn-outline-success"
                                        disabled>{{ lang('Make All as Read', 'user') }}</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
        </div>
        <div class="vr__dash__footer mt-auto">
            <div class="row justify-content-between">
                <div class="col-auto">
                    <p class="mb-0">&copy; <span data-year></span> {{ $settings['website_name'] }} -
                        {{ lang('All rights reserved') }}.</p>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('frontend.configurations.config')
    @include('frontend.configurations.widgets')
    @include('frontend.user.includes.scripts')
</body>

</html>
