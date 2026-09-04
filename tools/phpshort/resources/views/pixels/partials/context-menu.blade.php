<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.pixels.edit', $pixel->id) : route('pixels.edit', $pixel->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Edit') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.links', ['pixel_id' => $pixel->id]) : route('links', ['pixel_id' => $pixel->id]) }}">@include('icons.link', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Links') }}</a>

    <div class="dropdown-divider"></div>
    
    <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') ? route('admin.pixels.destroy', $pixel->id) : route('pixels.destroy', $pixel->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Are you sure you want to delete :name?', ['name' => $pixel->name]) }}">@include('icons.delete', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Delete') }}</a>
</div>