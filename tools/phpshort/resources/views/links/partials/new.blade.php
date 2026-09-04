@include('shared.toasts.link')

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <form action="{{ route('links.new') }}" method="post" enctype="multipart/form-data" autocomplete="off" id="form-link">
            @csrf

            <div class="row">
                <div class="col-12">
                    <div class="row mx-n1">
                        <div class="col-12 col-md px-1">
                            <div class="single-link d-none{{ (old('multiple_links') == 0 || old('multiple_links') == null) && count(request()->session()->get('toast')) <= 1 ? ' d-block' : '' }}">
                                <div class="input-group input-group-lg">
                                    <input type="text" dir="ltr" name="url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }} fs-lg" autocapitalize="none" spellcheck="false" id="i-url" value="{{ old('url') }}" placeholder="{{ __('Type or paste a link') }}" autofocus>

                                    <div class="input-group-append" data-tooltip="true" title="{{ __('UTM builder') }}">
                                        <a href="#" class="btn text-secondary bg-transparent input-group-text d-flex align-items-center" data-toggle="modal" data-target="#utm-modal" id="utm-builder">
                                            @include('icons.label', ['class' => 'fill-current w-4 h-4'])
                                        </a>
                                    </div>
                                </div>
                                @if ($errors->has('url'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('url') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="multiple-links d-none {{ old('multiple_links') || count(request()->session()->get('toast')) > 1 ? ' d-block' : '' }}">
                                <textarea class="form-control form-control-lg fs-lg {{ $errors->has('urls') ? ' is-invalid' : '' }} field-sizing-content min-h-22" rows="3" name="urls" id="i-urls" autocapitalize="none" spellcheck="false" dir="ltr">{{ old('urls') }}</textarea>
                                @if ($errors->has('urls'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('urls') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('Shorten up to :count links at once.', ['count' => config('settings.short_max_multi_links')]) }} {{ __('One per line.') }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-auto mt-4 mt-md-0 px-1">
                            <div class="row mx-n1">
                                <div class="col px-1">
                                    <div class="d-flex flex-wrap">
                                        <div class="btn-group btn-group-toggle d-flex flex-fill" data-toggle="buttons">
                                            <label class="btn btn-lg fs-lg btn-outline-primary w-full d-flex align-items-center justify-content-center {{ old('multiple_links') == 0 && count(request()->session()->get('toast')) <= 1 ? ' active' : ''}}" data-tooltip="true" title="{{ __('Single') }}" id="single-link">
                                                <input type="radio" name="multiple_links" id="i-multiple-links" value="0"{{ old('multiple_links') == 0 && count(request()->session()->get('toast')) <= 1 ? ' checked' : ''}}>
                                                @include('icons.crop-16-9', ['class' => 'w-4 h-4 fill-current'])&#8203;
                                            </label>
                                            <label class="btn btn-lg fs-lg btn-outline-primary w-full d-flex align-items-center justify-content-center{{ old('multiple_links') || count(request()->session()->get('toast')) > 1 ? ' active' : ''}}" data-tooltip="true" title="{{ __('Multiple') }}" id="multiple-links">
                                                <input type="radio" name="multiple_links" value="1"{{ old('multiple_links') || count(request()->session()->get('toast')) > 1 ? ' checked' : '' }}>
                                                @include('icons.view-agenda', ['class' => 'w-4 h-4 fill-current'])&#8203;
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col px-1">
                                    <a href="#" class="btn btn-lg btn-outline-primary fs-lg d-flex align-items-center justify-content-center" data-toggle="collapse" data-target="#advanced-options" aria-expanded="false" data-tooltip="true" title="{{ __('Advanced') }}">@include('icons.settings', ['class' => 'fill-current w-4 h-4'])&#8203;</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-auto mt-4 mt-md-0 px-1">
                            <button class="btn btn-primary btn-lg btn-block fs-lg position-relative" type="submit" data-button-loader>
                                <span class="position-absolute top-0 end-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                                    <span class="d-none spinner-border spinner-border-sm w-4 h-4" role="status"></span>
                                </span>
                                <span class="spinner-text">{{ __('Shorten') }}</span>&#8203;
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 collapse{{ ($errors->has('alias') || $errors->has('domain_id') || $errors->has('space_id') || $errors->has('pixel_ids') || $errors->has('active_period_start_at') || $errors->has('active_period_end_at') || $errors->has('clicks_limit') || $errors->has('expiration_url') || $errors->has('redirect_password') || $errors->has('sensitive_content') || $errors->has('privacy') || $errors->has('password') || $errors->has('targets_type') || $errors->has('targets.*.key') || $errors->has('targets.*.value')) ? ' show' : '' }}" id="advanced-options">
                    <div class="row mx-n2 mt-4">
                        <div class="col-12 col-md-6 px-2">
                            <div class="form-group">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-domain" class="d-flex align-items-center mb-0">
                                        {{ __('Domain') }}
                                    </label>
                                    @cannot('domains', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <select name="domain_id" id="i-domain" class="custom-select{{ $errors->has('domain_id') ? ' is-invalid' : '' }}">
                                    @foreach($domains->filter(function ($i) { if ($i->user_id || $i->id == config('settings.short_domain_id') || Auth::user()->can('additionalDomains', [App\Models\User::class])) { return $i; } }) as $domain)
                                        <option value="{{ $domain->id }}" @if(old('domain_id') == $domain->id) selected @elseif($latestLink && $latestLink->domain_id == $domain->id && old('domain_id') == null) selected @endif>{{ $domain->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('domain_id'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('domain_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-md-6 px-1">
                            <div class="form-group">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-alias" class="d-flex align-items-center mb-0">
                                        {{ __('Alias') }}
                                    </label>
                                    @cannot('linkAlias', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <input type="text" name="alias" class="form-control{{ $errors->has('alias') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" id="i-alias" value="{{ old('alias') }}" {{ old('multiple_links') == 0 && count(request()->session()->get('toast')) <= 1 ? '' : ' disabled' }} @cannot('linkAlias', [App\Models\User::class]) disabled @endcannot>
                                @if ($errors->has('alias'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('alias') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex align-items-center mb-2">
                            <label for="i-space" class="d-flex align-items-center mb-0">
                                {{ __('Space') }}
                            </label>
                            @cannot('spaces', [App\Models\User::class])
                                @if(enabledPaymentProcessors())
                                    <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                @endif
                            @endcannot
                        </div>
                        <select name="space_id" id="i-space" class="custom-select{{ $errors->has('space_id') ? ' is-invalid' : '' }}" @cannot('spaces', [App\Models\User::class]) disabled @endcannot>
                            <option value="">{{ __('None') }}</option>
                            @foreach($spaces as $space)
                                <option value="{{ $space->id }}" @if(old('space_id') == $space->id) selected @elseif($latestLink && $latestLink->domain_id == $space->id) selected @endif>{{ $space->name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('space_id'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('space_id') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <div class="d-flex align-items-center mb-2">
                            <label for="i-pixel-ids" class="d-flex align-items-center mb-0">
                                {{ __('Pixels') }}
                            </label>
                            @cannot('pixels', [App\Models\User::class])
                                @if(enabledPaymentProcessors())
                                    <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                @endif
                            @endcannot
                        </div>

                        <input type="hidden" name="pixel_ids[]" value="">
                        <select name="pixel_ids[]" id="i-pixel-ids" class="custom-select{{ $errors->has('pixel_ids') ? ' is-invalid' : '' }}" size="{{ (count($pixels) == 0 ? 1 : 3) }}" @cannot('pixels', [App\Models\User::class]) disabled @endcannot multiple>
                            @foreach($pixels as $pixel)
                                <option value="{{ $pixel->id }}" @if(old('pixel_ids') !== null && is_array(old('pixel_ids')) && in_array($pixel->id, old('pixel_ids'))) selected @endif>{{ $pixel->name }} ({{ config('pixels')[$pixel->type]['name'] }})</option>
                            @endforeach
                        </select>
                        @if ($errors->has('pixel_ids'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('pixel_ids') }}</strong>
                            </span>
                        @endif

                        <small class="form-text text-muted">{!! __('Hold :ctrl or :cmd to select or deselect multiple items.', ['ctrl' => '<kbd>CTRL</kbd>', 'cmd' => '<kbd>CMD</kbd>']) !!}</small>
                    </div>

                    <div class="pb-4">
                        <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseSchedules" aria-expanded="{{ ($errors->has('active_period_start_at') || $errors->has('active_period_end_at') || $errors->has('clicks_limit') || $errors->has('expiration_url') ? 'true' : 'false') }}" aria-controls="collapseSchedules">
                            @include('icons.date-range', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Schedules') }}
                        </button>

                        <div class="collapse {{ ($errors->has('active_period_start_at') || $errors->has('active_period_end_at') || $errors->has('clicks_limit') || $errors->has('expiration_url') ? 'show' : '') }}" id="collapseSchedules">
                            <div class="row mx-n2 mt-4">
                                <div class="col-12 px-2"><label for="i-active-period-start-at">{{ __('Active period') }}</label></div>
                                <div class="col-12 col-lg-6 px-2">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label for="i-active-period-start-at" class="input-group-text">{{ __('Start') }}</label>
                                            </div>
                                            <input type="datetime-local" step="1" dir="ltr" name="active_period_start_at" class="form-control{{ $errors->has('active_period_start_at') ? ' is-invalid' : '' }}" id="i-active-period-start-at" value="{{ old('active_period_start_at') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
                                        </div>
                                        @if ($errors->has('active_period_start_at'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('active_period_start_at') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6 px-2">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label for="i-active-period-end-at" class="input-group-text">{{ __('End') }}</label>
                                            </div>
                                            <input type="datetime-local" step="1" dir="ltr" name="active_period_end_at" class="form-control{{ $errors->has('active_period_end_at') ? ' is-invalid' : '' }}" id="i-active-period-end-at" value="{{ old('active_period_end_at') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
                                        </div>
                                        @if ($errors->has('active_period_end_at'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('active_period_end_at') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 mt-n4 mb-4 px-2">
                                    <small class="form-text text-muted">{{ __('The period during which the link will be active.') }}</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-clicks-limit" class="d-flex align-items-center mb-0">
                                        {{ __('Clicks limit') }}
                                    </label>
                                    @cannot('linkExpiration', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <input type="number" name="clicks_limit" id="i-clicks-limit" class="form-control {{ $errors->has('clicks_limit') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" value="{{ old('clicks_limit') }}" @cannot('linkExpiration', [App\Models\User::class]) disabled @endcannot>
                                @if ($errors->has('clicks_limit'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('clicks_limit') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('The number of clicks the link will be active for.') }}</small>
                            </div>

                            <div class="form-group mb-0">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-expiration-url" class="d-flex align-items-center mb-0">
                                        {{ __('Expiration URL') }}
                                    </label>
                                    @cannot('linkExpiration', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <div class="input-group">
                                    <input type="text" dir="ltr" name="expiration_url" id="i-expiration-url" class="form-control{{ $errors->has('expiration_url') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" value="{{ old('expiration_url') }}" @cannot('linkExpiration', [App\Models\User::class]) disabled @endcannot>
                                </div>
                                @if ($errors->has('expiration_url'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('expiration_url') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('The URL to redirect to once the link has expired.') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseProtections" aria-expanded="{{ ($errors->has('redirect_password') || $errors->has('sensitive_content') ? 'true' : 'false') }}" aria-controls="collapseProtections">
                            @include('icons.security', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Protections') }}
                        </button>

                        <div class="collapse {{ ($errors->has('redirect_password') || $errors->has('sensitive_content') ? 'show' : '') }}" id="collapseProtections">
                            <div class="form-group mt-4">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-redirect-password" class="d-flex align-items-center mb-0">
                                        {{ __('Redirect password') }}
                                    </label>
                                    @cannot('linkPassword', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <div class="input-group">
                                    <input type="password" name="redirect_password" class="form-control{{ $errors->has('redirect_password') ? ' is-invalid' : '' }}" id="i-redirect-password" value="{{ old('redirect_password') }}" autocomplete="new-password" @cannot('linkPassword', [App\Models\User::class]) disabled @endcannot>
                                    <div class="input-group-append">
                                        <div class="input-group-text cursor-pointer" data-tooltip="true" data-title="{{ __('Show password') }}" data-password="i-redirect-password" data-password-show-text="{{ __('Show password') }}" data-password-hide-text="{{ __('Hide password') }}">@include('icons.visibility', ['class' => 'w-4 h-4 fill-current text-muted'])@include('icons.visibility_off', ['class' => 'w-4 h-4 fill-current text-muted d-none'])</div>
                                    </div>
                                </div>
                                @if ($errors->has('redirect_password'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('redirect_password') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('Require the user to enter a password to access the link.') }}</small>
                            </div>

                            <div class="form-group mb-0">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-sensitive-content" class="d-flex align-items-center mb-0">
                                        {{ __('Sensitive content') }}
                                    </label>
                                </div>
                                <select name="sensitive_content" id="i-sensitive-content" class="custom-select{{ $errors->has('sensitive_content') ? ' is-invalid' : '' }}">
                                    @foreach([0 => __('No'), 1 => __('Yes')] as $key => $value)
                                        <option value="{{ $key }}" @if (old('sensitive_content') !== null && old('sensitive_content') == $key) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('sensitive_content'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('sensitive_content') }}</strong>
                                    </span>
                                @endif
                                <small class="form-text text-muted">{{ __('Inform the user that the link contains sensitive content and require consent to access the link.') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseStats" aria-expanded="{{ ($errors->has('privacy') ? 'true' : 'false') }}" aria-controls="collapseStats">
                            @include('icons.bar-chart', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Stats') }}
                        </button>

                        <div class="collapse {{ ($errors->has('privacy') || $errors->has('password') ? 'show' : '') }}" id="collapseStats">
                            <div class="form-group mt-4 mb-0">
                                <div class="d-flex align-items-center mb-2">
                                    <label for="i-privacy" class="d-flex align-items-center mb-0">
                                        {{ __('Privacy') }}
                                    </label>
                                    @cannot('linkStats', [App\Models\User::class])
                                        @if(enabledPaymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center ms-2">@include('icons.lock-open', ['class' => 'fill-current text-primary w-4 h-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                                <div class="form-group mb-0">
                                    <div class="row mx-n2">
                                        <div class="col-12 col-lg-4 px-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if(old('privacy') == null || old('privacy') == 1) checked @elseif($latestLink && $latestLink->privacy == 1 && old('privacy') == null) checked @endif @cannot('linkStats', [App\Models\User::class]) disabled @endcannot>
                                                <label class="custom-control-label w-full d-flex flex-column" for="i-privacy1">
                                                    <span>{{ __('Private') }}</span>
                                                    <span class="small text-muted">{{ __('Stats accessible only by you.') }}</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-4 px-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if(old('privacy') == 0 && old('privacy') != null) checked @elseif($latestLink && $latestLink->privacy == 0 && old('privacy') == null) checked @endif @cannot('linkStats', [App\Models\User::class]) disabled @endcannot>
                                                <label class="custom-control-label w-full d-flex flex-column" for="i-privacy0">
                                                    <span>{{ __('Public') }}</span>
                                                    <span class="small text-muted">{{ __('Stats accessible by anyone.') }}</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-4 px-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if(old('privacy') == 2) checked @endif @cannot('linkStats', [App\Models\User::class]) disabled @endcannot>
                                                <label class="custom-control-label w-full d-flex flex-column" for="i-privacy2">
                                                    <span>{{ __('Password') }}</span>
                                                    <span class="small text-muted">{{ __('Stats accessible by password.') }}</span>
                                                </label>

                                                <div id="input-password" class="{{ (old('privacy') != 2 ? 'd-none' : '') }}">
                                                    <div class="input-group mt-2">
                                                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') }}" autocomplete="new-password">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text cursor-pointer" data-tooltip="true" data-title="{{ __('Show password') }}" data-password="i-password" data-password-show-text="{{ __('Show password') }}" data-password-hide-text="{{ __('Hide password') }}">@include('icons.visibility', ['class' => 'w-4 h-4 fill-current text-muted'])@include('icons.visibility_off', ['class' => 'w-4 h-4 fill-current text-muted d-none'])</div>
                                                        </div>
                                                    </div>
                                                    @if ($errors->has('password'))
                                                    <span class="invalid-feedback d-block" role="alert">
                                                        <strong>{{ $errors->first('password') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($errors->has('privacy'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('privacy') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button class="btn btn-soft-inverse d-block w-full d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseTargets" aria-expanded="{{ ($errors->has('targets_type') || $errors->has('targets') || $errors->has('targets.*.key') || $errors->has('targets.*.value') ? 'true' : 'false') }}" aria-controls="collapseTargets">
                            @include('icons.gps-fixed', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('Targets') }}
                        </button>

                        <div class="collapse {{ ($errors->has('targets_type') || $errors->has('targets') || $errors->has('targets.*.key') || $errors->has('targets.*.value') ? 'show' : '') }}" id="collapseTargets">
                            <div class="form-group mt-4 mb-0">
                                <label for="i-targets-type">{{ __('Target') }}</label>

                                @if ($errors->has('targets'))
                                    <span class="invalid-feedback d-block mt-0 mb-2" role="alert">
                                        <strong>{{ $errors->first('targets') }}</strong>
                                    </span>
                                @endif

                                <div class="mb-4">
                                    <div class="form-group">
                                        <select name="targets_type" id="i-targets-type" class="custom-select{{ $errors->has('targets_type') ? ' is-invalid' : '' }}">
                                            <option value="">{{ __('None') }}</option>
                                            @foreach(config('targets') as $key => $value)
                                                <option value="{{ $key }}" @if(old('targets_type') !== null && old('targets_type') == $key) selected @endif>{{ __($value) }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('targets_type'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('targets_type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="{{ old('targets_type') && empty(old('targets_type')) ? '' : 'd-none' }}" data-inputs-container=""></div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'continents' ? '' : 'd-none' }}" data-inputs-container="continents">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="continents_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Continent') }}</option>
                                                    @foreach(config('continents') as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto d-flex align-items-start px-1">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'continents') {
                                                    $continentList = old('targets');
                                                } else {
                                                    $continentList = [];
                                                }
                                            @endphp

                                            @foreach($continentList as $id => $continent)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                            <option value="">{{ __('Continent') }}</option>
                                                            @foreach(config('continents') as $key => $value)
                                                                <option value="{{ $key }}" @if($continent['key'] == $key) selected @endif>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $continent['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto d-flex align-items-start px-1">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'countries' ? '' : 'd-none' }}" data-inputs-container="countries">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="countries_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Country') }}</option>
                                                    @foreach(config('countries') as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'countries') {
                                                    $countryList = old('targets');
                                                } else {
                                                    $countryList = [];
                                                }
                                            @endphp

                                            @foreach($countryList as $id => $country)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                                <option value="">{{ __('Country') }}</option>
                                                                @foreach(config('countries') as $key => $value)
                                                                    <option value="{{ $key }}" @if($country['key'] == $key) selected @endif>{{ $value }}</option>
                                                                @endforeach
                                                            </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $country['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto px-1 d-flex align-items-start">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'operating_systems' ? '' : 'd-none' }}" data-inputs-container="operating_systems">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="operating_systems_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Operating system') }}</option>
                                                    @foreach(config('operating_systems') as $operatingSystem)
                                                        <option value="{{ $operatingSystem }}">{{ $operatingSystem }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'operating_systems') {
                                                    $operatingSystemList = old('targets');
                                                } else {
                                                    $operatingSystemList = [];
                                                }
                                            @endphp

                                            @foreach($operatingSystemList as $id => $operatingSystem)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                            <option value="">{{ __('Operating system') }}</option>
                                                            @foreach(config('operating_systems') as $value)
                                                                <option value="{{ $value }}" @if($operatingSystem['key'] == $value) selected @endif>{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $operatingSystem['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto px-1 d-flex align-items-start">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'browsers' ? '' : 'd-none' }}" data-inputs-container="browsers">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="browsers_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Browser') }}</option>
                                                    @foreach(config('browsers') as $value)
                                                        <option value="{{ $value }}">{{ __($value) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'browsers') {
                                                    $browserList = old('targets');
                                                } else {
                                                    $browserList = [];
                                                }
                                            @endphp

                                            @foreach($browserList as $id => $browser)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                            <option value="">{{ __('Browser') }}</option>
                                                            @foreach(config('browsers') as $value)
                                                                <option value="{{ $value }}" @if($browser['key'] == $value) selected @endif>{{ __($value) }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $browser['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto px-1 d-flex align-items-start">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'languages' ? '' : 'd-none' }}" data-inputs-container="languages">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="languages_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Language') }}</option>
                                                    @foreach(config('languages') as $key => $value)
                                                        <option value="{{ $key }}">{{ __($value['name']) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'languages') {
                                                    $languageList = old('targets');
                                                } else {
                                                    $languageList = [];
                                                }
                                            @endphp

                                            @foreach($languageList as $id => $language)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                            <option value="">{{ __('Language') }}</option>
                                                            @foreach(config('languages') as $key => $value)
                                                                <option value="{{ $key }}" @if($language['key'] == $key) selected @endif>{{ __($value['name']) }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $language['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto px-1 d-flex align-items-start">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'devices' ? '' : 'd-none' }}" data-inputs-container="devices">
                                        <input name="targets[empty][key]" type="hidden" disabled>
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <select name="devices_key[]" data-input="key" class="custom-select" disabled>
                                                    <option value="" selected>{{ __('Device') }}</option>
                                                    @foreach(config('devices') as $key => $value)
                                                        <option value="{{ $key }}">{{ __($value) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'devices') {
                                                    $deviceList = old('targets');
                                                } else {
                                                    $deviceList = [];
                                                }
                                            @endphp

                                            @foreach($deviceList as $id => $device)
                                                <div class="row mx-n1 form-group">
                                                    <div class="col px-1">
                                                        <select name="targets[{{ $id }}][key]" data-input="key" class="custom-select{{ $errors->has('targets.' . $id . '.key') ? ' is-invalid' : '' }}">
                                                            <option value="">{{ __('Device') }}</option>
                                                            @foreach(config('devices') as $key => $value)
                                                                <option value="{{ $key }}" @if($device['key'] == $key) selected @endif>{{ __($value) }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('targets.' . $id . '.key'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.key') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col px-1">
                                                        <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $device['value'] }}">
                                                        @if ($errors->has('targets.' . $id . '.value'))
                                                            <span class="invalid-feedback d-block" role="alert">
                                                                <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="col-auto px-1 d-flex align-items-start">
                                                        <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>

                                    <div class="{{ old('targets_type') && old('targets_type') == 'rotations' ? '' : 'd-none' }}" data-inputs-container="rotations">
                                        <input name="targets[empty][value]" type="hidden" disabled>

                                        <div class="row mx-n1 form-group d-none" data-inputs-template>
                                            <div class="col px-1">
                                                <input type="text" dir="ltr" data-input="value" class="form-control" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="" disabled>
                                            </div>

                                            <div class="col-auto px-1 d-flex align-items-start">
                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                            </div>
                                        </div>

                                        <div data-inputs="true">
                                            @php
                                                if (old('targets') && old('targets_type') == 'rotations') {
                                                    $rotationList = old('targets');
                                                } else {
                                                    $rotationList = [];
                                                }
                                            @endphp

                                            @foreach($rotationList as $id => $rotation)
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="row mx-n1 form-group">
                                                            <div class="col px-1">
                                                                <input type="text" dir="ltr" name="targets[{{ $id }}][value]" data-input="value" class="form-control{{ $errors->has('targets.' . $id . '.value') ? ' is-invalid' : '' }}" autocapitalize="none" spellcheck="false" placeholder="https://example.com" value="{{ $rotation['value'] }}">
                                                                @if ($errors->has('targets.' . $id . '.value'))
                                                                    <span class="invalid-feedback d-block" role="alert">
                                                                        <strong>{{ $errors->first('targets.' . $id . '.value') }}</strong>
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            <div class="col-auto px-1 d-flex align-items-start">
                                                                <button type="button" class="btn btn-soft-secondary d-flex align-items-center" data-inputs-delete>@include('icons.delete', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @can('linkTargeting', [App\Models\User::class])
                                            <button type="button" class="btn btn-soft-secondary d-inline-flex align-items-center" data-inputs-add>@include('icons.add', ['class' => 'w-4 h-4 fill-current'])&#8203;</button>
                                        @else
                                            @if(enabledPaymentProcessors())
                                                <a href="{{ route('pricing') }}" class="btn btn-soft-primary d-inline-flex align-items-center" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'w-4 h-4 fill-current'])&#8203;</a>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('shared.modals.utm')