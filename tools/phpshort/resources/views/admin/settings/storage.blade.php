@extends('layouts.app')

@section('site_title', formatTitle([__('Storage'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Storage') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Storage') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col">
                                {{ __('Storage service used to store uploaded files on the website.') }}
                            </div>
                            <div class="col-auto">
                                <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#storage" class="alert-link fw-medium" target="_blank">{{ __('Learn more') }}</a>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings', 'storage') }}" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="form-group">
                            <label for="i-storage-driver">{{ __('Driver') }}</label>
                            <select name="storage_driver" id="i-storage-driver" class="custom-select{{ $errors->has('storage_driver') ? ' is-invalid' : '' }}">
                                @foreach(['public' => __('Local'), 's3' => 'S3'] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('storage_driver') !== null && old('storage_driver') == $key) || (config('settings.storage_driver') == $key && old('storage_driver') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('storage_driver'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_driver') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-key">{{ __('Access key') }}</label>
                            <input id="i-storage-key" type="text" class="form-control{{ $errors->has('storage_key') ? ' is-invalid' : '' }}" name="storage_key" value="{{ old('storage_key') ?? config('settings.storage_key') }}">
                            @if ($errors->has('storage_key'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_key') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-secret-key">{{ __('Secret key') }}</label>
                            <input id="i-storage-secret-key" type="password" class="form-control{{ $errors->has('storage_secret') ? ' is-invalid' : '' }}" name="storage_secret" value="{{ old('storage_secret') ?? config('settings.storage_secret') }}">
                            @if ($errors->has('storage_secret'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_secret') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-bucket">{{ __('Bucket') }}</label>
                            <input id="i-storage-bucket" type="text" class="form-control{{ $errors->has('storage_bucket') ? ' is-invalid' : '' }}" name="storage_bucket" value="{{ old('storage_bucket') ?? config('settings.storage_bucket') }}">
                            @if ($errors->has('storage_bucket'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_bucket') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-region">{{ __('Region') }}</label>
                            <input id="i-storage-region" type="text" class="form-control{{ $errors->has('storage_region') ? ' is-invalid' : '' }}" name="storage_region" value="{{ old('storage_region') ?? config('settings.storage_region') }}">
                            @if ($errors->has('storage_region'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_region') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-endpoint">{{ __('Endpoint') }}</label>
                            <input id="i-storage-endpoint" type="text" class="form-control{{ $errors->has('storage_endpoint') ? ' is-invalid' : '' }}" name="storage_endpoint" value="{{ old('storage_endpoint') ?? config('settings.storage_endpoint') }}">
                            @if ($errors->has('storage_endpoint'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_endpoint') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-use-path-style-endpoint">{{ __('Path style endpoint') }}</label>
                            <select name="storage_use_path_style_endpoint" id="i-storage-use-path-style-endpoint" class="custom-select{{ $errors->has('google') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('storage_use_path_style_endpoint') !== null && old('storage_use_path_style_endpoint') == $key) || (config('settings.storage_use_path_style_endpoint') == $key && old('storage_use_path_style_endpoint') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('storage_use_path_style_endpoint'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_use_path_style_endpoint') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-url">{{ __('URL') }}</label>
                            <input id="i-storage-url" type="text" class="form-control{{ $errors->has('storage_url') ? ' is-invalid' : '' }}" name="storage_url" value="{{ old('storage_url') ?? config('settings.storage_url') }}">
                            @if ($errors->has('storage_url'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_url') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-storage-signed-urls">{{ __('Signed URLs') }}</label>
                            <select name="storage_signed_urls" id="i-storage-signed-urls" class="custom-select{{ $errors->has('google') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('storage_signed_urls') !== null && old('storage_signed_urls') == $key) || (config('settings.storage_signed_urls') == $key && old('storage_signed_urls') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('storage_signed_urls'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('storage_signed_urls') }}</strong>
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
