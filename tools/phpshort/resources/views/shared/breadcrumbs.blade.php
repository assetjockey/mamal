@if(count($breadcrumbs))
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb px-0 bg-transparent fw-medium mb-0">
            @foreach ($breadcrumbs as $breadcrumb)
                @if(!isset($breadcrumb['title']))
                    @continue
                @endif
                @if (!$loop->last)
                    <li class="breadcrumb-item d-flex align-items-center">
                        @if(isset($breadcrumb['url']))
                            <a href="{{ $breadcrumb['url'] }}" class="text-muted">{{ $breadcrumb['title'] }}</a>
                        @else
                            <div class="text-muted">{{ $breadcrumb['title'] }}</div>
                        @endif
                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'fill-current w-3 h-3 mx-4 text-muted'])</li>
                @else
                    <li class="breadcrumb-item active text-inverse">{{ $breadcrumb['title'] }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
