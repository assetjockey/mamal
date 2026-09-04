@extends('layouts.app')

@section('site_title', formatTitle([__('Pixels'), __('Developers'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container h-full py-4 my-4">

            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('home'), 'title' => __('Home')],
                ['url' => route('developers'), 'title' => __('Developers')],
                ['title' => __('Pixels')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Pixels') }}</h1>
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
<pre class="m-0">{{ route('api.pixels.index') }}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request GET '{{ route('api.pixels.index') }}' \
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
                                    __(':value for :name', ['value' => '<code>name</code>', 'name' => '<span class="fw-medium">' . __('Name') . '</span>'])
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>name</code>'])
                        ], [
                            'name' => 'type',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Type') . '. ' . __('Possible values are: :values.', ['values' => '<code>'.implode('</code>, <code>', array_keys(config('pixels'))).'</code>'])
                        ], [
                            'name' => 'sort_by',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Sort by') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>id</code>', 'name' => '<span class="fw-medium">' . __('Date created') . '</span>']),
                                    __(':value for :name', ['value' => '<code>name</code>', 'name' => '<span class="fw-medium">' . __('Name') . '</span>'])
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
<pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.show', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-0 rounded text-left" dir="ltr">
curl --location --request GET '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.show', ['id' => ':id'])) !!}' \
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
<pre class="m-0">{{ route('api.pixels.store') }}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request POST '{{ route('api.pixels.store') }}' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>' \
--data-urlencode 'name=<span class="text-primary">{name}</span>' \
--data-urlencode 'type=<span class="text-primary">{type}</span>' \
--data-urlencode 'value=<span class="text-primary">{value}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
                        [
                            'name' => 'name',
                            'type' => 1,
                            'format' => 'string',
                            'description' => __('Name') . '.'
                        ],
                        [
                            'name' => 'type',
                            'type' => 1,
                            'format' => 'string',
                            'description' => __('Type') . '. ' . __('Possible values are: :values.', ['values' => '<code>'.implode('</code>, <code>', array_keys(config('pixels'))).'</code>'])
                        ],
                        [
                            'name' => 'value',
                            'type' => 1,
                            'format' => 'string',
                            'description' => __('The pixel ID value.')
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
<pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.update', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request PUT '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.update', ['id' => ':id'])) !!}' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
                        [
                            'name' => 'name',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Name') . '.'
                        ],
                        [
                            'name' => 'type',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Type') . '. ' . __('Possible values are: :values.', ['values' => '<code>'.implode('</code>, <code>', array_keys(config('pixels'))).'</code>'])
                        ],
                        [
                            'name' => 'value',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('The pixel ID value.')
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
<pre class="m-0">{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.destroy', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-0 rounded text-left" dir="ltr">
curl --location --request DELETE '{!! str_replace(':id', '<span class="text-primary">{id}</span>', route('api.pixels.destroy', ['id' => ':id'])) !!}' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')