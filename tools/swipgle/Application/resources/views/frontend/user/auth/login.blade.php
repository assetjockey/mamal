@extends('frontend.user.layouts.auth')
@section('title', lang('Sign In', 'user'))
@section('content')
    <div class="vr__sign__form vr__login">
        <div class="vr__sign__header">
            <p class="h3 mb-1">{{ lang('Welcome!', 'user') }}</p>
            <p class="mb-0">{{ lang('Login to your account', 'user') }}</p>
        </div>
        <div class="sign-body">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control"
                        placeholder="{{ lang('Email address', 'forms') }}" value="{{ old('email') }}" required>
                    <label>{{ lang('Email address', 'forms') }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="{{ lang('Password', 'forms') }}" required>
                    <label>{{ lang('Password', 'forms') }}</label>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">{{ lang('Remember Me', 'user') }}</label>
                    </div>
                    <a class="vr__link__color" href="{{ route('password.request') }}">
                        {{ lang('Forgot Your Password?', 'user') }}
                    </a>
                </div>
                {!! display_captcha() !!}
                <div class="d-flex">
                    <button type="submit" class="btn btn-secondary btn-lg w-100">{{ lang('Sign In', 'user') }}</button>
                </div>
                {!! facebook_login() !!}
            </form>
        </div>
    </div>
@endsection
