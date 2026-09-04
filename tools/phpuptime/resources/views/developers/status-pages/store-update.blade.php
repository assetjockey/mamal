@php
    if (!$type) {
        $parameters[] = [
            'name' => '_method',
            'type' => 1,
            'format' => 'string',
            'description' => __('Must be set to :value.', ['value' => '<code>PUT</code>'])
        ];
    }

    $parameters[] = [
        'name' => 'name',
        'type' => $type,
        'format' => 'string',
        'description' => __('Name') . '.'
    ];

    $parameters[] = [
        'name' => 'slug',
        'type' => $type,
        'format' => 'string',
        'description' => __('Slug') . '.'
    ];

    $parameters[] = [
        'name' => 'monitor_ids[]',
        'type' => 0,
        'format' => 'array',
        'description' => __('Monitor IDs')
    ];

    $parameters[] = [
        'name' => 'privacy',
        'type' => 0,
        'format' => 'integer',
        'description' => __('Privacy') . '. ' . __('Possible values are: :values.', [
            'values' => implode(', ', [
                __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="font-weight-medium">'.__('Public').'</span>']),
                __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="font-weight-medium">'.__('Private').'</span>']),
                __(':value for :name', ['value' => '<code>2</code>', 'name' => '<span class="font-weight-medium">'.__('Password').'</span>'])
                ])
            ]) . ($type ? ' ' . __('Defaults to: :value.', ['value' => '<code>0</code>']) : '')
    ];

    $parameters[] = [
        'name' => 'password',
        'type' => 0,
        'format' => 'string',
        'description' => __('Password') . '. ' . __('Only works with :field field set to :value.', ['field' => '<code>privacy</code>', 'value' => '<code>2</code>'])
    ];

    $parameters[] = [
        'name' => 'domain',
        'type' => 0,
        'format' => 'string',
        'description' => __('Custom domain') . '.'
    ];

    $parameters[] = [
        'name' => 'logo',
        'type' => 0,
        'format' => 'file',
        'description' => __('Logo') . '.'
    ];

    $parameters[] = [
        'name' => 'favicon',
        'type' => 0,
        'format' => 'file',
        'description' => __('Favicon') . '.'
    ];

    $parameters[] = [
        'name' => 'remove_logo',
        'type' => 0,
        'format' => 'boolean',
        'description' => __('Remove logo') .'.'
    ];

    $parameters[] = [
        'name' => 'remove_favicon',
        'type' => 0,
        'format' => 'boolean',
        'description' => __('Remove favicon') .'.'
    ];

    $parameters[] = [
        'name' => 'website_url',
        'type' => 0,
        'format' => 'string',
        'description' => __('Website URL') . '.'
    ];

    $parameters[] = [
        'name' => 'contact_url',
        'type' => 0,
        'format' => 'string',
        'description' => __('Contact URL') . '.'
    ];

    $parameters[] = [
        'name' => 'custom_css',
        'type' => 0,
        'format' => 'string',
        'description' => __('Custom CSS') . '.'
    ];

    $parameters[] = [
        'name' => 'custom_js',
        'type' => 0,
        'format' => 'string',
        'description' => __('Custom JS') . '.'
    ];

    $parameters[] = [
        'name' => 'meta_title',
        'type' => 0,
        'format' => 'string',
        'description' => __('Meta title') . '.'
    ];

    $parameters[] = [
        'name' => 'meta_description',
        'type' => 0,
        'format' => 'string',
        'description' => __('Meta description') . '.'
    ];

    $parameters[] = [
        'name' => 'noindex',
        'type' => 0,
        'format' => 'boolean',
        'description' => __('Exclude the status page from search engines.')
    ];
@endphp

@include('developers.parameters')
