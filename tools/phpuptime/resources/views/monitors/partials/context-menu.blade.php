@if(request()->is('admin/*') || request()->is('dashboard') || request()->is('monitors') || request()->is('monitors/new') || request()->is('monitors/*/edit'))
    <a href="#" class="btn d-flex align-items-center btn-sm text-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
@endif

<div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow">
    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id)
        <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $monitor->user_id != Auth::user()->id) ? route('admin.monitors.edit', $monitor->id) : route('monitors.edit', $monitor->id) }}">@include('icons.edit', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Edit') }}</a>
    @endif

    <a class="dropdown-item d-flex align-items-center" href="{{ route('monitors.overview', ['id' => $monitor->id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id ? null : $monitor->token)]) }}">@include('icons.eye', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('View') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ $monitor->url }}" target="_blank" rel="nofollow noreferrer noopener">@include('icons.open-in-new', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Open') }}</a>

    <a class="dropdown-item d-flex align-items-center" href="{{ request()->is('admin/*') ? route('admin.incidents', ['monitor_id' => $monitor->id]) : route('incidents', ['monitor_id' => $monitor->id]) }}">@include('icons.error', ['class' => 'text-muted fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Incidents') }}</a>

    @if(request()->is('monitors/*'))
        @if(Auth::check() && Auth::user()->id == $monitor->user_id)
            <div class="dropdown-divider"></div>
            <a class="dropdown-item {{ ($monitor->status == 'paused' ? 'text-success' : 'text-warning') }} d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') ? route('admin.monitors.edit', $monitor->id) : route('monitors.edit', $monitor->id) }}" data-button-name="pause" data-button-value="{{ ($monitor->status == 'paused' ? '' : (request()->is('monitors/*/checks') || request()->is('monitors/*/incidents') ? 'back' : 'true')) }}" data-button-class="btn {{ ($monitor->status == 'paused' ? 'btn-success' : 'btn-warning') }} position-relative" data-title="{{ ($monitor->status == 'paused' ? __('Resume') : __('Pause')) }}" data-text="{{ ($monitor->status == 'paused' ? __('Are you sure you want to resume :name?', ['name' => $monitor->name]) : __('Are you sure you want to pause :name?', ['name' => $monitor->name])) }}">@include('icons.' . ($monitor->status == 'paused' ? 'play' : 'pause'), ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ ($monitor->status == 'paused' ? __('Resume') : __('Pause')) }}</a>
        @endif
    @endif

    @if(request()->is('admin/*') || Auth::check() && Auth::user()->isAdmin() || Auth::check() && $monitor->user_id == Auth::user()->id)
        <div class="dropdown-divider"></div>

        <a class="dropdown-item text-danger d-flex align-items-center" href="#" data-toggle="modal" data-target="#modal" data-action="{{ request()->is('admin/*') || (Auth::user()->isAdmin() && $monitor->user_id != Auth::user()->id) ? route('admin.monitors.destroy', $monitor->id) : route('monitors.destroy', $monitor->id) }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Delete') }}" data-text="{{ __('Deleting this monitor is permanent, and will remove all the data associated with it.') }}" data-sub-text="{{ __('Are you sure you want to delete :name?', ['name' => $monitor->name]) }}">@include('icons.delete', ['class' => 'fill-current width-4 height-4 '.(__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3')]) {{ __('Delete') }}</a>
    @endif
</div>
