@if(request()->session()->get('toast'))
    <div class="position-relative position-lg-fixed z-1001 w-lg-96 top-0 end-0">
        @foreach(request()->session()->get('toast') as $link)
            <div aria-live="polite" aria-atomic="true" class="position-relative">
                <div class="toast backdrop-blur-sm fade show border-0 fs-base mx-lg-4 shadow-sm mt-4 overflow-hidden max-w-full" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false" style="max-width: inherit;">
                    <div class="toast-header px-1 py-2">
                        <div class="d-flex align-items-center ps-2 pe-4">@include('icons.link', ['class' => 'fill-current w-4 h-4'])</div>
                        <div class="me-auto">{{ __('Link ready') }}</div>
                        <button type="button" class="close d-flex align-items-center justify-content-center p-2" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current w-4 h-4'])</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        <div class="row">
                            <div class="col d-flex text-truncate">
                                <div class="me-2"><img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4" alt=""></div>

                                <div class="text-truncate">
                                    <a href="{{ route('stats.overview', $link->id) }}" dir="ltr">{{ $link->displayShortUrl }}</a>

                                    <div class="text-inverse text-truncate small">
                                        <span class="text-secondary cursor-help" data-tooltip="true" data-html="true" title='@include('links.partials.tooltip')'><span dir="ltr">{{ $link->displayUrl }}</span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-auto d-flex">
                                @include('links.partials.copy-link-button', ['class' => 'btn-sm text-primary'])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif