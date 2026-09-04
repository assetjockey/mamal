@extends('layouts.app')

@section('site_title', formatTitle([__('Developers'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container h-full py-16">
            <div class="text-center">
                <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Developers') }}</h1>
                <div class="mx-auto mt-4">
                    <p class="text-muted fw-normal fs-lg pb-6 mb-4">{{ __('Explore our API documentation.') }}</p>
                </div>
            </div>

            @php
                $resources = [
                    [
                        'icon' => 'link',
                        'title' => __('Links'),
                        'description' => __('Manage the links.'),
                        'route' => route('developers.links')
                    ],
                    [
                        'icon' => 'workspaces',
                        'title' => __('Spaces'),
                        'description' => __('Manage the spaces.'),
                        'route' => route('developers.spaces')
                    ],
                    [
                        'icon' => 'website',
                        'title' => __('Domains'),
                        'description' => __('Manage the domains.'),
                        'route' => route('developers.domains')
                    ],
                    [
                        'icon' => 'filter-center-focus',
                        'title' => __('Pixels'),
                        'description' => __('Manage the pixels.'),
                        'route' => route('developers.pixels')
                    ],
                    [
                        'icon' => 'bar-chart',
                        'title' => __('Stats'),
                        'description' => __('Manage the stats.'),
                        'route' => route('developers.stats')
                    ],
                    [
                        'icon' => 'account-box',
                        'title' => __('Account'),
                        'description' => __('Manage the account.'),
                        'route' => route('developers.account')
                    ]
                ];
            @endphp

            <div class="row m-n2">
                @foreach($resources as $resource)
                    <div class="col-12 col-sm-6 col-md-4 p-2">
                        <div class="card border-0 h-full shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.' . $resource['icon'], ['class' => 'fill-current w-6 h-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ $resource['route'] }}" class="text-inverse fw-medium text-decoration-none stretched-link">{{ $resource['title'] }}</a>

                                    <div class="text-muted">
                                        {{ $resource['description'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')