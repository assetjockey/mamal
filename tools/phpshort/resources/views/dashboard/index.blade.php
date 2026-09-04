@extends('layouts.app')

@section('site_title', formatTitle([__('Dashboard'), config('settings.title')]))

@section('content')
<div class="bg-base-1 flex-fill">
    <div class="bg-base-0">
        <div class="container py-12">
            <div class="row m-n2">
                <div class="d-flex col-12 col-md p-2">
                    <div class="flex-shrink-1">
                        <a href="{{ route('account') }}" class="d-block"><img src="{{ Auth::user()->avatarUrl }}" class="rounded-circle w-16 h-16" alt=""></a>
                    </div>
                    <div class="flex-grow-1 d-flex align-items-center ms-4">
                        <div>
                            <h4 class="fs-2xl fw-medium tracking-tight mb-0">{{ Auth::user()->name }}</h4>

                            <div class="d-flex flex-wrap">
                                @if(enabledPaymentProcessors())
                                    <div class="d-inline-block mt-2 me-6">
                                        <div class="d-flex">
                                            <div class="d-inline-flex align-items-center">
                                                @include('icons.package', ['class' => 'text-muted fill-current w-4 h-4'])
                                            </div>

                                            <div class="d-inline-block ms-2">
                                                <a href="{{ route('account.plan') }}" class="text-inverse text-decoration-none">{{ Auth::user()->active_plan->name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="d-inline-block mt-2 me-6">
                                        <div class="d-flex">
                                            <div class="d-inline-flex align-items-center">
                                                @include('icons.email', ['class' => 'text-muted fill-current w-4 h-4'])
                                            </div>

                                            <div class="d-inline-block ms-2">
                                                {{ Auth::user()->email }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-auto d-md-flex align-items-center p-2">
                    <div class="row m-n2">
                        @if(enabledPaymentProcessors())
                            <div class="col-12 col-md-auto d-flex align-items-center p-2">
                                @if(Auth::user()->planIsDefault())
                                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.unarchive', ['class' => 'w-4 h-4 fill-current me-2']){{ __('Upgrade') }}</a>
                                @else
                                    <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.package', ['class' => 'w-4 h-4 fill-current me-2']){{ __('Plans') }}</a>
                                @endif
                            </div>
                        @endif

                        <div class="col-12 col-md-auto d-flex align-items-center p-2">
                            <a href="{{ route('links') }}" class="btn btn-primary btn-block d-flex justify-content-center align-items-center">@include('icons.add', ['class' => 'w-4 h-4 fill-current me-2']) {{ __('New link') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-base-1">
        <div class="container pt-12 pb-16">
            <h4 class="fs-2xl fw-medium tracking-tight mb-4">{{ __('Overview') }}</h4>

            <div class="row m-n2">
                @php
                    $cards = [
                        [
                            'title' => 'Links',
                            'value' => Auth::user()->linksCount,
                            'route' => 'links',
                            'icon' => 'link'
                        ],
                        [
                            'title' => 'Spaces',
                            'value' => Auth::user()->spaces()->count(),
                            'route' => 'spaces',
                            'icon' => 'workspaces'
                        ],
                        [
                            'title' => 'Domains',
                            'value' => Auth::user()->domains()->count(),
                            'route' => 'domains',
                            'icon' => 'website'
                        ],
                        [
                            'title' => 'Pixels',
                            'value' => Auth::user()->pixels()->count(),
                            'route' => 'pixels',
                            'icon' => 'filter-center-focus'
                        ]
                    ];
                @endphp

                @foreach($cards as $card)
                    <div class="col-12 col-md-6 col-xl-3 p-2">
                        <div class="card border-0 shadow-sm h-full overflow-hidden">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary w-10 h-10 align-items-center justify-content-center flex-shrink-0">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.' . $card['icon'], ['class' => 'fill-current w-5 h-5'])
                                </div>

                                <div class="flex-grow-1"></div>

                                <div class="d-flex align-items-center h2 fw-bold mb-0 text-truncate">
                                    {{ number_format($card['value'], 0, __('.'), __(',')) }}
                                </div>
                            </div>
                            <div class="card-footer bg-base-2 border-0">
                                <a href="{{ route($card['route']) }}" class="text-secondary fw-medium d-inline-flex align-items-baseline w-full">{{ __($card['title']) }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h4 class="fs-2xl fw-medium tracking-tight mb-4 mt-12">{{ __('Activity') }}</h4>

            <div class="row m-n2">
                <div class="col-12 col-lg-6 p-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Latest links') }}</div>
                        </div>
                        <div class="card-body">
                            @if(count($latestLinks) == 0)
                                {{ __('No data') }}.
                            @else
                                <div class="list-group list-group-flush my-n4">
                                    @foreach($latestLinks as $link)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col d-flex text-truncate">
                                                    <div class="text-truncate">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4 me-4" alt="">

                                                            <div class="text-truncate">
                                                                <a href="{{ route('stats.overview', $link->id) }}" dir="ltr">{{ $link->displayShortUrl }}</a>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="w-4 flex-shrink-0 me-4"></div>
                                                            <div class="text-muted text-truncate small cursor-help" data-tooltip="true" data-html="true" title='@include('links.partials.tooltip')'>
                                                                <span dir="ltr">{{ $link->displayUrl }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="row mx-n1">
                                                        <div class="col px-1">
                                                            @include('links.partials.copy-link-button', ['class' => 'btn-ghost-primary btn-sm'])
                                                        </div>
                                                        <div class="col px-1">
                                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                            @include('links.partials.context-menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(count($latestLinks) > 0)
                            <div class="card-footer bg-base-2 border-0">
                                <a href="{{ route('links') }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-6 p-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Popular links') }}</div>
                        </div>
                        <div class="card-body">
                            @if(count($popularLinks) == 0)
                                {{ __('No data') }}.
                            @else
                                <div class="list-group list-group-flush my-n4">
                                    @foreach($popularLinks as $link)
                                        <div class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col d-flex text-truncate">
                                                    <div class="text-truncate">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4 me-4" alt="">

                                                            <div class="text-truncate">
                                                                <a href="{{ route('stats.overview', $link->id) }}" dir="ltr">{{ $link->displayShortUrl }}</a>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="w-4 flex-shrink-0 me-4"></div>
                                                            <div class="text-muted text-truncate small cursor-help" data-tooltip="true" data-html="true" title='@include('links.partials.tooltip')'>
                                                                <span dir="ltr">{{ $link->displayUrl }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="row mx-n1">
                                                        <div class="col px-1">
                                                            <a href="{{ route('stats.overview', $link->id) }}" class="btn btn-ghost-primary btn-sm d-flex align-items-center" data-tooltip="true" title="{{ __('Stats') }}">
                                                                @include('icons.bar-chart', ['class' => 'fill-current w-4 h-4'])&#8203;
                                                            </a>
                                                        </div>
                                                        <div class="col px-1">
                                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                            @include('links.partials.context-menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @if(count($popularLinks) > 0)
                            <div class="card-footer bg-base-2 border-0">
                                <a href="{{ route('links', ['sort_by' => 'clicks_count']) }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <h4 class="fs-2xl fw-medium tracking-tight mb-4 mt-12">{{ __('More') }}</h4>

            <div class="row m-n2">
                <div class="col-12 col-lg-4 p-2">
                    <div class="card border-0 h-full shadow-sm">
                        <div class="card-body d-flex">
                            <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                @include('icons.workspaces', ['class' => 'fill-current w-6 h-6'])
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <a href="{{ route('spaces.new') }}" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Space') }}</a>

                                <div class="text-muted">
                                    {{ __('Create a new space.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 p-2">
                    <div class="card border-0 h-full shadow-sm">
                        <div class="card-body d-flex">
                            <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                @include('icons.website', ['class' => 'fill-current w-6 h-6'])
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <a href="{{ route('domains.new') }}" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Domain') }}</a>

                                <div class="text-muted">
                                    {{ __('Add a new domain.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 p-2">
                    <div class="card border-0 h-full shadow-sm">
                        <div class="card-body d-flex">
                            <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                @include('icons.filter-center-focus', ['class' => 'fill-current w-6 h-6'])
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <a href="{{ route('pixels.new') }}" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Pixel') }}</a>

                                <div class="text-muted">
                                    {{ __('Integrate a new pixel.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('shared.modals.share-link')
@endsection

@include('shared.sidebars.user')
