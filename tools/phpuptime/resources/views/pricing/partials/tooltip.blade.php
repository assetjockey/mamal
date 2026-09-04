<div class="mx-2">
    <div class="my-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">{{ __('The number of webhook URLs that can be configured per monitor to receive alerts.') }}</div>
    <div class="mt-3 mb-2 {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">{{ __('Supported services:') }}</div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('teams') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['teams'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['teams'] }}
        </div>
    </div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('slack') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['slack'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['slack'] }}
        </div>
    </div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('discord') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['discord'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['discord'] }}
        </div>
    </div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('telegram') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['telegram'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['telegram'] }}
        </div>
    </div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('flock') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['flock'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['flock'] }}
        </div>
    </div>
    <div class="d-flex align-items-center my-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
        <img src="{{ asset('img/icons/brands/' . md5('webhook') . '.svg') }}" class="width-4 height-4 vertical-align-n0.5 mx-1" alt="{{ config('alert.channels')['webhook'] }}">
        <div class="{{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
            {{ config('alert.channels')['webhook'] }}
        </div>
    </div>
</div>