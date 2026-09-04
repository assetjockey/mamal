@extends('layouts.app')

@section('site_title', formatTitle([__('Links'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => route('dashboard'), 'title' => __('Home')],
                        ['title' => __('Links')],
                    ]])

                    <div class="row mx-n2">
                        <div class="col px-2 text-truncate">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Links') }}</h1>
                        </div>
                    </div>

                    @include('links.partials.new')

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header align-items-center">
                            <div class="row mx-n2">
                                <div class="col px-2">
                                    <div class="fw-medium py-1">{{ __('Links') }}</div>
                                </div>
                                <div class="col-auto px-2">
                                    <div class="row mx-n1">
                                        <div class="col px-1">
                                            <form method="GET" action="{{ route('links') }}" class="d-md-flex">
                                                <div class="input-group input-group-sm">
                                                    <input class="form-control max-w-32 max-w-sm-full" name="search" placeholder="{{ __('Search') }}" value="{{ request()->input('search') }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow w-64 p-0" id="search-filters">
                                                            <div class="dropdown-header py-4">
                                                                <div class="row">
                                                                    <div class="col"><div class="fw-medium m-0 text-body">{{ __('Filters') }}</div></div>
                                                                    <div class="col-auto">
                                                                        @if(request()->input('per_page'))
                                                                            <a href="{{ route('links') }}" class="text-secondary">{{ __('Reset') }}</a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="dropdown-divider my-0"></div>

                                                            <div class="max-h-96 overflow-auto pt-4">
                                                                <div class="form-group px-6">
                                                                    <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                                    <select name="search_by" id="i-search-by" class="custom-select custom-select-sm rounded-sm">
                                                                        @foreach(['title' => __('Title'), 'alias' => __('Alias'), 'url' => __('URL')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-6">
                                                                    <label for="i-domain-id" class="small">{{ __('Domain') }}</label>
                                                                    <select name="domain_id" id="i-domain-id" class="custom-select custom-select-sm rounded-sm">
                                                                        <option value="">{{ __('All') }}</option>
                                                                        @foreach($domains as $domain)
                                                                            <option value="{{ $domain->id }}" @if(request()->input('domain_id') == $domain->id && request()->input('domain_id') !== null) selected @endif>{{ $domain->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-6">
                                                                    <label for="i-space-id" class="small">{{ __('Space') }}</label>
                                                                    <select name="space_id" id="i-space-id" class="custom-select custom-select-sm rounded-sm">
                                                                        <option value="">{{ __('All') }}</option>
                                                                        <option value="0" @if(request()->input('space_id') == 0 && request()->input('space_id') !== null) selected @endif>{{ __('None') }}</option>
                                                                        @foreach($spaces as $space)
                                                                            <option value="{{ $space->id }}" @if(request()->input('space_id') == $space->id && request()->input('space_id') !== null) selected @endif>{{ $space->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-6">
                                                                    <label for="i-pixel-id" class="small">{{ __('Pixel') }}</label>
                                                                    <select name="pixel_id" id="i-pixel-id" class="custom-select custom-select-sm rounded-sm">
                                                                        <option value="">{{ __('All') }}</option>
                                                                        @foreach($pixels as $pixel)
                                                                            <option value="{{ $pixel->id }}" @if(request()->input('pixel_id') == $pixel->id && request()->input('pixel_id') !== null) selected @endif>{{ $pixel->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-6">
                                                                    <label for="i-status" class="small">{{ __('Status') }}</label>
                                                                    <select name="status" id="i-status" class="custom-select custom-select-sm rounded-sm">
                                                                        @foreach([0 => __('All'), 1 => __('Active'), 2 => __('Expired')] as $key => $value)
                                                                            <option value="{{ $key }}" @if(request()->input('status') == $key && request()->input('status') !== null) selected @endif>{{ $value }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="form-group px-6">
                                                                    <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                                    <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm rounded-sm">
                                                                        @foreach(['id' => __('Date created'), 'clicks_count' => __('Clicks'), 'title' => __('Title'), 'alias' => __('Alias'), 'url' => __('URL')] as $key => $value)
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
                                                        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('links.destroy', 0) }}" data-action-original="{{ route('links.destroy', '__placeholder__') }}" data-button-class="btn btn-danger position-relative" data-button-name="bulk" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" data-text-original="{{ __('Are you sure you want to delete :count records?', ['count' => 0]) }}" id="bulk-delete">@include('icons.delete', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Delete') }}</a>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Close') }}" id="bulk-close">@include('icons.close', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                            </div>
                                        </div>
                                        <div class="col-auto px-1" id="bulk-open-container">
                                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Bulk actions') }}" id="bulk-open">@include('icons.list', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                        </div>
                                        <div class="col-auto px-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-toggle="modal" data-target="#export-modal" data-tooltip="true" title="{{ __('Export') }}">@include('icons.file-download', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(count($links) == 0)
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
                                                            <div class="col-12 col-md-8 col-lg-6 d-flex text-truncate">
                                                                {{ __('URL') }}
                                                            </div>

                                                            <div class="d-none d-md-block col-md-4 col-lg-2 text-truncate">
                                                                {{ __('Space') }}
                                                            </div>

                                                            <div class="d-none d-lg-block col-lg-2 text-truncate">
                                                                {{ __('Clicks') }}
                                                            </div>

                                                            <div class="d-none d-lg-block col-lg-2 text-truncate">
                                                                {{ __('Created at') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="row mx-n1">
                                                            <div class="col px-1">
                                                                <button type="button" class="invisible btn btn-ghost-primary btn-sm d-flex align-items-center">@include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                                            </div>
                                                            <div class="col px-1">
                                                                <div class="invisible btn btn-ghost-primary btn-sm d-flex align-items-center">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach($links as $link)
                                        <div class="list-group-item px-0">
                                            <div class="row">
                                                <div class="d-none col-auto align-items-center" data-bulk-checkbox-column>
                                                    <div class="custom-control custom-checkbox" data-bulk-check>
                                                        <input type="checkbox" class="custom-control-input" id="bulk-check-{{ $link->id }}" name="bulk[]" value="{{ $link->id }}" data-bulk-checkbox>
                                                        <label class="custom-control-label user-select-none" for="bulk-check-{{ $link->id }}"></label>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="row align-items-center">
                                                        <div class="col text-truncate">
                                                            <div class="row align-items-center">
                                                                <div class="col-12 col-md-8 col-lg-6 d-flex text-truncate">
                                                                    <div class="text-truncate">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4 me-4" alt="">

                                                                            <div class="text-truncate">
                                                                                <a href="{{ route('stats.overview', $link->id) }}" dir="ltr">{{ $link->displayShortUrl }}</a>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex align-items-center">
                                                                            <div class="w-4 flex-shrink-0 me-4"></div>
                                                                            <div class="text-muted text-truncate small cursor-help" data-tooltip="true" data-html="true" title='@include('links.partials.tooltip')'>
                                                                                <span dir="ltr">{{ $link->displayUrl }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-none d-md-flex align-items-center col-md-4 col-lg-2">
                                                                    @if(isset($link->space->name))
                                                                        <a href="{{ route('links', ['space_id' => $link->space->id]) }}" class="badge {{ match ((int) $link->space->color) { 1 => 'badge-success', 2 => 'badge-danger', 3 => 'badge-warning', 4 => 'badge-info', 5 => 'badge-inverse', 6 => 'badge-primary' } }} text-truncate">{{ $link->space->name }}</a>
                                                                    @else
                                                                        <div class="badge badge-secondary text-truncate">{{ __('None') }}</div>
                                                                    @endif
                                                                </div>

                                                                <div class="d-none d-lg-block col-lg-2 text-truncate">
                                                                    <a href="{{ route('stats.overview', $link->id) }}" dir="ltr" class="text-inverse text-truncate">{{ number_format($link->clicks_count, 0, __('.'), __(',')) }}</a>
                                                                </div>

                                                                <div class="d-none d-lg-flex col-lg-2 text-truncate">
                                                                    <span class="text-truncate" data-tooltip="true" title="{{ $link->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}">{{ $link->created_at->diffForHumans() }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <div class="row mx-n1">
                                                                <div class="col px-1">
                                                                    @include('links.partials.copy-link-button', ['class' => 'btn-ghost-primary btn-sm'])
                                                                </div>
                                                                <div class="col px-1">
                                                                    <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                                    @include('links.partials.context-menu')
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-4 align-items-center">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mt-2 mb-4">{{ __('Showing :from-:to of :total', ['from' => $links->firstItem(), 'to' => $links->lastItem(), 'total' => $links->total()]) }}
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                {{ $links->onEachSide(1)->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal fade" id="export-modal" tabindex="-1" role="dialog" aria-labelledby="export-modal-label" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="dialog">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h6 class="modal-title" id="export-modal-label">{{ __('Export') }}</h6>
                                    <button type="button" class="close d-flex align-items-center justify-content-center w-12 h-14" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current w-4 h-4'])</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    @can('dataExport', [App\Models\User::class])
                                        {{ __('Are you sure you want to export this table?') }}
                                    @else
                                        @if(enabledPaymentProcessors())
                                            @include('shared.features.locked')
                                        @else
                                            @include('shared.features.unavailable')
                                        @endif
                                    @endcan
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                                    @can('dataExport', [App\Models\User::class])
                                        <a href="{{ route('links.export', Request::query()) }}" target="_self" class="btn btn-primary" id="exportButton" rel="nofollow">{{ __('Export') }}</a>
                                    @endcan
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
    @include('shared.modals.share-link')
@endsection

@include('shared.sidebars.user')
