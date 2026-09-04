@extends('layouts.app')

@section('site_title', formatTitle([$link->alias, __('Countries'), __('Stats'), config('settings.title')]))

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
                <div class="d-flex flex-column">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row mx-n2">
                                <div class="col px-2">
                                    <div class="fw-medium py-1">{{ __('Countries') }}</div>
                                </div>
                                <div class="col-auto px-2">
                                    <div class="row mx-n1">
                                        <div class="col-auto px-1">
                                            @include('stats.partials.table-search', ['name' => __('Name'), 'count' => __('Clicks')])
                                        </div>

                                        <div class="col-auto px-1">
                                            @include('stats.partials.table-export')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($countries) == 0)
                                {{ __('No data') }}.
                            @else
                                <div id="world-map-chart"></div>
                                <script>
                                    'use strict';

                                    window.addEventListener("DOMContentLoaded", function () {
                                        new svgMap({
                                            targetElementID: 'world-map-chart',
                                            data: {
                                                data: {
                                                    country: {
                                                        name: '',
                                                        format: '{0}'
                                                    },
                                                    clicks: {
                                                        name: '',
                                                        format: '{0} <span class="text-lowercase">{{ __('Clicks') }}</span>',
                                                        thousandSeparator: '{{ __(',') }}'
                                                    }
                                                },
                                                applyData: 'clicks',
                                                values: {
                                                    @foreach($countriesChart as $country)
                                                    '{{ (explode(':', $country->value)[0]) ?? '' }}': {clicks: {{ $country->count }}, country: '{{ (explode(':', $country->value)[1]) ?? '' }}'},
                                                    @endforeach
                                                }
                                            },
                                            colorMin: '#b8b8ff',
                                            colorMax: '#313164',
                                            hideFlag: true,
                                            noDataText: '{{ __('No data') }}'
                                        });
                                    });
                                </script>

                                <div class="list-group list-group-flush mb-n4 mt-4">
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

                                    <div class="list-group-item px-0 small text-muted">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex text-truncate align-items-center">
                                                    <div class="text-truncate">
                                                        {{ __('Total') }}
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-baseline ms-4 text-end">
                                                    <span>{{ number_format($total->count, 0, __('.'), __(',')) }}</span>

                                                    <div class="w-16 text-muted ms-4">
                                                        {{ number_format((($total->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($countries as $country)
                                        <div class="list-group-item px-0 border-0">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="d-flex text-truncate align-items-center">
                                                        <div class="d-flex align-items-center me-2"><img src="{{ asset('img/icons/countries/' . flagIcon($country->value)) }}.svg" class="w-4 h-4" alt="">
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
                                                        <span>{{ number_format($country->count, 0, __('.'), __(',')) }}</span>

                                                        <div class="w-16 text-muted ms-4">
                                                            {{ number_format((($country->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="progress h-1.25 w-full">
                                                    <div class="progress-bar rounded" role="progressbar" style="width: {{ (($country->count / $total->count) * 100) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-4 align-items-center">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mt-2 mb-4">{{ __('Showing :from-:to of :total', ['from' => $countries->firstItem(), 'to' => $countries->lastItem(), 'total' => $countries->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $countries->onEachSide(1)->links() }}
                                            </div>
                                        </div>
                                    </div>
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
