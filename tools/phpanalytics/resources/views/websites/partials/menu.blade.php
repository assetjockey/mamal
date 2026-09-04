@if(request()->is('admin/*') || request()->is('dashboard') || request()->is('websites') || request()->is('websites/*'))
    <a href="#" class="btn d-flex align-items-center btn-sm text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
@endif

<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    @if(request()->is('admin/*') || Auth::check() && Auth::user()->role == 1 || Auth::check() && $website->user_id == Auth::user()->id)
        <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->role == 1 && $website->user_id != Auth::user()->id) ? route('admin.websites.edit', $website->id) : route('websites.edit', $website->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="{{ route('stats.overview', ['id' => $website->domain, 'from' => $range['from'] ?? null, 'to' => $range['to'] ?? null]) }}">@include('icons.eye', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('View') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ 'http://' . $website->domain }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.open-in-new', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Open') }}</a>

    @if(Auth::check() && Auth::user()->id == $website->user_id && !request()->is('admin/*'))
        <div class="dropdown-divider"></div>
        <a class="dropdown-item {{ ($website->favorited_at ? 'text-warning' : '') }} d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('websites.edit', $website->id) }}" data-button-class="btn btn-warning position-relative" data-button-name="favorite" data-button-value="{{ ($website->favorited_at ? '0' : '1') }}" data-button="btn {{ ($website->favorited_at ? 'btn-danger' : 'btn-warning') }}" data-title="{{ ($website->favorited_at ? __('Delete') : __('Favorite')) }}" data-text="{{ ($website->favorited_at ? __('Are you sure you want to remove :name from favorites?', ['name' => $website->name]) : __('Are you sure you want to add :name as favorite?', ['name' => $website->name])) }}">@include('icons.' . ($website->favorited_at ? 'star' : 'grade'), ['class' => 'fill-current width-4 height-4 ' . ($website->favorited_at ? 'text-warning' : 'text-muted') . (__('lang_dir') == 'rtl' ? ' ml-3' : ' mr-3')]) {{ __('Favorite') }}</a>
    @endif

    @if(request()->is('admin/*') || Auth::check() && Auth::user()->role == 1 || Auth::check() && $website->user_id == Auth::user()->id)
        <div class="dropdown-divider"></div>

        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->role == 1 && $website->user_id != Auth::user()->id) ? route('admin.websites.destroy', $website->id) : route('websites.destroy', $website->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => $website->domain]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
    @endif
</div>
