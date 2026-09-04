@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['title' => __('Stats')]
]])

<script src="{{ asset('js/app.extras.js?v=' . config('info.software.version')) }}" defer></script>

<div class="row mx-n2 mb-4">
    <div class="col px-2 text-truncate">
        <h1 class="fs-3xl fw-medium d-block text-truncate tracking-tight m-0" dir="ltr">{{ $link->displayShortUrl }}</h1>
    </div>
    <div class="col-auto px-2 d-flex align-items-center flex-grow-0">
        <div class="row mx-n1 flex-nowrap">
            <div class="col px-1">
                @include('links.partials.copy-link-button', ['class' => 'btn-ghost-secondary'])
            </div>

            <div class="col px-1">
                <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                @include('links.partials.context-menu')
            </div>

            @if($link->user)
                <div class="col px-1">
                    <a href="#" class="btn btn-ghost-secondary border" id="date-range-selector">
                        <div class="d-flex align-items-center cursor-pointer">
                            @include('icons.date-range', ['class' => 'fill-current w-4 h-4 flex-shrink-0'])&#8203;

                            <span class="ms-2 d-none d-lg-block text-nowrap" id="date-range-value">
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
                                    @elseif(Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $link->created_at->format('Y-m-d') && Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d') == $now->format('Y-m-d'))
                                        {{ __('All time') }}
                                    @else
                                        {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }} - {{ Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}
                                    @endif
                                @endif
                            </span>

                            @include('icons.expand-more', ['class' => 'flex-shrink-0 fill-current w-3 h-3 ms-2'])
                        </div>
                    </a>

                    <form method="GET" name="date-range" action="{{ route(Route::currentRouteName(), ['id' => $link->id]) }}">
                        <input name="from" type="hidden">
                        <input name="to" type="hidden">
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

@if($link->user)
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
                "{{ __('All time') }}": [moment('{{ $link->created_at->format('Y-m-d') }}'), moment().utcOffset({{ (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->utcOffset() }})]
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

<div class="d-flex mb-4">
    <nav class="navbar navbar-expand-lg navbar-light w-full p-0 bg-base-0 rounded shadow-sm">
        <button class="d-flex d-lg-none align-items-center justify-content-between text-secondary fs-base navbar-toggler border-0 p-0 collapsed w-full" type="button" data-toggle="collapse" data-target="#stats-navbar" aria-controls="stats-navbar" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="text-body d-flex align-items-center d-lg-none px-4 py-4 fw-medium">
                @php
                    $menu = [
                        'stats.overview' => ['Overview', 'assesment'],
                        'stats.referrers' => ['Referrers', 'link'],
                        'stats.countries' => ['Countries', 'flag'],
                        'stats.cities' => ['Cities', 'business'],
                        'stats.languages' => ['Languages', 'language'],
                        'stats.operating-systems' => ['Operating systems', 'terminal'],
                        'stats.browsers' => ['Browsers', 'tab'],
                        'stats.devices' => ['Devices', 'devices-other']
                    ];
                @endphp

                @if(isset($menu[Route::currentRouteName()]))
                    @include('icons.' . $menu[Route::currentRouteName()][1], ['class' => 'fill-current w-4 h-4 ' . (Route::currentRouteName() == 'stats.realtime' ? 'text-success' : '') . ' me-2'])
                    {{ __($menu[Route::currentRouteName()][0]) }}
                @endif
            </span>

            <span class="p-4 my-1">
                @include('icons.menu', ['class' => 'w-4 h-4 fill-current'])&#8203;
            </span>
        </button>

        <div class="collapse navbar-collapse w-full border-top border-top-lg-0" id="stats-navbar">
            <ul class="navbar-nav justify-content-around w-full">
                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.overview' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.overview', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.assesment', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Overview') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.referrers' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.referrers', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.link', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Referrers') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.countries' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.countries', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.flag', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Countries') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.cities' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.cities', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.business', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Cities') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.languages' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.languages', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.language', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Languages') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.operating-systems' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.operating-systems', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.terminal', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Operating systems') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.browsers' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.browsers', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.tab', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Browsers') }}</span>
                    </a>
                </li>

                <li class="nav-item min-w-0 max-w-full {{ Route::currentRouteName() == 'stats.devices' ? 'active' : '' }}">
                    <a class="nav-link d-flex align-items-center fw-medium py-4 px-4" href="{{ route('stats.devices', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                        <span class="d-flex align-items-center">@include('icons.devices-other', ['class' => 'flex-shrink-0 fill-current w-4 h-4 me-2'])</span>
                        <span class="text-truncate">{{ __('Devices') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>
@endif