@if(request()->is('admin/*') || request()->is('dashboard') || request()->is('incidents') || request()->is('incidents/new') || request()->is('incidents/*/edit') || request()->is('monitors/*'))
    <a href="#" class="btn d-flex align-items-center btn-sm text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
@endif

<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $incident->user_id == Auth::user()->id)
        <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $incident->user_id != Auth::user()->id) ? route('admin.incidents.edit', $incident->id) : route('incidents.edit', $incident->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="{{ route('incidents.show', ['id' => $incident->id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $incident->user_id == Auth::user()->id ? null : $incident->token)]) }}">@include('icons.eye', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('View') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.monitors.edit', ['id' => $incident->monitor_id]) : route('monitors.overview', ['id' => $incident->monitor_id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $incident->monitor->user_id == Auth::user()->id ? null : $incident->monitor->token)]) }}">@include('icons.monitor-heart', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Monitor') }}</a>

    @if(!$incident->ended_at && !$incident->acknowledged_at && Auth::check() && Auth::user()->id == $incident->user_id)
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-warning d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('incidents.acknowledge', $incident->id) }}" data-button-class="btn btn-warning position-relative" data-button-name="acknowledged_at" data-title="{{ __('Acknowledge') }}" data-text="{{ __('Are you sure you want to acknowledge the incident for the :name monitor?', ['name' => $incident->monitor->name]) }}">@include('icons.bolt', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Acknowledge') }}</a>
    @endif

    @if(!$incident->ended_at && $incident->acknowledged_at && Auth::check() && Auth::user()->id == $incident->user_id)
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-success d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ route('incidents.resolve', $incident->id) }}" data-button-class="btn btn-success position-relative" data-button-name="ended_at" data-title="{{ __('Resolve') }}" data-text="{{ __('Are you sure you want to mark the incident as resolved for the :name monitor?', ['name' => $incident->monitor->name]) }}">@include('icons.checkmark', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Resolve') }}</a>
    @endif

    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $incident->user_id == Auth::user()->id)
        <div class="dropdown-divider"></div>

        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $incident->user_id != Auth::user()->id) ? route('admin.incidents.destroy', $incident->id) : route('incidents.destroy', $incident->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Deleting this incident is permanent, and will remove all the data associated with it.') }}" data-sub-text="{{ __('Are you sure you want to delete :name?', ['name' => $incident->name]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
    @endif
</div>
