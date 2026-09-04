@if(config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user')))
    @if(request()->cookie('announcement_' . (Auth::guest() ? 'guest' : 'user') . '_id') != config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user') . '_id'))
        <div class="d-flex flex-column {{ Auth::guest() ? '' : 'ms-lg-64' }}" id="announcement-banner">
            <div class="alert-{{ config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user') . '_type') }} z-1030">
                <div class="{{ Auth::guest() ? 'container' : 'container-fluid container-lg' }}">
                    <div class="alert alert-{{ match (config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user') . '_type')) { 'primary' => 'alert-primary', 'secondary' => 'alert-secondary', 'success' => 'alert-success', 'danger' => 'alert-danger', 'warning' => 'alert-warning', 'info' => 'alert-info', 'inverse' => 'alert-inverse' } }} alert-dismissible fade show mb-0 mx-n4 text-break">
                        {!! config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user') . '_content') !!}

                        <button type="button" class="close d-flex align-items-center justify-content-center w-12 h-12 p-0" data-dismiss="alert" aria-label="{{ __('Close') }}" id="announcement-banner-dismiss">
                            <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current w-4 h-4'])</span>
                        </button>
                    </div>
                </div>
            </div>

            <script>
                'use strict';

                window.addEventListener('DOMContentLoaded', function () {
                    document.querySelector('#announcement-banner-dismiss').addEventListener('click', function () {
                        setCookie('announcement_{{ (Auth::guest() ? 'guest' : 'user') }}_id', '{{ config('settings.announcement_' . (Auth::guest() ? 'guest' : 'user') . '_id') }}', new Date().getTime() + (10 * 365 * 24 * 60 * 60 * 1000), '/');
                        document.querySelector('#announcement-banner').classList.add('d-none');
                    });
                });
            </script>
        </div>
    @endif
@endif
