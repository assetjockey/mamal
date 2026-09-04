<div class="row m-n1 py-3 py-lg-2 px-3 flex-grow-1 flex-nowrap">
    <div class="col col-lg-auto p-1 order-1 order-lg-3">
        <div class="flex-shrink-0 d-flex align-items-center justify-content-center position-relative py-1 px-2" data-tooltip="true" data-html="true" data-placement="bottom" title='@include('monitors.partials.http-status-tooltip')'>
            <div class="bg-{{ monitorStatusColor($monitor->status) }} position-absolute opacity-5 top-0 right-0 bottom-0 left-0 rounded z-0"></div>

            <div class="d-flex align-items-center">
                @include('icons.status', ['class' => 'width-4 height-4 fill-current text-' . monitorStatusColor($monitor->status)])&#8203;
            </div>

            @if (isset($label) && $label || true)
                <span class="font-weight-medium text-{{ monitorStatusColor($monitor->status) }} {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">{{ __(Str::ucfirst($monitor->status)) }}</span>
            @endif
        </div>
    </div>

    @if ($monitor->ssl_alert_days && parse_url($monitor->url, PHP_URL_SCHEME) == 'https' && $monitor->user->can('sslMonitoring', [App\Models\User::class]))
        <div class="col col-lg-auto p-1 order-2 order-lg-2">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center position-relative py-1 px-2" data-tooltip="true" data-placement="bottom" data-html="true" title='@include('monitors.partials.ssl-status-tooltip')'>
                <div class="bg-{{ monitorSslStatusColor($monitor) }} position-absolute opacity-5 top-0 right-0 bottom-0 left-0 rounded z-0"></div>

                <div class="d-flex align-items-center">
                    @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-' . monitorSslStatusColor($monitor)])&#8203;
                </div>

                <span class="font-weight-medium text-{{ monitorSslStatusColor($monitor) }} {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">{{ (in_array(monitorSslStatusColor($monitor), ['success', 'warning']) ? __('Valid') : __('Invalid')) }}</span>
            </div>
        </div>
    @endif

    @if ($monitor->domain_alert_days && $monitor->user->can('domainMonitoring', [App\Models\User::class]))
        <div class="col col-lg-auto p-1 order-3 order-lg-1">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center position-relative py-1 px-2" data-tooltip="true" data-placement="bottom" data-html="true" title='@include('monitors.partials.domain-status-tooltip')'>
                <div class="bg-{{ monitorDomainStatusColor($monitor) }} position-absolute opacity-5 top-0 right-0 bottom-0 left-0 rounded z-0"></div>

                <div class="d-flex align-items-center">
                    @include('icons.website', ['class' => 'fill-current width-4 height-4 text-' . monitorDomainStatusColor($monitor)])&#8203;
                </div>

                <span class="font-weight-medium text-{{ monitorDomainStatusColor($monitor) }} {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">{{ (in_array(monitorDomainStatusColor($monitor), ['success', 'warning']) ? __('Active') : (in_array(monitorDomainStatusColor($monitor), ['secondary']) ? __('Pending') : __('Expired'))) }}</span>
            </div>
        </div>
    @endif
</div>
