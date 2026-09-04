@extends('layouts.app')

@section('site_title', formatTitle([__('Tools'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @if(config('settings.tools_guest') && !Auth::check())
                        <div class="text-center mt-3 mb-5">
                            <h1 class="h2 my-3 d-inline-block">{{ __('Tools') }}</h1>
                            <div class="m-auto">
                                <p class="text-muted font-weight-normal font-size-lg mb-0">{{ __('Web tools and utilities.') }}</p>
                            </div>
                        </div>
                    @else
                        @include('shared.breadcrumbs', ['breadcrumbs' => [
                            ['url' => route('dashboard'), 'title' => __('Home')],
                            ['title' => __('Tools')],
                        ]])

                        <div class="d-flex align-items-end">
                            <h1 class="h2 mb-3 flex-grow-1 text-truncate">{{ __('Tools') }}</h1>
                        </div>
                    @endif

                    <div class="row no-gutters bg-base-0 rounded shadow-sm mb-3 overflow-hidden" id="tool-filters">
                        <div class="col-12">
                            <div class="border-bottom px-3 py-3">
                                @include('shared.message')

                                <form enctype="multipart/form-data" autocomplete="off" id="form-tools-search" onsubmit="event.preventDefault();" class="{{ (__('lang_dir') == 'rtl' ? 'ml-1' : 'mr-1') }}">
                                    @csrf

                                    <div class="input-group input-group-lg">
                                        <input type="text" name="search" class="form-control font-size-lg" autocapitalize="none" spellcheck="false" id="i-search" placeholder="{{ __('Search') }}" autofocus>
                                    </div>

                                    <div class="input-group-append border-left-0 d-none" id="clear-button-container">
                                        <button type="button" class="btn text-secondary bg-transparent input-group-text d-flex align-items-center" id="b-clear">
                                            @include('icons.close', ['class' => 'fill-current width-4 height-4'])
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col">
                            <div class="d-flex flex-column flex-md-row justify-content-around w-100">
                                <a href="#" class="text-truncate text-decoration-none text-primary d-flex align-items-center justify-content-md-center font-weight-medium py-3 px-3 cursor-pointer w-100" data-filter-category="" data-text-color-active="text-primary" data-text-color-inactive="text-secondary" data-filter-category-active>
                                    <span class="text-truncate">{{ __('All') }}</span>

                                    <span class="flex-shrink-0 badge badge-primary {{ (__('lang_dir' == 'rtl') ? 'mr-2' : 'ml-2') }}">{{ $tools->count() }}</span>
                                </a>

                                @foreach($tools as $tool)
                                    @if(!isset($menuCategoryName) || isset($menuCategoryName) && $menuCategoryName != $tool->category->name)
                                        <div class="border-left-0 border-left-md border-bottom border-bottom-md-0"></div>
                                        <a href="#" class="text-truncate text-decoration-none text-secondary d-flex align-items-center justify-content-md-center font-weight-medium py-3 px-3 cursor-pointer w-100" data-filter-category="{{ __($tool->category->id) }}" data-text-color-active="text-{{ categoryColor($tool->category->id) }}" data-text-color-inactive="text-secondary">
                                            <span class="text-truncate">{{ __($tool->category->name) }}</span>

                                            <span class="flex-shrink-0 badge badge-{{ categoryColor($tool->category->id) }} {{ (__('lang_dir' == 'rtl') ? 'mr-2' : 'ml-2') }}">{{ $tools->countBy('category_id')->toArray()[$tool->category_id] }}</span>
                                        </a>
                                    @endif

                                    @php $menuCategoryName = $tool->category->name; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row m-n2" id="tools">
                        @foreach($tools as $tool)
                            @if(!isset($categoryName) || isset($categoryName) && $categoryName != $tool->category->name)
                                <div class="col-12 p-2 mt-3" data-category-label="{{ __($tool->category->id) }}">
                                    <div class="badge badge-{{ categoryColor($tool->category->id) }}">{{ __($tool->category->name) }}</div>
                                </div>
                            @endif

                            <div class="col-12 col-md-6 col-lg-4 p-2" data-tool="{{ __($tool->name) }} {{ __($tool->description) }}" data-tool-parent="{{ __($tool->category->name) }}" data-tool-category="{{ __($tool->category->id) }}">
                                <div class="card border-0 h-100 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="row">
                                            <div class="col">
                                                <div class="d-flex position-relative text-{{ categoryColor($tool->category_id) }} width-10 height-10 align-items-center justify-content-center flex-shrink-0">
                                                    <div class="position-absolute bg-{{ categoryColor($tool->category_id) }} opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                                    @include('icons.' . $tool->icon, ['class' => 'fill-current width-5 height-5'])
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ $tool->url }}" class="text-dark font-weight-bold stretched-link text-decoration-none text-truncate mt-3 mb-1">{{ __($tool->name) }}</a>

                                        <div class="text-muted">{{ __($tool->description) }}</div>
                                    </div>
                                </div>
                            </div>

                            @php $categoryName = $tool->category->name; @endphp
                        @endforeach
                    </div>

                    @if ($tools->hasPages())
                        <div class="mt-3 align-items-center">
                            <div class="row">
                                <div class="col-auto">
                                    {{ $tools->onEachSide(1)->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@include('shared.sidebars.user')
