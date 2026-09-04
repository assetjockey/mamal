@section('site_title', formatTitle([$website->domain, __('Pages'), config('settings.title')]))

<div class="d-flex flex-column">
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Pages') }}</div></div>
                <div class="col-12 col-md-auto">
                    <div class="form-row">
                        @include('stats.filters', ['name' => __('URL'), 'count' => __('Pageviews')])
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(count($pages) == 0)
                {{ __('No data') }}.
            @else
                <div class="list-group list-group-flush my-n3">
                    <div class="list-group-item px-0 text-muted">
                        <div class="row align-items-center">
                            <div class="col">
                                {{ __('URL') }}
                            </div>
                            <div class="col-auto">
                                {{ __('Pageviews') }}
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

                    @foreach($pages as $page)
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="d-flex text-truncate align-items-center">
                                        <div class="d-flex text-truncate">
                                            <div class="text-truncate" dir="ltr">{{ $page->value }}</div> <a href="http://{{ $website->domain . $page->value }}" target="_blank" rel="nofollow noreferrer noopener" class="text-secondary d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.open-in-new', ['class' => 'fill-current width-3 height-3'])</a>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                        <span>{{ number_format($page->count, 0, __('.'), __(',')) }}</span>

                                        <div class="width-16 text-muted {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                            {{ number_format((($page->count / $total->count) * 100), 1, __('.'), __(',')) }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="progress height-1.25 w-100">
                                    <div class="progress-bar bg-danger rounded" role="progressbar" style="width: {{ (($page->count / $total->count) * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3 align-items-center">
                        <div class="row">
                            <div class="col">
                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $pages->firstItem(), 'to' => $pages->lastItem(), 'total' => $pages->total()]) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $pages->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
