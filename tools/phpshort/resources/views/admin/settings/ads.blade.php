@extends('layouts.app')

@section('site_title', formatTitle([__('Ads'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Ads') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Ads') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-info">
                        {!! __('Ads will only be displayed to guest users and users without the :feature plan feature.', ['feature' => '<span class="fw-medium">' . __('No ads') . '</span>']) !!}
                    </div>

                    <form action="{{ route('admin.settings', 'Ads') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-ad-stats-header">{{ __('Stats header') }}</label>
                            <textarea name="ad_stats_header" id="i-ad-stats-header" class="form-control{{ $errors->has('ad_stats_header') ? ' is-invalid' : '' }}">{{ old('ad_stats_header') ?? config('settings.ad_stats_header') }}</textarea>
                            @if ($errors->has('ad_stats_header'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('ad_stats_header') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-ad-stats-footer">{{ __('Stats footer') }}</label>
                            <textarea name="ad_stats_footer" id="i-ad-stats-footer" class="form-control{{ $errors->has('ad_stats_footer') ? ' is-invalid' : '' }}">{{ old('ad_stats_footer') ?? config('settings.ad_stats_footer') }}</textarea>
                            @if ($errors->has('ad_stats_footer'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('ad_stats_footer') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-ad-splash-header">{{ __('Splash header') }}</label>
                            <textarea name="ad_splash_header" id="i-ad-splash-header" class="form-control{{ $errors->has('ad_splash_header') ? ' is-invalid' : '' }}">{{ old('ad_splash_header') ?? config('settings.ad_splash_header') }}</textarea>
                            @if ($errors->has('ad_splash_header'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('ad_splash_header') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-ad-splash-footer">{{ __('Splash footer') }}</label>
                            <textarea name="ad_splash_footer" id="i-ad-splash-footer" class="form-control{{ $errors->has('ad_splash_footer') ? ' is-invalid' : '' }}">{{ old('ad_splash_footer') ?? config('settings.ad_splash_footer') }}</textarea>
                            @if ($errors->has('ad_splash_footer'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('ad_splash_footer') }}</strong>
                                </span>
                            @endif
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
