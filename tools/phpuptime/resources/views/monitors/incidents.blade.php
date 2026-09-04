@extends('layouts.app')

@section('site_title', formatTitle([$monitor->name, __('Incidents'), config('settings.title')]))

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
                                    <div class="font-weight-medium py-1">{{ __('Incidents') }}</div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-row">
                                        <div class="col">
                                            <form method="GET" action="{{ route(Route::currentRouteName(), ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to']]) }}">
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control" name="search" placeholder="{{ __('Search') }}" value="{{ app('request')->input('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current width-4 height-4'])&#8203;</button>
                                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow width-64 p-0" id="search-filters">
                                                            <div class="dropdown-header py-3">
                                                                <div class="row">
                                                                    <div class="col">
                                                                        <div class="font-weight-medium m-0 text-body">{{ __('Filters') }}</div>
                                                                    </div>
                                                                    <div class="col-auto">
                                                                        <a href="{{ route(Route::currentRouteName(), ['id' => $monitor->id, 'from' => $dateRange['from'], 'to' => $dateRange['to'], 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}" class="text-secondary">{{ __('Reset') }}</a>
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
                                                                        @foreach(['cause' => __('Cause'), 'comment' => __('Comment')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || request()->input('search_by') == null && $key == 'monitor') selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-status" class="small">{{ __('Status') }}</label>
                                                                    <select name="status" id="i-status" class="custom-select custom-select-sm">
                                                                        <option value="">{{ __('All') }}</option>
                                                                        @foreach(['resolved' => __('Resolved'), 'acknowledged' => __('Acknowledged'), 'unresolved' => __('Unresolved')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('status') == $key && request()->input('status') !== null) selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                                    <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm">
                                                                        @foreach(['started_at' => __('Date started'), 'ended_at' => __('Date ended')] as $key => $value)
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
                                        <div class="col-auto">
                                            <a href="{{ route('monitors.export.incidents', ['id' => $monitor->id] + Request::query()) }}" data-toggle="modal" data-target="#export-modal" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Export') }}">@include('icons.file-download', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(count($incidents) == 0)
                                {{ __('No results found.') }}
                            @else
                                <div class="list-group list-group-flush my-n3">
                                    <div class="list-group-item px-0 text-muted">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="row align-items-center">
                                                    <div class="col-12 col-lg-4 text-truncate">
                                                        {{ __('Cause') }}
                                                    </div>

                                                    <div class="d-none d-lg-block col-lg-2 text-truncate">
                                                        {{ __('Duration') }}
                                                    </div>

                                                    <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                        {{ __('Started at') }}
                                                    </div>

                                                    <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                        {{ __('Ended at') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="form-row">
                                                    <div class="col">
                                                        <div class="invisible btn d-flex align-items-center btn-sm text-primary">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($incidents as $incident)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col text-truncate">
                                                    <div class="row align-items-center">
                                                        <div class="col-12 col-lg-4 d-flex align-items-center text-truncate">
                                                            <div class="text-truncate">
                                                                <div class="d-flex align-items-center text-truncate">
                                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}" data-tooltip="true" data-html="true" title='@include('incidents.partials.tooltip')'>
                                                                        @include('icons.' . ($incident->status == 'resolved' ? 'check-circle-filled' : ($incident->status == 'unresolved' ? 'error-filled' : 'offline-bolt-filled')), ['class' => 'width-4 height-4 fill-current ' . ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning'))])&#8203;
                                                                    </div>

                                                                    <a href="{{ route('incidents.show', ['id' => $incident->id]) }}" class="text-truncate">{{ ($incident->cause ? __($incident->cause) : __('No data')) }}</a>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="d-none d-lg-block col-lg-2">
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

                                                        <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                            <span class="text-truncate" data-tooltip="true" title="{{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}">{{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                                                        </div>

                                                        <div class="d-none d-lg-block col-lg-3 text-truncate">
                                                            @if ($incident->ended_at)
                                                                <span class="text-truncate" data-tooltip="true" title="{{ $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}">{{ $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                                                            @else
                                                                <span class="badge badge-danger">{{ __('Ongoing') }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-row">
                                                        <div class="col">
                                                            @include('incidents.partials.context-menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-3 align-items-center">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $incidents->firstItem(), 'to' => $incidents->lastItem(), 'total' => $incidents->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $incidents->onEachSide(1)->links() }}
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

                    <div class="modal fade" id="export-modal" tabindex="-1" role="dialog" aria-labelledby="export-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="dialog">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="export-modal-label">{{ __('Export') }}</h6>
                                    <button type="button" class="close d-flex align-items-center justify-content-center width-12 height-14" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current width-4 height-4'])</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    @if($monitor->user->can('dataExport', [App\Models\User::class]))
                                        {{ __('Are you sure you want to export this table?') }}
                                    @else
                                        @if(enabledPaymentProcessors())
                                            @if(Auth::check() && $monitor->user->id == Auth::user()->id)
                                                @include('shared.features.locked')
                                            @else
                                                @include('shared.features.unavailable')
                                            @endif
                                        @else
                                            @include('shared.features.unavailable')
                                        @endif
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                    @if($monitor->user->can('dataExport', [App\Models\User::class]))
                                        <a href="{{ route('monitors.export.incidents', ['id' => $monitor->id] + Request::query()) }}" target="_self" class="btn btn-primary" id="exportButton">{{ __('Export') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        'use strict';

                        window.addEventListener('DOMContentLoaded', function () {
                            jQuery('#exportButton').on('click', function () {
                                jQuery('#export-modal').modal('hide');
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
