@extends('layouts.app')

@section('site_title', formatTitle([$monitor->name, __('Monitor'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('monitors.partials.header')

                    <div class="row m-n2">
                        <div class="col-12 p-2">
                            <div class="card border-0 rounded-top shadow-sm overflow-hidden" id="monitor-chart-container">
                                <div class="px-3 border-bottom">
                                    <div class="row">
                                        <!-- Uptime -->
                                        <div class="col-12 col-lg-4 border-bottom border-bottom-lg-0 {{ (__('lang_dir') == 'rtl' ? 'border-left-lg' : 'border-right-lg')  }}">
                                            <div class="px-2 py-4">
                                                <div class="d-flex">
                                                    <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        <div class="d-flex align-items-center text-truncate">
                                                            <div class="flex-grow-1 d-flex font-weight-bold text-truncate">
                                                                <div class="text-truncate">{{ __('Uptime') }}</div>
                                                            </div>
                                                        </div>

                                                        @php
                                                            $fromNew = (Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->lt($monitor->created_at) ? $monitor->created_at : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay());

                                                            $toNew = (Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->isSameDay((clone $now)) || Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->gt((clone $now)) ? (clone $now) : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->addDay()->startOfDay());
                                                        @endphp

                                                        <div class="text-muted text-truncate d-flex align-items-center">
                                                            @php
                                                                Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                            @endphp
                                                            <div class="text-truncate">
                                                                {{ addSpacingToTimeUnits($fromNew->diffForHumans((clone $toNew)->subMicroseconds($totalIncidentsDuration), ['syntax' => true, 'parts' => 2, 'join' => true, 'short' => true])) }}
                                                            </div>

                                                            <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ $fromNew->diffForHumans((clone $toNew)->subMicroseconds($totalIncidentsDuration), ['syntax' => true, 'parts' => 3, 'join' => true]) }}">
                                                                @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                                            </div>
                                                            @php
                                                                Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                            @endphp
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                                        <div class="h2 font-weight-bold mb-0 flex-shrink-0">
                                                            {{ formatUptimePercentageNumber((Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->lt($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))) ? $monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->diffInMicroseconds($toNew) : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->diffInMicroseconds($toNew)), $totalIncidentsDuration, 2, __('.'), __(',')) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Response time -->
                                        <div class="col-12 col-lg-4 border-bottom border-bottom-lg-0 {{ (__('lang_dir') == 'rtl' ? 'border-left-lg' : 'border-right-lg')  }}">
                                            <div class="px-2 py-4">
                                                <div class="d-flex">
                                                    <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        <div class="d-flex align-items-center text-truncate">
                                                            <div class="d-flex align-items-center justify-content-center bg-primary rounded width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" id="response-time-legend"></div>

                                                            <div class="font-weight-bold text-truncate">
                                                                {{ __('Response time') }}
                                                            </div>
                                                        </div>

                                                        <div class="text-muted text-truncate">
                                                            {{ __(':number checks', ['number' => number_format($totalChecks, 0, __('.'), __(','))]) }}
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                                        <div class="h2 font-weight-bold mb-0">
                                                            {{ ($totalChecksResponseTime ? number_format((($totalChecksResponseTime / $totalChecks) / 1000), 0, __('.'), __(',')) : '0') }} ms
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Incidents -->
                                        <div class="col-12 col-lg-4">
                                            <div class="px-2 py-4">
                                                <div class="d-flex">
                                                    <div class="text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        <div class="d-flex align-items-center text-truncate">
                                                            <div class="d-flex align-items-center justify-content-center bg-danger rounded width-4 height-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" id="incidents-legend"></div>

                                                            <div class="font-weight-bold text-truncate">
                                                                {{ __('Incidents') }}
                                                            </div>
                                                        </div>

                                                        <div class="text-muted text-truncate d-flex align-items-center">
                                                            @php
                                                                Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                            @endphp
                                                            <div class="text-truncate">
                                                                {{ addSpacingToTimeUnits((clone $now)->diffForHumans((clone $now)->subMicroseconds($totalIncidentsDuration), ['syntax' => true, 'parts' => 2, 'join' => true, 'short' => true])) }}
                                                            </div>

                                                            <div class="flex-shrink-0 d-flex align-items-center mx-2" data-tooltip="true" title="{{ (clone $now)->diffForHumans((clone $now)->subMicroseconds($totalIncidentsDuration), ['syntax' => true, 'parts' => 3, 'join' => true]) }}">
                                                                @include('icons.info', ['class' => 'width-4 height-4 fill-current text-muted'])
                                                            </div>
                                                            @php
                                                                Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                            @endphp
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">
                                                        <div class="h2 font-weight-bold mb-0 flex-shrink-0">
                                                            {{ $totalIncidents ?: '0' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div id="clicks-legend" class="bg-primary"></div>
                                    <div class="height-58">
                                        <canvas id="monitor-chart"></canvas>
                                    </div>

                                    <script>
                                        'use strict';

                                        window.addEventListener("DOMContentLoaded", function () {
                                            Chart.defaults.font = {
                                                family: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'",
                                                size: 12
                                            };

                                            const phBgColor = window.getComputedStyle(document.getElementById('monitor-chart-container')).getPropertyValue('background-color');
                                            const responseTimeColor = window.getComputedStyle(document.getElementById('response-time-legend')).getPropertyValue('background-color');
                                            const incidentsColor = window.getComputedStyle(document.getElementById('incidents-legend')).getPropertyValue('background-color');

                                            const ctx = document.querySelector('#monitor-chart').getContext('2d');
                                            const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
                                            gradient1.addColorStop(0, responseTimeColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                            gradient1.addColorStop(1, responseTimeColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                            const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
                                            gradient2.addColorStop(0, incidentsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                            gradient2.addColorStop(1, incidentsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                            let checks = [
                                                @foreach($checksMap as $date => $value)
                                                        {{ $value }},
                                                @endforeach
                                            ];
                                            let checksString = [
                                                @foreach($checksMap as $date => $value)
                                                    '{{ __(":number checks", ['number' => $value]) }}',
                                                @endforeach
                                            ];

                                            let incidents = [
                                                @foreach($incidentsMap as $date => $value)
                                                        {{ $value }},
                                                @endforeach
                                            ];

                                            let incidentsDuration = [
                                                @foreach($incidentsDurationMap as $date => $value)
                                                    '{{ ($value > 0 ? (clone $now)->diffForHumans((clone $now)->subMicroseconds($value), ['syntax' => true, 'parts' => 2]) : 0) }}',
                                                @endforeach
                                            ];

                                            let tooltipTitles = [
                                                @foreach($checksResponseTimeMap as $date => $value)
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
                                                        @foreach($checksResponseTimeMap as $date => $value)
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
                                                        label: '{{ __('Response time') }}',
                                                        data: [
                                                            @foreach($checksResponseTimeMap as $date => $value)
                                                                    {{ ($value ? number_format((($value / $checksMap[$date])/ 1000), 0, '', '') : 0) }},
                                                            @endforeach
                                                        ],
                                                        fill: true,
                                                        backgroundColor: gradient1,
                                                        borderColor: responseTimeColor,
                                                        pointBorderColor: responseTimeColor,
                                                        pointBackgroundColor: responseTimeColor,
                                                        pointHoverBackgroundColor: phBgColor,
                                                        pointHoverBorderColor: responseTimeColor,
                                                        ...lineOptions
                                                    }, {
                                                        label: '{{ __('Incidents') }}',
                                                        data: [
                                                            @foreach($checksResponseTimeMap as $date => $value)
                                                                    {{ ($value ? number_format((($value / $checksMap[$date])/ 1000), 0, '', '') : 0) }},
                                                            @endforeach
                                                        ],
                                                        fill: true,
                                                        borderColor: incidentsColor,
                                                        pointBorderColor: incidentsColor,
                                                        pointBackgroundColor: incidentsColor,
                                                        pointHoverBackgroundColor: phBgColor,
                                                        pointHoverBorderColor: incidentsColor,
                                                        showLine: false,
                                                        pointRadius: [
                                                            @foreach($incidentsMap as $date => $value)
                                                                    {{ $value > 0 ? '7' : '0' }},
                                                            @endforeach
                                                        ],
                                                        pointHoverRadius: [
                                                            @foreach($incidentsMap as $date => $value)
                                                                    {{ $value > 0 ? '9' : '0' }},
                                                            @endforeach
                                                        ],
                                                        hitRadius: 5,
                                                        pointHoverBorderWidth: 3,
                                                        lineTension: 0,
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
                                                            itemSort: function (a, b) {
                                                                return a.datasetIndex - b.datasetIndex;
                                                            },

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
                                                                    // If the dataset is Response time
                                                                    if (tooltipItem.datasetIndex === 0) {
                                                                        return ' ' + tooltipItem.dataset.label + ': ' + parseFloat(tooltipItem.dataset.data[tooltipItem.dataIndex]).format(0, 3, '{{ __(',') }}').toString() + ' ms' + (checks[tooltipItem.dataIndex] !== 0 ? ' (' + checksString[tooltipItem.dataIndex] + ')' : '');
                                                                    } else {
                                                                        return ' ' + tooltipItem.dataset.label + ': ' + parseFloat(incidents[tooltipItem.dataIndex]).format(0, 3, '{{ __(',') }}').toString() + (incidents[tooltipItem.dataIndex] !== 0 ? ' (' + incidentsDuration[tooltipItem.dataIndex] + ')' : '');
                                                                    }
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
                                            const observer = (new MutationObserver(function (mutationsList, observer) {
                                                for (const mutation of mutationsList) {
                                                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                                        setTimeout(function () {
                                                            const phBgColor = window.getComputedStyle(document.getElementById('monitor-chart-container')).getPropertyValue('background-color');
                                                            const responseTimeColor = window.getComputedStyle(document.getElementById('response-time-legend')).getPropertyValue('background-color');
                                                            const incidentsColor = window.getComputedStyle(document.getElementById('incidents-legend')).getPropertyValue('background-color');

                                                            const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
                                                            gradient1.addColorStop(0, responseTimeColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                                            gradient1.addColorStop(1, responseTimeColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                                            const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
                                                            gradient2.addColorStop(0, incidentsColor.replace('rgb', 'rgba').replace(')', ', 0.35)'));
                                                            gradient2.addColorStop(1, incidentsColor.replace('rgb', 'rgba').replace(')', ', 0.01)'));

                                                            trendChart.data.datasets[0].backgroundColor = gradient1;
                                                            trendChart.data.datasets[0].borderColor = responseTimeColor;
                                                            trendChart.data.datasets[0].pointBorderColor = responseTimeColor;
                                                            trendChart.data.datasets[0].pointBackgroundColor = responseTimeColor;
                                                            trendChart.data.datasets[0].pointHoverBackgroundColor = phBgColor;
                                                            trendChart.data.datasets[0].pointHoverBorderColor = responseTimeColor;

                                                            trendChart.data.datasets[1].borderColor = incidentsColor;
                                                            trendChart.data.datasets[1].pointBorderColor = incidentsColor;
                                                            trendChart.data.datasets[1].pointBackgroundColor = incidentsColor;
                                                            trendChart.data.datasets[1].pointHoverBackgroundColor = phBgColor;
                                                            trendChart.data.datasets[1].pointHoverBorderColor = incidentsColor;

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

                                            let realTime = () => {
                                                let requestPromise = fetch('{{ route('monitors.realtime', ['id' => $monitor->id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}', {
                                                    headers: {
                                                        "Accept": "application/json, text/javascript; charset=utf-8",
                                                        "Content-Type": "application/json, text/javascript; charset=utf-8"
                                                    }
                                                })
                                                    .then(res => res.json())
                                                    .then(response => {
                                                        document.querySelectorAll('[data-monitor-status-container]').forEach(function () {
                                                            element.querySelector('[data-tooltip="true"]').tooltip('dispose');
                                                            element.innerHTML = response.real_time;
                                                            element.querySelector('[data-tooltip="true"]').tooltip({
                                                                animation: true,
                                                                trigger: 'hover',
                                                                boundary: 'window',
                                                                placement: 'bottom'
                                                            });
                                                        });
                                                    })
                                                    .catch(err => {
                                                        console.log(err);
                                                    });

                                                // This promise will resolve when the delay has ended
                                                let timeOutPromise = new Promise(function (resolve, reject) {
                                                    // Set the delay
                                                    setTimeout(resolve, {{ (5 * 1000) }});
                                                });

                                                // Check if all promises are resolved
                                                Promise.all([requestPromise, timeOutPromise])
                                                    .then(function (values) {
                                                        realTime();
                                                    });
                                            }

                                            realTime();

                                            observer.observe(document.querySelector('html'), {attributes: true});
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 p-2">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 col-md">
                                            <div class="font-weight-medium py-1">{{ __('Checks') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if(count($checks) == 0)
                                        {{ __('No data') }}.
                                    @else
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0 text-muted">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        {{ __('Response status code') }}
                                                    </div>
                                                    <div class="col-auto">
                                                        {{ __('Response time') }}
                                                    </div>
                                                </div>
                                            </div>

                                            @foreach($checks as $check)
                                                <div class="list-group-item px-0">
                                                    <div class="row">
                                                        <div class="col text-truncate">
                                                            <div class="d-flex text-truncate align-items-center">
                                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                                                    <img src="{{ asset('img/icons/countries/' . flagIcon($check->country)) }}.svg" class="width-4 height-4" data-tooltip="true" title="@if(!empty(explode(':', $check->country)[1])) {{ __(explode(':', $check->country)[1]) }}@if(!empty(explode(':', $check->city)[1])), {{ __(explode(':', $check->city)[1]) }}@endif @else {{ __('Unknown') }} @endif">
                                                                </div>

                                                                <span class="badge {{ (($check->response_status_code >= 200 && $check->response_status_code <= 299) ? 'badge-success' : 'badge-danger') }}">{{ $check->response_status_code }}</span>

                                                                &#8203;
                                                            </div>
                                                        </div>

                                                        <div class="col-auto">
                                                            <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                                                <div>
                                                                    {{ ($check->response_time ? number_format(($check->response_time / 1000), 0, __('.'), __(',')) : 0) }} ms
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if(count($checks) > 0)
                                    <div class="card-footer bg-base-2 border-0">
                                        <a href="{{ route('monitors.checks', ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-lg-6 p-2">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-12 col-md">
                                            <div class="font-weight-medium py-1">{{ __('Incidents') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if(count($incidents) == 0)
                                        <div class="d-flex align-items-center">
                                            @include('icons.check-circle-filled', ['class' => 'width-4 height-4 fill-current text-success ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])

                                            <div class="text-muted">{{ __('No incidents found.') }}</div>
                                        </div>
                                    @else
                                        <div class="list-group list-group-flush my-n3">
                                            <div class="list-group-item px-0 text-muted">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        {{ __('Cause') }}
                                                    </div>
                                                    <div class="col-auto">
                                                        {{ __('Duration') }}
                                                    </div>
                                                </div>
                                            </div>

                                            @foreach($incidents as $incident)
                                                <div class="list-group-item px-0">
                                                    <div class="row">
                                                        <div class="col text-truncate">
                                                            <div class="d-flex text-truncate align-items-center">
                                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}" data-tooltip="true" data-html="true" title='@include('incidents.partials.tooltip')'>
                                                                    @include('icons.' . ($incident->status == 'resolved' ? 'check-circle-filled' : ($incident->status == 'unresolved' ? 'error-filled' : 'offline-bolt-filled')), ['class' => 'width-4 height-4 fill-current ' . ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning'))])&#8203;
                                                                </div>

                                                                <div class="text-truncate">
                                                                    <a href="{{ route('incidents.show', ['id' => $incident->id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $incident->token)]) }}">{{ ($incident->cause ? __($incident->cause) : __('No data')) }}</a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-auto">
                                                            <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                                                @php
                                                                    Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                                @endphp
                                                                <span class="text-truncate" data-tooltip="true" title="{{ $incident->started_at->diffForHumans($incident->ended_at ?? Carbon\Carbon::now(), ['syntax' => true, 'parts' => 3, 'join' => true]) }}">
                                                                    {{ addSpacingToTimeUnits($incident->started_at->diffForHumans($incident->ended_at ?? Carbon\Carbon::now(), ['syntax' => true, 'parts' => 2, 'join' => true, 'short' => true])) }}
                                                                </span>
                                                                @php
                                                                    Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                                @endphp
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if(count($incidents) > 0)
                                    <div class="card-footer bg-base-2 border-0">
                                        <a href="{{ route('monitors.incidents', ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 small text-muted">
                        <div class="col">
                            {{ __('Report generated on :date at :time (UTC :offset).', ['date' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('H:i:s'), 'offset' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->getOffsetString()]) }}
                            <a href="{{ Request::fullUrl() }}" class="text-dark">{{ __('Refresh report') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
