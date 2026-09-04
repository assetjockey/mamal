<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.tax-rates.edit', $taxRate->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current w-4 h-4 me-4']) {{ __('Edit') }}</a>

    <div class="dropdown-divider"></div>

    @if($taxRate->trashed())
        <a class="dropdown-item text-success d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('admin.tax-rates.restore', $taxRate->id) }}" data-button-class="btn btn-success position-relative" data-title="{{ __('Restore') }}" data-text="{{ __('Are you sure you want to restore :name?', ['name' => $taxRate->name]) }}">@include('icons.settings-backup-restore', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Restore') }}</a>
    @else
        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('admin.tax-rates.disable', $taxRate->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Disable') }}" data-text="{{ __('Disabling this account will cancel any active subscription.') }}" data-sub-text="{{ __('Are you sure you want to disable :name?', ['name' => $taxRate->name]) }}">@include('icons.block', ['class' => 'fill-current w-4 h-4 me-4']) {{ __('Disable') }}</a>
    @endif
</div>
