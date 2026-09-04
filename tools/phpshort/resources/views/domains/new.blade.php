@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Domain'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.domains') : route('domains'), 'title' => __('Domains')],
                        ['title' => __('New')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('New') }}</h1>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row">
                                <div class="col">
                                    <div class="fw-medium py-1">{{ __('Domain') }}</div>
                                </div>
                                <div class="col-auto d-flex align-items-center">
                                    <div class="badge badge-danger">{{ __('Expert level') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(request()->is('admin/*'))
                                <div class="alert alert-warning">{{ __('This domain will be available as a plan feature.') }}</div>
                            @endif

                            <form action="{{ request()->is('admin/*') ? route('admin.domains.new') : route('domains.new') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @if(request()->is('admin/*'))
                                    <input type="hidden" name="user_id" value="0">
                                @endif

                                <div class="form-group">
                                    <label for="i-name">{{ __('Domain') }}</label>
                                    <input type="text" dir="ltr" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __('The DNS of the domain must include an A record pointing to :ip, or a CNAME record pointing to :domain.', ['ip' => '<strong>' . getHostIp() . '</strong>', 'domain' => '<strong>' . parse_url(config('app.url'), PHP_URL_HOST) . '</strong>']) !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-homepage-url">{{ __('Homepage URL') }}</label>
                                    <input type="text" dir="ltr" name="homepage_url" id="i-homepage-url" class="form-control{{ $errors->has('homepage_url') ? ' is-invalid' : '' }}" value="{{ old('homepage_url') }}">
                                    @if ($errors->has('homepage_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('homepage_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="text-muted">{{ __('The URL to which visitors are redirected when accessing the website\'s homepage.') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-not-found-url">{{ __('404 Not Found URL') }}</label>
                                    <input type="text" dir="ltr" name="not_found_url" id="i-not-found-url" class="form-control{{ $errors->has('not_found_url') ? ' is-invalid' : '' }}" value="{{ old('not_found_url') }}">
                                    @if ($errors->has('not_found_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('not_found_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="text-muted">{{ __('The URL to which visitors are redirected when a link does not exist.') }}</small>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if(request()->is('admin/*'))
    @include('shared.sidebars.admin')
@else
    @include('shared.sidebars.user')
@endif
