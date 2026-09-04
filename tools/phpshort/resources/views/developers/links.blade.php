@extends('layouts.app')

@section('site_title', formatTitle([__('Links'), __('Developers'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container h-full py-4 my-4">

            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('home'), 'title' => __('Home')],
                ['url' => route('developers'), 'title' => __('Developers')],
                ['title' => __('Links')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Links') }}</h1>
                </div>
            </div>

            @include('developers.partials.authentication')

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('List') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-success px-2 py-1 me-4">GET</span>
<pre class="m-0">{{ route('api.links.index') }}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request GET '{{ route('api.links.index') }}' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
                        [
                            'name' => 'search',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Search query') . '.'
                        ], [
                            'name' => 'search_by',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Search by') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>title</code>', 'name' => '<span class="fw-medium">' . __('Title') . '</span>']),
                                    __(':value for :name', ['value' => '<code>alias</code>', 'name' => '<span class="fw-medium">' . __('Alias') . '</span>']),
                                    __(':value for :name', ['value' => '<code>url</code>', 'name' => '<span class="fw-medium">' . __('URL') . '</span>'])
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>title</code>'])
                        ], [
                            'name' => 'status',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Status') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="fw-medium">' . __('All') . '</span>']),
                                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="fw-medium">' . __('Active') . '</span>']),
                                    __(':value for :name', ['value' => '<code>2</code>', 'name' => '<span class="fw-medium">' . __('Expired') . '</span>']),
                                    __(':value for :name', ['value' => '<code>3</code>', 'name' => '<span class="fw-medium">' . __('Disabled') . '</span>'])
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>0</code>'])
                        ], [
                            'name' => 'space_id',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Space ID') . '.'
                        ], [
                            'name' => 'domain_id',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Domain ID') . '.'
                        ], [
                            'name' => 'pixel_id',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Pixel ID') . '.'
                        ], [
                            'name' => 'sort_by',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Sort by') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>id</code>', 'name' => '<span class="fw-medium">' . __('Date created') . '</span>']),
                                    __(':value for :name', ['value' => '<code>clicks</code>', 'name' => '<span class="fw-medium">' . __('Clicks') . '</span>']),
                                    __(':value for :name', ['value' => '<code>title</code>', 'name' => '<span class="fw-medium">' . __('Title') . '</span>']),
                                    __(':value for :name', ['value' => '<code>alias</code>', 'name' => '<span class="fw-medium">' . __('Alias') . '</span>']),
                                    __(':value for :name', ['value' => '<code>url</code>', 'name' => '<span class="fw-medium">' . __('URL') . '</span>'])
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>id</code>'])
                        ], [
                            'name' => 'sort',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Sort') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>desc</code>', 'name' => '<span class="fw-medium">' . __('Descending') . '</span>']),
                                    __(':value for :name', ['value' => '<code>asc</code>', 'name' => '<span class="fw-medium">' . __('Ascending') . '</span>'])
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>desc</code>'])
                        ], [
                            'name' => 'per_page',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Results per page') . '. '. __('Possible values are: :values.', [
                                'values' => '<code>' . implode('</code>, <code>', [10, 25, 50, 100]) . '</code>'
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>' . config('settings.paginate') . '</code>'])
                        ]
                    ]])
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Show') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-success px-2 py-1 me-4">GET</span>
<pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.show', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-0 rounded text-left" dir="ltr">
curl --location --request GET '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.show', ['id' => ':id'])) !!}' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Store') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-info px-2 py-1 me-4">POST</span>
<pre class="m-0">{{ route('api.links.store') }}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request POST '{{ route('api.links.store') }}' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \
--data-urlencode 'url=<span class="text-primary">{url}</span>' \
--data-urlencode 'domain=<span class="text-primary">{id}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
                        [
                            'name' => 'url',
                            'type' => 1,
                            'format' => 'string',
                            'description' => __('Destination URL') . '.'
                        ],
                        [
                            'name' => 'domain_id',
                            'type' => 1,
                            'format' => 'integer',
                            'description' => __('Domain ID') . '.'
                        ],
                        [
                            'name' => 'alias',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Alias') . '.'
                        ],
                        [
                            'name' => 'space_id',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Space ID') . '.'
                        ],
                        [
                            'name' => 'pixel_ids[]',
                            'type' => 0,
                            'format' => 'array',
                            'description' => __('Pixel IDs') . '.'
                        ],
                        [
                            'name' => 'redirect_password',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Redirect password') . '.'
                        ],
                        [
                            'name' => 'sensitive_content',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Sensitive content') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="fw-medium">' . __('No') . '</span>']),
                                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="fw-medium">' . __('Yes') . '</span>'])
                                    ])
                                ]) . ' ' . __('Defaults to: :value.', ['value' => '<code>0</code>'])
                        ],
                        [
                            'name' => 'privacy',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Stats privacy') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="fw-medium">' . __('Public') . '</span>']),
                                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="fw-medium">' . __('Private') . '</span>']),
                                    __(':value for :name', ['value' => '<code>2</code>', 'name' => '<span class="fw-medium">' . __('Password') . '</span>'])
                                    ])
                                ]) . ' ' . __('Defaults to: :value.', ['value' => '<code>0</code>'])
                        ],
                        [
                            'name' => 'password',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Stats password') . '. ' . __('Only works with :field field set to :value.', ['field' => '<code>privacy</code>', 'value' => '<code>2</code>'])
                        ],
                        [
                            'name' => 'active_period_start_at',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Active period starting date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
                        ],
                        [
                            'name' => 'active_period_end_at',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Active period ending date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
                        ],
                        [
                            'name' => 'clicks_limit',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Clicks limit') . '.'
                        ],
                        [
                            'name' => 'expiration_url',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Expiration URL') . '.'
                        ],
                        [
                            'name' => 'targets_type',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Targeting') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', array_map(function ($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="fw-medium">' . $name . '</span>']); }, array_keys(config('targets')), config('targets')))])
                        ],
                        [
                            'name' => 'targets[index][key]',
                            'type' => 0,
                            'format' => 'string',
                            'description' =>
                            '<p>' . __('For :field, the value must be in :format format.', ['field' => '<code>targets_type=country</code>', 'format' => '<a href="https://wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="nofollow noreferrer noopener">ISO 3166-1 alpha-2</a>']) . '</p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=operating_systems</code>', 'values' => '<code>'.implode('</code>, <code>', config('operating_systems'))]) . '</code></p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=browsers</code>', 'values' => '<code>'.implode('</code>, <code>', config('browsers'))]) . '</code></p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=devices</code>', 'values' => '<code>'.implode('</code>, <code>', config('devices'))]) . '</code></p>' .
                            '<p>' . __('For :field, the value must be in :format format.', ['field' => '<code>targets_type=languages</code>', 'format' => '<a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank" rel="nofollow noreferrer noopener">ISO 639-1 alpha-2</a>']) . '</p>' .
                            '<p class="mb-0">' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=continents</code>', 'values' => implode(', ', array_map(function ($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="fw-medium">' . $name . '</span>']); }, array_keys(config('continents')), config('continents')))]) . '</code></p>'
                        ],
                        [
                            'name' => 'targets[index][value]',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Destination URL') . '.'
                        ]
                    ]])
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Update') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-warning px-2 py-1 me-2">PUT</span> <span class="badge badge-warning px-2 py-1 me-4">PATCH</span>
                        <pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.update', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request PUT '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.update', ['id' => ':id'])) !!}' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
                        [
                            'name' => 'url',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Destination URL') . '.'
                        ],
                        [
                            'name' => 'alias',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Alias') . '.'
                        ],
                        [
                            'name' => 'space_id',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Space ID') . '.'
                        ],
                        [
                            'name' => 'pixel_ids[]',
                            'type' => 0,
                            'format' => 'array',
                            'description' => __('Pixel IDs') . '.'
                        ],
                        [
                            'name' => 'redirect_password',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Redirect password') . '.'
                        ],
                        [
                            'name' => 'sensitive_content',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Sensitive content') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="fw-medium">' . __('No') . '</span>']),
                                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="fw-medium">' . __('Yes') . '</span>'])
                                    ])
                                ])
                        ],
                        [
                            'name' => 'privacy',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Stats privacy') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>0</code>', 'name' => '<span class="fw-medium">' . __('Public') . '</span>']),
                                    __(':value for :name', ['value' => '<code>1</code>', 'name' => '<span class="fw-medium">' . __('Private') . '</span>']),
                                    __(':value for :name', ['value' => '<code>2</code>', 'name' => '<span class="fw-medium">' . __('Password') . '</span>'])
                                    ])
                                ])
                        ],
                        [
                            'name' => 'password',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Stats password') . '. ' . __('Only works with :field field set to :value.', ['field' => '<code>privacy</code>', 'value' => '<code>2</code>'])
                        ],
                        [
                            'name' => 'active_period_start_at',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Active period starting date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
                        ],
                        [
                            'name' => 'active_period_end_at',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Active period ending date in :format format.', ['format' => '<code>Y-m-dTH:i:s</code>'])
                        ],
                        [
                            'name' => 'clicks_limit',
                            'type' => 0,
                            'format' => 'integer',
                            'description' => __('Clicks limit') . '.'
                        ],
                        [
                            'name' => 'expiration_url',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Expiration URL') . '.'
                        ],
                        [
                            'name' => 'targets_type',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Targeting') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', array_map(function ($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="fw-medium">' . $name . '</span>']); }, array_keys(config('targets')), config('targets')))])
                        ],
                        [
                            'name' => 'targets[index][key]',
                            'type' => 0,
                            'format' => 'string',
                            'description' =>
                            '<p>' . __('For :field, the value must be in :format format.', ['field' => '<code>targets_type=country</code>', 'format' => '<a href="https://wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank" rel="nofollow noreferrer noopener">ISO 3166-1 alpha-2</a>']) . '</p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=operating_systems</code>', 'values' => '<code>'.implode('</code>, <code>', config('operating_systems'))]) . '</code></p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=browsers</code>', 'values' => '<code>'.implode('</code>, <code>', config('browsers'))]) . '</code></p>' .
                            '<p>' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=devices</code>', 'values' => '<code>'.implode('</code>, <code>', config('devices'))]) . '</code></p>' .
                            '<p>' . __('For :field, the value must be in :format format.', ['field' => '<code>targets_type=languages</code>', 'format' => '<a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank" rel="nofollow noreferrer noopener">ISO 639-1 alpha-2</a>']) . '</p>' .
                            '<p class="mb-0">' . __('For :field, the possible values are: :values.', ['field' => '<code>targets_type=continents</code>', 'values' => implode(', ', array_map(function ($value, $name) { return __(':value for :name', ['value' => '<code>' . $value . '</code>', 'name' => '<span class="fw-medium">' . $name . '</span>']); }, array_keys(config('continents')), config('continents')))]) . '</code></p>'
                        ],
                        [
                            'name' => 'targets[index][value]',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Destination URL') . '.'
                        ]
                    ]])
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Delete') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-danger px-2 py-1 me-4">DELETE</span>
<pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.destroy', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
                    <pre class="bg-light text-inverse p-4 mb-0 rounded text-left" dir="ltr">
curl --location --request DELETE '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.links.destroy', ['id' => ':id'])) !!}' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')