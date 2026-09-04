<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.domains.edit', $domain->id) : route('domains.edit', $domain->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Edit') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.links', ['domain_id' => $domain->id]) : route('links', ['domain_id' => $domain->id]) }}">@include('icons.link', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Links') }}</a>

    <div class="dropdown-divider"></div>
    
    <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') ? route('admin.domains.destroy', $domain->id) : route('domains.destroy', $domain->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Deleting this domain is permanent, and will remove all the links associated with it.') }}" data-sub-text="{{ __('Are you sure you want to delete :name?', ['name' => $domain->name]) }}">@include('icons.delete', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Delete') }}</a>
</div>