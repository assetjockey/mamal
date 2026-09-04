@extends('frontend.user.layouts.auth')
@section('title', lang('Reset Password', 'user'))
@section('content')
    <div class="vr__sign__form vr__reset">
        <div class="vr__sign__header">
            <p class="h3 mb-1">{{ lang('Reset Password', 'user') }}</p>
            <p class="mb-0">
                {{ lang('Enter a new password to continue.', 'user') }}
            </p>
        </div>
        <div class="sign-body">
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control"
                        placeholder="{{ lang('Email address', 'forms') }}" value="{{ $email }}" required readonly>
                    <label>{{ lang('Email address', 'forms') }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="{{ lang('Password', 'forms') }}" required autofocus>
                    <label>{{ lang('Password', 'forms') }}</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                        placeholder="{{ lang('Confirm password', 'forms') }}" required>
                    <label>{{ lang('Confirm password', 'forms') }}</label>
                </div>
                {!! display_captcha() !!}
                <div class="d-flex">
                    <button class="btn btn-secondary btn-lg w-100">{{ lang('Reset', 'user') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
