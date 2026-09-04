@extends('frontend.user.layouts.auth')
@section('title', lang('2Fa Verification', 'user'))
@section('content')
    <div class="vr__sign__form vr__reset">
        <div class="vr__sign__header">
            <p class="h3 mb-1">{{ lang('2Fa Verification', 'user') }}</p>
            <p class="mb-0">{{ lang('Please enter the OTP code to continue', 'user') }}</p>
        </div>
        <div class="sign-body vr__checkpoint">
            <form action="{{ route('2fa.verify') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <input type="text" name="otp_code" id="vr__otp__code" class="form-control" placeholder="••• •••"
                        maxlength="6" required>
                </div>
                <button class="btn btn-primary btn-lg w-100">{{ lang('Continue', 'user') }}</button>
            </form>
            <div class="vr__login__with mt-3">
                <div class="divider">
                    <span>{{ lang('Or', 'user') }}</span>
                </div>
                <div class="mt-3">
                    <form class="d-inline" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-dark btn-lg w-100">
                            {{ lang('Logout', 'user') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
