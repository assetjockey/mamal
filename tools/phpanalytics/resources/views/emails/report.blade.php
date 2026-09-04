@component('mail::message')
{{-- Greeting --}}
# @lang('Hello!')

{{-- Intro Lines --}}
@isset($introLines)
@foreach ($introLines as $line)
{{ $line }}

@endforeach
@endisset

{{-- Stats --}}
@component('mail::table')
<table>
<tbody>
<tr>
<th style="text-align: {{ (__('lang_dir') == 'rtl' ? 'right' : 'left') }}; width: 40%;">{{ __('Website') }}</th>
<th style="text-align: {{ (__('lang_dir') == 'rtl' ? 'left' : 'right') }}; width: 30%;">{{ __('Visitors') }}</th>
<th style="text-align: {{ (__('lang_dir') == 'rtl' ? 'left' : 'right') }}; width: 30%;">{{ __('Pageviews') }}</th>
</tr>
@foreach($stats as $website)
<tr>
<td style="text-align: {{ (__('lang_dir') == 'rtl' ? 'right' : 'left') }}; width: 40%; color: #262626;"><a href="{{ route('stats.overview', ['id' => $website['domain'], 'from' => $range['from'], 'to' => $range['to']]) }}">{{ $website['domain'] }}</a></td>
<td style="text-align: {{ (__('lang_dir') == 'rtl' ? 'left' : 'right') }}; width: 30%; color: #3383ff;">{{ number_format($website['visitors'], 0, __('.'), __(',')) }}</td>
<td style="text-align: {{ (__('lang_dir') == 'rtl' ? 'left' : 'right') }}; width: 30%; color: #DC3545;">{{ number_format($website['pageviews'], 0, __('.'), __(',')) }}</td>
</tr>
@endforeach
<tr>
<td colspan="3">
{{ __('Report generated for :from - :to period.', ['from' => \Carbon\Carbon::createFromFormat('Y-m-d', $range['from'])->format(__('Y-m-d')), 'to' => \Carbon\Carbon::createFromFormat('Y-m-d', $range['to'])->format(__('Y-m-d'))]) }}
</td>
</tr>
</tbody>
</table>
@endcomponent

{{-- Salutation --}}
@lang('Regards'),

{{ config('settings.title') }}

{{-- Subcopy --}}
@slot('subcopy')
@lang('If you do not wish to receive these emails in the future, you can disable them from the Settings of your websites.')
@endslot
@endcomponent
