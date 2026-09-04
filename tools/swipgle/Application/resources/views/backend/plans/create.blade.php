@extends('backend.layouts.form')
@section('title', __('Create new plan'))
@section('container', 'container-max-lg')
@section('back', route('admin.plans.index'))
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.plans.store') }}" method="POST">
        @csrf
        <div class="card custom-card mb-4">
            <div class="card-header bg-primary text-white">
                {{ __('Plan details') }}
            </div>
            <ul class="custom-list-group list-group list-group-flush">
                <li class="list-group-item">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Plan Name') }} : <span
                                        class="red">*</span></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input type="checkbox" name="featured_plan" class="form-check-input">
                            <label>{{ __('Featured plan') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                                autofocus placeholder="{{ __('Enter plan name') }}" />
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Plan badge color') }}
                                    : </strong><span class="red">*</span></strong></label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="vironeer-color-picker input-group">
                                <span class="input-group-text colorpicker-input-addon">
                                    <i></i>
                                </span>
                                <input type="text" name="color" class="form-control"
                                    value="{{ old('color') ?? '#000000' }}" required>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-8">
                            <label class="col-form-label d-block"><strong>{{ __('Short description') }} : <span
                                        class="red">*</span></strong></label>
                        </div>
                        <div class="col-12 col-lg-4">
                            <textarea name="short_description" class="form-control" required placeholder="{{ __('Max 150 character') }}">{{ old('short_description') }}</textarea>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Plan Interval') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col col-lg-4">
                            <select name="interval" class="form-select" required>
                                <option value="0" {{ old('interval') == 0 ? 'selected' : '' }}>{{ __('Monthly') }}
                                </option>
                                <option value="1" {{ old('interval') == 1 ? 'selected' : '' }}>{{ __('Yearly') }}
                                </option>
                                <option value="2" {{ old('interval') == 2 ? 'selected' : '' }}>{{ __('Lifetime') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </li>
                <li class="list-group-item plans-list-group">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Plan Price') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input id="freePlan" type="checkbox" name="free_plan" class="form-check-input">
                            <label>{{ __('Free') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="planPrice" type="text" name="price" class="form-control input-price"
                                    value="{{ old('price') }}" placeholder="0.00" required />
                                <span id="priceSymbol"
                                    class="input-group-text"><strong>{{ currencySymbolAndCode() }}</strong></span>
                            </div>
                        </div>
                    </div>
                </li>
                <li id="loginRequire" class="fadeIn list-group-item d-none">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Require Login') }} :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="auth" data-toggle="toggle" data-off="{{ __('No') }}"
                                data-on="{{ __('Yes') }}" {{ old('auth') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
                <li class="list-group-item plans-list-group">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Storage space') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input id="unlimitedStorageSpace" type="checkbox" name="unlimited_storage_space"
                                class="form-check-input">
                            <label>{{ __('Unlimited') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="storageSpace" type="number" name="storage_space" class="form-control"
                                    value="{{ old('storage_space') }}" placeholder="0" required />
                                <span id="storageSpaceSymbol" class="input-group-text"><strong><i
                                            class="fas fa-hdd me-2"></i>{{ __('MB') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-group-item plans-list-group">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Size per transfer') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input id="unlimitedTransferSize" type="checkbox" name="unlimited_transfer_size"
                                class="form-check-input">
                            <label>{{ __('Unlimited') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="transferSize" type="number" name="transfer_size" class="form-control"
                                    value="{{ old('transfer_size') }}" placeholder="0" required />
                                <span id="transferSizeSymbol" class="input-group-text"><strong><i
                                            class="fas fa-hdd me-2"></i>{{ __('MB') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-group-item plans-list-group">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Files available for') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input id="unlimitedTransferTime" type="checkbox" name="unlimited_transfer_time"
                                class="form-check-input">
                            <label>{{ __('Unlimited time') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="transferTime" type="number" name="transfer_interval" class="form-control"
                                    value="{{ old('transfer_interval') }}" placeholder="0" required />
                                <span id="transferTimeSymbol" class="input-group-text"><strong><i
                                            class="fas fa-calendar-alt me-2"></i>{{ __('Days') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Password Protection') }}
                                    : </strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="transfer_password" data-toggle="toggle"
                                data-off="{{ __('OFF') }}" data-on="{{ __('ON') }}"
                                {{ old('transfer_password') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Email Notification') }}
                                    :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="transfer_notify" data-toggle="toggle"
                                data-off="{{ __('OFF') }}" data-on="{{ __('ON') }}"
                                {{ old('transfer_notify') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Transfer Expiry Time') }}
                                    :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="transfer_expiry" data-toggle="toggle"
                                data-off="{{ __('OFF') }}" data-on="{{ __('ON') }}"
                                {{ old('transfer_expiry') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Create Transfer Link') }}
                                    :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="transfer_link" data-toggle="toggle"
                                data-off="{{ __('OFF') }}" data-on="{{ __('ON') }}"
                                {{ old('transfer_link') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Advertisements') }}
                                    :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="advertisements" data-toggle="toggle"
                                data-off="{{ __('OFF') }}" data-on="{{ __('ON') }}"
                                {{ old('advertisements') ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div id="customFeaturesCard" class="card custom-card mb-3 d-none">
            <div class="card-header bg-secondary text-white">
                {{ __('Custom features') }}
            </div>
            <ul id="customFeatures" class="custom-list-group list-group list-group-flush plans-list-group"></ul>
        </div>
        <button type="button" id="addCustomFeature" class="btn btn-primary"><i
                class="fa fa-plus me-2"></i>{{ __('Add custom feature') }}</button>
    </form>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js') }}"></script>
    @endpush
    @push('top_scripts')
        <script>
            "use strict";
            var customFeatureI = -1;
        </script>
    @endpush
    @push('scripts')
        <script>
            "use strict";
            $(function() {
                $('.vironeer-color-picker').colorpicker();
            });
        </script>
    @endpush
@endsection
