@section('site_title', formatTitle([$website->domain, __('Tracking code'), config('settings.title')]))

@extends('layouts.app')

@section('content')
    <div class="bg-base-1 d-flex align-items-center flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <div class="my-1 font-weight-medium">{{ __('Tracking code') }}</div>
                                </div>

                                <div class="col-auto d-flex align-items-center">
                                    <span class="sidebar-icon d-flex align-items-center">
                                        <img src="{{ favicon($website->domain) }}" rel="noreferrer" class="width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                    </span>
                                    <span class="flex-grow-1">{{ $website->domain }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <div class="form-group">
                                @include('shared.tracking-code')
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <a href="{{ route('stats.overview', ['id' => $website->domain]) }}" class="btn btn-block btn-primary d-flex align-items-center justify-content-center">{{ __('Start') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
