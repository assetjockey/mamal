@php
    $parameters[] = [
        'name' => 'name',
        'type' => $type,
        'format' => 'string',
        'description' => __('Name') . '.'
    ];

    $parameters[] = [
        'name' => 'url',
        'type' => $type,
        'format' => 'string',
        'description' => __('URL') . '.'
    ];

    $parameters[] = [
        'name' => 'interval',
        'type' => $type,
        'format' => 'integer',
        'description' => __('Interval') . '. '. __('Possible values are: :values.', [
                'values' => '<code>' . implode('</code>, <code>', config('intervals.http')) . '</code>'])
    ];

    $parameters[] = [
        'name' => 'alert_condition',
        'type' => 0,
        'format' => 'string',
        'description' => __('Alert condition') . '. ' . __('Possible values are: :values.', [
            'values' => implode(', ', array_map(function($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="font-weight-medium">' . __($name) . '</span>']); }, array_keys(config('alert.conditions')), config('alert.conditions')))]) . ' ' . __('Defaults to: :value.', ['value' => '<code>url_unavailable</code>'])
    ];

    $parameters[] = [
        'name' => 'alert_text_lookup',
        'type' => 0,
        'format' => 'string',
        'description' => __('Text to be looked-up to trigger alerts.') . ' ' . __('Only works with :field field set to :value.', ['field' => '<code>alert_condition</code>', 'value' => '<code>url_text</code>, <code>url_no_text</code>'])
    ];

    $parameters[] = [
        'name' => 'request_method',
        'type' => 0,
        'format' => 'string',
        'description' => __('Request method') . '. '. __('Possible values are: :values.', ['values' => '<code>' . implode('</code>, <code>', config('request.methods')) . '</code>']) . ' ' . __('Defaults to: :value.', ['value' => '<code>GET</code>'])
    ];

    $parameters[] = [
        'name' => 'request_headers[index][key]',
        'type' => 0,
        'format' => 'string',
        'description' => __('Request header name.')
    ];

    $parameters[] = [
        'name' => 'request_headers[index][value]',
        'type' => 0,
        'format' => 'string',
        'description' => __('Request header value.')
    ];

    $parameters[] = [
        'name' => 'request_auth_username',
        'type' => 0,
        'format' => 'string',
        'description' => __('Username for Basic HTTP request authentication.')
    ];

    $parameters[] = [
        'name' => 'request_auth_password',
        'type' => 0,
        'format' => 'string',
        'description' => __('Password for Basic HTTP request authentication.')
    ];

    $parameters[] = [
        'name' => 'cache_buster',
        'type' => 0,
        'format' => 'integer',
        'description' => __('Cache buster') . '. ' . __('Possible values are: :values.', [
            'values' => implode(', ', [
                __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="font-weight-medium">'.__('Disabled').'</span>']),
                __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="font-weight-medium">'.__('Enabled').'</span>'])
                ])
            ]) . ($type ? ' ' . __('Defaults to: :value.', ['value' => '<code>0</code>']) : '')
    ];

    $parameters[] = [
        'name' => 'ssl_alert_days',
        'type' => 0,
        'format' => 'integer',
        'description' => __('The number of days before SSL certificate expiration to receive an alert.') . ' '. __('Possible values are: :values.', [
                'values' => '<code>' . implode('</code>, <code>', config('intervals.ssl')) . '</code>'])
    ];

    $parameters[] = [
        'name' => 'domain_alert_days',
        'type' => 0,
        'format' => 'integer',
        'description' => __('The number of days before domain name expiration to receive an alert.') . ' '. __('Possible values are: :values.', [
                'values' => '<code>' . implode('</code>, <code>', config('intervals.ssl')) . '</code>'])
    ];

    $parameters[] = [
        'name' => 'maintenance_start_at',
        'type' => 0,
        'format' => 'string',
        'description' => __('Maintenance starting date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
    ];

    $parameters[] = [
        'name' => 'maintenance_end_at',
        'type' => 0,
        'format' => 'string',
        'description' => __('Maintenance ending date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
    ];

    $parameters[] = [
        'name' => 'alerts[index][key]',
        'type' => 0,
        'format' => 'string',
        'description' => __('Alert channels') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', array_map(function($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="font-weight-medium">' . $name . '</span>']); }, array_keys(config('alert.channels')), config('alert.channels')))])
    ];

    $parameters[] = [
        'name' => 'alerts[index][value]',
        'type' => 0,
        'format' => 'string',
        'description' => __('Value of the alert.')
    ];

    if (!$type) {
        $parameters[] = [
                'name' => 'pause',
                'type' => 0,
                'format' => 'boolean',
                'description' => __('Pause') . '.'
            ];
    }
@endphp

@include('developers.parameters')
