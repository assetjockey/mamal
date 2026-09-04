@php
    $parameters = [
        [
            'name' => 'name',
            'type' => 1,
            'format' => 'string',
            'description' => __('Name') . '. ' . __('Possible values are: :values.', ['values' => '<code>'.implode('</code>, <code>', config('stats.types')).'</code>'])
        ], [
            'name' => 'from',
            'type' => 1,
            'format' => 'string',
            'description' => __('Starting date in :format format.', ['format' => '<code>Y-m-d</code>'])
        ], [
            'name' => 'to',
            'type' => 1,
            'format' => 'string',
            'description' => __('Ending date in :format format.', ['format' => '<code>Y-m-d</code>'])
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
        ]
    ];
@endphp

@include('developers.parameters')
