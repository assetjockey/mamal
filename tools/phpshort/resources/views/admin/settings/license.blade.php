@extends('layouts.app')

@section('site_title', formatTitle([__('License'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('License') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('License') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if (config('settings.license_key'))
                        <div class="alert alert-success">
                            <div class="row">
                                <div class="col">
                                    {{ __(':name license active.', ['name' => (config('settings.license_type') ? 'Extended' : 'Regular')]) }}
                                </div>
                                <div class="col-auto">
                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/#pricing" class="alert-link fw-medium" target="_blank">{{ __('Learn more') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings', 'license') }}">
                        @csrf

                        <div class="form-group">
                            <label for="i-license-key">{{ __('License key') }}</label>
                            <input id="i-license-key" type="text" class="form-control{{ $errors->has('license_key') ? ' is-invalid' : '' }}" name="license_key" value="{{ old('license_key') ?? config('settings.license_key') }}" autofocus>
                            @if ($errors->has('license_key'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('license_key') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="row mt-4">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
