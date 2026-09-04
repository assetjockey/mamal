@extends('layouts.app')

@section('site_title', formatTitle([__('Monitors'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Monitors')],
            ]])

            <div class="d-flex">
                <div class="flex-grow-1">
                    <h1 class="h2 mb-0 d-inline-block">{{ __('Monitors') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header align-items-center">
                    <div class="row">
                        <div class="col-12 col-md">
                            <div class="font-weight-medium py-1">{{ __('Monitors') }}</div>
                        </div>
                        <div class="col-12 col-md-auto">
                            <div class="form-row">
                                <div class="col">
                                    <form method="GET" action="{{ route('admin.monitors') }}" class="d-md-flex">
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
                                                                <a href="{{ route('admin.monitors') }}" class="text-secondary">{{ __('Reset') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="dropdown-divider my-0"></div>

                                                    <div class="max-height-96 overflow-auto pt-3">
                                                        <div class="form-group px-4">
                                                            <div class="text-truncate d-block">
                                                                <label for="i-user-id" class="small">{{ __('User') }}</label>
                                                                @if ($user)
                                                                    <a href="{{ route('admin.users.edit', ['id' => $user->id]) }}" class="small text-truncate">{{ $user->name }}</a>
                                                                @endif
                                                            </div>
                                                            <input type="text" name="user_id" class="form-control form-control-sm" id="i-user-id" value="{{ request()->input('user_id') }}" placeholder="{{ __('ID') }}">
                                                        </div>

                                                        <div class="form-group px-4">
                                                            <div class="text-truncate d-block">
                                                                <label for="i-status-page-id" class="small">{{ __('Status page') }}</label>
                                                                @if ($statusPage)
                                                                    <a href="{{ route('admin.status_pages.edit', ['id' => $statusPage->id]) }}" class="small text-truncate">{{ $statusPage->name }}</a>
                                                                @endif
                                                            </div>
                                                            <input type="text" name="status_page_id" class="form-control form-control-sm" id="i-status-page-id" value="{{ request()->input('status_page_id') }}" placeholder="{{ __('ID') }}">
                                                        </div>

                                                        <div class="form-group px-4">
                                                            <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                            <select name="search_by" id="i-search-by" class="custom-select custom-select-sm">
                                                                @foreach(['title' => __('Title'), 'alias' => __('Alias'), 'url' => __('URL')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-4">
                                                            <label for="i-status" class="small">{{ __('Status') }}</label>
                                                            <select name="status" id="i-status" class="custom-select custom-select-sm">
                                                                @foreach([0 => __('All'), 1 => __('Active'), 2 => __('Expired'), 3 => __('Disabled')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('status') == $key && request()->input('status') !== null) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-4">
                                                            <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                            <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm">
                                                                @foreach(['id' => __('Date created'), 'clicks' => __('Clicks'), 'title' => __('Title'), 'alias' => __('Alias'), 'url' => __('URL')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('sort_by') == $key) selected @endif>{{ $value }}</option>
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
                                <div class="col-auto d-none" id="bulk-actions-container">
                                    <div class="btn-group" role="group" aria-label="{{ __('Bulk actions') }}">
                                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-toggle="dropdown" aria-expanded="false" id="bulk-dropdown">{{ __('Actions') }} @include('icons.expand-more', ['class' => 'fill-current width-3 height-3 ' . (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</button>
                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
                                            <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('admin.monitors.destroy', 0) }}" data-action-original="{{ route('admin.monitors.destroy', 'id') }}" data-button-class="btn btn-danger position-relative" data-button-name="bulk" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" data-text-original="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" id="bulk-delete">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Close') }}" id="bulk-close">@include('icons.close', ['class' => 'fill-current width-4 height-4'])&#8203;</button>
                                    </div>
                                </div>
                                <div class="col-auto" id="bulk-open-container">
                                    <button class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Bulk actions') }}" id="bulk-open">@include('icons.list', ['class' => 'fill-current width-4 height-4'])&#8203;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if(count($monitors) == 0)
                        {{ __('No results found.') }}
                    @else
                        <div class="list-group list-group-flush my-n3">
                            <div class="list-group-item px-0 text-muted">
                                <div class="row">
                                    <div class="d-none col-auto align-items-center" data-bulk-checkbox-column>
                                        <div class="custom-control custom-checkbox" data-bulk-check>
                                            <input type="checkbox" class="custom-control-input" id="bulk-check-all" value="true">
                                            <label class="custom-control-label user-select-none" for="bulk-check-all"></label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="row align-items-center">
                                                    <div class="col-12 col-lg-5 d-flex">
                                                        {{ __('URL') }}
                                                    </div>

                                                    <div class="col-12 col-lg-5 d-flex">
                                                        {{ __('User') }}
                                                    </div>

                                                    <div class="col-12 col-lg-2 d-flex">
                                                        {{ __('Interval') }}
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
                                </div>
                            </div>

                            @foreach($monitors as $monitor)
                                <div class="list-group-item px-0">
                                    <div class="row">
                                        <div class="d-none col-auto align-items-center" data-bulk-checkbox-column>
                                            <div class="custom-control custom-checkbox" data-bulk-check>
                                                <input type="checkbox" class="custom-control-input" id="bulk-check-{{ $monitor->id }}" name="bulk[]" value="{{ $monitor->id }}" data-bulk-checkbox>
                                                <label class="custom-control-label user-select-none" for="bulk-check-{{ $monitor->id }}"></label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="row align-items-center">
                                                <div class="col text-truncate">
                                                    <div class="row">
                                                        <div class="col-12 col-lg-5 d-flex">
                                                            <div class="text-truncate">
                                                                <div class="d-flex align-items-center text-truncate">
                                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.http-status-tooltip')'>
                                                                        <div class="d-flex align-items-center">
                                                                            @include('icons.status', ['class' => 'width-4 height-4 fill-current text-' . monitorStatusColor($monitor->status)])&#8203;
                                                                        </div>
                                                                    </div>

                                                                    <a href="{{ route('admin.monitors.edit', ['id' => $monitor->id]) }}" class="text-truncate">{{ $monitor->name }}</a>

                                                                    @if ($monitor->ssl_alert_days && parse_url($monitor->url, PHP_URL_SCHEME) == 'https' && $monitor->user->can('sslMonitoring', [App\Models\User::class]))
                                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.ssl-status-tooltip')'>
                                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-' . monitorSslStatusColor($monitor)])&#8203;
                                                                        </div>
                                                                    @endif

                                                                    @if ($monitor->domain_alert_days && Auth::user()->can('domainMonitoring', [App\Models\User::class]))
                                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.domain-status-tooltip')'>
                                                                            @include('icons.website', ['class' => 'fill-current width-4 height-4 text-' . monitorDomainStatusColor($monitor)])&#8203;
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-5 d-flex align-items-center">
                                                            <div class="d-inline-block {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                                                <img src="{{ $monitor->user->avatarUrl }}" class="rounded-circle width-6 height-6">
                                                            </div>

                                                            <a href="{{ route('admin.users.edit', $monitor->user->id) }}">{{ $monitor->user->name }}</a>
                                                        </div>

                                                        <div class="col-12 col-lg-2 d-flex">
                                                            {{ Carbon\CarbonInterval::seconds($monitor->interval)->cascade()->forHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-row">
                                                        <div class="col">
                                                            @include('monitors.partials.context-menu')
                                                        </div>
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
                                        <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $monitors->firstItem(), 'to' => $monitors->lastItem(), 'total' => $monitors->total()]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        {{ $monitors->onEachSide(1)->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
