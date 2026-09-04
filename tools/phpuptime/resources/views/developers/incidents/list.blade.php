@php
    $parameters = [
        [
            'name' => 'search',
            'type' => 0,
            'format' => 'string',
            'description' => __('The search query.')
        ], [
            'name' => 'search_by',
            'type' => 0,
            'format' => 'string',
            'description' => __('Search by') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>monitor</code>', 'name' => '<span class="font-weight-medium">'.__('Monitor').'</span>']),
                    __(':value for :name', ['value' => '<code>cause</code>', 'name' => '<span class="font-weight-medium">'.__('Cause').'</span>'])
                    ])
                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>url</code>'])
        ], [
            'name' => 'monitor_id',
            'type' => 0,
            'format' => 'string',
            'description' => __('Monitor ID') . '.'
        ], [
            'name' => 'status',
            'type' => 0,
            'format' => 'string',
            'description' => __('Status') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>unresolved</code>', 'name' => '<span class="font-weight-medium">'.__('Unresolved').'</span>']),
                    __(':value for :name', ['value' => '<code>acknowledged</code>', 'name' => '<span class="font-weight-medium">'.__('Acknowledged').'</span>']),
                    __(':value for :name', ['value' => '<code>resolved</code>', 'name' => '<span class="font-weight-medium">'.__('Resolved').'</span>'])])
                ])
        ], [
            'name' => 'sort_by',
            'type' => 0,
            'format' => 'string',
            'description' => __('Sort by') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>started_at</code>', 'name' => '<span class="font-weight-medium">'.__('Date started').'</span>']),
                    __(':value for :name', ['value' => '<code>ended_at</code>', 'name' => '<span class="font-weight-medium">'.__('Date ended').'</span>']),
                    ])
                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>ended_at</code>'])
        ], [
            'name' => 'sort',
            'type' => 0,
            'format' => 'string',
            'description' => __('Sort') . '. ' . __('Possible values are: :values.', [
                'values' => implode(', ', [
                    __(':value for :name', ['value' => '<code>desc</code>', 'name' => '<span class="font-weight-medium">'.__('Descending').'</span>']),
                    __(':value for :name', ['value' => '<code>asc</code>', 'name' => '<span class="font-weight-medium">'.__('Ascending').'</span>'])
                    ])
                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>desc</code>'])
        ], [
            'name' => 'per_page',
            'type' => 0,
            'format' => 'integer',
            'description' => __('Results per page') . '. '. __('Possible values are: :values.', [
                'values' => '<code>' . implode('</code>, <code>', [10, 25, 50, 100]) . '</code>'
                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>'.config('settings.paginate').'</code>'])
        ]
    ];
@endphp

@include('developers.parameters')
