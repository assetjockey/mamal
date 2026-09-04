<a href="#" class="btn d-flex align-items-center btn-sm text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>

<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $statusPage->user_id != Auth::user()->id) ? route('admin.status_pages.edit', $statusPage->id) : route('status_pages.edit', $statusPage->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ $statusPage->url }}">@include('icons.eye', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('View') }}</a>

    <div class="dropdown-divider"></div>

    <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $statusPage->user_id != Auth::user()->id) ? route('admin.status_pages.destroy', $statusPage->id) : route('status_pages.destroy', $statusPage->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Deleting this status page is permanent, and will remove all the data associated with it.') }}" data-sub-text="{{ __('Are you sure you want to delete :name?', ['name' => $statusPage->name]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
</div>
