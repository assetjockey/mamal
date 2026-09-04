@extends('layouts.app')

@section('site_title', formatTitle([__('Pixels'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Pixels')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Pixels') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="row mx-n2">
                        <div class="col px-2">
                            <div class="fw-medium py-1">{{ __('Pixels') }}</div>
                        </div>
                        <div class="col-auto px-2">
                            <div class="row mx-n1">
                                <div class="col px-1">
                                    <form method="GET" action="{{ route('admin.pixels') }}" class="d-md-flex">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control max-w-32 max-w-sm-full" name="search" placeholder="{{ __('Search') }}" value="{{ request()->input('search') }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                                <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow w-64 p-0" id="search-filters">
                                                    <div class="dropdown-header py-4">
                                                        <div class="row">
                                                            <div class="col"><div class="fw-medium m-0 text-body">{{ __('Filters') }}</div></div>
                                                            <div class="col-auto"><a href="{{ route('admin.pixels') }}" class="text-secondary">{{ __('Reset') }}</a></div>
                                                        </div>
                                                    </div>

                                                    <div class="dropdown-divider my-0"></div>

                                                    <div class="max-h-96 overflow-auto pt-4">
                                                        <div class="form-group px-6">
                                                            <div class="text-truncate d-block">
                                                                <label for="i-user-id" class="small">{{ __('User') }}</label>
                                                                @if ($user) <a href="{{ route('admin.users.edit', ['id' => $user->id]) }}" class="small text-truncate">{{ $user->name }}</a> @endif
                                                            </div>
                                                            <input type="text" name="user_id" class="form-control form-control-sm" id="i-user-id" value="{{ request()->input('user_id') }}" placeholder="{{ __('ID') }}">
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <div class="text-truncate d-block">
                                                                <label for="i-link-id" class="small">{{ __('Link') }}</label>
                                                                @if ($link) <a href="{{ route('admin.links.edit', ['id' => $link->id]) }}" class="small text-truncate" dir="ltr">{{ $link->displayShortUrl }}</a> @endif
                                                            </div>
                                                            <input type="text" name="link_id" class="form-control form-control-sm" id="i-link-id" value="{{ request()->input('link_id') }}" placeholder="{{ __('ID') }}">
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                            <select name="search_by" id="i-search-by" class="custom-select custom-select-sm rounded-sm">
                                                                @foreach(['name' => __('Name')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <label for="i-type" class="small">{{ __('Type') }}</label>
                                                            <select name="type" id="i-type" class="custom-select custom-select-sm rounded-sm">
                                                                <option value="">{{ __('All') }}</option>
                                                                @foreach(config('pixels') as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('type') == $key && request()->input('type') !== null) selected @endif>{{ $value['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                            <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm rounded-sm">
                                                                @foreach(['id' => __('Date created'), 'name' => __('Name')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('sort_by') == $key) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <label for="i-sort" class="small">{{ __('Sort') }}</label>
                                                            <select name="sort" id="i-sort" class="custom-select custom-select-sm rounded-sm">
                                                                @foreach(['desc' => __('Descending'), 'asc' => __('Ascending')] as $key => $value)
                                                                    <option value="{{ $key }}" @if(request()->input('sort') == $key) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="form-group px-6">
                                                            <label for="i-per-page" class="small">{{ __('Results per page') }}</label>
                                                            <select name="per_page" id="i-per-page" class="custom-select custom-select-sm rounded-sm">
                                                                @foreach([10, 25, 50, 100] as $value)
                                                                    <option value="{{ $value }}" @if(request()->input('per_page') == $value || request()->input('per_page') == null && $value == config('settings.paginate')) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="dropdown-divider my-0"></div>

                                                    <div class="px-6 py-4">
                                                        <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Search') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-auto px-1 d-none" id="bulk-actions-container">
                                    <div class="btn-group" role="group" aria-label="{{ __('Bulk actions') }}">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-expanded="false" id="bulk-dropdown">{{ __('Actions') }} @include('icons.expand-more', ['class' => 'fill-current w-3 h-3 ms-2'])</button>
                                            <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
                                                <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('admin.pixels.destroy', 0) }}" data-action-original="{{ route('admin.pixels.destroy', '__placeholder__') }}" data-button-class="btn btn-danger position-relative" data-button-name="bulk" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" data-text-original="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" id="bulk-delete">@include('icons.delete', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Delete') }}</a>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Close') }}" id="bulk-close">@include('icons.close', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                    </div>
                                </div>
                                <div class="col-auto px-1" id="bulk-open-container">
                                    <button class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Bulk actions') }}" id="bulk-open">@include('icons.list', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if(count($pixels) == 0)
                        {{ __('No results found.') }}
                    @else
                        <div class="list-group list-group-flush my-n4">
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
                                                        {{ __('Name') }}
                                                    </div>

                                                    <div class="col-12 col-lg-5 d-flex">
                                                        {{ __('User') }}
                                                    </div>

                                                    <div class="col-12 col-lg-2 d-flex">
                                                        {{ __('Links') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="invisible btn btn-ghost-primary btn-sm d-flex align-items-center">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @foreach($pixels as $pixel)
                                <div class="list-group-item px-0">
                                    <div class="row">
                                        <div class="d-none col-auto align-items-center" data-bulk-checkbox-column>
                                            <div class="custom-control custom-checkbox" data-bulk-check>
                                                <input type="checkbox" class="custom-control-input" id="bulk-check-{{ $pixel->id }}" name="bulk[]" value="{{ $pixel->id }}" data-bulk-checkbox>
                                                <label class="custom-control-label user-select-none" for="bulk-check-{{ $pixel->id }}"></label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="row align-items-center">
                                                <div class="col text-truncate">
                                                    <div class="row align-items-center">
                                                        <div class="col-12 col-lg-5 d-flex">
                                                            <div class="text-truncate">
                                                                <div class="d-flex">
                                                                    <div class="d-flex align-items-center text-truncate">
                                                                        <img src="{{ asset('img/icons/pixels/' . md5(strtolower($pixel->type))) }}.svg" rel="noreferrer" class="w-4 h-4 me-4" alt="">
                                                                        <div class="text-truncate"><a href="{{ route('admin.pixels.edit', $pixel->id) }}">{{ $pixel->name }}</a></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12 col-lg-5 d-flex align-items-center">
                                                            <div class="d-inline-block me-4">
                                                                <img src="{{ $pixel->user->avatarUrl }}" class="rounded-circle w-6 h-6" alt="">
                                                            </div>
                                                            <a href="{{ route('admin.users.edit', $pixel->user->id) }}">{{ $pixel->user->name }}</a>
                                                        </div>

                                                        <div class="col-12 col-lg-2 d-flex">
                                                            <a href="{{ route('admin.links', ['pixel_id' => $pixel->id]) }}" class="text-inverse">{{ $pixel->totalLinksCount }}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                    @include('pixels.partials.context-menu')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4 align-items-center">
                                <div class="row">
                                    <div class="col">
                                        <div class="mt-2 mb-4">{{ __('Showing :from-:to of :total', ['from' => $pixels->firstItem(), 'to' => $pixels->lastItem(), 'total' => $pixels->total()]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        {{ $pixels->onEachSide(1)->links() }}
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
