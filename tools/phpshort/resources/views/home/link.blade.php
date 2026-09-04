@if($link)
    <div class="form-group mb-0" id="copy-form-container">
        <div class="row mx-n1">
            <div class="col-12 col-sm px-1">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-success border-end-0"><img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4" alt=""></span>
                    </div>
                    <input type="text" dir="ltr" name="url" class="form-control form-control-lg fs-lg is-valid bg-transparent border-start-0 ps-0" value="{{ $link->displayShortUrl }}" onclick="this.select();" style="background-image: none;" readonly>
                </div>
                <span class="valid-feedback text-break d-block" role="alert">
                    <strong>{{ __('Link successfully shortened.') }}</strong>
                </span>
            </div>

            <div class="col-12 col-sm-auto px-1">
                <div class="btn-group btn-group-lg d-flex mt-4 mt-sm-0">
                    <button type="button" class="btn btn-lg btn-primary fs-lg flex-grow-1 home-copy" data-clipboard-copy="{{ ($link->shortUrl) }}">
                        <span>{{ __('Copy') }}</span><span class="d-none">{{ __('Copied') }}</span>
                    </button>
                    <button type="button" class="btn btn-primary fs-lg dropdown-toggle dropdown-toggle-split reset-after flex-grow-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @include('icons.expand-more', ['class' => 'flex-shrink-0 fill-current w-3 h-3'])
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    @include('links.partials.context-menu')
                </div>
            </div>
        </div>
    </div>
@endif