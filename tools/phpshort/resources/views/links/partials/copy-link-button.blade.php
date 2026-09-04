<a href="#" class="btn d-flex align-items-center {{ $class }}" data-clipboard-copy="{{ ($link->shortUrl) }}" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}">
    @include('icons.copy-link', ['class' => 'fill-current w-4 h-4'])&#8203;
</a>