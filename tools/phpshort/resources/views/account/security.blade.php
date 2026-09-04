@extends('layouts.app')

@section('site_title', formatTitle([__('Security'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('dashboard'), 'title' => __('Home')],
                ['url' => route('account'), 'title' => __('Account')],
                ['title' => __('Security')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Security') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Security') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('account.security') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-current-password">{{ __('Current password') }}</label>
                            <input type="password" name="current_password" id="i-current-password" class="form-control{{ $errors->has('current_password') ? ' is-invalid' : '' }}">
                            @if ($errors->has('current_password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-password">{{ __('New password') }}</label>
                            <input type="password" name="password" id="i-password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-password-confirmation">{{ __('Confirm new password') }}</label>
                            <input type="password" name="password_confirmation" id="i-password-confirmation" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}">
                            @if ($errors->has('password_confirmation'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>

                        @if(config('settings.login_tfa'))
                            <div class="row mx-n2 mb-4">
                                <div class="col-auto fw-bold px-2">
                                    <span class="badge badge-secondary text-uppercase">
                                        {{ __('Two-factor authentication') }}
                                    </span>
                                </div>
                                <div class="col d-flex align-items-center px-2">
                                    <hr class="my-0 w-full">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="i-tfa">{{ __('Two-factor authentication') }}</label>
                                <select name="tfa" id="i-tfa" class="custom-select{{ $errors->has('tfa') ? ' is-invalid' : '' }}">
                                    @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                        <option value="{{ $key }}" @if ((old('tfa') !== null && old('tfa') == $key) || ($user->tfa == $key && old('tfa') == null)) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('tfa'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('tfa') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">
                                    {{ __('Receive two-factor authentication (2FA) codes at :email.', ['email' => $user->email]) }}
                                </small>
                            </div>
                        @endif

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
