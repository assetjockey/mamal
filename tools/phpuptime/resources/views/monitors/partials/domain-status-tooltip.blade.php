<div class="mx-2">
    @if($monitor->domain_created_at && $monitor->domain_ends_at)
        <div class="my-2 d-flex font-weight-bold font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            @include('icons.website', ['class' => 'width-4 height-4 fill-current mt-1 flex-shrink-0 text-' . monitorDomainStatusColor($monitor) . ' ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
            @if (now()->isBetween($monitor->domain_created_at, $monitor->domain_ends_at))
                @if (now()->isBetween($monitor->domain_ends_at->subDays($monitor->domain_alert_days), $monitor->domain_ends_at))
                    <span class="text-warning">{{ __('Active domain name') }}</span>
                @else
                    <span class="text-success">{{ __('Active domain name') }}</span>
                @endif
            @elseif ($monitor->domain_created_at->isFuture() || $monitor->domain_ends_at->isPast())
                <span class="text-danger">{{ __('Expired domain name') }}</span>
            @endif
        </div>

        @if (isset($monitor->domain_information->issuer) || isset($monitor->domain_information->organization) || isset($monitor->domain_information->signature_algorithm) || $monitor->domain_created_at || $monitor->domain_ends_at)
            <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
                @if (isset($monitor->domain_information->domain_name) && $monitor->domain_information->domain_name)
                    <div>
                        <span class="font-weight-bold">{{ __('Domain name') }}</span>: {{ $monitor->domain_information->domain_name }}
                    </div>
                @endif

                @if (isset($monitor->domain_information->owner) && $monitor->domain_information->owner)
                    <div>
                        <span class="font-weight-bold">{{ __('Owner') }}</span>: {{ $monitor->domain_information->owner }}
                    </div>
                @endif

                @if (isset($monitor->domain_information->registrar) && $monitor->domain_information->registrar)
                    <div>
                        <span class="font-weight-bold">{{ __('Registrar') }}</span>: {{ $monitor->domain_information->registrar }}
                    </div>
                @endif

                @if ($monitor->domain_created_at)
                    <div><span class="font-weight-bold">{{ __('Created at') }}</span>:
                        <span class="{{ ($monitor->domain_ends_at->isPast() ? 'text-danger font-weight-bold' : '') }}">{{ $monitor->domain_created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                    </div>
                @endif

                @if ($monitor->domain_ends_at)
                    <div><span class="font-weight-bold">{{ __('Expires at') }}</span>:
                        <span class="{{ ($monitor->domain_ends_at->isPast() ? 'text-danger font-weight-bold' : (Carbon\Carbon::now()->isBetween($monitor->domain_ends_at->subDays($monitor->domain_alert_days), $monitor->domain_ends_at) ? 'text-warning font-weight-bold' : '')) }}">{{ $monitor->domain_ends_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            {{ $monitor->domain_information ? implode('-', array_filter((array) $monitor->domain_information)) : __('Pending') }}
        </div>
    @endif

    @if ($monitor->domain_checked_at)
        <div class="mt-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            <div>
                <span class="font-weight-bold">{{ __('Checked at') }}</span>: {{ $monitor->domain_checked_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}
            </div>
        </div>
    @endif

    <div class="{{ $monitor->domain_checked_at ? 'mb-2' : 'my-2' }} {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <div>
            <span class="font-weight-bold">{{ __('Check interval') }}</span>: {{ Carbon\CarbonInterval::seconds(86400)->cascade()->forHumans() }}
        </div>
    </div>
</div>
