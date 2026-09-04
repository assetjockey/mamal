<div class="mx-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
    <div class="my-2 d-flex font-weight-bold font-size-base">
        @include('icons.' . ($incident->status == 'resolved' ? 'check-circle-filled' : ($incident->status == 'unresolved' ? 'error-filled' : 'offline-bolt-filled')), ['class' => 'width-4 height-4 fill-current mt-1 flex-shrink-0 ' . ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning')) . ' ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')])
        <span class="{{ ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning')) }}">{{ __(Str::ucfirst($incident->status)) }}</span>
    </div>
    @php
        Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
    @endphp
    <div>
        <span class="font-weight-bold">{{ __('Duration') }}</span>: <span>
            {{ $incident->started_at->diffForHumans($incident->ended_at ?? Carbon\Carbon::now(), ['syntax' => true, 'parts' => 3, 'join' => true]) }}
        </span>
    </div>
    @php
        Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
    @endphp

    <div>
        <span class="font-weight-bold">{{ __('Started at') }}</span>: <span>{{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
    </div>

    <div class="mb-2">
        <span class="font-weight-bold">{{ __('Ended at') }}</span>: <span class="{{ (!$incident->ended_at ? 'text-danger font-weight-bold' : '') }}">{{ ($incident->ended_at ? $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') : __('Ongoing')) }}</span>
    </div>
</div>
