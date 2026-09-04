<div class="mx-2">
    <div class="d-flex my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        @include('icons.status', ['class' => 'width-4 height-4 fill-current mt-1 flex-shrink-0 text-' . monitorStatusColor($monitor->status) . ' ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
        &#8203;

        <div>
            @if($monitor->status === 'maintenance')
                {{ __(':status ending on :date', ['status' => '<span class="font-weight-bold text-' . monitorStatusColor($monitor->status) . '"> ' . __(Str::ucfirst($monitor->status)) . '</span>', 'date' => $monitor->maintenance_end_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]) }}
            @else
                {!! __(':status for :duration', ['status' => '<span class="font-weight-bold text-' . monitorStatusColor($monitor->status) . '"> ' . __(Str::ucfirst($monitor->status)) . '</span>', 'duration' => Carbon\Carbon::now()->diffForHumans(($monitor->started_at ?: $monitor->created_at), ['syntax' => true, 'parts' => 2, 'join' => true])]) !!}
            @endif
        </div>
    </div>

    @if ($monitor->checked_at)
        <div class="mt-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            <div>
                <span class="font-weight-bold">{{ __('Checked at') }}:</span> {{ $monitor->checked_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}
            </div>
        </div>
    @endif

    <div class="{{ $monitor->checked_at ? 'mb-2' : 'my-2' }} {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <div>
            <span class="font-weight-bold">{{ __('Check interval') }}</span>: {{ Carbon\CarbonInterval::seconds($monitor->interval)->cascade()->forHumans() }}
        </div>
    </div

    @if ($monitor->maintenance_start_at !== null && Carbon\Carbon::now()->lt($monitor->maintenance_start_at))
        <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
            {!! __(':status is scheduled to start on :start_date and end on :end_date.', ['status' => '<span class="font-weight-bold text-info"> ' . __('Maintenance') . '</span>', 'start_date' => $monitor->maintenance_start_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s'), 'end_date' => $monitor->maintenance_end_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]) !!}
        </div>
    @endif
</div>
