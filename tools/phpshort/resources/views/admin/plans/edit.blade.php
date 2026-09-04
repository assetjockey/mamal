@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Plan'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['url' => route('admin.plans'), 'title' => __('Plans')],
                ['title' => __('Edit')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                </div>
                <div class="col-auto px-2">
                    <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                    @include('admin.plans.partials.context-menu')
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Plan') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if($plan->trashed())
                        <div class="alert alert-danger">
                            {{ __('This :resource is disabled.', ['resource' => mb_strtolower(__('Plan'))]) }}
                        </div>
                    @endif

                    <form action="{{ route('admin.plans.edit', $plan->id) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="i-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') ?? $plan->name }}">
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-description">{{ __('Description') }}</label>
                            <input type="text" name="description" id="i-description" class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" value="{{ old('description') ?? $plan->description }}">
                            @if ($errors->has('description'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('description') }}</strong>
                                </span>
                            @endif
                        </div>

                        @if(config('settings.license_type'))
                            @if(!$plan->isDefault())
                                <div class="form-group">
                                    <label for="i-trial-days">{{ __('Trial days') }}</label>
                                    <input type="number" name="trial_days" id="i-trial-days" class="form-control{{ $errors->has('trial_days') ? ' is-invalid' : '' }}" value="{{ old('trial_days') ?? $plan->trial_days }}">
                                    @if ($errors->has('trial_days'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('trial_days') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-currency">{{ __('Currency') }}</label>
                                    <select name="currency" id="i-currency" class="custom-select{{ $errors->has('currency') ? ' is-invalid' : '' }}">
                                        @foreach(config('currencies.all') as $key => $value)
                                            <option value="{{ $key }}" @if((old('currency') !== null && old('currency') == $key) || ($plan->currency == $key && old('currency') == null)) selected @endif>{{ $key }} - {{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('currency'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('currency') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="row mx-n2">
                                    <div class="col-12 col-lg-6 px-2">
                                        <div class="form-group">
                                            <label for="i-amount-month">{{ __('Monthly amount') }}</label>
                                            <input type="text" name="amount_month" id="i-amount-month" class="form-control{{ $errors->has('amount_month') ? ' is-invalid' : '' }}" value="{{ old('amount_month') ?? $plan->amount_month }}">
                                            @if ($errors->has('amount_month'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('amount_month') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-6 px-2">
                                        <div class="form-group">
                                            <label for="i-amount-year">{{ __('Yearly amount') }}</label>
                                            <input type="text" name="amount_year" id="i-amount-year" class="form-control{{ $errors->has('amount_year') ? ' is-invalid' : '' }}" value="{{ old('amount_year') ?? $plan->amount_year }}">
                                            @if ($errors->has('amount_year'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('amount_year') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-n2">
                                    <div class="col-12 col-lg-6 px-2">
                                        <div class="form-group">
                                            <label for="i-tax-rates">{{ __('Tax rates') }}</label>
                                            <select name="tax_rates[]" id="i-tax-rates" class="custom-select{{ $errors->has('tax_rates') ? ' is-invalid' : '' }}" size="3" multiple>
                                                @foreach($taxRates as $taxRate)
                                                    <option value="{{ $taxRate->id }}" @if(old('taxRates') !== null && in_array($taxRate->id, old('taxRates')) || old('taxRates') == null && is_array($plan->tax_rates) && in_array($taxRate->id, $plan->tax_rates)) selected @endif>{{ $taxRate->name }} ({{ number_format($taxRate->percentage, 2, __('.'), __(',')) }}% {{ ($taxRate->type ? __('Exclusive') : __('Inclusive')) }})</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('tax_rates'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('tax_rates') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-6 px-2">
                                        <div class="form-group">
                                            <label for="i-coupons">{{ __('Coupons') }}</label>
                                            <select name="coupons[]" id="i-coupons" class="custom-select{{ $errors->has('coupons') ? ' is-invalid' : '' }}" size="3" multiple>
                                                @foreach($coupons as $coupon)
                                                    <option value="{{ $coupon->id }}" @if(old('coupons') !== null && in_array($coupon->id, old('coupons')) || old('coupons') == null && is_array($plan->coupons) && in_array($coupon->id, $plan->coupons)) selected @endif>{{ $coupon->code }} ({{ number_format($coupon->percentage, 2, __('.'), __(',')) }}% {{ ($coupon->type ? __('Redeemable') : __('Discount')) }})</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('coupons'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('coupons') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="i-visibility">{{ __('Visibility') }}</label>
                                <select name="visibility" id="i-visibility" class="custom-select{{ $errors->has('visibility') ? ' is-invalid' : '' }}">
                                    @foreach([1 => __('Public'), 0 => __('Unlisted')] as $key => $value)
                                        <option value="{{ $key }}" @if((old('visibility') !== null && old('visibility') == $key) || ($plan->visibility == $key && old('visibility') == null)) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('visibility'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('visibility') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="i-position">{{ __('Position') }}</label>
                                <input type="number" name="position" id="i-position" class="form-control{{ $errors->has('position') ? ' is-invalid' : '' }}" value="{{ old('position') ?? $plan->position }}">
                                @if ($errors->has('position'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('position') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('The display position of the plan on the pricing page.') }} {{ __('Lower values appear first.') }}</small>
                            </div>
                        @endif

                        <div class="row mx-n2 mb-4">
                            <div class="col-auto fw-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Features') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-full">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="i-features-links">{{ __('Links') }}</label>
                            <input type="number" name="features[links]" id="i-features-links" class="form-control{{ $errors->has('features.links') ? ' is-invalid' : '' }}" value="{{ old('features.links') ?? $plan->features->links }}">
                            @if ($errors->has('features.links'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.links') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __(':value for unlimited.', ['value' => '<code class="badge badge-secondary">-1</code>']) !!} {!! __(':value for none.', ['value' => '<code class="badge badge-secondary">0</code>']) !!} {!! __(':value for number.', ['value' => '<code class="badge badge-secondary">N</code>']) !!}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-spaces">{{ __('Spaces') }}</label>
                            <input type="number" name="features[spaces]" id="i-features-spaces" class="form-control{{ $errors->has('features.spaces') ? ' is-invalid' : '' }}" value="{{ old('features.spaces') ?? $plan->features->spaces }}">
                            @if ($errors->has('features.spaces'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.spaces') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __(':value for unlimited.', ['value' => '<code class="badge badge-secondary">-1</code>']) !!} {!! __(':value for none.', ['value' => '<code class="badge badge-secondary">0</code>']) !!} {!! __(':value for number.', ['value' => '<code class="badge badge-secondary">N</code>']) !!}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-domains">{{ __('Domains') }}</label>
                            <input type="number" name="features[domains]" id="i-features-domains" class="form-control{{ $errors->has('features.domains') ? ' is-invalid' : '' }}" value="{{ old('features.domains') ?? $plan->features->domains }}">
                            @if ($errors->has('features.domains'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.domains') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __(':value for unlimited.', ['value' => '<code class="badge badge-secondary">-1</code>']) !!} {!! __(':value for none.', ['value' => '<code class="badge badge-secondary">0</code>']) !!} {!! __(':value for number.', ['value' => '<code class="badge badge-secondary">N</code>']) !!}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-additional-domains">{{ __('Additional domains') }}</label>
                            <select name="features[additional_domains]" id="i-features-additional-domains" class="custom-select{{ $errors->has('features.additional_domains') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.additional_domains') !== null && old('features.additional_domains') == $key) || ($plan->features->additional_domains == $key && old('features.additional_domains') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.additional_domains'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.additional_domains') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{{ __('When enabled, users can use the domains added from the admin panel.') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-pixels">{{ __('Pixels') }}</label>
                            <input type="number" name="features[pixels]" id="i-features-pixels" class="form-control{{ $errors->has('features.pixels') ? ' is-invalid' : '' }}" value="{{ old('features.pixels') ?? $plan->features->pixels }}">
                            @if ($errors->has('features.pixels'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.pixels') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __(':value for unlimited.', ['value' => '<code class="badge badge-secondary">-1</code>']) !!} {!! __(':value for none.', ['value' => '<code class="badge badge-secondary">0</code>']) !!} {!! __(':value for number.', ['value' => '<code class="badge badge-secondary">N</code>']) !!}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-stats">{{ __('Link stats') }}</label>
                            <select name="features[link_stats]" id="i-features-link-stats" class="custom-select{{ $errors->has('features.link_stats') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.stats') !== null && old('features.stats') == $key) || ($plan->features->link_stats == $key && old('features.stats') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_stats'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.stats') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-targeting">{{ __('Link targeting') }}</label>
                            <select name="features[link_targeting]" id="i-features-link-targeting" class="custom-select{{ $errors->has('features.link_targeting') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_targeting') !== null && old('features.link_targeting') == $key) || ($plan->features->link_targeting == $key && old('features.link_targeting') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_targeting'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_targeting') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-alias">{{ __('Link alias') }}</label>
                            <select name="features[link_alias]" id="i-features-link-alias" class="custom-select{{ $errors->has('features.link_alias') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_alias') !== null && old('features.link_alias') == $key) || ($plan->features->link_alias == $key && old('features.link_alias') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_alias'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_alias') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-password">{{ __('Link password') }}</label>
                            <select name="features[link_password]" id="i-features-link-password" class="custom-select{{ $errors->has('features.link_password') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_password') !== null && old('features.link_password') == $key) || ($plan->features->link_password == $key && old('features.link_password') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-expiration">{{ __('Link expiration') }}</label>
                            <select name="features[link_expiration]" id="i-features-link-expiration" class="custom-select{{ $errors->has('features.link_expiration') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_expiration') !== null && old('features.link_expiration') == $key) || ($plan->features->link_expiration == $key && old('features.link_expiration') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_expiration'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_expiration') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-deep">{{ __('Deep linking') }}</label>
                            <select name="features[link_deep]" id="i-features-link-deep" class="custom-select{{ $errors->has('features.link_deep') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_deep') !== null && old('features.link_deep') == $key) || ($plan->features->link_deep == $key && old('features.link_deep') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_deep'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_deep') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-link-utm">{{ __('UTM builder') }}</label>
                            <select name="features[link_utm]" id="i-features-link-utm" class="custom-select{{ $errors->has('features.link_utm') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.link_utm') !== null && old('features.link_utm') == $key) || ($plan->features->link_utm == $key && old('features.link_utm') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.link_utm'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.link_utm') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-data-retention">{{ __('Data retention') }}</label>
                            <input type="number" name="features[data_retention]" id="i-features-data-retention" class="form-control{{ $errors->has('features.data_retention') ? ' is-invalid' : '' }}" value="{{ old('features.data_retention') ?? $plan->features->data_retention }}">
                            @if ($errors->has('features.data_retention'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.data_retention') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">{!! __('The number of days the :list data will be retained.', ['list' => '<span class="fw-medium">' . __('Stats') . '</span>']) !!} {!! __(':value for unlimited.', ['value' => '<code class="badge badge-secondary">-1</code>']) !!} {!! __(':value for number.', ['value' => '<code class="badge badge-secondary">N</code>']) !!}</small>
                        </div>

                        <div class="form-group">
                            <label for="i-features-data-export">{{ __('Data export') }}</label>
                            <select name="features[data_export]" id="i-features-data-export" class="custom-select{{ $errors->has('features.data_export') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.data_export') !== null && old('features.data_export') == $key) || ($plan->features->data_export == $key && old('features.data_export') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.data_export'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.data_export') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-api">{{ __('API') }}</label>
                            <select name="features[api]" id="i-features-api" class="custom-select{{ $errors->has('features.api') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.api') !== null && old('features.api') == $key) || ($plan->features->api == $key && old('features.api') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.api'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.api') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-features-no-ads">{{ __('No ads') }}</label>
                            <select name="features[no_ads]" id="i-features-no-ads" class="custom-select{{ $errors->has('features.no_ads') ? ' is-invalid' : '' }}">
                                @foreach([1 => __('Enabled'), 0 => __('Disabled')] as $key => $value)
                                    <option value="{{ $key }}" @if((old('features.no_ads') !== null && old('features.no_ads') == $key) || ($plan->features->no_ads == $key && old('features.no_ads') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('features.no_ads'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('features.no_ads') }}</strong>
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
