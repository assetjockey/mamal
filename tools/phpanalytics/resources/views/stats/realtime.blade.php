@section('site_title', formatTitle([$website->domain, __('Realtime'), config('settings.title')]))

<div class="card border-0 rounded-top shadow-sm mb-3">
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
                                    {{ __('Visitors') }}
                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('A visitor represents a page load of your website through direct access, or through a referrer.') }}">
                                        @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center text-truncate" id="visitors-growth">
                                —
                            </div>
                        </div>

                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                            <div class="h2 font-weight-bold mb-0" id="visitors-count">0</div>
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
                                    {{ __('Pageviews') }}
                                    <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ __('A pageview represents a page load of your website.') }}">
                                        @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center text-truncate" id="pageviews-growth">
                                —
                            </div>
                        </div>

                        <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                            <div class="h2 font-weight-bold mb-0" id="pageviews-count">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="height-58">
            <canvas id="realtime-chart"></canvas>
        </div>
    </div>
</div>

<div class="d-flex flex-column">
    <div class="card border-0 shadow-sm">
        <div class="card-header align-items-center">
            <div class="row">
                <div class="col"><div class="font-weight-medium py-1">{{ __('Realtime activity') }}</div></div>
                <div class="col-auto"><button type="button" class="btn btn-sm btn-outline-primary" id="pause-button">{{ __('Pause') }}</button></div>
            </div>
        </div>

        <div class="card-body" id="recent">
            {{ __('Loading') }}...
        </div>
    </div>
</div>

<script>
    'use strict';

    let pauseButton = document.querySelector('#pause-button');

    window.addEventListener("DOMContentLoaded", function () {
        Chart.defaults.font = {
            family: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'",
            size: 12
        };

        const uniqueColor = window.getComputedStyle(document.getElementById('visitors-legend')).getPropertyValue('background-color');
        const pageViewsColor = window.getComputedStyle(document.getElementById('pageviews-legend')).getPropertyValue('background-color');

        const ctx = document.querySelector('#realtime-chart').getContext('2d');

        let range = (start, end) => {
            let foo = [];
            for (var i = end; i >= start; i--) {
                if (i === 0) {
                    foo.push('' + i + ' {{ mb_substr(mb_strtolower(__('Seconds')), 0, 3) }}');
                } else {
                    foo.push('-' + i + ' {{ mb_substr(mb_strtolower(__('Seconds')), 0, 3) }}');
                }
            }
            return foo;
        };

        let tooltipTitles = [
            ...range(0, 60)
        ];

        window.realtimeChart = new Chart(ctx, {
            type: 'bar',

            data: {
                labels: [
                    ...range(0, 60)
                ],
                datasets: [{
                    label: '{{ __('Visitors') }}',
                    data: [],
                    backgroundColor : uniqueColor,
                    borderColor: uniqueColor,
                    xAxisID: "xA",
                }, {
                    label: '{{ __('Pageviews') }}',
                    data: [],
                    backgroundColor : pageViewsColor,
                    borderColor: pageViewsColor,
                    xAxisID: "xB",
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
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

                        cornerRadius: 4,
                        caretSize: 7,

                        boxPadding: 4,

                        callbacks: {
                            label: function (tooltipItem, data) {
                                return ' ' + tooltipItem.dataset.label + ': ' + parseFloat(tooltipItem.dataset.data[tooltipItem.dataIndex]).format(0, 3, '{{ __(',') }}').toString();
                            },
                            title: function (tooltipItem) {
                                return tooltipTitles[tooltipItem[0].dataIndex];
                            }
                        }
                    },
                },
                scales: {
                    xA: {
                        display: false,
                        stacked: true
                    },
                    xB: {
                        display: true,
                        offset: true,
                        stacked: true,
                        grid: {
                            lineWidth: 0,
                            tickLength: 0
                        },
                        ticks: {
                            maxTicksLimit: 13,
                            padding: 10,
                        }
                    },
                    y: {
                        display: true,
                        stacked: false,
                        beginAtZero: true,
                        grid: {
                            lineWidth: 1,
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

        let realTime = () => {
            // Cancel future requests
            if(pauseButton.classList.contains('active')) {
                return;
            }

            let requestPromise = fetch('{{ route('stats.realtime', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}', {
                    headers: {
                        "Accept" : "application/json, text/javascript; charset=utf-8",
                        "Content-Type": "application/json, text/javascript; charset=utf-8"
                    }
                })
                .then(res => res.json())
                .then(response => {
                    let visitorsCount = 0;
                    let index = 0;
                    for (const count in response.visitors) {
                        let time = count.split(' ');

                        if (isNaN(response.visitors[count]) === false) {
                            // Add the unique users
                            visitorsCount += Number(response.visitors[count]);
                        } else {
                            // Set the chart value
                            visitorsCount += 0;
                        }

                        // Set the chart value
                        realtimeChart.data.datasets[0].data[index] = isNaN(response.visitors[count]) ? 0 : response.visitors[count];
                        tooltipTitles[index] = count;

                        index++;
                    }

                    let pageviewsCount = 0;
                    index = 0;
                    for (const count in response.pageviews) {
                        if (isNaN(response.pageviews[count]) === false) {
                            // Add the unique users
                            pageviewsCount += Number(response.pageviews[count]);
                        } else {
                            // Set the chart value
                            pageviewsCount += 0;
                        }

                        // Set the chart value
                        realtimeChart.data.datasets[1].data[index] = isNaN(response.pageviews[count]) ? 0 : response.pageviews[count];
                        tooltipTitles[index] = count;

                        index++;
                    }

                    realtimeChart.update();

                    visitorsCount = visitorsCount.format(0, 3, '{{ __(',') }}').toString();
                    pageviewsCount = pageviewsCount.format(0, 3, '{{ __(',') }}').toString();

                    let targets = [
                        ['#recent', response.recent],
                        ['#visitors-count', visitorsCount],
                        ['#pageviews-count', pageviewsCount],
                        ['#visitors-growth', response.visitors_growth],
                        ['#pageviews-growth', response.pageviews_growth]
                    ];

                    targets.forEach(function (element) {
                        if (document.querySelector(element[0]).innerHTML !== element[1]) {
                            document.querySelector(element[0]).classList.add('d-none');
                            document.querySelector(element[0]).innerHTML = element[1];
                            document.querySelector(element[0]).classList.remove('d-none');
                        }
                    });
                })
                .catch(err => {
                    console.log(err);
                });

            // This promise will resolve when the delay has ended
            let timeOutPromise = new Promise(function (resolve, reject) {
                // Set the delay
                setTimeout(resolve, 2000);
            });

            // Check if all promises are resolved
            Promise.all([requestPromise, timeOutPromise])
                .then(function (values) {
                    realTime();
                });
        }

        realTime();

        pauseButton.addEventListener('click', function () {
            // Disable the pause state
            if (pauseButton.classList.contains('active')) {

                pauseButton.classList.remove('active');
                pauseButton.textContent = '{{ __('Pause') }}';

                realTime();
            }
            // Enable the pause state
            else {
                pauseButton.classList.add('active');
                pauseButton.textContent = '{{ __('Paused') }}';
            }
        });

        // The time to wait before attempting to change the colors on first attempt
        let colorSchemeTimer = 500;

        // Update the chart colors when the color scheme changes
        const observer = (new MutationObserver(function (mutationsList, observer) {
            for (const mutation of mutationsList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    // Set a small timeout to allow for the DOM
                    setTimeout(function () {
                        const visitorsColor = window.getComputedStyle(document.getElementById('visitors-legend')).getPropertyValue('background-color');
                        const pageViewsColor = window.getComputedStyle(document.getElementById('pageviews-legend')).getPropertyValue('background-color');

                        realtimeChart.data.datasets[0].backgroundColor = visitorsColor;
                        realtimeChart.data.datasets[0].borderColor = visitorsColor;

                        realtimeChart.data.datasets[1].backgroundColor = pageViewsColor;
                        realtimeChart.data.datasets[1].borderColor = pageViewsColor;

                        realtimeChart.options.plugins.tooltip.backgroundColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#000' : '#FFF');
                        realtimeChart.options.plugins.tooltip.titleColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                        realtimeChart.options.plugins.tooltip.bodyColor = (document.querySelector('html').classList.contains('dark') == 0 ? '#FFF' : '#000');
                        realtimeChart.update();

                        // Update the color scheme timer to be faster next time it's used
                        colorSchemeTimer = 100;
                    }, colorSchemeTimer);
                }
            }
        }));

        observer.observe(document.querySelector('html'), { attributes: true });
    });
</script>
