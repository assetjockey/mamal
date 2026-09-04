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
                                        —
                                    </div>

                                    <div class="d-flex align-items-center ms-auto">
                                        <div class="fs-3xl fw-bold m-0">{{ $link->clicks_count }}</div>
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
                    <div class="alert alert-info mb-0">
                        {{ __('This link has limited stats as it was created without using an account.') }}
                    </div>
                </div>
            </div>

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
