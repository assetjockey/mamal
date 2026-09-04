<div class="plans">
    <div class="plans-item active">
        @if ($monthlyPlans->count() > 0)
            <div
                class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4 g-3 justify-content-center">
                @foreach ($monthlyPlans as $monthlyPlan)
                    <div class="col" data-aos="zoom-out-right" data-aos-duration="1000">
                        <div class="plan {{ planClass($monthlyPlan->id) }}">
                            {!! planBadge($monthlyPlan->id) !!}
                            <p class="plan-title">{{ $monthlyPlan->name }}</p>
                            <p class="plan-text">{{ $monthlyPlan->short_description }}</p>
                            <div class="plan-price-content">
                                <div class="plan-price">
                                    <div>
                                        @if ($monthlyPlan->price != 0)
                                            <span>{{ currencySymbol() }}</span>
                                            <span>{{ price($monthlyPlan->price) }}</span>
                                            <span class="plan-price-text">/
                                                @if ($monthlyPlan->interval == 0)
                                                    {{ lang('month', 'plans') }}
                                                @elseif($monthlyPlan->interval == 1)
                                                    {{ lang('year', 'plans') }}
                                                @elseif($monthlyPlan->interval == 2)
                                                    {{ lang('lifetime', 'plans') }}
                                                @endif
                                            </span>
                                        @else
                                            <span class="text">{{ lang('free') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="plan-features mb-4">
                                <div class="plan-feature-item">
                                    <div class="plan-feature-icon">
                                        <i class="fa fa-check fa-sm"></i>
                                    </div>
                                    <span>
                                        @if ($monthlyPlan->storage_space)
                                            <strong>{{ formatBytes($monthlyPlan->storage_space) }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                        @else
                                            <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="plan-feature-item">
                                    <div class="plan-feature-icon">
                                        <i class="fa fa-check fa-sm"></i>
                                    </div>
                                    <span>
                                        @if ($monthlyPlan->transfer_size)
                                            <strong>{{ formatBytes($monthlyPlan->transfer_size) }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                        @else
                                            <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="plan-feature-item">
                                    <div class="plan-feature-icon">
                                        <i class="fa fa-check fa-sm"></i>
                                    </div>
                                    <span>
                                        @if ($monthlyPlan->transfer_interval)
                                            {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ $monthlyPlan->transfer_interval }}&nbsp;{{ $monthlyPlan->transfer_interval > 1 ? lang('days') : lang('day') }}</strong>
                                        @else
                                            {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ lang('Unlimited time', 'plans') }}</strong>
                                        @endif
                                    </span>
                                </div>
                                @if ($monthlyPlan->transfer_password)
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span> {{ lang('Password protection', 'plans') }}</span>
                                    </div>
                                @endif
                                @if ($monthlyPlan->transfer_notify)
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>{{ lang('Email notification', 'plans') }}</span>
                                    </div>
                                @endif
                                @if ($monthlyPlan->transfer_expiry)
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>{{ lang('Expiry time control', 'plans') }}</span>
                                    </div>
                                @endif
                                @if ($monthlyPlan->transfer_link)
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>{{ lang('Generate transfer links', 'plans') }}</span>
                                    </div>
                                @endif
                                @if (!$monthlyPlan->advertisements)
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>{{ lang('No Advertisements', 'plans') }}</span>
                                    </div>
                                @endif
                                @if ($monthlyPlan->custom_features)
                                    @foreach ($monthlyPlan->custom_features as $custom_feature)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span> {{ $custom_feature->name }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            {!! planButton($monthlyPlan) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="row">
                <div class="col-lg-6 m-auto">
                    <div class="alert alert-primary">{{ lang('No monthly plans available', 'plans') }}</div>
                </div>
            </div>
        @endif
    </div>
    @if ($yearlyPlans->count() > 0 || $lifetimePlans->count() > 0)
        <div class="plans-item">
            @if ($yearlyPlans->count() > 0)
                <div
                    class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4 g-3 justify-content-center">
                    @foreach ($yearlyPlans as $yearlyPlan)
                        <div class="col" data-aos="zoom-out-right" data-aos-duration="1000">
                            <div class="plan {{ planClass($yearlyPlan->id) }}">
                                {!! planBadge($yearlyPlan->id) !!}
                                <p class="plan-title">{{ $yearlyPlan->name }}</p>
                                <p class="plan-text">{{ $yearlyPlan->short_description }}</p>
                                <div class="plan-price-content">
                                    <div class="plan-price">
                                        <div>
                                            @if ($yearlyPlan->price != 0)
                                                <span>{{ currencySymbol() }}</span>
                                                <span>{{ price($yearlyPlan->price) }}</span>
                                                <span class="plan-price-text">/
                                                    @if ($yearlyPlan->interval == 0)
                                                        {{ lang('month', 'plans') }}
                                                    @elseif($yearlyPlan->interval == 1)
                                                        {{ lang('year', 'plans') }}
                                                    @elseif($yearlyPlan->interval == 2)
                                                        {{ lang('lifetime', 'plans') }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text">{{ lang('free') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="plan-features mb-4">
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($yearlyPlan->storage_space)
                                                <strong>{{ formatBytes($yearlyPlan->storage_space) }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                            @else
                                                <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($yearlyPlan->transfer_size)
                                                <strong>{{ formatBytes($yearlyPlan->transfer_size) }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                            @else
                                                <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($yearlyPlan->transfer_interval)
                                                {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ $yearlyPlan->transfer_interval }}&nbsp;{{ $yearlyPlan->transfer_interval > 1 ? lang('days') : lang('day') }}</strong>
                                            @else
                                                {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ lang('Unlimited time', 'plans') }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                    @if ($yearlyPlan->transfer_password)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span> {{ lang('Password protection', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($yearlyPlan->transfer_notify)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Email notification', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($yearlyPlan->transfer_expiry)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Expiry time control', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($yearlyPlan->transfer_link)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Generate transfer links', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if (!$yearlyPlan->advertisements)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('No Advertisements', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($yearlyPlan->custom_features)
                                        @foreach ($yearlyPlan->custom_features as $custom_feature)
                                            <div class="plan-feature-item">
                                                <div class="plan-feature-icon">
                                                    <i class="fa fa-check fa-sm"></i>
                                                </div>
                                                <span> {{ $custom_feature->name }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                {!! planButton($yearlyPlan) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="row">
                    <div class="col-lg-6 m-auto">
                        <div class="alert alert-primary">{{ lang('No yearly plans available', 'plans') }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
    @if ($lifetimePlans->count() > 0)
        <div class="plans-item">
            @if ($lifetimePlans->count() > 0)
                <div
                    class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4 g-3 justify-content-center">
                    @foreach ($lifetimePlans as $lifetimePlan)
                        <div class="col" data-aos="zoom-out-right" data-aos-duration="1000">
                            <div class="plan {{ planClass($lifetimePlan->id) }}">
                                {!! planBadge($lifetimePlan->id) !!}
                                <p class="plan-title">{{ $lifetimePlan->name }}</p>
                                <p class="plan-text">{{ $lifetimePlan->short_description }}</p>
                                <div class="plan-price-content">
                                    <div class="plan-price">
                                        <div>
                                            @if ($lifetimePlan->price != 0)
                                                <span>{{ currencySymbol() }}</span>
                                                <span>{{ price($lifetimePlan->price) }}</span>
                                                <span class="plan-price-text">/
                                                    @if ($lifetimePlan->interval == 0)
                                                        {{ lang('month', 'plans') }}
                                                    @elseif($lifetimePlan->interval == 1)
                                                        {{ lang('year', 'plans') }}
                                                    @elseif($lifetimePlan->interval == 2)
                                                        {{ lang('lifetime', 'plans') }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text">{{ lang('free') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="plan-features mb-4">
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($lifetimePlan->storage_space)
                                                <strong>{{ formatBytes($lifetimePlan->storage_space) }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                            @else
                                                <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Storage Space', 'plans') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($lifetimePlan->transfer_size)
                                                <strong>{{ formatBytes($lifetimePlan->transfer_size) }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                            @else
                                                <strong>{{ lang('Unlimited', 'plans') }}</strong>&nbsp;{{ lang('Size per transfer', 'plans') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="plan-feature-item">
                                        <div class="plan-feature-icon">
                                            <i class="fa fa-check fa-sm"></i>
                                        </div>
                                        <span>
                                            @if ($lifetimePlan->transfer_interval)
                                                {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ $lifetimePlan->transfer_interval }}&nbsp;{{ $lifetimePlan->transfer_interval > 1 ? lang('days') : lang('day') }}</strong>
                                            @else
                                                {{ lang('Files available for', 'plans') }}&nbsp;<strong>{{ lang('Unlimited time', 'plans') }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                    @if ($lifetimePlan->transfer_password)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span> {{ lang('Password protection', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($lifetimePlan->transfer_notify)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Email notification', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($lifetimePlan->transfer_expiry)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Expiry time control', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($lifetimePlan->transfer_link)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('Generate transfer links', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if (!$lifetimePlan->advertisements)
                                        <div class="plan-feature-item">
                                            <div class="plan-feature-icon">
                                                <i class="fa fa-check fa-sm"></i>
                                            </div>
                                            <span>{{ lang('No Advertisements', 'plans') }}</span>
                                        </div>
                                    @endif
                                    @if ($lifetimePlan->custom_features)
                                        @foreach ($lifetimePlan->custom_features as $custom_feature)
                                            <div class="plan-feature-item">
                                                <div class="plan-feature-icon">
                                                    <i class="fa fa-check fa-sm"></i>
                                                </div>
                                                <span> {{ $custom_feature->name }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                {!! planButton($lifetimePlan) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="row">
                    <div class="col-lg-6 m-auto">
                        <div class="alert alert-primary">{{ lang('No lifetime plans available', 'plans') }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
