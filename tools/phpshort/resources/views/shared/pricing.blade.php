<div class="text-center mb-4 mt-12 pb-4">
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        <label class="btn btn-outline-inverse active" id="plan-month">
            <input type="radio" name="options" autocomplete="off" checked>{{ __('Monthly') }}
        </label>
        <label class="btn btn-outline-inverse" id="plan-year">
            <input type="radio" name="options" autocomplete="off">{{ __('Yearly') }}
        </label>
    </div>
</div>

<div class="row flex-column-reverse flex-md-row justify-content-center m-n2 m-md-n4">
    @foreach($plans as $plan)
        <div class="col-12 col-md-6 col-xl-4 p-2 p-md-4">
            <div class="card border-0 shadow-sm rounded h-full overflow-hidden plan">
                <div class="card-body p-6 d-flex flex-column">
                    <div>
                        <div class="row mx-n1">
                            <div class="col px-1">
                                <div class="badge badge-pill badge-primary text-uppercase px-2 py-1">{{ $plan->name }}</div>
                            </div>
                            <div class="col-auto px-1">
                                <div class="d-none" data-plan="year">
                                    @if(($plan->amount_month * 12) > $plan->amount_year)
                                        <span class="badge badge-pill badge-success text-uppercase px-2 py-1">
                                            {{ __(':value% off', ['value' => number_format(((($plan->amount_month*12) - $plan->amount_year)/($plan->amount_month * 12) * 100), 0)]) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 mb-6">
                        @if(!$plan->isDefault())
                            <div class="d-none d-block" data-plan="month">
                                <div>
                                    <span class="fs-4xl fw-bold tracking-tight m-0">
                                        {{ formatMoney($plan->amount_month, $plan->currency) }}
                                    </span>
                                    <span>
                                        {{ $plan->currency }}
                                    </span>
                                    <span class="text-muted text-lowercase"> / {{ mb_strtolower(__('Month')) }}</span>
                                </div>
                            </div>

                            <div class="d-none" data-plan="year">
                                <div>
                                    <span class="fs-4xl fw-bold tracking-tight m-0">
                                        {{ formatMoney($plan->amount_year, $plan->currency) }}
                                    </span>
                                    <span>
                                        {{ $plan->currency }}
                                    </span>
                                    <span class="text-muted text-lowercase"> / {{ mb_strtolower(__('Year')) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="d-none d-block" data-plan="month">
                                <div class="fs-4xl fw-bold tracking-tight m-0">
                                    {{ __('Free') }}
                                </div>
                            </div>

                            <div class="d-none" data-plan="year">
                                <div class="fs-4xl fw-bold tracking-tight m-0">
                                    {{ __('Free') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row m-n2">
                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->links != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->links == 0 ? 'text-muted' : '') }} ms-4">
                                @if($plan->features->links < 0)
                                    {{ __('Unlimited links') }}
                                @else
                                    {{ __(($plan->features->links == 1 ? ':number link' : ':number links'), ['number' => number_format($plan->features->links, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->spaces != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->spaces == 0 ? 'text-muted' : '') }} ms-4">
                                @if($plan->features->spaces < 0)
                                    {{ __('Unlimited spaces') }}
                                @else
                                    {{ __(($plan->features->spaces == 1 ? ':number space' : ':number spaces'), ['number' => number_format($plan->features->spaces, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->domains != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->domains == 0 ? 'text-muted' : '') }} ms-4">
                                @if($plan->features->domains < 0)
                                    {{ __('Unlimited domains') }}
                                @else
                                    {{ __(($plan->features->domains == 1 ? ':number domain' : ':number domains'), ['number' => number_format($plan->features->domains, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->pixels != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->pixels == 0 ? 'text-muted' : '') }} ms-4">
                                @if($plan->features->pixels < 0)
                                    {{ __('Unlimited pixels') }}
                                @else
                                    {{ __(($plan->features->pixels == 1 ? ':number pixel' : ':number pixels'), ['number' => number_format($plan->features->pixels, 0, __('.'), __(','))]) }}
                                @endif
                            </div>
                        </div>

                        @if(count($domains))
                            <div class="col-12 p-2 d-flex">
                                @if($plan->features->additional_domains)
                                    @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                                @else
                                    @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                                @endif

                                <div class="{{ ($plan->features->additional_domains == 0 ? 'text-muted' : '') }} ms-4">
                                    {{ __('Additional domains') }}
                                </div>

                                <div class="d-flex align-content-center mt-1 ms-2" data-tooltip="true" title="{{ __('Access to additional domains: :domains.', ['domains' => implode(', ', $domains)]) }}">@include('icons.info', ['class' => 'text-muted w-4 h-4 fill-current'])</div>
                            </div>
                        @endif

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_stats)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_stats == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Link stats') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_targeting)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_targeting == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Link targeting') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_alias)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_alias == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Link alias') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_password)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_password == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Link password') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_expiration)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_expiration == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Link expiration') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_deep)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_deep == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Deep linking') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->link_utm)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->link_utm == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('UTM builder') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->data_retention != 0)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->data_retention == 0 ? 'text-muted' : '') }} ms-4">
                                @if($plan->features->data_retention < 0)
                                    {{ __('Unlimited data retention') }}
                                @else
                                    {{ __(($plan->features->data_retention == 1 ? ':number day data retention' : ':number days data retention'), ['number' => number_format($plan->features->data_retention, 0, __('.'), __(','))]) }}
                                @endif
                            </div>

                            <div class="d-flex align-content-center mt-1 ms-2" data-tooltip="true" data-html="true" title='{{ __('The number of days the :list data will be retained.', ['list' => '<span class="fw-medium">' . __('Stats') . '</span>']) }}'>@include('icons.info', ['class' => 'text-muted w-4 h-4 fill-current'])</div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->data_export)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->data_export == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('Data export') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->api)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->api == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('API') }}
                            </div>
                        </div>

                        <div class="col-12 p-2 d-flex">
                            @if($plan->features->no_ads)
                                @include('icons.checkmark', ['class' => 'flex-shrink-0 text-success fill-current w-4 h-4 mt-1'])
                            @else
                                @include('icons.close', ['class' => 'flex-shrink-0 text-muted fill-current w-4 h-4 mt-1'])
                            @endif

                            <div class="{{ ($plan->features->no_ads == 0 ? 'text-muted' : '') }} ms-4">
                                {{ __('No ads') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-0 pt-0 pe-6 pb-6 ps-6 d-flex">
                    <div class="z-1 w-full">
                        @auth
                            @if(!$plan->isDefault())
                                @if(Auth::user()->active_plan->id == $plan->id)
                                    <div class="btn btn-primary btn-block text-uppercase py-2 disabled">{{ __('Active') }}</div>
                                @else
                                    <div class="d-none d-block" data-plan="month">
                                        <a href="{{ route('checkout.index', ['id' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">
                                            @if($plan->trial_days > 0 && ! Auth::user()->plan_trial_ends_at)
                                                {{ __('Free trial') }}
                                            @else
                                                {{ __('Subscribe') }}
                                            @endif
                                        </a>
                                    </div>
                                    <div class="d-none" data-plan="year">
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
                                <div class="d-none d-block" data-plan="month">
                                    <a href="{{ route('register', ['plan' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Register') }}</a>
                                </div>
                                <div class="d-none" data-plan="year">
                                    <a href="{{ route('register', ['plan' => $plan->id, 'interval' => 'year']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Register') }}</a>
                                </div>
                            @else
                                <div class="d-none d-block" data-plan="month">
                                    <a href="{{ route('login', ['plan' => $plan->id, 'interval' => 'month']) }}" class="btn btn-primary btn-block text-uppercase py-2">{{ __('Login') }}</a>
                                </div>
                                <div class="d-none" data-plan="year">
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