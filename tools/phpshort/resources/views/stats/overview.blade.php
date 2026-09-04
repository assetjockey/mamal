@extends('layouts.app')

@section('site_title', formatTitle([$link->alias, __('Overview'), __('Stats'), config('settings.title')]))

@section('head_content')
    @if (count(request()->all()) > 0)
        <link rel="canonical" href="{{ route(Route::currentRouteName(), ['id' => $link->id]) }}" />
        <meta name="robots" content="noindex">
    @endif
@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @if (config('settings.ad_stats_header'))
                @if(!Auth::check() || !Auth::user()->active_plan->features->no_ads)
                    <div class="mb-4 d-print-none">{!! config('settings.ad_stats_header') !!}</div>
                @endif
            @endif

            @include('stats.partials.header')

            @if($link->user->can('linkStats', [App\Models\User::class]) || (Auth::check() && Auth::user()->isAdmin()))
                <div class="card border-0 rounded-top shadow-sm mb-4 overflow-hidden" id="trend-chart-container">
                    <div class="px-4 border-bottom">
                        <div class="row">
                            <!-- Clicks -->
                            <div class="col-12 col-lg-4 border-bottom border-bottom-lg-0 border-end-lg">
                                <div class="px-2 py-6">
                                    <div class="d-flex">
                                        <div class="text-truncate me-2">
                                            <div class="d-flex align-items-center text-truncate">
                                                <div class="d-flex align-items-center justify-content-center bg-primary rounded-sm w-4 h-4 flex-shrink-0 me-2" id="clicks-legend"></div>

                                                <div class="flex-grow-1 d-flex fw-bold text-truncate">
                                                    <div class="text-truncate">{{ __('Clicks') }}</div>
                                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('The total number of clicks for the current dataset.') }}">
                                                        @include('icons.info', ['class' => 'w-4 h-4 fill-current text-muted'])
                                                    </div>
                                                </div>
                                            </div>

                                            @include('stats.partials.growth', ['growthCurrent' => $totalClicks, 'growthPrevious' => $totalClicksOld])
                                        </div>

                                        <div class="d-flex align-items-center ms-auto">
                                            <div class="fs-3xl fw-bold m-0">{{ number_format($totalClicks, 0, __('.'), __(',')) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Most -->
                            <div class="col-12 col-lg-4 border-bottom border-bottom-lg-0 border-end-lg">
                                <div class="px-2 py-6">
                                    <div class="row">
                                        <div class="col">
                                            <div class="d-flex align-items-center text-truncate">
                                                <div class="flex-grow-1 d-flex fw-bold text-truncate">
                                                    <div class="text-truncate">{{ __('Original link') }}</div>
                                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('The number of characters in the link.') }}">
                                                        @include('icons.info', ['class' => 'w-4 h-4 fill-current text-muted'])
                                                    </div>
                                                </div>
                                                <div class="align-self-end">
                                                    {{ mb_strlen($link->url) }}
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center text-truncate @if(mb_strlen($link->shortUrl) < mb_strlen($link->url)) text-danger @elseif(mb_strlen($link->shortUrl) > mb_strlen($link->url)) text-success @endif">
                                                @if(mb_strlen($link->shortUrl) < mb_strlen($link->url))
                                                    <div class="d-flex align-items-center justify-content-center w-4 h-4 me-2">
                                                        @include('icons.trending-down', ['class' => 'fill-current w-3 h-3'])
                                                    </div>

                                                    <div class="flex-grow-1 text-truncate me-2">
                                                        {{ mb_strtolower(__('Longer')) }}
                                                    </div>
                                                @elseif(mb_strlen($link->shortUrl) > mb_strlen($link->url))
                                                    <div class="d-flex align-items-center justify-content-center w-4 h-4 me-2">
                                                        @include('icons.trending-up', ['class' => 'fill-current w-3 h-3'])
                                                    </div>

                                                    <div class="flex-grow-1 text-truncate me-2">
                                                        {{ mb_strtolower(__('Shorter')) }}
                                                    </div>
                                                @else
                                                    <div class="flex-grow-1 text-truncate text-muted">
                                                        {{ __('Identical') }}
                                                    </div>
                                                @endif

                                                <div>{{ calcPercentageChange(mb_strlen($link->shortUrl), mb_strlen($link->url)) != 0 ? abs(calcPercentageChange(mb_strlen($link->shortUrl), mb_strlen($link->url))) . '%' :  '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Least -->
                            <div class="col-12 col-lg-4">
                                <div class="px-2 py-6">
                                    <div class="row">
                                        <div class="col">
                                            <div class="d-flex align-items-center text-truncate">
                                                <div class="flex-grow-1 d-flex fw-bold text-truncate">
                                                    <div class="text-truncate">{{ __('Shortened link') }}</div>
                                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('The number of characters in the link.') }}">
                                                        @include('icons.info', ['class' => 'w-4 h-4 fill-current text-muted'])
                                                    </div>
                                                </div>

                                                <div class="align-self-end">{{ mb_strlen($link->shortUrl) }}</div>
                                            </div>

                                            <div class="d-flex align-items-center text-truncate @if(mb_strlen($link->shortUrl) < mb_strlen($link->url)) text-success @elseif(mb_strlen($link->shortUrl) > mb_strlen($link->url)) text-danger @endif">
                                                @if(mb_strlen($link->shortUrl) < mb_strlen($link->url))
                                                    <div class="d-flex align-items-center justify-content-center w-4 h-4 me-2">
                                                        @include('icons.trending-up', ['class' => 'fill-current w-3 h-3'])
                                                    </div>

                                                    <div class="flex-grow-1 text-truncate me-2">
                                                        {{ mb_strtolower(__('Shorter')) }}
                                                    </div>
                                                @elseif(mb_strlen($link->shortUrl) > mb_strlen($link->url))
                                                    <div class="d-flex align-items-center justify-content-center w-4 h-4 me-2">
                                                        @include('icons.trending-down', ['class' => 'fill-current w-3 h-3'])
                                                    </div>

                                                    <div class="flex-grow-1 text-truncate me-2">
                                                        {{ mb_strtolower(__('Longer')) }}
                                                    </div>
                                                @else
                                                    <div class="flex-grow-1 text-truncate text-muted">
                                                        {{ __('Identical') }}
                                                    </div>
                                                @endif

                                                <div>{{ calcPercentageChange(mb_strlen($link->url), mb_strlen($link->shortUrl)) != 0 ? abs(calcPercentageChange(mb_strlen($link->url), mb_strlen($link->shortUrl))) . '%' :  '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="h-58">
                            <canvas id="trendChart"></canvas>
                        </div>
                        <script>
                            'use strict';

                            window.addEventListener("DOMContentLoaded", function () {
                                Chart.defaults.font = {
                                    family: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'",
                                    size: 12
                                };

                                const phBgColor = window.getComputedStyle(document.getElementById('trend-chart-container')).getPropertyValue('background-color');
                                const clicksColor = window.getComputedStyle(document.getElementById('clicks-legend')).getPropertyValue('background-color');

                                const ctx = document.querySelector('#trendChart').getContext('2d');
                                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                gradient.addColorStop(0, clicksColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                gradient.addColorStop(1, clicksColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                let tooltipTitles = [
                                    @foreach($clicksMap as $date => $value)
                                        @if($dateRange['unit'] == 'hour')
                                            '{{ Carbon\Carbon::createFromFormat($dateRange['format'], $date)->format(__('H:i')) }}',
                                        @elseif($dateRange['unit'] == 'day')
                                            '{{ Carbon\Carbon::createFromFormat($dateRange['format'], $date)->format(__('Y-m-d')) }}',
                                        @elseif($dateRange['unit'] == 'month')
                                            '{{ Carbon\Carbon::createFromFormat($dateRange['format'], $date)->format(__('Y-m')) }}',
                                        @else
                                            '{{ $date }}',
                                        @endif
                                    @endforeach
                                ];

                                const lineOptions = {
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    hitRadius: 5,
                                    pointHoverBorderWidth: 3,
                                    lineTension: 0,
                                }

                                let trendChart = new Chart(ctx, {
                                    type: 'line',

                                    data: {
                                        labels: [
                                            @foreach($clicksMap as $date => $value)
                                                @if($dateRange['unit'] == 'hour')
                                                    '{{ Carbon\Carbon::createFromFormat($dateRange['format'], $date)->format(__('H:i')) }}',
                                                @elseif($dateRange['unit'] == 'day')
                                                    '{{ __(':month :day', ['month' => mb_substr(__(Carbon\Carbon::parse($date)->format('F')), 0, 3), 'day' => __(Carbon\Carbon::parse($date)->format('j'))]) }}',
                                                @elseif($dateRange['unit'] == 'month')
                                                    '{{ __(':year :month', ['year' => Carbon\Carbon::parse($date)->format('Y'), 'month' => mb_substr(__(Carbon\Carbon::parse($date)->format('F')), 0, 3)]) }}',
                                                @else
                                                    '{{ $date }}',
                                                @endif
                                            @endforeach
                                        ],
                                        datasets: [{
                                            label: '{{ __('Clicks') }}',
                                            data: [
                                                @foreach($clicksMap as $date => $value)
                                                        {{ $value }},
                                                @endforeach
                                            ],
                                            fill: true,
                                            backgroundColor: gradient,
                                            borderColor: clicksColor,
                                            pointBorderColor: clicksColor,
                                            pointBackgroundColor: clicksColor,
                                            pointHoverBackgroundColor: phBgColor,
                                            pointHoverBorderColor: clicksColor,
                                            ...lineOptions
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        interaction: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        plugins: {
                                            legend: {
                                                rtl: {{ (__('lang_dir') == 'rtl' ? 'true' : 'false') }},
                                                display: false
                                            },
                                            tooltip: {
                                                rtl: {{ (__('lang_dir') == 'rtl' ? 'true' : 'false') }},
                                                mode: 'index',
                                                intersect: false,
                                                reverse: true,

                                                padding: {
                                                    top: 14,
                                                    right: 16,
                                                    bottom: 16,
                                                    left: 16
                                                },

                                                backgroundColor: '{{ (config('settings.dark_mode') == 1 ? '#FFF' : '#000') }}',

                                                titleColor: '{{ (config('settings.dark_mode') == 1 ? '#000' : '#FFF') }}',
                                                titleMarginBottom: 7,
                                                titleFont: {
                                                    size: 16,
                                                    weight: 'normal'
                                                },

                                                bodyColor: '{{ (config('settings.dark_mode') == 1 ? '#000' : '#FFF') }}',
                                                bodySpacing: 7,
                                                bodyFont: {
                                                    size: 14
                                                },

                                                footerMarginTop: 10,
                                                footerFont: {
                                                    size: 12,
                                                    weight: 'normal'
                                                },

                                                cornerRadius: 6,
                                                caretSize: 7,

                                                boxPadding: 4,

                                                callbacks: {
                                                    label: function (tooltipItem) {
                                                        return ' ' + tooltipItem.dataset.label + ': ' + parseFloat(tooltipItem.dataset.data[tooltipItem.dataIndex]).format(0, 3, '{{ __(',') }}').toString();
                                                    },
                                                    title: function (tooltipItem) {
                                                        return tooltipTitles[tooltipItem[0].dataIndex];
                                                    }
                                                }
                                            },
                                        },
                                        scales: {
                                            x: {
                                                display: true,
                                                grid: {
                                                    lineWidth: 0,
                                                    tickLength: 0
                                                },
                                                ticks: {
                                                    maxTicksLimit: @if($dateRange['unit'] == 'day') 12 @else 15 @endif,
                                                    padding: 10,
                                                }
                                            },
                                            y: {
                                                display: true,
                                                beginAtZero: true,
                                                grid: {
                                                    tickLength: 0
                                                },
                                                ticks: {
                                                    maxTicksLimit: 8,
                                                    padding: 10,
                                                    callback: function (value) {
                                                        return commarize(value, 1000);
                                                    }
                                                }
                                            },
                                        }
                                    }
                                });

                                // The time to wait before attempting to change the colors on first attempt
                                let colorSchemeTimer = 500;

                                // Update the chart colors when the color scheme changes
                                const observer = new MutationObserver(function (mutationsList, observer) {
                                    for (const mutation of mutationsList) {
                                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                            setTimeout(function () {
                                                const phBgColor = window.getComputedStyle(document.getElementById('trend-chart-container')).getPropertyValue('background-color');
                                                const clicksColor = window.getComputedStyle(document.getElementById('clicks-legend')).getPropertyValue('background-color');

                                                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                                gradient.addColorStop(0, clicksColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                                gradient.addColorStop(1, clicksColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                                trendChart.data.datasets[0].backgroundColor = gradient;
                                                trendChart.data.datasets[0].borderColor = clicksColor;
                                                trendChart.data.datasets[0].pointBorderColor = clicksColor;
                                                trendChart.data.datasets[0].pointBackgroundColor = clicksColor;
                                                trendChart.data.datasets[0].pointHoverBackgroundColor = phBgColor;
                                                trendChart.data.datasets[0].pointHoverBorderColor = clicksColor;

                                                trendChart.options.plugins.tooltip.backgroundColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#000' : '#FFF');
                                                trendChart.options.plugins.tooltip.titleColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                                                trendChart.options.plugins.tooltip.bodyColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                                                trendChart.update();

                                                // Update the color scheme timer to be faster next time it's used
                                                colorSchemeTimer = 100;
                                            }, colorSchemeTimer);
                                        }
                                    }
                                });

                                observer.observe(document.querySelector('html'), {attributes: true});
                            });
                        </script>
                    </div>
                </div>

                <div class="row m-n2">
                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-header align-items-center">
                                <div class="fw-medium py-1">{{ __('Referrers') }}</div>
                            </div>
                            <div class="card-body">
                                @if(count($referrers) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n4">
                                        <div class="list-group-item px-0 text-muted">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    {{ __('Website') }}
                                                </div>
                                                <div class="col-auto">
                                                    {{ __('Clicks') }}
                                                </div>
                                            </div>
                                        </div>

                                        @foreach($referrers as $referrer)
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            @if($referrer->value)
                                                                <div class="d-flex align-items-center me-2">
                                                                    <img src="{{ faviconUrl($referrer->value) }}" rel="noreferrer" class="w-4 h-4" alt="">
                                                                </div>

                                                                <div class="d-flex text-truncate">
                                                                    <div class="text-truncate" dir="ltr">{{ $referrer->value }}</div>
                                                                    <a href="http://{{ $referrer->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center ms-2">@include('icons.open-in-new', ['class' => 'fill-current w-3 h-3'])</a>
                                                                </div>
                                                            @else
                                                                <div class="d-flex align-items-center me-2">
                                                                    <img src="{{ asset('img/icons/referrers/unknown.svg') }}" rel="noreferrer" class="w-4 h-4" alt="">
                                                                </div>

                                                                <div class="text-truncate">
                                                                    {{ __('Direct, Email, SMS') }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div class="d-flex align-items-baseline ms-4 text-end">
                                                            <div>
                                                                {{ number_format($referrer->count, 0, __('.'), __(',')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress h-1.25 w-full">
                                                        <div class="progress-bar rounded" role="progressbar" style="width: {{ (($referrer->count / $totalClicks) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($referrers) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('stats.referrers', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-header align-items-center">
                                <div class="fw-medium py-1">{{ __('Countries') }}</div>
                            </div>
                            <div class="card-body">
                                @if(count($countries) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n4">
                                        <div class="list-group-item px-0 text-muted">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    {{ __('Name') }}
                                                </div>
                                                <div class="col-auto">
                                                    {{ __('Clicks') }}
                                                </div>
                                            </div>
                                        </div>

                                        @foreach($countries as $country)
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center me-2">
                                                                <img src="{{ asset('img/icons/countries/' . flagIcon($country->value)) }}.svg" class="w-4 h-4" alt="">
                                                            </div>
                                                            <div class="text-truncate">
                                                                @if(!empty(explode(':', $country->value)[1]))
                                                                    <a href="{{ route('stats.cities', ['id' => $link->id, 'search' => explode(':', $country->value)[0].':', 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-body" data-tooltip="true" title="{{ __(explode(':', $country->value)[1]) }}">{{ explode(':', $country->value)[1] }}</a>
                                                                @else
                                                                    {{ __('Unknown') }}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline ms-4 text-end">
                                                            <div>
                                                                {{ number_format($country->count, 0, __('.'), __(',')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress h-1.25 w-full">
                                                        <div class="progress-bar rounded" role="progressbar" style="width: {{ (($country->count / $totalClicks) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($countries) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('stats.countries', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-header align-items-center">
                                <div class="fw-medium py-1">{{ __('Browsers') }}</div>
                            </div>
                            <div class="card-body">
                                @if(count($browsers) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n4">
                                        <div class="list-group-item px-0 text-muted">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    {{ __('Name') }}
                                                </div>
                                                <div class="col-auto">
                                                    {{ __('Clicks') }}
                                                </div>
                                            </div>
                                        </div>

                                        @foreach($browsers as $browser)
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center me-2">
                                                                <img src="{{ asset('img/icons/browsers/' . browserIcon($browser->value)) }}.svg" class="w-4 h-4" alt="">
                                                            </div>
                                                            <div class="text-truncate">
                                                                @if($browser->value)
                                                                    {{ $browser->value }}
                                                                @else
                                                                    {{ __('Unknown') }}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline ms-4 text-end">
                                                            <div>
                                                                {{ number_format($browser->count, 0, __('.'), __(',')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress h-1.25 w-full">
                                                        <div class="progress-bar rounded" role="progressbar" style="width: {{ (($browser->count / $totalClicks) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($browsers) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('stats.browsers', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-header align-items-center">
                                <div class="fw-medium py-1">{{ __('Operating systems') }}</div>
                            </div>
                            <div class="card-body">
                                @if(count($operatingSystems) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n4">
                                        <div class="list-group-item px-0 text-muted">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    {{ __('Name') }}
                                                </div>
                                                <div class="col-auto">
                                                    {{ __('Clicks') }}
                                                </div>
                                            </div>
                                        </div>

                                        @foreach($operatingSystems as $operatingSystem)
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex text-truncate align-items-center">
                                                            <div class="d-flex align-items-center me-2">
                                                                <img src="{{ asset('img/icons/os/' . operatingSystemIcon($operatingSystem->value)) }}.svg" class="w-4 h-4" alt="">
                                                            </div>
                                                            <div class="text-truncate">
                                                                @if($operatingSystem->value)
                                                                    {{ $operatingSystem->value }}
                                                                @else
                                                                    {{ __('Unknown') }}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="d-flex align-items-baseline ms-4 text-end">
                                                            <div>
                                                                {{ number_format($operatingSystem->count, 0, __('.'), __(',')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="progress h-1.25 w-full">
                                                        <div class="progress-bar rounded" role="progressbar" style="width: {{ (($operatingSystem->count / $totalClicks) * 100) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($operatingSystems) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('stats.operating-systems', ['id' => $link->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body my-4 py-4">
                            @if(enabledPaymentProcessors())
                                @if(Auth::check() && $link->user->id == Auth::user()->id)
                                    @include('shared.features.locked')
                                @else
                                    @include('shared.features.unavailable')
                                @endif
                            @else
                                @include('shared.features.unavailable')
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mt-4 small text-muted">
                <div class="col">
                    {{ __('Report generated on :date at :time (UTC :offset).', ['date' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('H:i:s'), 'offset' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->getOffsetString()]) }} <a href="{{ Request::fullUrl() }}" class="text-inverse">{{ __('Refresh report') }}</a>
                </div>
            </div>

            @if (config('settings.ad_stats_footer'))
                @if(!Auth::check() || !Auth::user()->active_plan->features->no_ads)
                    <div class="mt-4 d-print-none">{!! config('settings.ad_stats_footer') !!}</div>
                @endif
            @endif

            @include('shared.modals.share-link')
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
