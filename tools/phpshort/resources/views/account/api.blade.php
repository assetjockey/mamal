@extends('layouts.app')

@section('site_title', formatTitle([__('API'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('dashboard'), 'title' => __('Home')],
                ['url' => route('account'), 'title' => __('Account')],
                ['title' => __('API')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('API') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('API') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="form-group">
                        <label for="i-api-token">{{ __('API key') }}</label>
                        <div class="input-group">
                            <input type="text" id="i-api-token" class="form-control" value="{{ $user->api_token }}" readonly>
                            <div class="input-group-append">
                                <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-api-token">{{ __('Copy') }}</div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal" data-action="{{ route('account.api') }}" data-button-name="api_token" data-button-value="1" data-button-class="btn btn-danger position-relative" data-title="{{ __('Regenerate') }}" data-text="{{ __('Are you sure you want to regenerate the :name key?', ['name' => __('API')]) }}">{{ __('Regenerate') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
