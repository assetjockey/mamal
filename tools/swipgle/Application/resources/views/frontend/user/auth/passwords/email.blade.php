@extends('frontend.user.layouts.auth')
@section('title', lang('Reset Password', 'user'))
@section('content')
    <div class="vr__sign__form vr__reset">
        <div class="vr__sign__header">
            <p class="h3 mb-1">{{ lang('Reset Password', 'user') }}</p>
            <p class="mb-0">
                {{ lang('After submitting a valid email address on this form, you will receive instructions telling you how to reset your password.', 'user') }}
            </p>
        </div>
        <div class="sign-body">
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address"
                        value="{{ old('email') }}" required>
                    <label>{{ lang('Email address', 'forms') }}</label>
                </div>
                {!! display_captcha() !!}
                <div class="d-flex">
                    <button class="btn btn-secondary btn-lg w-100">{{ lang('Reset', 'user') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
