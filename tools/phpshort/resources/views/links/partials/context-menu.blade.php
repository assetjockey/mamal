<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $link->user_id == Auth::user()->id)
        <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $link->user_id != Auth::user()->id) ? route('admin.links.edit', $link->id) : route('links.edit', $link->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Edit') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="{{ ($link->shortUrl) }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.eye', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('View') }}</a>

    @if(isset($link->user_id))
        <a class="dropdown-item d-flex align-items-center" href="{{ route('stats.overview', $link->id) }}">@include('icons.bar-chart', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Stats') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="#" data-toggle="modal" data-target="#share-modal" data-url="{{ ($link->shortUrl) }}" data-text="@if($link->title){{ $link->title }}@else{{ $link->displayUrl }}@endif" data-qr="{{ route('qr.show', ['url' => $link->shortUrl]) }}" data-share-link>@include('icons.share', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Share') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ $link->url }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.open-in-new', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Open') }}</a>

    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $link->user_id == Auth::user()->id)
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $link->user_id != Auth::user()->id) ? route('admin.links.destroy', $link->id) : route('links.destroy', $link->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => $link->displayShortUrl]) }}">@include('icons.delete', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Delete') }}</a>
    @endif
</div>