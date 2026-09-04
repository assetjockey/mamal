@extends('layouts.app')

@section('site_title', formatTitle([$monitor->name, __('Checks'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('monitors.partials.header')

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Checks') }}</div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-row">
                                        <div class="col">
                                            <form method="GET" action="{{ route(Route::currentRouteName(), ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control" name="search" placeholder="{{ __('Search') }}" value="{{ app('request')->input('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current width-4 height-4'])
                                                            &#8203;
                                                        </button>
                                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow width-64 p-0" id="search-filters">
                                                            <div class="dropdown-header py-3">
                                                                <div class="row">
                                                                    <div class="col">
                                                                        <div class="font-weight-medium m-0 text-body">{{ __('Filters') }}</div>
                                                                    </div>
                                                                    <div class="col-auto">
                                                                        <a href="{{ route('incidents') }}" class="text-secondary">{{ __('Reset') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <input name="from" type="hidden" value="{{ $dateRange['from'] }}">
                                                            <input name="to" type="hidden" value="{{ $dateRange['to'] }}">
                                                            <input name="token" type="hidden" value="{{ (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token) }}">

                                                            <div class="dropdown-divider my-0"></div>

                                                            <div class="max-height-96 overflow-auto pt-3">
                                                                <div class="form-group px-4">
                                                                    <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                                    <select name="search_by" id="i-search-by" class="custom-select custom-select-sm">
                                                                        @foreach(['response_status_code' => __('Response status code')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || request()->input('search_by') == null && $key == 'monitor') selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                                    <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm">
                                                                        @foreach(['checked_at' => __('Date checked')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('sort_by') == $key || request()->input('sort_by') == null && $key == 'ended_at') selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-sort" class="small">{{ __('Sort') }}</label>
                                                                    <select name="sort" id="i-sort" class="custom-select custom-select-sm">
                                                                        @foreach(['desc' => __('Descending'), 'asc' => __('Ascending')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('sort') == $key) selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-per-page" class="small">{{ __('Results per page') }}</label>
                                                                    <select name="per_page" id="i-per-page" class="custom-select custom-select-sm">
                                                                        @foreach([10, 25, 50, 100] as $value)
                                                                            <option value="{{ $value }}" @if(request()->input('per_page') == $value || request()->input('per_page') == null && $value == config('settings.paginate')) selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="dropdown-divider my-0"></div>

                                                            <div class="px-4 py-3">
                                                                <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Search') }}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(count($checks) == 0)
                                {{ __('No results found.') }}
                            @else
                                <div class="list-group list-group-flush my-n3">
                                    <div class="list-group-item px-0 text-muted">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="row align-items-center">
                                                    <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                        {{ __('Location') }}
                                                    </div>

                                                    <div class="col col-lg-3 text-truncate">
                                                        {{ __('Response status code') }}
                                                    </div>

                                                    <div class="col-auto col-lg-3 text-truncate">
                                                        {{ __('Response time') }}
                                                    </div>

                                                    <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                        {{ __('Checked at') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-none d-lg-block col-auto">
                                                <div class="form-row">
                                                    <div class="col">
                                                        <div class="invisible btn d-flex align-items-center btn-sm text-primary">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])
                                                            &#8203;
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($checks as $check)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col text-truncate">
                                                    <div class="row align-items-center">
                                                        <div class="d-none col-lg-3 d-lg-flex align-items-center text-truncate">
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                                    <img src="{{ asset('img/icons/countries/'. flagIcon($check->country)) }}.svg"
                                                                         class="width-4 height-4">
                                                                </div>
                                                                <span class="text-truncate" data-tooltip="true" title="@if(!empty(explode(':', $check->country)[1])) {{ __(explode(':', $check->country)[1]) }}@if(!empty(explode(':', $check->city)[1])), {{ __(explode(':', $check->city)[1]) }}@endif @else {{ __('Unknown') }} @endif">
                                                                    @if(!empty(explode(':', $check->country)[1]))
                                                                        {{ explode(':', $check->country)[1] }}@if(!empty(explode(':', $check->city)[1]))
                                                                            , {{ explode(':', $check->city)[1] }}
                                                                        @endif
                                                                    @else
                                                                        {{ __('Unknown') }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="col col-lg-3 d-flex align-items-center">
                                                            <div class="d-flex d-lg-none align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                                                <img src="{{ asset('img/icons/countries/'. flagIcon($check->country)) }}.svg"
                                                                     class="width-4 height-4" data-tooltip="true"
                                                                     title="@if(!empty(explode(':', $check->country)[1])) {{ __(explode(':', $check->country)[1]) }}@if(!empty(explode(':', $check->city)[1])), {{ __(explode(':', $check->city)[1]) }}@endif @else {{ __('Unknown') }} @endif">
                                                            </div>
                                                            <span class="badge {{ (($check->response_status_code >= 200 && $check->response_status_code <= 299) ? 'badge-success' : 'badge-danger') }}">{{ $check->response_status_code }}</span>
                                                        </div>

                                                        <div class="col-auto col-lg-3">
                                                            {{ ($check->response_time ? number_format(($check->response_time / 1000), 0, __('.'), __(',')) : 0) }}
                                                            ms
                                                        </div>

                                                        <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                            <span class="text-truncate" data-tooltip="true" title="{{ $check->checked_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}">{{ $check->checked_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-none d-lg-block col-auto">
                                                    <div class="form-row">
                                                        <div class="col">
                                                            <div class="invisible btn d-flex align-items-center btn-sm text-primary">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])
                                                                &#8203;
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-3 align-items-center">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $checks->firstItem(), 'to' => $checks->lastItem(), 'total' => $checks->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $checks->onEachSide(1)->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
