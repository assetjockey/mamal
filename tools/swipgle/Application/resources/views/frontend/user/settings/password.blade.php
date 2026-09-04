@extends('frontend.user.layouts.dash')
@section('title', lang('Change Password', 'user'))
@section('content')
    <div class="vr__settings__v2">
        <div class="row g-3">
            <div class="col-xl-3">
                @include('frontend.user.includes.list')
            </div>
            <div class="col-xl-9">
                <div class="vr__card">
                    <div class="vr__settings__box">
                        <div class="vr__settings__box__header border-bottom">
                            <h5 class="mb-0">{{ lang('Change Password', 'user') }}</h5>
                        </div>
                        <div class="vr__settings__box__body">
                            <form id="deatilsForm" action="{{ route('user.settings.password.update') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ lang('Password', 'forms') }} : <span
                                            class="red">*</span></label>
                                    <input type="password" class="form-control" name="current-password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ lang('New Password', 'forms') }} : <span
                                            class="red">*</span></label>
                                    <input type="password" class="form-control" name="new-password" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ lang('Confirm New Password', 'forms') }} : <span
                                            class="red">*</span></label>
                                    <input type="password" class="form-control" name="new-password_confirmation" required>
                                </div>
                                <button class="btn btn-secondary"><i class="far fa-save"></i>
                                    {{ lang('Save Changes', 'user') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
