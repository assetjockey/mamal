@extends('layouts.app')

@section('site_title', formatTitle([__('Progressive web app'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Progressive web app') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Progressive web app') }}</div>
                </div>
                <div class="card-body">

                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'storage') }}" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="form-group">
                            <label for="i-pwa">{{ __('Progressive web app') }}</label>
                            <select name="pwa" id="i-pwa" class="custom-select{{ $errors->has('google') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('pwa') !== null && old('pwa') == $key) || (config('settings.pwa') == $key && old('pwa') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('pwa'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('pwa') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable Progressive Web App (PWA) functionality for the website.') }} {{ __('When enabled, users can install the website as an app.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Display') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-display">{{ __('Display') }}</label>
                            <select name="pwa_display" id="i-pwa-display" class="custom-select{{ $errors->has('pwa_display') ? ' is-invalid' : '' }}">
                                @foreach(['fullscreen' => 'fullscreen', 'standalone' => 'standalone', 'minimal-ui' => 'minimal-ui', 'browser' => 'browser'] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('pwa_display') !== null && old('pwa_display') == $key) || (config('settings.pwa_display') == $key && old('pwa_display') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('pwa_display'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('pwa_display') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#display-modes" target="_blank" rel="nofollow noreferrer noopener">Display modes - W3C</a>']) !!}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-orientation">{{ __('Orientation') }}</label>
                            <select name="pwa_orientation" id="i-pwa-orientation" class="custom-select{{ $errors->has('pwa_orientation') ? ' is-invalid' : '' }}">
                                @foreach(['any' => 'any', 'natural' => 'natural', 'landscape' => 'landscape', 'landscape-primary' => 'landscape-primary', 'landscape-secondary' => 'landscape-secondary', 'portrait' => 'portrait', 'portrait-primary' => 'portrait-primary', 'portrait-secondary' => 'portrait-secondary',] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('pwa_orientation') !== null && old('pwa_orientation') == $key) || (config('settings.pwa_orientation') == $key && old('pwa_orientation') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('pwa_orientation'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('pwa_orientation') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#orientation-member" target="_blank" rel="nofollow noreferrer noopener">Orientation - W3C</a>']) !!}
                            </small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Appearance') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-theme-color">{{ __('Theme color') }}</label>
                            <input id="i-pwa-theme-color" type="color" class="form-control{{ $errors->has('pwa_theme_color') ? ' is-invalid' : '' }}" name="pwa_theme_color" value="{{ old('pwa_theme_color') ?? config('settings.pwa_theme_color') }}">
                            @if ($errors->has('pwa_theme_color'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('pwa_theme_color') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#theme_color-member" target="_blank" rel="nofollow noreferrer noopener">Theme color - W3C</a>']) !!}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-background-color">{{ __('Background color') }}</label>
                            <input id="i-pwa-background-color" type="color" class="form-control{{ $errors->has('pwa_background_color') ? ' is-invalid' : '' }}" name="pwa_background_color" value="{{ old('pwa_background_color') ?? config('settings.pwa_background_color') }}">
                            @if ($errors->has('pwa_background_color'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('pwa_background_color') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#background_color-member" target="_blank" rel="nofollow noreferrer noopener">Background color - W3C</a>']) !!}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-logo" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Logo') }}</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.pwa_logo')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="pwa_logo" id="i-pwa-logo" class="custom-file-input{{ $errors->has('pwa_logo') ? ' is-invalid' : '' }} cursor-pointer" accept="png">
                                    <label class="custom-file-label" for="i-pwa-logo" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('pwa_logo'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('pwa_logo') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Image size must be :width by :height.', ['width' => '512px', 'height' => '512px']) }}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-logo-maskable" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Logo') }}</span><span class="badge badge-secondary">{{ __('Maskable') }}</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.pwa_logo_maskable')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="pwa_logo_maskable" id="i-pwa-logo-maskable" class="custom-file-input{{ $errors->has('pwa_logo_maskable') ? ' is-invalid' : '' }} cursor-pointer" accept="png">
                                    <label class="custom-file-label" for="i-pwa-logo-maskable" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('pwa_logo_maskable'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('pwa_logo_maskable') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Image size must be :width by :height.', ['width' => '512px', 'height' => '512px']) }} {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#icon-masks" target="_blank" rel="nofollow noreferrer noopener">Icon masks and safe zone - W3C</a>']) !!}
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="i-pwa-logo-monochrome" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Logo') }}</span><span class="badge badge-secondary">{{ __('Monochrome') }}</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.pwa_logo_monochrome')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="pwa_logo_monochrome" id="i-pwa-logo-monochrome" class="custom-file-input{{ $errors->has('pwa_logo_monochrome') ? ' is-invalid' : '' }} cursor-pointer" accept="png">
                                    <label class="custom-file-label" for="i-pwa-logo-monochrome" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('pwa_logo_monochrome'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('pwa_logo_monochrome') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Image size must be :width by :height.', ['width' => '512px', 'height' => '512px']) }} {!! __('Learn more at :url.', ['url' => '<a href="https://www.w3.org/TR/appmanifest/#monochrome-icons-and-solid-fills" target="_blank" rel="nofollow noreferrer noopener">Monochrome icons and solid fills - W3C</a>']) !!}
                            </small>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
