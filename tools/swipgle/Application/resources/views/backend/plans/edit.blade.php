@extends('backend.layouts.form')
@section('title', __('Edit Plan | ') . $plan->name . ' (' . $interval . ')')
@section('container', 'container-max-lg')
@section('back', route('admin.plans.index'))
@section('content')
    <form id="vironeer-submited-form" action="{{ route('admin.plans.update', $plan->id) }}" method="POST">
        @csrf
        @method('PUT')
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
                            <input type="checkbox" name="featured_plan" class="form-check-input"
                                {{ $plan->featured_plan ? 'checked' : '' }}>
                            <label>{{ __('Featured plan') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <input type="text" name="name" class="form-control" value="{{ $plan->name }}" required
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
                                <input type="text" name="color" class="form-control" value="{{ $plan->color }}"
                                    required>
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
                            <textarea name="short_description" class="form-control" required placeholder="{{ __('Max 150 character') }}">{{ $plan->short_description }}</textarea>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Plan Interval') }} : </strong></strong></label>
                        </div>
                        <div class="col col-lg-4">
                            <select class="form-select" disabled>
                                <option value="0" {{ $plan->interval == 0 ? 'selected' : '' }}>{{ __('Monthly') }}
                                </option>
                                <option value="1" {{ $plan->interval == 1 ? 'selected' : '' }}>{{ __('Yearly') }}
                                </option>
                                <option value="2" {{ $plan->interval == 2 ? 'selected' : '' }}>{{ __('Lifetime') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row g-2 align-items-center plans-list-group">
                        <div class="col-12 col-lg-6">
                            <label class="col-form-label"><strong>{{ __('Plan Price') }} : <span
                                        class="red">*</span></strong></strong></label>
                        </div>
                        <div class="col-12 col-lg-2">
                            <input id="freePlan" type="checkbox" name="free_plan" class="form-check-input"
                                {{ $plan->price == 0 ? 'checked' : '' }}>
                            <label>{{ __('Free') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="planPrice" type="text" name="price" class="form-control input-price"
                                    placeholder="0.00" value="{{ price($plan->price) }}" required
                                    {{ $plan->price == 0 ? 'disabled' : '' }} />
                                <span id="priceSymbol"
                                    class="input-group-text {{ $plan->price == 0 ? 'disabled' : '' }}"><strong>{{ currencySymbolAndCode() }}</strong></span>
                            </div>
                        </div>
                    </div>
                </li>
                <li id="loginRequire" class="fadeIn list-group-item {{ $plan->price != 0 ? 'd-none' : '' }}">
                    <div class="row align-items-center">
                        <div class="col-8 col-lg-8">
                            <label class="col-form-label"><strong>{{ __('Require Login') }} :</strong></label>
                        </div>
                        <div class="col-4 col-lg-4">
                            <input type="checkbox" name="auth" data-toggle="toggle" data-off="{{ __('No') }}"
                                data-on="{{ __('Yes') }}" {{ $plan->auth ? 'checked' : '' }}>
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
                                class="form-check-input" {{ !$plan->storage_space ? 'checked' : '' }}>
                            <label>{{ __('Unlimited') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="storageSpace" type="number" name="storage_space" class="form-control"
                                    value="{{ $plan->storage_space / 1048576 }}" placeholder="0" required
                                    {{ !$plan->storage_space ? 'disabled' : '' }} />
                                <span id="storageSpaceSymbol"
                                    class="input-group-text {{ !$plan->storage_space ? 'disabled' : '' }}"><strong><i
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
                                class="form-check-input" {{ !$plan->transfer_size ? 'checked' : '' }}>
                            <label>{{ __('Unlimited') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="transferSize" type="number" name="transfer_size" class="form-control"
                                    placeholder="0" required value="{{ $plan->transfer_size / 1048576 }}"
                                    {{ !$plan->transfer_size ? 'disabled' : '' }} />
                                <span id="transferSizeSymbol"
                                    class="input-group-text {{ !$plan->transfer_size ? 'disabled' : '' }}"><strong><i
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
                                class="form-check-input" {{ !$plan->transfer_interval ? 'checked' : '' }}>
                            <label>{{ __('Unlimited time') }}</label>
                        </div>
                        <div class="col col-lg-4">
                            <div class="input-group">
                                <input id="transferTime" type="number" name="transfer_interval" class="form-control"
                                    placeholder="0" required value="{{ $plan->transfer_interval }}"
                                    {{ !$plan->transfer_interval ? 'disabled' : '' }} />
                                <span id="transferTimeSymbol"
                                    class="input-group-text {{ !$plan->transfer_interval ? 'disabled' : '' }}"><strong><i
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
                                {{ $plan->transfer_password ? 'checked' : '' }}>
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
                                {{ $plan->transfer_notify ? 'checked' : '' }}>
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
                                {{ $plan->transfer_expiry ? 'checked' : '' }}>
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
                                {{ $plan->transfer_link ? 'checked' : '' }}>
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
                                {{ $plan->advertisements ? 'checked' : '' }}>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div id="customFeaturesCard" class="card custom-card mb-3 {{ !$plan->custom_features ? 'd-none' : '' }}">
            <div class="card-header bg-secondary text-white">
                {{ __('Custom features') }}
            </div>
            <ul id="customFeatures" class="custom-list-group list-group list-group-flush plans-list-group">
                @if ($plan->custom_features)
                    @foreach ($plan->custom_features as $key => $value)
                        <li id="customFeature{{ $key }}" class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <label><strong>{{ __('Feature name') }} : </strong><span
                                            class="red">*</span></label>
                                </div>
                                <div class="col">
                                    <input type="text" name="custom_features[{{ $key }}][name]"
                                        placeholder="{{ __('Enter name') }}" class="form-control"
                                        value="{{ $value->name }}" required>
                                </div>
                                <div class="col-auto">
                                    <button type="button" data-id="{{ $key }}"
                                        class="removeFeature btn btn-danger"><i class="fa fa-trash-alt"></i></button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endif
            </ul>
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
            var customFeatureI = {{ $plan->custom_features ? count($plan->custom_features) - 1 : -1 }};
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
