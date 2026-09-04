@extends('layouts.app')

@section('site_title', formatTitle([__('Plan'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('dashboard'), 'title' => __('Home')],
                ['url' => route('account'), 'title' => __('Account')],
                ['title' => __('Plan')]
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Plan') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="row">
                        <div class="col"><div class="font-weight-medium py-1">{{ __('Plan') }}</div></div>
                        @if(enabledPaymentProcessors())
                            <div class="col-auto">
                                @if(Auth::user()->planIsDefault())
                                    <a href="{{ route('pricing') }}" class="btn btn-sm btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.unarchive', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Upgrade') }}</a>
                                @else
                                    <a href="{{ route('pricing') }}" class="btn btn-sm btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.package', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Plans') }}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('account.plan') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3">
                                <div class="text-muted">{{ __('Plan') }}</div>
                                <div>{{ $user->active_plan->name }}</div>
                            </div>

                            @if(!$user->planIsDefault())
                                @if($user->plan_payment_processor)
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="text-muted">{{ __('Processor') }}</div>
                                        <div>{{ config('payment.processors.' . $user->plan_payment_processor)['name'] }}</div>
                                    </div>
                                @endif

                                @if($user->plan_amount && $user->plan_currency && $user->plan_interval)
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="text-muted">{{ __('Amount') }}</div>
                                        <div>{{ formatMoney($user->plan_amount, $user->plan_currency) }} {{ $user->plan_currency }} / <span class="text-lowercase">{{ $user->plan_interval == 'month' ? __('Month') : __('Year') }}</span></div>
                                    </div>
                                @endif

                                @if($user->plan_recurring_at)
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="text-muted">{{ __('Recurring at') }}</div>
                                        <div>{{ $user->plan_recurring_at->tz($user->timezone ?? config('timezone.timezone'))->format(__('Y-m-d')) }}</div>
                                    </div>
                                @endif

                                @if($user->plan_trial_ends_at && $user->plan_trial_ends_at->gt(Carbon\Carbon::now()))
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="text-muted">{{ __('Trial ends at') }}</div>
                                        <div>{{ $user->plan_trial_ends_at->tz($user->timezone ?? config('timezone.timezone'))->format(__('Y-m-d')) }}</div>
                                    </div>
                                @endif

                                @if($user->plan_ends_at)
                                    <div class="col-12 col-lg-6 mb-3">
                                        <div class="text-muted">{{ __('Ends at') }}</div>
                                        <div>{{ $user->plan_ends_at->tz($user->timezone ?? config('timezone.timezone'))->format(__('Y-m-d')) }}</div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="row mx-n2 mb-3">
                            <div class="col-auto font-weight-bold px-2">
                                <span class="badge badge-secondary text-uppercase">
                                    {{ __('Features') }}
                                </span>
                            </div>
                            <div class="col d-flex align-items-center px-2">
                                <hr class="my-0 w-100">
                            </div>
                        </div>

                        <div class="row m-n2">
                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->monitors != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->monitors == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->monitors < 0)
                                        {{ __('Unlimited monitors') }}
                                    @else
                                        <span class="text-muted">{{ number_format(Auth::user()->monitorsCount, 0, __('.'), __(',')) }} /</span> {{ __(($user->active_plan->features->monitors == 1 ? ':number monitor' : ':number monitors'), ['number' => number_format($user->active_plan->features->monitors, 0, __('.'), __(','))]) }}
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->monitor_intervals != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->monitor_intervals == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->monitor_intervals < 0)
                                        {{ __('Unlimited monitors') }}
                                    @else
                                        {{ __(':interval check interval', ['interval' => Carbon\CarbonInterval::seconds(min($user->active_plan->features->monitor_intervals))->cascade()->forHumans(), 0, __('.'), __(',')]) }}
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->ssl_monitoring)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->ssl_monitoring == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    {{ __('SSL certificate monitoring') }}
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('SSL expiration date monitoring.')}}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->domain_monitoring)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->domain_monitoring == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    {{ __('Domain name monitoring') }}
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('Domain name expiration date monitoring.')}}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->status_pages != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->status_pages == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->status_pages < 0)
                                        {{ __('Unlimited status pages') }}
                                    @else
                                        <span class="text-muted">{{ number_format(Auth::user()->status_pages_count, 0, __('.'), __(',')) }} /</span> {{ __(($user->active_plan->features->status_pages == 1 ? ':number status page' : ':number status pages'), ['number' => number_format($user->active_plan->features->status_pages, 0, __('.'), __(','))]) }}
                                    @endif
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->status_page_customization)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->status_page_customization == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    {{ __('Status page customization') }}
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ implode(', ', array_map('__', [__('Custom domain'), __('Custom CSS'), __('Custom JS')])) }}.">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->email_alerts != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->email_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->email_alerts < 0)
                                        {{ __('Unlimited email alerts') }}
                                    @else
                                        {{ __(($user->active_plan->features->email_alerts == 1 ? ':number email alert' : ':number email alerts'), ['number' => number_format($user->active_plan->features->email_alerts, 0, __('.'), __(','))]) }}
                                    @endif
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('The number of email addresses that can be configured per monitor to receive email alerts.') }}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            @if (config('settings.twilio'))
                                <div class="col-12 col-md-6 p-2 d-flex">
                                    @if($user->active_plan->features->sms_alerts != 0)
                                        @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                    @else
                                        @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                    @endif

                                    <div class="{{ ($user->active_plan->features->sms_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                        @if($user->active_plan->features->sms_alerts < 0)
                                            {{ __('Unlimited sms alerts') }}
                                        @else
                                            {{ __(($user->active_plan->features->sms_alerts == 1 ? ':number SMS alert' : ':number SMS alerts'), ['number' => number_format($user->active_plan->features->sms_alerts, 0, __('.'), __(','))]) }}
                                        @endif
                                    </div>

                                    <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" title="{{ __('The number of phone numbers that can be configured per monitor to receive SMS alerts.') }}">@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                                </div>
                            @endif

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->webhook_alerts != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->webhook_alerts == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->webhook_alerts < 0)
                                        {{ __('Unlimited webhook alerts') }}
                                    @else
                                        {!! __(($user->active_plan->features->webhook_alerts == 1 ? ':number webhook alert' : ':number webhook alerts'), ['number' => number_format($user->active_plan->features->webhook_alerts, 0, __('.'), __(','))]) !!}
                                    @endif
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-tooltip="true" data-html="true" title='@include('pricing.partials.tooltip')'>@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->data_retention != 0)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->data_retention == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    @if($user->active_plan->features->data_retention < 0)
                                        {{ __('Unlimited data retention') }}
                                    @else
                                        {{ __(($user->active_plan->features->data_retention == 1 ? ':number day data retention' : ':number days data retention'), ['number' => number_format($user->active_plan->features->data_retention, 0, __('.'), __(','))]) }}
                                    @endif
                                </div>

                                <div class="d-flex align-content-center mt-1 {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='{{ __('The number of days the :list data will be retained.', ['list' => '<span class="font-weight-medium">' . __('Stats') . '</span>']) }}'>@include('icons.info', ['class' => 'text-muted width-4 height-4 fill-current'])</div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->data_export)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->data_export == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    {{ __('Data export') }}
                                </div>
                            </div>

                            <div class="col-12 col-md-6 p-2 d-flex">
                                @if($user->active_plan->features->api)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current width-4 height-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current width-4 height-4 mt-1'])
                                @endif

                                <div class="{{ ($user->active_plan->features->api == 0 ? 'text-muted' : '') }} {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                    {{ __('API') }}
                                </div>
                            </div>
                        </div>

                        @if(enabledPaymentProcessors())
                            @if($user->plan_recurring_at)
                                <button type="button" class="btn btn-outline-danger mt-3" data-toggle="modal" data-target="#modal" data-action="{{ route('account.plan') }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Cancel') }}" data-text="{{ __('You\'ll continue to have access to the features you\'ve paid for until the end of your billing cycle.') }}" data-sub-text="{{ __('Are you sure you want to cancel :name?', ['name' => $user->active_plan->name]) }}">{{ __('Cancel') }}</button>
                            @endif
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
