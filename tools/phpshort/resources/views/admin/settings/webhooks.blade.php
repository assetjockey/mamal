@extends('layouts.app')

@section('site_title', formatTitle([__('Webhooks'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Webhooks') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Webhooks') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-info">
                        {{ __('Webhooks can be used to automatically notify external systems when certain events occur.') }}
                    </div>

                    <div class="row mx-n1">
                        <div class="col-12 px-1">
                            <label for="i-webhook-secret-key">{{ __('Webhook secret key') }}</label>
                        </div>
                        <div class="col px-1">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" dir="ltr" name="webhook" id="i-webhook-secret-key" class="form-control" value="{{ config('settings.webhook_secret_key') }}" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-webhook-secret-key">{{ __('Copy') }}</button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    {!! __('This key will be included in the :header header of each webhook request, using the :scheme scheme.', ['header' => '<code>Authorization</code>', 'scheme' => '<code>Bearer</code>']) !!} {{ __('You can use it to verify the authenticity of the request source.') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-auto px-1">
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal" data-button-name="webhook_secret_key" data-action="{{ route('admin.settings', 'webhook_secret_key') }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Regenerate') }}" data-text="{{ __('If you regenerate the cron job key, you will need to update the cron job task with the new command.') }}" data-sub-text="{{ __('Are you sure you want to regenerate the :name key?', ['name' => mb_strtolower(__('Cron job'))]) }}">{{ __('Regenerate') }}</button>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings', 'webhooks') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="pb-4">
                            <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseUser" aria-expanded="{{ ($errors->has('webhook_user_created') || $errors->has('webhook_user_updated') || $errors->has('webhook_user_deleted') ? 'true' : 'false') }}" aria-controls="collapseUser">
                                @include('icons.people-alt', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('User') }}
                            </button>

                            <div class="collapse {{ ($errors->has('webhook_user_created') || $errors->has('webhook_user_updated') || $errors->has('webhook_user_deleted') ? 'show' : '') }}" id="collapseUser">
                                <div class="form-group mt-4">
                                    <label for="i-webhook-user-created" class="d-inline-flex align-items-center"><span class="badge badge-info">{{ __('Store') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_user_created" id="i-webhook-user-created" class="form-control{{ $errors->has('webhook_user_created') ? ' is-invalid' : '' }}" value="{{ old('webhook_user_created') ?? config('settings.webhook_user_created') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_user_created'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_user_created') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a user is created.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'name', 'email', 'email_verified_at', 'locale', 'timezone', 'action']) . '</code>']) !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-webhook-user-updated" class="d-inline-flex align-items-center"><span class="badge badge-warning">{{ __('Update') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_user_updated" id="i-webhook-user-updated" class="form-control{{ $errors->has('webhook_user_updated') ? ' is-invalid' : '' }}" value="{{ old('webhook_user_updated') ?? config('settings.webhook_user_updated') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_user_updated'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_user_updated') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a user is updated.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'name', 'email', 'email_verified_at', 'locale', 'timezone', 'action']) . '</code>']) !!}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-webhook-user-deleted" class="d-inline-flex align-items-center"><span class="badge badge-danger">{{ __('Delete') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_user_deleted" id="i-webhook-user-deleted" class="form-control{{ $errors->has('webhook_user_deleted') ? ' is-invalid' : '' }}" value="{{ old('webhook_user_deleted') ?? config('settings.webhook_user_deleted') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_user_deleted'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_user_deleted') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a user is deleted.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'name', 'email', 'email_verified_at', 'locale', 'timezone', 'action']) . '</code>']) !!}</small>
                                </div>
                            </div>
                        </div>

                        <div class="pb-4">
                            <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapsePayment" aria-expanded="{{ ($errors->has('webhook_payment_created') || $errors->has('webhook_payment_updated') ? 'true' : 'false') }}" aria-controls="collapsePayment">
                                @include('icons.credit-card', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Payment') }}
                            </button>

                            <div class="collapse {{ ($errors->has('webhook_payment_created') || $errors->has('webhook_payment_updated') ? 'show' : '') }}" id="collapsePayment">
                                <div class="form-group mt-4">
                                    <label for="i-webhook-payment-created" class="d-inline-flex align-items-center"><span class="badge badge-info">{{ __('Store') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_payment_created" id="i-webhook-payment-created" class="form-control{{ $errors->has('webhook_payment_created') ? ' is-invalid' : '' }}" value="{{ old('webhook_payment_created') ?? config('settings.webhook_payment_created') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_payment_created'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_payment_created') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a payment is created.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'user_id', 'plan_id', 'payment_id', 'invoice_id', 'processor', 'amount', 'currency', 'interval', 'status', 'product', 'coupon', 'tax_rates', 'seller', 'customer', 'created_at', 'updated_at', 'action']) . '</code>']) !!}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-webhook-payment-updated" class="d-inline-flex align-items-center"><span class="badge badge-warning">{{ __('Update') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_payment_updated" id="i-webhook-payment-updated" class="form-control{{ $errors->has('webhook_payment_updated') ? ' is-invalid' : '' }}" value="{{ old('webhook_payment_updated') ?? config('settings.webhook_payment_updated') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_payment_updated'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_payment_updated') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a payment is updated.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'user_id', 'plan_id', 'payment_id', 'invoice_id', 'processor', 'amount', 'currency', 'interval', 'status', 'product', 'coupon', 'tax_rates', 'seller', 'customer', 'created_at', 'updated_at', 'action']) . '</code>']) !!}</small>
                                </div>
                            </div>
                        </div>

                        <div class="pb-4">
                            <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseDomain" aria-expanded="{{ ($errors->has('webhook_domain_created') || $errors->has('webhook_domain_updated') ? 'true' : 'false') }}" aria-controls="collapseDomain">
                                @include('icons.website', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Domain') }}
                            </button>

                            <div class="collapse {{ ($errors->has('webhook_domain_created') || $errors->has('webhook_domain_updated') ? 'show' : '') }}" id="collapseDomain">
                                <div class="form-group mt-4">
                                    <label for="i-webhook-domain-created" class="d-inline-flex align-items-center"><span class="badge badge-info">{{ __('Store') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_domain_created" id="i-webhook-domain-created" class="form-control{{ $errors->has('webhook_domain_created') ? ' is-invalid' : '' }}" value="{{ old('webhook_domain_created') ?? config('settings.webhook_domain_created') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_domain_created'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_domain_created') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a domain is created.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'name', 'action']) . '</code>']) !!}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="i-webhook-domain-deleted" class="d-inline-flex align-items-center"><span class="badge badge-danger">{{ __('Delete') }}</span></label>
                                    <input type="text" dir="ltr" name="webhook_domain_deleted" id="i-webhook-domain-deleted" class="form-control{{ $errors->has('webhook_domain_deleted') ? ' is-invalid' : '' }}" value="{{ old('webhook_domain_deleted') ?? config('settings.webhook_domain_deleted') }}" placeholder="https://example.com">
                                    @if ($errors->has('webhook_domain_deleted'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('webhook_domain_deleted') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __(':fields fields are being sent when a domain is deleted.', ['fields' => '<code class="badge badge-secondary">' . implode('</code>, <code class="badge badge-secondary">', ['id', 'name', 'action']) . '</code>']) !!}</small>
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
