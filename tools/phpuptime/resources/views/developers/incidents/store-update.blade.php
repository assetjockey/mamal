@php
    if ($type) {
        $parameters[] = [
            'name' => 'monitor_id',
            'type' => 1,
            'format' => 'integer',
            'description' => __('Monitor ID') .'.'
        ];

        $parameters[] = [
            'name' => 'started_at',
            'type' => 1,
            'format' => 'string',
            'description' => __('Started at date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
        ];
    }

    $parameters[] = [
        'name' => 'acknowledged_at',
        'type' => 0,
        'format' => 'string',
        'description' => __('Acknowledged at date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
    ];

    $parameters[] = [
        'name' => 'ended_at',
        'type' => 0,
        'format' => 'string',
        'description' => __('Ended at date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
    ];

    $parameters[] = [
        'name' => 'cause',
        'type' => 0,
        'format' => 'string',
        'description' => __('Cause') . '.'
    ];

    $parameters[] = [
        'name' => 'comment',
        'type' => 0,
        'format' => 'string',
        'description' => __('Comment') . '.'
    ];
@endphp

@include('developers.parameters')
