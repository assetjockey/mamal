@section('site_title', formatTitle([$website->domain, __('Overview'), config('settings.title')]))

<div class="card border-0 rounded-top shadow-sm mb-3 overflow-hidden" id="trend-chart-container">
    <div class="px-3 border-bottom">
        <div class="row">
            <!-- Visitors -->
            <div class="col-12 col-md-6 border-bottom border-bottom-md-0 {{ (__('lang_dir') == 'rtl' ? 'border-left-md' : 'border-right-md')  }}">
                <div class="px-2 py-4">
                    <div class="d-flex">
                        <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                            <div class="d-flex align-items-center text-truncate">
                                <div class="d-flex align-items-center justify-content-center bg-primary rounded width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" id="visitors-legend"></div>

                                <div class="flex-grow-1 d-flex font-weight-bold text-truncate">
                                    <div class="text-truncate">{{ __('Visitors') }}</div>
                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('A visitor represents a page load of your website through direct access, or through a referrer.') }}">
                                        @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                    </div>
                                </div>
                            </div>

                            @include('stats.growth', ['growthCurrent' => $totalVisitors, 'growthPrevious' => $totalVisitorsOld])
                        </div>

                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                            <div class="h2 font-weight-bold mb-0">{{ number_format($totalVisitors, 0, __('.'), __(',')) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pageviews -->
            <div class="col-12 col-md-6">
                <div class="px-2 py-4">
                    <div class="d-flex">
                        <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                            <div class="d-flex align-items-center text-truncate">
                                <div class="d-flex align-items-center justify-content-center bg-danger rounded width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" id="pageviews-legend"></div>

                                <div class="flex-grow-1 d-flex font-weight-bold text-truncate">
                                    <div class="text-truncate">{{ __('Pageviews') }}</div>
                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('A pageview represents a page load of your website.') }}">
                                        @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                    </div>
                                </div>
                            </div>

                            @include('stats.growth', ['growthCurrent' => $totalPageviews, 'growthPrevious' => $totalPageviewsOld])
                        </div>

                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                            <div class="h2 font-weight-bold mb-0">{{ number_format($totalPageviews, 0, __('.'), __(',')) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="height-58">
            <canvas id="trend-chart"></canvas>
        </div>
        <script>
            'use strict';

            window.addEventListener("DOMContentLoaded", function () {
                Chart.defaults.font = {
                    family: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'",
                    size: 12
                };

                const phBgColor = window.getComputedStyle(document.getElementById('trend-chart-container')).getPropertyValue('background-color');
                const visitorsColor = window.getComputedStyle(document.getElementById('visitors-legend')).getPropertyValue('background-color');
                const pageViewsColor = window.getComputedStyle(document.getElementById('pageviews-legend')).getPropertyValue('background-color');

                const ctx = document.querySelector('#trend-chart').getContext('2d');
                const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
                gradient1.addColorStop(0, visitorsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                gradient1.addColorStop(1, visitorsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
                gradient2.addColorStop(0, pageViewsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                gradient2.addColorStop(1, pageViewsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                let tooltipTitles = [
                    @foreach($visitorsMap as $date => $value)
                        @if($range['unit'] == 'hour')
                            '{{ \Carbon\Carbon::createFromFormat($range['format'], $date)->format(__('H:i')) }}',
                        @elseif($range['unit'] == 'day')
                            '{{ \Carbon\Carbon::createFromFormat($range['format'], $date)->format(__('Y-m-d')) }}',
                        @elseif($range['unit'] == 'month')
                            '{{ \Carbon\Carbon::createFromFormat($range['format'], $date)->format(__('Y-m')) }}',
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
                            @foreach($pageviewsMap as $date => $value)
                                @if($range['unit'] == 'hour')
                                    '{{ \Carbon\Carbon::createFromFormat($range['format'], $date)->format(__('H:i')) }}',
                                @elseif($range['unit'] == 'day')
                                    '{{ __(':month :day', ['month' => mb_substr(__(\Carbon\Carbon::parse($date)->format('F')), 0, 3), 'day' => __(\Carbon\Carbon::parse($date)->format('j'))]) }}',
                                @elseif($range['unit'] == 'month')
                                    '{{ __(':year :month', ['year' => \Carbon\Carbon::parse($date)->format('Y'), 'month' => mb_substr(__(\Carbon\Carbon::parse($date)->format('F')), 0, 3)]) }}',
                                @else
                                    '{{ $date }}',
                                @endif
                            @endforeach
                        ],
                        datasets: [{
                            label: '{{ __('Visitors') }}',
                            data: [
                                @foreach($visitorsMap as $date => $value)
                                    {{ $value }},
                                @endforeach
                            ],
                            fill: true,
                            backgroundColor: gradient1,
                            borderColor: visitorsColor,
                            pointBorderColor: visitorsColor,
                            pointBackgroundColor: visitorsColor,
                            pointHoverBackgroundColor: phBgColor,
                            pointHoverBorderColor: visitorsColor,
                            ...lineOptions
                        }, {
                            label: '{{ __('Pageviews') }}',
                            data: [
                                @foreach($pageviewsMap as $date => $value)
                                    {{ $value }},
                                @endforeach
                            ],
                            fill: true,
                            backgroundColor: gradient2,
                            borderColor: pageViewsColor,
                            pointBorderColor: pageViewsColor,
                            pointBackgroundColor: pageViewsColor,
                            pointHoverBackgroundColor: phBgColor,
                            pointHoverBorderColor: pageViewsColor,
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

                                cornerRadius: 4,
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
                                    maxTicksLimit: @if($range['unit'] == 'day') 12 @else 15 @endif,
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
                const observer = (new MutationObserver(function (mutationsList, observer) {
                    for (const mutation of mutationsList) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            setTimeout(function () {
                                const phBgColor = window.getComputedStyle(document.getElementById('trend-chart-container')).getPropertyValue('background-color');
                                const visitorsColor = window.getComputedStyle(document.getElementById('visitors-legend')).getPropertyValue('background-color');
                                const pageViewsColor = window.getComputedStyle(document.getElementById('pageviews-legend')).getPropertyValue('background-color');

                                const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
                                gradient1.addColorStop(0, visitorsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                gradient1.addColorStop(1, visitorsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
                                gradient2.addColorStop(0, pageViewsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                gradient2.addColorStop(1, pageViewsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                trendChart.data.datasets[0].backgroundColor = gradient1;
                                trendChart.data.datasets[0].borderColor = visitorsColor;
                                trendChart.data.datasets[0].pointBorderColor = visitorsColor;
                                trendChart.data.datasets[0].pointBackgroundColor = visitorsColor;
                                trendChart.data.datasets[0].pointHoverBackgroundColor = phBgColor;
                                trendChart.data.datasets[0].pointHoverBorderColor = visitorsColor;

                                trendChart.data.datasets[1].backgroundColor = gradient2;
                                trendChart.data.datasets[1].borderColor = pageViewsColor;
                                trendChart.data.datasets[1].pointBorderColor = pageViewsColor;
                                trendChart.data.datasets[1].pointBackgroundColor = pageViewsColor;
                                trendChart.data.datasets[1].pointHoverBackgroundColor = phBgColor;
                                trendChart.data.datasets[1].pointHoverBorderColor = pageViewsColor;

                                trendChart.options.plugins.tooltip.backgroundColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#000' : '#FFF');
                                trendChart.options.plugins.tooltip.titleColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                                trendChart.options.plugins.tooltip.bodyColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                                trendChart.update();

                                // Update the color scheme timer to be faster next time it's used
                                colorSchemeTimer = 100;
                            }, colorSchemeTimer);
                        }
                    }
                }));

                observer.observe(document.querySelector('html'), { attributes: true });
            });
        </script>
    </div>
</div>
<div class="row m-n2">
    <div class="col-12 col-lg-6 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Pages') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($pages) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('URL') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Pageviews') }}
                                </div>
                            </div>
                        </div>

                        @foreach($pages as $page)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex text-truncate">
                                                <div class="text-truncate" dir="ltr">{{ $page->value }}</div> <a href="http://{{ $website->domain . $page->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-in-new', ['class' => 'fill-current width-3 height-3'])</a>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($page->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-1.25 w-100">
                                        <div class="progress-bar bg-danger rounded" role="progressbar" style="width: {{ (($page->count / $totalPageviews) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($pages) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.pages', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-6 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Referrers') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($referrers) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Website') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($referrers as $referrer)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            @if($referrer->value)
                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                    <img src="{{ favicon($referrer->value) }}" rel="noreferrer" class="width-4 height-4" alt="{{ $referrer->value }}">
                                                </div>

                                                <div class="d-flex text-truncate">
                                                    <div class="text-truncate" dir="ltr">{{ $referrer->value }}</div> <a href="http://{{ $referrer->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-in-new', ['class' => 'fill-current width-3 height-3'])</a>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                    <img src="{{ asset('img/icons/referrers/unknown.svg') }}" rel="noreferrer" class="width-4 height-4" alt="{{ __('Unknown') }}">
                                                </div>

                                                <div class="text-truncate">
                                                    {{ __('Direct, Email, SMS') }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($referrer->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-1.25 w-100">
                                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ (($referrer->count / $totalReferrers['count']) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($referrers) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.referrers', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Countries') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($countries) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($countries as $country)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('img/icons/countries/'. formatFlag($country->value)) }}.svg" class="width-4 height-4" alt="{{ (!empty(explode(':', $country->value)[1]) ? explode(':', $country->value)[1] : __('Unknown')) }}"></div>
                                            <div class="text-truncate">
                                                @if(!empty(explode(':', $country->value)[1]))
                                                    <a href="{{ route('stats.cities', ['id' => $website->domain, 'search' => explode(':', $country->value)[0].':', 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-body" data-tooltip="true" title="{{ __(explode(':', $country->value)[1]) }}">{{ explode(':', $country->value)[1] }}</a>
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($country->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-1.25 w-100">
                                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ (($country->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($countries) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.countries', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Browsers') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($browsers) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($browsers as $browser)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('img/icons/browsers/'.formatBrowser($browser->value)) }}.svg" class="width-4 height-4" alt="{{ $browser->value ?? __('Unknown') }}"></div>
                                            <div class="text-truncate">
                                                @if($browser->value)
                                                    {{ $browser->value }}
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($browser->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-1.25 w-100">
                                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ (($browser->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($browsers) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.browsers', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-4 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Operating systems') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($operatingSystems) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Visitors') }}
                                </div>
                            </div>
                        </div>

                        @foreach($operatingSystems as $operatingSystem)
                            <div class="list-group-item px-0 border-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"><img src="{{ asset('img/icons/os/'.formatOperatingSystem($operatingSystem->value)) }}.svg" class="width-4 height-4" alt="{{ $operatingSystem->value ?? __('Unknown') }}"></div>
                                            <div class="text-truncate">
                                                @if($operatingSystem->value)
                                                    {{ $operatingSystem->value }}
                                                @else
                                                    {{ __('Unknown') }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div>
                                                {{ number_format($operatingSystem->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress height-1.25 w-100">
                                        <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ (($operatingSystem->count / $totalVisitors) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($operatingSystems) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.operating_systems', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>

    <div class="col-12 col-lg-12 p-2">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Events') }}</div></div>
                </div>
            </div>
            <div class="card-body">
                @if(count($events) == 0)
                    {{ __('No data') }}.
                @else
                    <div class="list-group list-group-flush my-n3">
                        <div class="list-group-item px-0 text-muted">
                            <div class="row align-items-center">
                                <div class="col">
                                    {{ __('Name') }}
                                </div>
                                <div class="col-auto">
                                    {{ __('Completions') }}
                                </div>
                            </div>
                        </div>

                        @foreach($events as $event)
                            <div class="list-group-item px-0">
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex text-truncate align-items-center">
                                            <div class="text-truncate">
                                                {{ explode(':', $event->value)[0] }}
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                            <div class="d-flex align-items-center justify-content-end {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                                @if(!empty(explode(':', $event->value)[1]) || !empty(explode(':', $event->value)[2]))
                                                    <span class="badge badge-secondary {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        @if(!empty(explode(':', $event->value)[1]))
                                                            {{ number_format((explode(':', $event->value)[1] * $event->count), 2, __('.'), __(',')) }}
                                                        @endif

                                                        @if(!empty(explode(':', $event->value)[2]))
                                                            {{ explode(':', $event->value)[2] }}
                                                        @endif
                                                    </span>
                                                @endif

                                                {{ number_format($event->count, 0, __('.'), __(',')) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($events) > 0)
                <div class="card-footer bg-base-2 border-0">
                    <a href="{{ route('stats.events', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                </div>
            @endif
        </div>
    </div>
</div>
