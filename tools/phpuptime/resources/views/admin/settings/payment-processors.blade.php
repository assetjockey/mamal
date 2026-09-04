@extends('layouts.app')

@section('site_title', formatTitle([__('Payment processors'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Payment processors') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header"><div class="font-weight-medium py-1">{{ __('Payment processors') }}</div></div>
                <div class="card-body">
                    @include('shared.message')

                    @if (!config('settings.license_type'))
                        <div class="alert alert-danger">
                            <div class="row">
                                <div class="col">
                                    {{ __(':license license is required to enable payment processors.', ['license' => __('Extended')]) }}
                                </div>
                                <div class="col-auto">
                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/#pricing" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings', 'payment-processors') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapsePayPal" aria-expanded="{{ ($errors->has('paypal') || $errors->has('paypal_mode') || $errors->has('paypal_client_id') || $errors->has('paypal_secret') || $errors->has('paypal_webhook_id') ? 'true' : 'false') }}" aria-controls="collapsePayPal">
                                <img src="{{ asset('img/icons/payments/paypal.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="PayPal"> PayPal
                            </button>

                            <div class="collapse {{ ($errors->has('paypal') || $errors->has('paypal_mode') || $errors->has('paypal_client_id') || $errors->has('paypal_secret') || $errors->has('paypal_webhook_id') ? 'show' : '') }}" id="collapsePayPal">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'PayPal']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#paypal" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-paypal">PayPal</label>
                                    <select name="paypal" id="i-paypal" class="custom-select{{ $errors->has('paypal') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('paypal') !== null && old('paypal') == $key) || (config('settings.paypal') == $key && old('paypal') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('paypal'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paypal') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'PayPal']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-paypal-mode">{{ __('Mode') }}</label>
                                    <select name="paypal_mode" id="i-paypal-mode" class="custom-select{{ $errors->has('paypal_mode') ? ' is-invalid' : '' }}">
                                        @foreach(['live' => __('Live'), 'sandbox' => __('Sandbox')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('paypal_mode') !== null && old('paypal_mode') == $key) || (config('settings.paypal_mode') == $key && old('paypal_mode') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('paypal_mode'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paypal_mode') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paypal-client-id">{{ __('Client ID') }}</label>
                                    <input type="text" name="paypal_client_id" id="i-paypal-client-id" class="form-control{{ $errors->has('paypal_client_id') ? ' is-invalid' : '' }}" value="{{ old('paypal_client_id') ?? config('settings.paypal_client_id') }}">
                                    @if ($errors->has('paypal_client_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paypal_client_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paypal-secret">{{ __('Secret') }}</label>
                                    <input type="password" name="paypal_secret" id="i-paypal-secret" class="form-control{{ $errors->has('paypal_secret') ? ' is-invalid' : '' }}" value="{{ old('paypal_secret') ?? config('settings.paypal_secret') }}">
                                    @if ($errors->has('paypal_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paypal_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paypal-webhook-id">{{ __('Webhook ID') }}</label>
                                    <input type="text" name="paypal_webhook_id" id="i-paypal-webhook-id" class="form-control{{ $errors->has('paypal_webhook_id') ? ' is-invalid' : '' }}" value="{{ old('paypal_webhook_id') ?? config('settings.paypal_webhook_id') }}">
                                    @if ($errors->has('paypal_webhook_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paypal_webhook_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-paypal-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="paypal_wh_url" id="i-paypal-wh-url" class="form-control" value="{{ route('webhooks.paypal') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-paypal-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseStripe" aria-expanded="{{ ($errors->has('stripe') || $errors->has('stripe_key') || $errors->has('stripe_secret') || $errors->has('stripe_wh_secret') ? 'true' : 'false') }}" aria-controls="collapseStripe">
                                <img src="{{ asset('img/icons/payments/stripe.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Stripe"> Stripe
                            </button>

                            <div class="collapse {{ ($errors->has('stripe') || $errors->has('stripe_key') || $errors->has('stripe_secret') || $errors->has('stripe_wh_secret') ? 'show' : '') }}" id="collapseStripe">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Stripe']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#stripe" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe">Stripe</label>
                                    <select name="stripe" id="i-stripe" class="custom-select{{ $errors->has('stripe') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('stripe') !== null && old('stripe') == $key) || (config('settings.stripe') == $key && old('stripe') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('stripe'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Stripe']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-key">{{ __('Publishable key') }}</label>
                                    <input type="text" name="stripe_key" id="i-stripe-key" class="form-control{{ $errors->has('stripe_key') ? ' is-invalid' : '' }}" value="{{ old('stripe_key') ?? config('settings.stripe_key') }}">
                                    @if ($errors->has('stripe_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-secret">{{ __('Secret key') }}</label>
                                    <input type="password" name="stripe_secret" id="i-stripe-secret" class="form-control{{ $errors->has('stripe_secret') ? ' is-invalid' : '' }}" value="{{ old('stripe_secret') ?? config('settings.stripe_secret') }}">
                                    @if ($errors->has('stripe_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-wh-secret">{{ __('Signing secret') }}</label>
                                    <input type="password" name="stripe_wh_secret" id="i-stripe-wh-secret" class="form-control{{ $errors->has('stripe_wh_secret') ? ' is-invalid' : '' }}" value="{{ old('stripe_wh_secret') ?? config('settings.stripe_wh_secret') }}">
                                    @if ($errors->has('stripe_wh_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_wh_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="stripe_wh_url" id="i-stripe-wh-url" class="form-control" value="{{ route('webhooks.stripe') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-stripe-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-n2 mb-3">
                                    <div class="col-auto font-weight-bold px-2">
                                        <span class="badge badge-secondary text-uppercase">
                                            {{ __('Additional payment methods') }}
                                        </span>
                                    </div>
                                    <div class="col d-flex align-items-center px-2">
                                        <hr class="my-0 w-100">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-ideal">iDEAL</label>
                                    <select name="stripe_ideal" id="i-stripe-ideal" class="custom-select{{ $errors->has('stripe_ideal') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('stripe_ideal') !== null && old('stripe_ideal') == $key) || (config('settings.stripe_ideal') == $key && old('stripe_ideal') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('stripe_ideal'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_ideal') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable checkout through :service.', ['service' => 'iDEAL']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-stripe-klarna">Klarna</label>
                                    <select name="stripe_klarna" id="i-stripe-klarna" class="custom-select{{ $errors->has('stripe_klarna') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('stripe_klarna') !== null && old('stripe_klarna') == $key) || (config('settings.stripe_klarna') == $key && old('stripe_klarna') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('stripe_klarna'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_klarna') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable checkout through :service.', ['service' => 'Klarna']) }}
                                    </small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-stripe-sepa-direct">SEPA Direct Debit</label>
                                    <select name="stripe_sepa_direct_debit" id="i-stripe-sepa-direct" class="custom-select{{ $errors->has('stripe_sepa_direct_debit') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('stripe_sepa_direct_debit') !== null && old('stripe_sepa_direct_debit') == $key) || (config('settings.stripe_sepa_direct_debit') == $key && old('stripe_sepa_direct_debit') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('stripe_sepa_direct_debit'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('stripe_sepa_direct_debit') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable checkout through :service.', ['service' => 'SEPA Direct Debit']) }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseMollie" aria-expanded="{{ ($errors->has('mollie') || $errors->has('mollie_key') ? 'true' : 'false') }}" aria-controls="collapseMollie">
                                <img src="{{ asset('img/icons/payments/mollie.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Mollie"> Mollie
                            </button>

                            <div class="collapse {{ ($errors->has('mollie') || $errors->has('mollie_key') ? 'show' : '') }}" id="collapseMollie">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Mollie']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#mollie" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-mollie">Mollie</label>
                                    <select name="mollie" id="i-mollie" class="custom-select{{ $errors->has('mollie') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('mollie') !== null && old('mollie') == $key) || (config('settings.mollie') == $key && old('mollie') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('mollie'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('mollie') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Mollie']) }}
                                    </small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-mollie-key">{{ __('API key') }}</label>
                                    <input type="password" name="mollie_key" id="i-mollie-key" class="form-control{{ $errors->has('mollie_key') ? ' is-invalid' : '' }}" value="{{ old('mollie_key') ?? config('settings.mollie_key') }}">
                                    @if ($errors->has('mollie_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('mollie_key') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapsePaddle" aria-expanded="{{ ($errors->has('paddle') || $errors->has('paddle_api_key') || $errors->has('paddle_client_token') || $errors->has('paddle_wh_secret') ? 'true' : 'false') }}" aria-controls="collapsePaddle">
                                <img src="{{ asset('img/icons/payments/paddle.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Paddle"> Paddle
                            </button>

                            <div class="collapse {{ ($errors->has('paddle') || $errors->has('paddle_api_key') || $errors->has('paddle_client_token') || $errors->has('paddle_wh_secret') ? 'show' : '') }}" id="collapsePaddle">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Paddle']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#paddle" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-paddle">Paddle</label>
                                    <select name="paddle" id="i-paddle" class="custom-select{{ $errors->has('paddle') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('paddle') !== null && old('paddle') == $key) || (config('settings.paddle') == $key && old('paddle') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('paddle'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paddle') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Paddle']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-paddle-mode">{{ __('Mode') }}</label>
                                    <select name="paddle_mode" id="i-paddle-mode" class="custom-select{{ $errors->has('paddle_mode') ? ' is-invalid' : '' }}">
                                        @foreach(['live' => __('Live'), 'sandbox' => __('Sandbox')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('paddle_mode') !== null && old('paddle_mode') == $key) || (config('settings.paddle_mode') == $key && old('paddle_mode') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('paddle_mode'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paddle_mode') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paddle-api-key">{{ __('API key') }}</label>
                                    <input type="password" name="paddle_api_key" id="i-paddle-api-key" class="form-control{{ $errors->has('paddle_api_key') ? ' is-invalid' : '' }}" value="{{ old('paddle_api_key') ?? config('settings.paddle_api_key') }}">
                                    @if ($errors->has('paddle_api_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paddle_api_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paddle-client-token">{{ __('Client token') }}</label>
                                    <input type="password" name="paddle_client_token" id="i-paddle-client-token" class="form-control{{ $errors->has('paddle_client_token') ? ' is-invalid' : '' }}" value="{{ old('paddle_client_token') ?? config('settings.paddle_client_token') }}">
                                    @if ($errors->has('paddle_client_token'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paddle_client_token') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paddle-wh-secret">{{ __('Signing secret') }}</label>
                                    <input type="password" name="paddle_wh_secret" id="i-paddle-wh-secret" class="form-control{{ $errors->has('paddle_wh_secret') ? ' is-invalid' : '' }}" value="{{ old('paddle_wh_secret') ?? config('settings.paddle_wh_secret') }}">
                                    @if ($errors->has('paddle_wh_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paddle_wh_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-paddle-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="paddle_wh_url" id="i-paddle-wh-url" class="form-control" value="{{ route('webhooks.paddle') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-paddle-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseRazorpay" aria-expanded="{{ ($errors->has('razorpay') || $errors->has('razorpay_key') || $errors->has('razorpay_secret') || $errors->has('razorpay_wh_secret') || $errors->has('razorpay_wh_url') ? 'true' : 'false') }}" aria-controls="collapseRazorpay">
                                <img src="{{ asset('img/icons/payments/razorpay.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Razorpay"> Razorpay
                            </button>

                            <div class="collapse {{ ($errors->has('razorpay') || $errors->has('razorpay_key') || $errors->has('razorpay_secret') || $errors->has('razorpay_wh_secret') || $errors->has('razorpay_wh_url') ? 'show' : '') }}" id="collapseRazorpay">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Razorpay']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#razorpay" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-razorpay">Razorpay</label>
                                    <select name="razorpay" id="i-razorpay" class="custom-select{{ $errors->has('razorpay') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('razorpay') !== null && old('razorpay') == $key) || (config('settings.razorpay') == $key && old('razorpay') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('razorpay'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('razorpay') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Razorpay']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-razorpay-key">{{ __('Key ID') }}</label>
                                    <input type="text" name="razorpay_key" id="i-razorpay-key" class="form-control{{ $errors->has('razorpay_key') ? ' is-invalid' : '' }}" value="{{ old('razorpay_key') ?? config('settings.razorpay_key') }}">
                                    @if ($errors->has('razorpay_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('razorpay_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-razorpay-secret">{{ __('Key secret') }}</label>
                                    <input type="password" name="razorpay_secret" id="i-razorpay-secret" class="form-control{{ $errors->has('razorpay_secret') ? ' is-invalid' : '' }}" value="{{ old('razorpay_secret') ?? config('settings.razorpay_secret') }}">
                                    @if ($errors->has('razorpay_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('razorpay_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-razorpay-wh-secret">{{ __('Webhook secret') }}</label>
                                    <input type="password" name="razorpay_wh_secret" id="i-razorpay-wh-secret" class="form-control{{ $errors->has('razorpay_wh_secret') ? ' is-invalid' : '' }}" value="{{ old('razorpay_wh_secret') ?? config('settings.razorpay_wh_secret') }}">
                                    @if ($errors->has('razorpay_wh_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('razorpay_wh_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-razorpay-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="razorpay_wh_url" id="i-razorpay-wh-url" class="form-control" value="{{ route('webhooks.razorpay') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-razorpay-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapsePaystack" aria-expanded="{{ ($errors->has('paystack') || $errors->has('paystack_key') || $errors->has('paystack_secret') || $errors->has('paystack_wh_url') ? 'true' : 'false') }}" aria-controls="collapsePaystack">
                                <img src="{{ asset('img/icons/payments/paystack.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Paystack"> Paystack
                            </button>

                            <div class="collapse {{ ($errors->has('paystack') || $errors->has('paystack_key') || $errors->has('paystack_secret') || $errors->has('paystack_wh_url') ? 'show' : '') }}" id="collapsePaystack">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Paystack']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#paystack" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-paystack">Paystack</label>
                                    <select name="paystack" id="i-paystack" class="custom-select{{ $errors->has('paystack') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('paystack') !== null && old('paystack') == $key) || (config('settings.paystack') == $key && old('paystack') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('paystack'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paystack') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Paystack']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-paystack-key">{{ __('Publishable key') }}</label>
                                    <input type="text" name="paystack_key" id="i-paystack-key" class="form-control{{ $errors->has('paystack_key') ? ' is-invalid' : '' }}" value="{{ old('paystack_key') ?? config('settings.paystack_key') }}">
                                    @if ($errors->has('paystack_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paystack_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-paystack-secret">{{ __('Secret key') }}</label>
                                    <input type="password" name="paystack_secret" id="i-paystack-secret" class="form-control{{ $errors->has('paystack_secret') ? ' is-invalid' : '' }}" value="{{ old('paystack_secret') ?? config('settings.paystack_secret') }}">
                                    @if ($errors->has('paystack_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('paystack_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-paystack-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="paystack_wh_url" id="i-paystack-wh-url" class="form-control" value="{{ route('webhooks.paystack') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-paystack-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseCoinbase" aria-expanded="{{ ($errors->has('coinbase') || $errors->has('coinbase_key') || $errors->has('coinbase_wh_secret') ? 'true' : 'false') }}" aria-controls="collapseCoinbase">
                                <img src="{{ asset('img/icons/payments/coinbase.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Coinbase"> Coinbase
                            </button>

                            <div class="collapse {{ ($errors->has('coinbase') || $errors->has('coinbase_key') || $errors->has('coinbase_wh_secret')  ? 'show' : '') }}" id="collapseCoinbase">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Coinbase']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#coinbase" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-coinbase">Coinbase</label>
                                    <select name="coinbase" id="i-coinbase" class="custom-select{{ $errors->has('coinbase') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('coinbase') !== null && old('coinbase') == $key) || (config('settings.coinbase') == $key && old('coinbase') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('coinbase'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('coinbase') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Coinbase']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-coinbase-key">{{ __('Client ID') }}</label>
                                    <input type="text" name="coinbase_key" id="i-coinbase-key" class="form-control{{ $errors->has('coinbase_key') ? ' is-invalid' : '' }}" value="{{ old('coinbase_key') ?? config('settings.coinbase_key') }}">
                                    @if ($errors->has('coinbase_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('coinbase_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-coinbase-wh-secret">{{ __('Webhook shared secret') }}</label>
                                    <input type="password" name="coinbase_wh_secret" id="i-coinbase-wh-secret" class="form-control{{ $errors->has('coinbase_wh_secret') ? ' is-invalid' : '' }}" value="{{ old('coinbase_wh_secret') ?? config('settings.coinbase_wh_secret') }}">
                                    @if ($errors->has('coinbase_wh_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('coinbase_wh_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-coinbase-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="coinbase_wh_url" id="i-coinbase-wh-url" class="form-control" value="{{ route('webhooks.coinbase') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-coinbase-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseCryptocom" aria-expanded="{{ ($errors->has('cryptocom') || $errors->has('cryptocom_key') || $errors->has('cryptocom_secret') || $errors->has('cryptocom_wh_secret') ? 'true' : 'false') }}" aria-controls="collapseCryptocom">
                                <img src="{{ asset('img/icons/payments/cryptocom.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Crypto.com"> Crypto.com
                            </button>

                            <div class="collapse {{ ($errors->has('cryptocom') || $errors->has('cryptocom_key') || $errors->has('cryptocom_secret') || $errors->has('cryptocom_wh_secret')  ? 'show' : '') }}" id="collapseCryptocom">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => 'Crypto.com']) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#cryptocom" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-cryptocom">Crypto.com</label>
                                    <select name="cryptocom" id="i-cryptocom" class="custom-select{{ $errors->has('cryptocom') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('cryptocom') !== null && old('cryptocom') == $key) || (config('settings.cryptocom') == $key && old('cryptocom') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('cryptocom'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cryptocom') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => 'Crypto.com']) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-cryptocom-key">{{ __('Publishable key') }}</label>
                                    <input type="text" name="cryptocom_key" id="i-cryptocom-key" class="form-control{{ $errors->has('cryptocom_key') ? ' is-invalid' : '' }}" value="{{ old('cryptocom_key') ?? config('settings.cryptocom_key') }}">
                                    @if ($errors->has('cryptocom_key'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cryptocom_key') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-cryptocom-secret">{{ __('Secret key') }}</label>
                                    <input type="password" name="cryptocom_secret" id="i-cryptocom-secret" class="form-control{{ $errors->has('cryptocom_secret') ? ' is-invalid' : '' }}" value="{{ old('cryptocom_secret') ?? config('settings.cryptocom_secret') }}">
                                    @if ($errors->has('cryptocom_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cryptocom_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-cryptocom-wh-secret">{{ __('Signing secret') }}</label>
                                    <input type="password" name="cryptocom_wh_secret" id="i-cryptocom-wh-secret" class="form-control{{ $errors->has('cryptocom_wh_secret') ? ' is-invalid' : '' }}" value="{{ old('cryptocom_wh_secret') ?? config('settings.cryptocom_wh_secret') }}">
                                    @if ($errors->has('cryptocom_wh_secret'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cryptocom_wh_secret') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-cryptocom-wh-url">{{ __('Webhook URL') }}</label>
                                    <div class="input-group">
                                        <input type="text" dir="ltr" name="cryptocom_wh_url" id="i-cryptocom-wh-url" class="form-control" value="{{ route('webhooks.cryptocom') }}" readonly>
                                        <div class="input-group-append">
                                            <div class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-cryptocom-wh-url">{{ __('Copy') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pb-3">
                            <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseBank" aria-expanded="{{ ($errors->has('bank') || $errors->has('bank_account_owner') || $errors->has('bank_account_number') || $errors->has('bank_name') || $errors->has('bank_routing_number') || $errors->has('bank_iban') || $errors->has('bank_bic_swift') ? 'true' : 'false') }}" aria-controls="collapseBank">
                                <img src="{{ asset('img/icons/payments/bank.svg') }}" class="width-6 rounded-sm {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}" alt="Bank"> {{ __('Bank') }}
                            </button>

                            <div class="collapse {{ ($errors->has('bank') || $errors->has('bank_account_owner') || $errors->has('bank_account_number') || $errors->has('bank_name') || $errors->has('bank_routing_number') || $errors->has('bank_iban') || $errors->has('bank_bic_swift') ? 'show' : '') }}" id="collapseBank">
                                <div class="alert alert-info mt-3">
                                    <div class="row">
                                        <div class="col">
                                            {{ __(':service integration can be configured by following the official documentation.', ['service' => __('Bank')]) }}
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#bank" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3">
                                    <label for="i-bank">{{ __('Bank') }}</label>
                                    <select name="bank" id="i-bank" class="custom-select{{ $errors->has('bank') ? ' is-invalid' : '' }}">
                                        @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                            <option value="{{ $key }}" @if ((old('bank') !== null && old('bank') == $key) || (config('settings.bank') == $key && old('bank') == null)) selected @endif>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('bank'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">
                                        {{ __('Enable or disable payments through :service.', ['service' => __('Bank')]) }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="i-bank-account-owner">{{ __('Account owner') }}</label>
                                    <input type="text" name="bank_account_owner" id="i-bank-account-owner" class="form-control{{ $errors->has('bank_account_owner') ? ' is-invalid' : '' }}" value="{{ old('bank_account_owner') ?? config('settings.bank_account_owner') }}">
                                    @if ($errors->has('bank_account_owner'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_account_owner') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-bank-account-number">{{ __('Account number') }}</label>
                                    <input type="text" name="bank_account_number" id="i-bank-account-number" class="form-control{{ $errors->has('bank_account_number') ? ' is-invalid' : '' }}" value="{{ old('bank_account_number') ?? config('settings.bank_account_number') }}">
                                    @if ($errors->has('bank_account_number'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_account_number') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-bank-name">{{ __('Bank name') }}</label>
                                    <input type="text" name="bank_name" id="i-bank-name" class="form-control{{ $errors->has('bank_name') ? ' is-invalid' : '' }}" value="{{ old('bank_name') ?? config('settings.bank_name') }}">
                                    @if ($errors->has('bank_name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-bank-routing-number">{{ __('Routing number') }}</label>
                                    <input type="text" name="bank_routing_number" id="i-bank-routing-number" class="form-control{{ $errors->has('bank_routing_number') ? ' is-invalid' : '' }}" value="{{ old('bank_routing_number') ?? config('settings.bank_routing_number') }}">
                                    @if ($errors->has('bank_routing_number'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_routing_number') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-bank-iban">{{ __('IBAN') }}</label>
                                    <input type="text" name="bank_iban" id="i-bank-iban" class="form-control{{ $errors->has('bank_iban') ? ' is-invalid' : '' }}" value="{{ old('bank_iban') ?? config('settings.bank_iban') }}">
                                    @if ($errors->has('bank_iban'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_iban') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-bank-bic-swift">{{ __('BIC') }} / {{ __('SWIFT') }}</label>
                                    <input type="text" name="bank_bic_swift" id="i-bank-bic-swift" class="form-control{{ $errors->has('bank_bic_swift') ? ' is-invalid' : '' }}" value="{{ old('bank_bic_swift') ?? config('settings.bank_bic_swift') }}">
                                    @if ($errors->has('bank_bic_swift'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('bank_bic_swift') }}</strong>
                                        </span>
                                    @endif
                                </div>
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
