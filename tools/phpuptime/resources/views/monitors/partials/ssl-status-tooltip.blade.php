<div class="mx-2">
    @if($monitor->ssl_created_at && $monitor->ssl_ends_at)
        <div class="my-2 d-flex font-weight-bold font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            @include('icons.lock', ['class' => 'width-4 height-4 fill-current mt-1 flex-shrink-0 text-' . monitorSslStatusColor($monitor) . ' ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
            @if (now()->isBetween($monitor->ssl_created_at, $monitor->ssl_ends_at))
                @if (now()->isBetween($monitor->ssl_ends_at->subDays($monitor->ssl_alert_days), $monitor->ssl_ends_at))
                    <span class="text-warning">{{ __('Valid SSL certificate') }}</span>
                @else
                    <span class="text-success">{{ __('Valid SSL certificate') }}</span>
                @endif
            @elseif ($monitor->ssl_created_at->isFuture() || $monitor->ssl_ends_at->isPast())
                <span class="text-danger">{{ __('Invalid SSL certificate') }}</span>
            @endif
        </div>

        @if (isset($monitor->ssl_information->issuer) || isset($monitor->ssl_information->organization) || isset($monitor->ssl_information->signature_algorithm) || $monitor->ssl_created_at || $monitor->ssl_ends_at)
            <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
                @if (isset($monitor->ssl_information->issuer) && $monitor->ssl_information->issuer)
                    <div>
                        <span class="font-weight-bold">{{ __('Issuer') }}</span>: {{ $monitor->ssl_information->issuer }}
                    </div>
                @endif

                @if (isset($monitor->ssl_information->organization) && $monitor->ssl_information->organization)
                    <div>
                        <span class="font-weight-bold">{{ __('Organization') }}</span>: {{ $monitor->ssl_information->organization }}
                    </div>
                @endif

                @if (isset($monitor->ssl_information->signature_algorithm) && $monitor->ssl_information->signature_algorithm)
                    <div>
                        <span class="font-weight-bold">{{ __('Signature algorithm') }}</span>: {{ $monitor->ssl_information->signature_algorithm }}
                    </div>
                @endif

                @if ($monitor->ssl_created_at)
                    <div><span class="font-weight-bold">{{ __('Created at') }}</span>: <span
                                class="{{ ($monitor->ssl_ends_at->isPast() ? 'text-danger font-weight-bold' : '') }}">{{ $monitor->ssl_created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                    </div>
                @endif

                @if ($monitor->ssl_ends_at)
                    <div><span class="font-weight-bold">{{ __('Expires at') }}</span>: <span
                                class="{{ ($monitor->ssl_ends_at->isPast() ? 'text-danger font-weight-bold' : (Carbon\Carbon::now()->isBetween($monitor->ssl_ends_at->subDays($monitor->ssl_alert_days), $monitor->ssl_ends_at) ? 'text-warning font-weight-bold' : '')) }}">{{ $monitor->ssl_ends_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                    </div>
                @endif
            </div>
        @endif
    @else
        <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            {{ $monitor->ssl_information ? implode('-', array_filter((array) $monitor->ssl_information)) : __('Pending') }}
        </div>
    @endif

    @if ($monitor->ssl_checked_at)
        <div class="mt-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            <div>
                <span class="font-weight-bold">{{ __('Checked at') }}</span>: {{ $monitor->ssl_checked_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}
            </div>
        </div>
    @endif

    <div class="{{ $monitor->ssl_checked_at ? 'mb-2' : 'my-2' }} {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <div>
            <span class="font-weight-bold">{{ __('Check interval') }}</span>: {{ Carbon\CarbonInterval::seconds(86400)->cascade()->forHumans() }}
        </div>
    </div>
</div>
