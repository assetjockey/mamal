@extends('layouts.app')

@section('site_title', formatTitle([__('Plans'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Plans')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Plans') }}</h1>
                </div>
                <div class="col-auto px-2">
                    <a href="{{ route('admin.plans.new') }}" class="btn btn-primary">{{ __('New') }}</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="row mx-n2">
                        <div class="col px-2">
                            <div class="fw-medium py-1">{{ __('Plans') }}</div>
                        </div>
                        <div class="col-auto px-2">
                            <form method="GET" action="{{ route('admin.plans') }}">
                                <div class="input-group input-group-sm">
                                    <input class="form-control max-w-32 max-w-sm-full" name="search" value="{{ request()->input('search') }}" placeholder="{{ __('Search') }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow w-64 p-0" id="search-filters">
                                            <div class="dropdown-header py-4">
                                                <div class="row">
                                                    <div class="col"><div class="fw-medium m-0 text-body">{{ __('Filters') }}</div></div>
                                                    <div class="col-auto"><a href="{{ route('admin.plans') }}" class="text-secondary">{{ __('Reset') }}</a></div>
                                                </div>
                                            </div>

                                            <div class="dropdown-divider my-0"></div>

                                            <div class="max-h-96 overflow-auto pt-4">
                                                <div class="form-group px-6">
                                                    <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                    <select name="search_by" id="i-search-by" class="custom-select custom-select-sm rounded-sm">
                                                        @foreach(['name' => __('Name')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-visibility" class="small">{{ __('Visibility') }}</label>
                                                    <select name="visibility" id="i-visibility" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach([1 => __('Public'), 0 => __('Unlisted')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('visibility') == $key && request()->input('visibility') !== null) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-status" class="small">{{ __('Status') }}</label>
                                                    <select name="status" id="i-status" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach([0 => __('Active'), 1 => __('Disabled')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('status') == $key && request()->input('status') !== null) selected @endif>{{ $value }}</option>
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
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if(count($plans) == 0)
                        {{ __('No results found.') }}
                    @else
                        <div class="list-group list-group-flush my-n4">
                            <div class="list-group-item px-0 text-muted">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-12 col-lg-5">{{ __('Name') }}</div>
                                            <div class="col-12 col-lg-5">{{ __('Visibility') }}</div>
                                            <div class="col-12 col-lg-2">{{ __('Status') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="invisible btn btn-ghost-primary btn-sm d-flex align-items-center">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</div>
                                    </div>
                                </div>
                            </div>

                            @foreach($plans as $plan)
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col text-truncate">
                                            <div class="row text-truncate">
                                                <div class="col-12 col-lg-5 d-flex align-items-center text-truncate">
                                                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="text-truncate">{{ $plan->name }}</a>
                                                    @if($plan->isDefault())
                                                        <span class="badge badge-secondary ms-2">{{ __('Default') }}</span>
                                                    @endif
                                                </div>

                                                <div class="col-12 col-lg-5 d-flex align-items-center">
                                                    <span class="badge badge-{{ ($plan->visibility ? 'success' : 'secondary') }} text-truncate">{{ ($plan->visibility ? __('Public') : __('Unlisted')) }}</span>
                                                </div>

                                                <div class="col-12 col-lg-2 d-flex align-items-center">
                                                    <span class="badge badge-{{ ($plan->trashed() ? 'danger' : 'success') }} text-truncate">{{ ($plan->trashed() ? __('Disabled') : __('Active')) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                            @include('admin.plans.partials.context-menu')
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4 align-items-center">
                                <div class="row">
                                    <div class="col">
                                        <div class="mt-2 mb-4">{{ __('Showing :from-:to of :total', ['from' => $plans->firstItem(), 'to' => $plans->lastItem(), 'total' => $plans->total()]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        {{ $plans->onEachSide(1)->links() }}
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
