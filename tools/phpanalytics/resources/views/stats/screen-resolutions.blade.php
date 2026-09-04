@section('site_title', formatTitle([$website->domain, __('Screen resolutions'), config('settings.title')]))

<div class="d-flex flex-column">
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Screen resolutions') }}</div></div>
                <div class="col-12 col-md-auto">
                    <div class="form-row">
                        @include('stats.filters', ['name' => __('Size'), 'count' => __('Visitors')])
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(count($screenResolutions) == 0)
                {{ __('No data') }}.
            @else
                <div class="list-group list-group-flush my-n3">
                    <div class="list-group-item px-0 text-muted">
                        <div class="row align-items-center">
                            <div class="col">
                                {{ __('Size') }}
                            </div>
                            <div class="col-auto">
                                {{ __('Visitors') }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0 small text-muted">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex text-truncate align-items-center">
                                    <div class="text-truncate">
                                        {{ __('Total') }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                    <span>{{ number_format($total->count, 0, __('.'), __(',')) }}</span>

                                    <div class="width-16 text-muted {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                        {{ number_format((($total->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($screenResolutions as $screenResolution)
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex text-truncate align-items-center">
                                        <div class="text-truncate">
                                            @if($screenResolution->value)
                                                {{ $screenResolution->value }}
                                            @else
                                                {{ __('Unknown') }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                        <span>{{ number_format($screenResolution->count, 0, __('.'), __(',')) }}</span>

                                        <div class="width-16 text-muted {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                            {{ number_format((($screenResolution->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="progress height-1.25 w-100">
                                    <div class="progress-bar bg-primary rounded" role="progressbar" style="width: {{ (($screenResolution->count / $total->count) * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3 align-items-center">
                        <div class="row">
                            <div class="col">
                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $screenResolutions->firstItem(), 'to' => $screenResolutions->lastItem(), 'total' => $screenResolutions->total()]) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $screenResolutions->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
