@extends('layouts.app')

@section('site_title', formatTitle([__('General'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('General') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('General') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'general') }}" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="form-group">
                            <label for="i-title">{{ __('Title') }}</label>
                            <input type="text" name="title" id="i-title" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" value="{{ old('title') ?? config('settings.title') }}">
                            @if ($errors->has('title'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The website title, displayed in the browser tab, email subjects, and various other contexts.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-tagline">{{ __('Tagline') }}</label>
                            <input type="text" name="tagline" id="i-tagline" class="form-control{{ $errors->has('tagline') ? ' is-invalid' : '' }}" value="{{ old('tagline') ?? config('settings.tagline') }}">
                            @if ($errors->has('tagline'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('tagline') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('A short descriptive phrase or slogan displayed in the browser tab on the homepage.') }}</small>
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
                            <label for="i-logo" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Logo') }}</span><span class="badge badge-secondary">{{ __('Light') }}</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.logo')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="logo" id="i-logo" class="custom-file-input{{ $errors->has('logo') ? ' is-invalid' : '' }} cursor-pointer" accept="jpeg,png,bmp,gif,svg,webp">
                                    <label class="custom-file-label" for="i-logo" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('logo'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('logo') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The website logo displayed on light backgrounds.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-logo-dark" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Logo') }}</span><span class="badge badge-secondary">{{ __('Dark') }}</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.logo_dark')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="logo_dark" id="i-logo-dark" class="custom-file-input{{ $errors->has('logo_dark') ? ' is-invalid' : '' }} cursor-pointer" accept="jpeg,png,bmp,gif,svg,webp">
                                    <label class="custom-file-label" for="i-logo-dark" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('logo_dark'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('logo_dark') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The website logo displayed on dark backgrounds.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-favicon">{{ __('Favicon') }}</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-1 px-2"><img src="{{ asset('uploads/brand/' . config('settings.favicon')) }}" class="max-h-6.5" alt=""></span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="favicon" id="i-favicon" class="custom-file-input{{ $errors->has('favicon') ? ' is-invalid' : '' }} cursor-pointer" accept="jpeg,png,bmp,gif,svg,webp">
                                    <label class="custom-file-label" for="i-favicon" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                </div>
                            </div>
                            @if ($errors->has('favicon'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('favicon') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The website icon displayed in the browser tab and bookmarks.') }}</small>
                        </div>

                        <div class="form-group mt-4">
                            <label for="i-theme" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Theme') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="theme" id="i-theme" class="custom-select{{ $errors->has('theme') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Light'), 1 => __('Dark')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('theme') !== null && old('theme') == $key) || (config('settings.theme') == $key && old('theme') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('theme'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('theme') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The default website theme color.') }} {{ __('Users can change this later in their account settings.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-custom-css">{{ __('Custom CSS') }}</label>
                            <textarea name="custom_css" id="i-custom-css" class="form-control{{ $errors->has('custom_css') ? ' is-invalid' : '' }}" rows="4" placeholder="body { color: red !important; }">{{ old('custom_css') ?? config('settings.custom_css') }}</textarea>
                            @if ($errors->has('custom_css'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('custom_css') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Custom CSS code to override or extend the website\'s default styling.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-custom-js">{{ __('Custom JS') }}</label>
                            <textarea name="custom_js" id="i-custom-js" class="form-control{{ $errors->has('custom_js') ? ' is-invalid' : '' }}" rows="4" placeholder="<script>
alert('Hello World');
</script>">{{ old('custom_js') ?? config('settings.custom_js') }}</textarea>
                            @if ($errors->has('custom_js'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('custom_js') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Custom JavaScript code to extend or modify the website\'s behavior.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Localization') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-locale" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Language') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="locale" id="i-locale" class="custom-select{{ $errors->has('locale') ? ' is-invalid' : '' }}">
                                @foreach(config('app.locales') as $code => $language)
                                    <option value="{{ $code }}" @if ((old('locale') !== null && old('locale') == $code) || (config('settings.locale') == $code && old('locale') == null)) selected @endif>{{ $language['name'] }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('locale'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('locale') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The default language used for the website interface.') }} {{ __('Users can change this later in their account settings.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-timezone" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Timezone') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="timezone" id="i-timezone" class="custom-select{{ $errors->has('timezone') ? ' is-invalid' : '' }}">
                                @foreach(timezone_identifiers_list() as $value)
                                    <option value="{{ $value }}" @if ((old('timezone') !== null && old('timezone') == $value) || (config('settings.timezone') == $value && old('timezone') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('timezone'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('timezone') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The default timezone used for displaying dates and times across the website.') }} {{ __('Users can change this later in their account settings.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Navigation') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-paginate" class="d-inline-flex align-items-center"><span class="me-2">{{ __('Results per page') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="paginate" id="i-paginate" class="custom-select{{ $errors->has('paginate') ? ' is-invalid' : '' }}">
                                @foreach([10, 25, 50, 100] as $value)
                                    <option value="{{ $value }}" @if ((old('paginate') !== null && old('paginate') == $value) || (config('settings.paginate') == $value && old('paginate') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('paginate'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('paginate') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The default number of results or items displayed per page in listings and search results.') }} {{ __('Users can change this later in their account settings.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-homepage-redirect-url">{{ __('Homepage redirect URL') }}</label>
                            <input type="text" dir="ltr" name="homepage_redirect_url" id="i-homepage-redirect-url" class="form-control{{ $errors->has('homepage_redirect_url') ? ' is-invalid' : '' }}" value="{{ old('homepage_redirect_url') ?? config('settings.homepage_redirect_url') }}">
                            @if ($errors->has('homepage_redirect_url'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('homepage_redirect_url') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('The URL to which visitors are redirected when accessing the website\'s homepage.') }} {{ __('This setting overrides the default homepage and replaces it with the specified destination.') }}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Security') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-force-https">{{ __('Force HTTPS') }}</label>
                            <select name="force_https" id="i-force-https" class="custom-select{{ $errors->has('force_https') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('force_https') !== null && old('force_https') == $key) || (config('settings.force_https') == $key && old('force_https') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('force_https'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('force_https') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Enable or disable automatic redirection to HTTPS.') }} {{ __('When enabled, all website traffic will be redirected securely.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-banned-words">{{ __('Banned words') }}</label>
                            <textarea name="banned_words" id="i-banned-words" class="form-control{{ $errors->has('banned_words') ? ' is-invalid' : '' }}" rows="3">{{ config('settings.banned_words') }}</textarea>
                            @if ($errors->has('banned_words'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('banned_words') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('A list of inappropriate or offensive words entered on separate lines that will be automatically blocked or filtered.') }}</small>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
