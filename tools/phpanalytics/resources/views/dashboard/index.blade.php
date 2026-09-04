@extends('layouts.app')

@section('site_title', formatTitle([__('Dashboard'), config('settings.title')]))

@section('content')
<div class="bg-base-1 flex-fill">
    <div class="bg-base-0">
        <div class="container py-5">
            <div class="row m-n2">
                <div class="d-flex col-12 col-md p-2">
                    <div class="flex-shrink-1">
                        <a href="{{ route('account') }}" class="d-block"><img src="{{ Auth::user()->avatarUrl }}" class="rounded-circle width-16 height-16"></a>
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                        <div>
                            <h4 class="font-weight-medium mb-0">{{ Auth::user()->name }}</h4>

                            <div class="d-flex flex-wrap">
                                @if(paymentProcessors())
                                    <div class="d-inline-block mt-2 {{ (__('lang_dir') == 'rtl' ? 'ml-4' : 'mr-4') }}">
                                        <div class="d-flex">
                                            <div class="d-inline-flex align-items-center">
                                                @include('icons.package', ['class' => 'text-muted fill-current width-4 height-4'])
                                            </div>

                                            <div class="d-inline-block {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                <a href="{{ route('account.plan') }}" class="text-dark text-decoration-none">{{ Auth::user()->active_plan->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-inline-block mt-2 {{ (__('lang_dir') == 'rtl' ? 'ml-4' : 'mr-4') }}">
                                        <div class="d-flex">
                                            <div class="d-inline-flex align-items-center">
                                                @include('icons.email', ['class' => 'text-muted fill-current width-4 height-4'])
                                            </div>

                                            <div class="d-inline-block {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                {{ Auth::user()->email }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto d-md-flex align-items-center p-2">
                    <div class="row m-n2">
                        @if(paymentProcessors())
                            <div class="col-12 col-md-auto d-flex align-items-center p-2">
                                @if(Auth::user()->planIsDefault())
                                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.unarchive', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Upgrade') }}</a>
                                @else
                                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.package', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Plans') }}</a>
                                @endif
                            </div>
                        @endif

                        <div class="col-12 col-md-auto d-flex align-items-center p-2">
                            <a href="{{ route('websites.new') }}" class="btn btn-primary btn-block d-flex justify-content-center align-items-center">@include('icons.add', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('New website') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-1">
        <div class="container pt-5 pb-6">
            <div class="row">
                <div class="col-12 col-lg">
                    <h4 class="mb-0">{{ __('Websites') }}</h4>
                </div>
                <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                    <ul class="nav nav-pills small">
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => $now->format('Y-m-d'), 'to' => $now->format('Y-m-d'), 'search' => request()->input('search'), 'search_by' => request()->input('search_by'), 'favorite' => request()->input('favorite'), 'sort_by' => request()->input('sort_by'), 'sort' => request()->input('sort'), 'per_page' => request()->input('per_page')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->isToday()) active @endif" href="#">{{ __('Today') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => (clone $now)->startOfMonth()->format('Y-m-d'), 'to' => (clone $now)->endOfMonth()->format('Y-m-d'), 'search' => request()->input('search'), 'search_by' => request()->input('search_by'), 'favorite' => request()->input('favorite'), 'sort_by' => request()->input('sort_by'), 'sort' => request()->input('sort'), 'per_page' => request()->input('per_page')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format('Y-m-d') == (clone $now)->startOfMonth()->format('Y-m-d') && \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format('Y-m-d') == (clone $now)->endOfMonth()->format('Y-m-d')) active @endif" href="#">{{ __('This month') }}</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('dashboard', ['from' => Auth::user()->created_at->format('Y-m-d'), 'to' => $now->format('Y-m-d'), 'search' => request()->input('search'), 'search_by' => request()->input('search_by'), 'favorite' => request()->input('favorite'), 'sort_by' => request()->input('sort_by'), 'sort' => request()->input('sort'), 'per_page' => request()->input('per_page')]) }}" class="nav-link py-1 px-2 @if(\Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format('Y-m-d') == Auth::user()->created_at->format('Y-m-d') && \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format('Y-m-d') == $now->format('Y-m-d')) active @endif" href="#">{{ __('All time') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mt-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <div class="row">
                                <div class="col"><div class="font-weight-medium py-1">{{ __('Websites') }}</div></div>
                                <div class="col-auto">
                                    <div class="form-row">
                                        <div class="col">
                                            <form method="GET" action="{{ route('dashboard') }}">
                                                <input name="from" type="hidden" value="{{ $range['from'] }}">
                                                <input name="to" type="hidden" value="{{ $range['to'] }}">
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control" name="search" placeholder="{{ __('Search') }}" value="{{ app('request')->input('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current width-4 height-4'])&#8203;</button>
                                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow width-64 p-0" id="search-filters">
                                                            <div class="dropdown-header py-3">
                                                                <div class="row">
                                                                    <div class="col"><div class="font-weight-medium m-0 text-body">{{ __('Filters') }}</div></div>
                                                                    <div class="col-auto">
                                                                        @if(request()->input('per_page'))
                                                                            <a href="{{ route('dashboard') }}" class="text-secondary">{{ __('Reset') }}</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="dropdown-divider my-0"></div>

                                                            <div class="max-height-96 overflow-auto pt-3">
                                                                <div class="form-group px-4">
                                                                    <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                                    <select name="search_by" id="i-search-by" class="custom-select custom-select-sm">
                                                                        @foreach(['domain' => __('Domain')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-favorite" class="small">{{ __('Favorite') }}</label>
                                                                    <select name="favorite" id="i-favorite" class="custom-select custom-select-sm">
                                                                        <option value="">{{ __('All') }}</option>
                                                                        @foreach([0 => __('No'), 1 => __('Yes')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('favorite') == $key && request()->input('favorite') !== null) selected @endif>{{ __($value) }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-4">
                                                                    <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                                    <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm">
                                                                        @foreach(['id' => __('Date created'), 'domain' => __('Domain')] as $key => $value)
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
                                                                        @foreach([10] as $value)
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
                                                    <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('websites.destroy', 0) }}" data-action-original="{{ route('websites.destroy', 'id') }}" data-button-class="btn btn-danger position-relative" data-button-name="bulk" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" data-text-original="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" id="bulk-delete">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
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

                            @if(count($websites) == 0)
                                {{ __('No data') }}.
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
                                                <div class="row d-flex align-items-center">
                                                    <div class="col">
                                                        <div class="row align-items-center">
                                                            <div class="col-12 col-lg-4 text-truncate">
                                                                {{ __('Domain') }}
                                                            </div>

                                                            <div class="col-12 col-lg-4 text-truncate">
                                                                {{ __('Visitors') }}
                                                            </div>

                                                            <div class="col-12 col-lg-4 text-truncate">
                                                                {{ __('Pageviews') }}
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

                                    @foreach($websites as $website)
                                        <div class="list-group-item px-0">
                                            <div class="row">
                                                <div class="d-none col-auto align-items-center" data-bulk-checkbox-column>
                                                    <div class="custom-control custom-checkbox" data-bulk-check>
                                                        <input type="checkbox" class="custom-control-input" id="bulk-check-{{ $website->id }}" name="bulk[]" value="{{ $website->id }}" data-bulk-checkbox>
                                                        <label class="custom-control-label user-select-none" for="bulk-check-{{ $website->id }}"></label>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="row d-flex align-items-center">
                                                        <div class="col text-truncate">
                                                            <div class="row text-truncate">
                                                                <div class="col-12 col-lg-4 d-flex align-items-center text-truncate">
                                                                    <img src="{{ favicon($website->domain) }}" rel="noreferrer" class="width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}"> <div class="text-truncate" dir="ltr"><a href="{{ route('stats.overview', ['id' => $website->domain, 'from' => $range['from'], 'to' => $range['to']]) }}">{{ $website->domain }}</a></div>

                                                                    @if($website->favorited_at) <div class="d-flex flex-shrink-0 width-4 height-4 text-warning {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('Favorite') }}">@include('icons.star', ['class' => 'fill-current width-4 height-4 flex-shrink-0'])</div> @endif
                                                                </div>

                                                                <div class="col-12 col-lg-4 d-flex align-items-center font-weight-medium">
                                                                    <div class="d-flex align-items-center text-truncate">
                                                                        <div class="d-flex align-items-center justify-content-center bg-primary rounded width-4 height-4 flex-shrink-0 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>
                                                                        <div class="text-truncate">{{ number_format($website->visitors_count ?? 0, 0, __('.'), __(',')) }}</div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-lg-4 d-flex align-items-center font-weight-medium">
                                                                    <div class="d-flex align-items-center text-truncate">
                                                                        <div class="d-flex align-items-center justify-content-center bg-danger rounded width-4 height-4 flex-shrink-0 text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"></div>
                                                                        <div class="text-truncate">{{ number_format($website->pageviews_count ?? 0, 0, __('.'), __(',')) }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <div class="form-row">
                                                                <div class="col">
                                                                    @include('websites.partials.menu')
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
                                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $websites->firstItem(), 'to' => $websites->lastItem(), 'total' => $websites->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $websites->onEachSide(1)->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')
