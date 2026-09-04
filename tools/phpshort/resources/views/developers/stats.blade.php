@extends('layouts.app')

@section('site_title', formatTitle([__('Stats'), __('Developers'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container h-full py-4 my-4">

            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('home'), 'title' => __('Home')],
                ['url' => route('developers'), 'title' => __('Developers')],
                ['title' => __('Stats')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Stats') }}</h1>
                </div>
            </div>

            @include('developers.partials.authentication')

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
<pre class="m-0">{!! str_replace(':id', '<span class="text-success">{link_id}</span>', route('api.stats.show', ['id' => ':id'])) !!}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-4 rounded text-left" dir="ltr">
curl --location --request GET '{!! str_replace([':id', '%3Aname', '%3Afrom', '%3Ato'], ['<span class="text-success">{link_id}</span>', '<span class="text-success">{name}</span>', '<span class="text-success">{from}</span>', '<span class="text-success">{to}</span>'], route('api.stats.show', ['id' => ':id', 'name' => ':name', 'from' => ':from', 'to' => ':to'])) !!}' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>

                    <p class="mb-2">
                        {{ __('Parameters') }}
                    </p>
                    @include('developers.partials.parameters-list', ['parameters' => [
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
                                    __(':value for :name', ['value' => '<code>value</code>', 'name' => '<span class="fw-medium">' . __('Value') . '</span>'])
                                    ])
                                ])
                        ], [
                            'name' => 'sort_by',
                            'type' => 0,
                            'format' => 'string',
                            'description' => __('Sort by') . '. ' . __('Possible values are: :values.', [
                                'values' => implode(', ', [
                                    __(':value for :name', ['value' => '<code>count</code>', 'name' => '<span class="fw-medium">' . __('Count') . '</span>']),
                                    __(':value for :name', ['value' => '<code>value</code>', 'name' => '<span class="fw-medium">' . __('Value') . '</span>']),
                                    ])
                                ]) .' ' . __('Defaults to: :value.', ['value' => '<code>count</code>'])
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
        </div>
    </div>
@endsection

@include('shared.sidebars.user')