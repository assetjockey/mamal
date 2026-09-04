@extends('layouts.app')

@section('site_title', formatTitle([__('Invoicing'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Invoicing') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Invoicing') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.settings', 'billing_information') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-invoicing">{{ __('Invoicing') }}</label>
                            <select name="invoicing" id="i-invoicing" class="custom-select{{ $errors->has('invoicing') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('invoicing') !== null && old('invoicing') == $key) || (config('settings.invoicing') == $key && old('invoicing') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('invoicing'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('invoicing') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Automatically generate invoices on the website, and email users upon payment completion.') }} {{ __('When disabled, invoices must be managed manually or through the payment processor.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-invoice-prefix">{{ __('Invoice prefix') }}</label>
                            <input type="text" name="invoice_prefix" id="i-invoice-prefix" class="form-control{{ $errors->has('invoice_prefix') ? ' is-invalid' : '' }}" value="{{ old('invoice_prefix') ?? config('settings.invoice_prefix') }}">
                            @if ($errors->has('invoice_prefix'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('invoice_prefix') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('Invoice number prefix for the website generated invoices.') }} {!! __('For example :example will show on invoice as :result.', ['example' => '<code class="badge badge-secondary">PREFIX-</code>', 'result' => '<code class="badge badge-secondary">PREFIX-1</code>']) !!}</small>
                        </div>

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Billing information') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label for="i-billing-vendor">{{ __('Vendor') }}</label>
                            <input type="text" name="billing_vendor" id="i-billing-vendor" class="form-control{{ $errors->has('billing_vendor') ? ' is-invalid' : '' }}" value="{{ old('billing_vendor') ?? config('settings.billing_vendor') }}">
                            @if ($errors->has('billing_vendor'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('billing_vendor') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-billing-address">{{ __('Address') }}</label>
                            <input type="text" name="billing_address" id="i-billing-address" class="form-control{{ $errors->has('billing_address') ? ' is-invalid' : '' }}" value="{{ old('billing_address') ?? config('settings.billing_address') }}">
                            @if ($errors->has('billing_address'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('billing_address') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="row mx-n2">
                            <div class="col-12 col-md-6 px-2">
                                <div class="form-group">
                                    <label for="i-billing-city">{{ __('City') }}</label>
                                    <input type="text" name="billing_city" id="i-billing-city" class="form-control{{ $errors->has('billing_city') ? ' is-invalid' : '' }}" value="{{ old('billing_city') ?? config('settings.billing_city') }}">
                                    @if ($errors->has('billing_city'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('billing_city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-3 px-2">
                                <div class="form-group">
                                    <label for="i-billing-state">{{ __('State') }}</label>
                                    <input type="text" name="billing_state" id="i-billing-state" class="form-control{{ $errors->has('billing_state') ? ' is-invalid' : '' }}" value="{{ old('billing_state') ?? config('settings.billing_state') }}">
                                    @if ($errors->has('billing_state'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('billing_state') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-3 px-2">
                                <div class="form-group">
                                    <label for="i-billing-postal-code">{{ __('Postal code') }}</label>
                                    <input type="tel" name="billing_postal_code" id="i-billing-postal-code" class="form-control{{ $errors->has('billing_postal_code') ? ' is-invalid' : '' }}" value="{{ old('billing_postal_code') ?? config('settings.billing_postal_code') }}">
                                    @if ($errors->has('billing_postal_code'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('billing_postal_code') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-billing-country">{{ __('Country') }}</label>
                            <select name="billing_country" id="i-billing-country" class="custom-select{{ $errors->has('billing_country') ? ' is-invalid' : '' }}">
                                <option value="" hidden disabled selected>{{ __('Country') }}</option>
                                @foreach(config('countries') as $key => $value)
                                    <option value="{{ $key }}" @if ((old('billing_country') !== null && old('billing_country') == $key) || (config('settings.billing_country') == $key && old('billing_country') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('billing_country'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('billing_country') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-billing-phone">{{ __('Phone') }}</label>
                            <input type="text" name="billing_phone" id="i-billing-phone" class="form-control{{ $errors->has('billing_phone') ? ' is-invalid' : '' }}" value="{{ old('billing_phone') ?? config('settings.billing_phone') }}">
                            @if ($errors->has('billing_phone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('billing_phone') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-billing-vat-number">{{ __('VAT number') }}</label>
                            <input type="text" name="billing_vat_number" id="i-billing-vat-number" class="form-control{{ $errors->has('billing_vat_number') ? ' is-invalid' : '' }}" value="{{ old('billing_vat_number') ?? config('settings.billing_vat_number') }}">
                            @if ($errors->has('billing_vat_number'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('billing_vat_number') }}</strong>
                                </span>
                            @endif
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
