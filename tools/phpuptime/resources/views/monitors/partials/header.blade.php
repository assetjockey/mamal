@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
    ['url' => request()->is('admin/*') ? route('admin.monitors') : route('monitors'), 'title' => __('Monitors')],
    ['title' => __('Monitor')],
]])

<script src="{{ asset('js/app.extras.js?v=' . config('info.software.version')) }}" defer></script>

<div class="d-flex align-items-end mb-3">
    <h1 class="h2 mb-0 flex-grow-1 text-truncate"><span data-tooltip="true" title="{{ $monitor->url }}">{{ $monitor->name }}</span></h1>

    <div class="d-flex align-items-center flex-grow-0">
        <div class="form-row flex-nowrap">
            <div class="col">
                <a href="#" class="btn text-secondary d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>

                @include('monitors.partials.context-menu')
            </div>

            <div class="col">
                <a href="#" class="btn border text-secondary" id="date-range-selector">
                    <div class="d-flex align-items-center cursor-pointer">
                        @include('icons.date-range', ['class' => 'fill-current width-4 height-4 flex-shrink-0'])&#8203;

                        <span class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }} d-none d-lg-block text-nowrap" id="date-range-value">
                            @if($dateRange['from'] == $dateRange['to'])
                                @if(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->isToday())
                                    {{ __('Today') }}
                                @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->isYesterday())
                                    {{ __('Yesterday') }}
                                @else
                                    {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }} - {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}
                                @endif
                            @else
                                @if(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->subDays(6)->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $now->format('Y-m-d'))
                                    {{ __('Last :days days', ['days' => 7]) }}
                                @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->subDays(29)->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $now->format('Y-m-d'))
                                    {{ __('Last :days days', ['days' => 30]) }}
                                @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->startOfMonth()->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->endOfMonth()->format('Y-m-d'))
                                    {{ __('This month') }}
                                @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->subMonthNoOverflow()->startOfMonth()->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == (clone $now)->subMonthNoOverflow()->endOfMonth()->format('Y-m-d'))
                                    {{ __('Last month') }}
                                @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $monitor->created_at->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $now->format('Y-m-d'))
                                    {{ __('All time') }}
                                @else
                                    {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }} - {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}
                                @endif
                            @endif
                        </span>

                        @include('icons.expand-more', ['class' => 'flex-shrink-0 fill-current width-3 height-3 '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])
                    </div>
                </a>

                <form method="GET" name="date-range" action="{{ route(Route::currentRouteName(), ['id' => $monitor->id]) }}">
                    <input name="from" type="hidden">
                    <input name="to" type="hidden">
                    <input name="token" type="hidden" value="{{ (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token) }}">
                </form>
            </div>
        </div>
    </div>
</div>

@if ($errors->has('pause'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first('pause') }}
        <button type="button" class="close d-flex align-items-center justify-content-center width-12 height-12 p-0" data-dismiss="alert" aria-label="{{ __('Close') }}">
            <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current width-4 height-4'])</span>
        </button>
    </div>
@endif

<script>
    'use strict';

    window.addEventListener('DOMContentLoaded', function () {
        document.querySelector('#date-range-selector') && document.querySelector('#date-range-selector').addEventListener('click', function (e) {
            e.preventDefault();
        });

        jQuery('#date-range-selector').daterangepicker({
            ranges: {
                "{{ __('Today') }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }})],
                "{{ __('Yesterday') }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(1, 'days'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(1, 'days')],
                "{{ __('Last :days days', ['days' => 7]) }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(6, 'days'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }})],
                "{{ __('Last :days days', ['days' => 30]) }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(29, 'days'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }})],
                "{{ __('This month') }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).startOf('month'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).endOf('month')],
                "{{ __('Last month') }}": [moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(1, 'month').startOf('month'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }}).subtract(1, 'month').endOf('month')],
                "{{ __('All time') }}": [moment('{{ $monitor->created_at->format('Y-m-d') }}'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }})]
            },
            locale: {
                direction: "{{ (__('lang_dir') == 'rtl' ? 'rtl' : 'ltr') }}",
                format: "{{ str_ireplace(['y', 'm', 'd'], ['YYYY', 'MM', 'DD'], __('Y-m-d')) }}",
                separator: " - ",
                applyLabel: "{{ __('Apply') }}",
                cancelLabel: "{{ __('Cancel') }}",
                customRangeLabel: "{{ __('Custom') }}",
                daysOfWeek: [
                    "{{ __('Su') }}",
                    "{{ __('Mo') }}",
                    "{{ __('Tu') }}",
                    "{{ __('We') }}",
                    "{{ __('Th') }}",
                    "{{ __('Fr') }}",
                    "{{ __('Sa') }}"
                ],
                monthNames: [
                    "{{ __('January') }}",
                    "{{ __('February') }}",
                    "{{ __('March') }}",
                    "{{ __('April') }}",
                    "{{ __('May') }}",
                    "{{ __('June') }}",
                    "{{ __('July') }}",
                    "{{ __('August') }}",
                    "{{ __('September') }}",
                    "{{ __('October') }}",
                    "{{ __('November') }}",
                    "{{ __('December') }}"
                ]
            },
            startDate : "{{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}",
            endDate : "{{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}",
            opens: "{{ (__('lang_dir') == 'rtl' ? 'right' : 'left') }}",
            applyClass: "btn-primary",
            cancelClass: "btn-secondary",
            linkedCalendars: false,
            alwaysShowCalendars: true
        });

        jQuery('#date-range-selector').on('apply.daterangepicker', function (ev, picker) {
            document.querySelector('input[name="from"]').value = picker.startDate.format('YYYY-MM-DD');
            document.querySelector('input[name="to"]').value = picker.endDate.format('YYYY-MM-DD');

            document.querySelector('form[name="date-range"]').submit();
        });

        jQuery('#date-range-selector').on('hide.daterangepicker', function (ev, picker) {
            document.querySelector('#date-range-selector').classList.remove('active');
        });

        jQuery('#date-range-selector').on('show.daterangepicker', function (ev, picker) {
            document.querySelector('#date-range-selector').classList.add('active');
        });
    });
</script>

<div class="d-flex justify-content-between flex-lg-row mb-3 bg-base-0 rounded shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-light min-width-0 p-0 flex-grow-1 px-lg-1">
        <button class="d-flex d-lg-none align-items-center justify-content-between text-secondary font-size-base navbar-toggler border-0 p-0 collapsed w-100" type="button" data-toggle="collapse" data-target="#stats-navbar" aria-controls="stats-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="text-body d-flex align-items-center d-lg-none px-3 py-3 font-weight-medium">
                @php
                    $menu = [
                        'monitors.overview' => ['Overview', 'assesment'],
                        'monitors.checks' => ['Checks', 'adjust'],
                        'monitors.incidents' => ['Incidents', 'error']
                    ];
                @endphp

                @if(isset($menu[Route::currentRouteName()]))
                    @include('icons.'.$menu[Route::currentRouteName()][1], ['class' => 'fill-current width-4 height-4 ' . (Route::currentRouteName() == 'stats.realtime' ? 'text-success' : '') . ' ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
                    {{ __($menu[Route::currentRouteName()][0]) }}
                @endif
            </span>

            <span class="p-3 my-1">
                @include('icons.menu', ['class' => 'width-4 height-4 fill-current'])&#8203;
            </span>
        </button>

        <div class="collapse navbar-collapse border-top border-top-lg-0 overflow-hidden" id="stats-navbar">
            <ul class="navbar-nav w-100">
                <li class="nav-item min-width-0 {{ Route::currentRouteName() == 'monitors.overview' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('monitors.overview', ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}">
                        <span class="d-flex align-items-center">@include('icons.assesment', ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])</span>
                        <span class="text-truncate">{{ __('Overview') }}</span>
                    </a>
                </li>

                <li class="nav-item min-width-0 {{ Route::currentRouteName() == 'monitors.checks' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('monitors.checks', ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}">
                        <span class="d-flex align-items-center">@include('icons.adjust', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])</span>
                        <span class="text-truncate">{{ __('Checks') }}</span>
                    </a>
                </li>

                <li class="nav-item min-width-0 {{ Route::currentRouteName() == 'monitors.incidents' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center font-weight-medium py-3 px-3" href="{{ route('monitors.incidents', ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}">
                        <span class="d-flex align-items-center">@include('icons.error', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])</span>
                        <span class="text-truncate">{{ __('Incidents') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="d-none d-lg-flex align-items-center flex-nowrap flex-grow-1 flex-lg-grow-0" data-monitor-status-container>
        @include('monitors.partials.real-time')
    </div>
</div>

<div class="d-flex d-lg-none justify-content-between flex-wrap flex-lg-row mb-3 bg-base-0 rounded shadow-sm">
    <div class="d-flex align-items-center flex-grow-1" data-monitor-status-container>
        @include('monitors.partials.real-time')
    </div>
</div>
