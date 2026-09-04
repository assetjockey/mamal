@section('site_title', formatTitle([$website->domain, __('Events'), config('settings.title')]))

<div class="d-flex flex-column">
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <div class="row">
                <div class="col-12 col-md"><div class="font-weight-medium py-1">{{ __('Events') }}</div></div>
                <div class="col-12 col-md-auto">
                    <div class="form-row">
                        @include('stats.filters', ['name' => __('Name'), 'count' => __('Completions')])
                        @if(Auth::check() && $website->user_id == Auth::user()->id)
                            <div class="col-auto">
                                <a href="#" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-toggle="modal" data-target="#event-code-modal" data-tooltip="true" title="{{ __('Event code') }}">@include('icons.brackets', ['class' => 'fill-current width-4 height-4'])&#8203;</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(count($events) == 0)
                {{ __('No data') }}.
            @else
                <div class="list-group list-group-flush my-n3">
                    <div class="list-group-item px-0 text-muted">
                        <div class="row align-items-center">
                            <div class="col">
                                {{ __('Name') }}
                            </div>
                            <div class="col-auto">
                                {{ __('Completions') }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0 small text-muted">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex text-truncate align-items-center">
                                    <div class="text-truncate">
                                        {{ __('Total') }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                    <span>{{ number_format($total->count, 0, __('.'), __(',')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($events as $event)
                        <div class="list-group-item px-0">
                            <div class="d-flex flex-column">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex text-truncate align-items-center">
                                        <div class="text-truncate">
                                            {{ explode(':', $event->value)[0] }}
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-baseline {{ (__('lang_dir') == 'rtl' ? 'mr-3 text-left' : 'ml-3 text-right') }}">
                                        <div class="d-flex align-items-center justify-content-end {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                                            @if(!empty(explode(':', $event->value)[1]) || !empty(explode(':', $event->value)[2]))
                                                <span class="badge badge-secondary {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                    @if(!empty(explode(':', $event->value)[1]))
                                                        {{ number_format((explode(':', $event->value)[1] * $event->count), 2, __('.'), __(',')) }}
                                                    @endif

                                                    @if(!empty(explode(':', $event->value)[2]))
                                                        {{ explode(':', $event->value)[2] }}
                                                    @endif
                                                </span>
                                            @endif

                                            {{ number_format($event->count, 0, __('.'), __(',')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3 align-items-center">
                        <div class="row">
                            <div class="col">
                                <div class="mt-2 mb-3">{{ __('Showing :from-:to of :total', ['from' => $events->firstItem(), 'to' => $events->lastItem(), 'total' => $events->total()]) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $events->onEachSide(1)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="event-code-modal" tabindex="-1" role="dialog" aria-labelledby="event-code-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title" id="event-code-modal-label">{{ __('Event code') }}</h6>
                <button type="button" class="close d-flex align-items-center justify-content-center width-12 height-14" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current width-4 height-4'])</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {!! __('Events can be tracked using the :function JavaScript function.', ['function' => '<code>pa.track()</code>']) !!}
                </p>

                <p>
                    {!! __('The :function function accepts an object as the argument.', ['function' => '<code>pa.track()</code>']) !!}
                    {!! __('The object must contain a key named :name.', ['name' => '<code>name</code>']) !!}
                </p>

                <p>
                    {!! __('Additionally, :value and :unit keys can be included to be tracked.', ['value' => '<code>value</code>', 'unit' => '<code>unit</code>']) !!}
                </p>

<pre class="bg-dark text-light p-3 mb-0 rounded text-left mb-3">
pa.track({name: '{{ __('Subscription') }}'})
</pre>

<pre class="bg-dark text-light p-3 mb-0 rounded text-left">
pa.track({name: '{{ __('Payment') }}', value: 5, unit: 'USD'})
</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
