@if(isset($report['results'][$name]))
    <div class="border-top">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="d-flex align-items-center">
                                @include('reports.partials.status')

                                <div class="text-truncate font-weight-medium">{{ __('DOCTYPE') }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8">
                            @if($report['results'][$name]['passed'])
                                <div>
                                    {{ __('The webpage has the DOCTYPE declaration tag set.') }}

                                    <div class="mt-1">
                                        <code>{{ $report['results'][$name]['value'] }}</code>
                                    </div>
                                </div>
                            @else
                                @if($report->user_id == 0)
                                    @include('reports.partials.limited')
                                @else
                                    @foreach($report['results'][$name]['errors'] as $error => $details)
                                        <div class="{{ (!$loop->first) ? 'mt-3' : ''}}">
                                            @if($error == 'missing')
                                                {{ __('The webpage does not have the DOCTYPE declaration tag set.') }}
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-auto">
                    <a href="#collapse{{ $name }}" class="text-secondary d-flex align-items-center" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapse{{ $name }}" data-tooltip="true" title="{{ __('More') }}">
                        @include('icons.info', ['class' => 'fill-current width-4 height-4'])&#8203;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="collapse{{ Auth::check() && Auth::user()->default_export_detailed ? ' d-print-block' : '' }}" id="collapse{{ $name }}">
        <div class="card-body pt-0">
            <div class="alert alert-secondary mb-0">
                {{ __('The DOCTYPE declaration tag defines which version of HTML the webpage is using, making compliant browsers render the webpage properly.') }}

                <hr>

                <div class="row">
                    <div class="col-12 col-md">
                        {{ __('Learn more') }}
                    </div>

                    <div class="col-12 col-md-auto">
                        <a href="https://www.w3.org/QA/2002/04/valid-dtd-list.html" class="alert-link font-weight-medium d-flex align-items-center" target="_blank" rel="nofollow noreferrer noopener">W3C @include('icons.external', ['class' => 'fill-current width-3 height-3 ' . (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1')])</a>
                    </div>

                    <div class="col-12 col-md-auto">
                        <a href="https://developer.mozilla.org/en-US/docs/Glossary/Doctype" class="alert-link font-weight-medium d-flex align-items-center" target="_blank" rel="nofollow noreferrer noopener">Mozilla @include('icons.external', ['class' => 'fill-current width-3 height-3 ' . (__('lang_dir') == 'rtl' ? 'mr-1' : 'ml-1')])</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
