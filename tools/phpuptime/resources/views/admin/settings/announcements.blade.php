@extends('layouts.app')

@section('site_title', formatTitle([__('Announcements'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Announcements') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header"><div class="font-weight-medium py-1">{{ __('Announcements') }}</div></div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-info">
                        {{ __('Announcements used to display important messages to guests and users on the website.') }}
                    </div>

                    <form action="{{ route('admin.settings', 'announcements') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseGuest" aria-expanded="{{ ($errors->has('announcement_guest') || $errors->has('announcement_guest_content') || $errors->has('announcement_guest_type') || $errors->has('announcement_guest_id') ? 'true' : 'false') }}" aria-controls="collapseGuest">
                                @include('icons.domino-mask', ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Guest') }}
                            </button>

                            <div class="collapse {{ ($errors->has('announcement_guest') || $errors->has('announcement_guest_content') || $errors->has('announcement_guest_type') || $errors->has('announcement_guest_id') ? 'show' : '') }}" id="collapseGuest">
                                <div class="form-group mt-3">
                                    <label for="i-announcement-guest">{{ __('Announcement') }}</label>
                                    <select name="announcement_guest" id="i-announcement-guest" class="custom-select{{ $errors->has('announcement_guest') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('announcement_guest') !== null && old('announcement_guest') == $key) || (config('settings.announcement_guest') == $key && old('announcement_guest') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('announcement_guest'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('announcement_guest') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable the announcement displayed to visitors who are not logged in.') }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-announcement-guest-content">{{ __('Content') }}</label>
                                    <textarea name="announcement_guest_content" id="i-announcement-guest-content" class="form-control{{ $errors->has('announcement_guest_content') ? ' is-invalid' : '' }}">{{ old('announcement_guest_content') ?? config('settings.announcement_guest_content') }}</textarea>
                                    @if ($errors->has('announcement_guest_content'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('announcement_guest_content') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('The message content displayed in the announcement.') }} {{ __('Supports HTML.') }}
                                    </small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-announcement-guest-type">{{ __('Type') }}</label>
                                    <select name="announcement_guest_type" id="i-announcement-guest-type" class="custom-select{{ $errors->has('announcement_guest_type') ? ' is-invalid' : '' }}">
                                        @foreach(['primary' => __('Primary'), 'secondary' => __('Secondary'), 'success' => __('Success'), 'danger' => __('Danger'), 'warning' => __('Warning'), 'info' => __('Info'), 'light' => __('Light'), 'dark' => __('Dark')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('announcement_guest_type') !== null && old('announcement_guest_type') == $key) || (config('settings.announcement_guest_type') == $key && old('announcement_guest_type') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('announcement_guest_type'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('announcement_guest_type') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('The type of the announcement.') }}
                                    </small>
                                </div>

                                <input type="hidden" name="announcement_guest_id" id="i-announcement-guest-id" class="form-control form-control-sm{{ $errors->has('announcement_guest_id') ? ' is-invalid' : '' }}" value="{{ old('announcement_guest_id') ?? Str::random(16) }}">
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseUser" aria-expanded="{{ ($errors->has('announcement_user') || $errors->has('announcement_user_content') || $errors->has('announcement_user_type') || $errors->has('announcement_user_id') ? 'true' : 'false') }}" aria-controls="collapseUser">
                                @include('icons.people-alt', ['class' => 'fill-current width-4 height-4 ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('User') }}
                            </button>

                            <div class="collapse {{ ($errors->has('announcement_user') || $errors->has('announcement_user_content') || $errors->has('announcement_user_type') || $errors->has('announcement_user_id') ? 'show' : '') }}" id="collapseUser">
                                <div class="form-group mt-3">
                                    <label for="i-announcement-user">{{ __('Announcement') }}</label>
                                    <select name="announcement_user" id="i-announcement-user" class="custom-select{{ $errors->has('announcement_user') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('announcement_user') !== null && old('announcement_user') == $key) || (config('settings.announcement_user') == $key && old('announcement_user') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('announcement_user'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('announcement_user') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable the announcement displayed to logged-in users.') }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-announcement-user-content">{{ __('Content') }}</label>
                                    <textarea name="announcement_user_content" id="i-announcement-user-content" class="form-control{{ $errors->has('announcement_user_content') ? ' is-invalid' : '' }}">{{ old('announcement_user_content') ?? config('settings.announcement_user_content') }}</textarea>
                                    @if ($errors->has('announcement_user_content'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('announcement_user_content') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('The message content displayed in the announcement.') }} {{ __('Supports HTML.') }}
                                    </small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-announcement-user-type">{{ __('Type') }}</label>
                                    <select name="announcement_user_type" id="i-announcement-user-type" class="custom-select{{ $errors->has('announcement_user_type') ? ' is-invalid' : '' }}">
                                        @foreach(['primary' => __('Primary'), 'secondary' => __('Secondary'), 'success' => __('Success'), 'danger' => __('Danger'), 'warning' => __('Warning'), 'info' => __('Info'), 'light' => __('Light'), 'dark' => __('Dark')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('announcement_user_type') !== null && old('announcement_user_type') == $key) || (config('settings.announcement_user_type') == $key && old('announcement_user_type') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('announcement_user_type'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('announcement_user_type') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('The type of the announcement.') }}
                                    </small>
                                </div>

                                <input type="hidden" name="announcement_user_id" id="i-announcement-user-id" class="form-control form-control-sm{{ $errors->has('announcement_user_id') ? ' is-invalid' : '' }}" value="{{ old('announcement_user_id') ?? Str::random(16) }}">
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
