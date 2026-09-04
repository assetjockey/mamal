<div class="text-center mb-3 mt-5 pb-3">
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-dark active" id="plan-month">
            <input type="radio" name="options" autocomplete="off" checked>{{ __('Monthly') }}
        </label>
        <label class="btn btn-outline-dark" id="plan-year">
            <input type="radio" name="options" autocomplete="off">{{ __('Yearly') }}
        </label>
    </div>
</div>

<div class="row flex-column-reverse flex-md-row justify-content-center m-n2 m-md-n3">
    @foreach($plans as $plan)
        <div class="col-12 col-md-6 col-xl-4 p-2 p-md-3">
            <div class="card border-0 shadow-sm rounded h-100 overflow-hidden plan">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="mb-3">
                        <div class="badge badge-pill badge-primary text-uppercase px-2 py-1">{{ $plan->name }}</div>
                    </div>

                    <div class="mb-4">
                        @if(!$plan->isDefault())
                            <div class="plan-month d-none d-block">
                                <div>
                                    <span class="h1 mb-0 font-weight-bold">
                                        {{ formatMoney($plan->amount_month, $plan->currency) }}
                                    </span>
                                    <span class="h5 font-weight-bold text-muted">
                                        {{ $plan->currency }}
                                    </span>
                                </div>
                                <span class="text-muted text-lowercase">{{ __('Month') }}</span>
                            </div>

                            <div class="plan-year d-none">
                                <div>
                                    <span class="h1 mb-0 font-weight-bold">
                                        {{ formatMoney($plan->amount_year, $plan->currency) }}
                                    </span>
                                    <span class="h5 font-weight-bold text-muted">
                                        {{ $plan->currency }}
                                    </span>
                                </div>

                                <span class="text-muted text-lowercase">{{ __('Year') }}</span>

                                @if(($plan->amount_month * 12) > $plan->amount_year)
                                    <span class="badge badge-success">
                                        {{ __(':value% off', ['value' => number_format(((($plan->amount_month*12) - $plan->amount_year)/($plan->amount_month * 12) * 100), 0)]) }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="plan-month d-none d-block">
                                <div class="h1 mb-0">
                                    <span class="font-weight-bold text-uppercase">
                                        {{ __('Free') }}
                                    </span>
                                </div>
                            </div>

                            <div class="plan-year d-none">
                                <div class="h1 mb-0">
                                    <span class="font-weight-bold text-uppercase">
                                        {{ __('Free') }}
                                    </span>
                                </div>
                            </div>

                            <div class="plan-month d-none d-block">
                                <span class="text-muted text-lowercase">{{ __('Month') }}</span>
                            </div>

                            <div class="plan-year d-none">
                                <span class="text-muted text-lowercase">{{ __('Year') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="row m-n2">
                         <div class="col-12 p-2 d-flex">
                            @if($plan->features->monitors != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->monitors == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->monitors < 0)
                                    {{ __('Unlimited monitors') }}
                                @else
                                    {{ __(($plan->features->monitors == 1 ? ':number monitor' : ':number monitors'), ['number' => number_format($plan->features->monitors, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->monitor_intervals != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->monitor_intervals == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->monitor_intervals < 0)
                                    {{ __('Unlimited monitors') }}
                                @else
                                    {{ __(':interval check interval', ['interval' => Carbon\CarbonInterval::seconds(min($plan->features->monitor_intervals))->cascade()->forHumans(), 0, __('.'), __(',')]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->ssl_monitoring)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->ssl_monitoring == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                {{ __('SSL certificate monitoring') }}
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('SSL expiration date monitoring.')}}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->domain_monitoring)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->domain_monitoring == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                {{ __('Domain name monitoring') }}
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('Domain name expiration date monitoring.')}}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->status_pages != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->status_pages == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->status_pages < 0)
                                    {{ __('Unlimited status pages') }}
                                @else
                                    {{ __(($plan->features->status_pages == 1 ? ':number status page' : ':number status pages'), ['number' => number_format($plan->features->status_pages, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->status_page_customization)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->status_page_customization == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                {{ __('Status page customization') }}
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ implode(', ', array_map('__', [__('Custom domain'), __('Custom CSS'), __('Custom JS')])) }}.">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->email_alerts != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->email_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->email_alerts < 0)
                                    {{ __('Unlimited email alerts') }}
                                @else
                                    {{ __(($plan->features->email_alerts == 1 ? ':number email alert' : ':number email alerts'), ['number' => number_format($plan->features->email_alerts, 0, __('.'), __(','))]) }}
                                @endif
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('The number of email addresses that can be configured per monitor to receive email alerts.') }}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        @if (config('settings.twilio'))
                            <div class="col-12 p-2 d-flex">
                                @if($plan->features->sms_alerts != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($plan->features->sms_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($plan->features->sms_alerts < 0)
                                        {{ __('Unlimited sms alerts') }}
                                    @else
                                        {{ __(($plan->features->sms_alerts == 1 ? ':number SMS alert' : ':number SMS alerts'), ['number' => number_format($plan->features->sms_alerts, 0, __('.'), __(','))]) }}
                                    @endif
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('The number of phone numbers that can be configured per monitor to receive SMS alerts.') }}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>
                        @endif

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->webhook_alerts != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->webhook_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->webhook_alerts < 0)
                                    {{ __('Unlimited webhook alerts') }}
                                @else
                                    {!! __(($plan->features->webhook_alerts == 1 ? ':number webhook alert' : ':number webhook alerts'), ['number' => number_format($plan->features->webhook_alerts, 0, __('.'), __(','))]) !!}
                                @endif
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-tooltip="true" data-html="true" title='@include('pricing.partials.tooltip')'>@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->data_retention != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->data_retention == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                @if($plan->features->data_retention < 0)
                                    {{ __('Unlimited data retention') }}
                                @else
                                    {{ __(($plan->features->data_retention == 1 ? ':number day data retention' : ':number days data retention'), ['number' => number_format($plan->features->data_retention, 0, __('.'), __(','))]) }}
                                @endif
                            </div>

                            <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='{{ __('The number of days the :list data will be retained.', ['list' => '<span class="font-weight-medium">' . __('Stats') . '</span>']) }}'>@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->data_export)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->data_export == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                {{ __('Data export') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->api)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->api == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                {{ __('API') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-0 pt-0 pr-4 pb-4 pl-4 d-flex">
                    <div class="z-1 w-100">
                        @auth
                            @if(!$plan->isDefault())
                                @if(Auth::user()->active_plan->id == $plan->id)
                                    <div class="btn btn-primary btn-block text-uppercase py-2 disabled">{{ __('Active') }}</div>
                                @else
                                    <div class="plan-month d-none d-block">
                                        <a href="{{ route('checkout.index', ['id' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">
                                            @if($plan->trial_days > 0 && ! Auth::user()->plan_trial_ends_at)
                                                {{ __('Free trial') }}
                                            @else
                                                {{ __('Subscribe') }}
                                            @endif
                                        </a>
                                    </div>
                                    <div class="plan-year d-none">
                                        <a href="{{ route('checkout.index', ['id' => $plan->id, 'interval' => 'year']) }}" class="btn btn-primary btn-block text-uppercase py-2">
                                            @if($plan->trial_days > 0 && ! Auth::user()->plan_trial_ends_at)
                                                {{ __('Free trial') }}
                                            @else
                                                {{ __('Subscribe') }}
                                            @endif
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="btn btn-primary btn-block text-uppercase py-2 disabled">{{ __('Free') }}</div>
                            @endif
                        @else
                            @if(config('settings.registration'))
                                <div class="plan-month d-none d-block">
                                    <a href="{{ route('register', ['plan' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Register') }}</a>
                                </div>
                                <div class="plan-year d-none">
                                    <a href="{{ route('register', ['plan' => $plan->id, 'interval' => 'year']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Register') }}</a>
                                </div>
                            @else
                                <div class="plan-month d-none d-block">
                                    <a href="{{ route('login', ['plan' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Login') }}</a>
                                </div>
                                <div class="plan-year d-none">
                                    <a href="{{ route('login', ['plan' => $plan->id, 'interval' => 'year']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Login') }}</a>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
