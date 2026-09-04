@component('mail::message')
# {{ mailTemplates('Hello!', 'transfer files notification') }}
{!! str_replace('{website_name}', '<a href="'.route('home').'" target="_blank">'.$settings['website_name'].'</a>', 
mailTemplates('You just received some files sent to you via {website_name}', 'transfer files notification')) !!}
<div class="transfer-details">
@component('mail::table')
| {{ mailTemplates('Details', 'transfer files notification') }}      |
| -------------- |
| {{ mailTemplates('Sent by', 'transfer files notification') }} :  <strong>{{ $details['sender'] }}</strong>|
@if(!is_null($details['password']))
| {{ mailTemplates('Password', 'transfer files notification') }} : <strong>{{ $details['password'] }}</strong> |
@endif
| {{ mailTemplates('Total files', 'transfer files notification') }} : <strong>{{ $details['total_files'] }}</strong> |
| {{ mailTemplates('Total files size', 'transfer files notification') }} : <strong>{{ $details['total_size'] }}</strong> |
@if(!is_null($details['expiry_at']))
| {{ mailTemplates('Files available until', 'transfer files notification') }} : <strong>{{ $details['expiry_at'] }}</strong> |
@endif
@endcomponent
</div>
@if(!is_null($details['message']))
<div class="message-box">
    <i>{!! allowBr($details['message']) !!}</i>
</div>
@endif
<div class="transfer-btn">
@component('mail::button', ['url' => $details['transfer_link']])
    {{ mailTemplates('Download', 'transfer files notification') }}
@endcomponent
</div>
@component('mail::table')
| {{ mailTemplates('Transferred files', 'transfer files notification') }}     |
| -------------- |
@foreach ($details['files'] as $file)
| {{ $file->name }}|
@endforeach
@endcomponent


{!! str_replace('{website_name}', '<a href="'.route('home').'" target="_blank">'.$settings['website_name'].'</a>', 
mailTemplates('These files are sent through our online service, and you can learn more by visiting our website directly {website_name}, Our website does not bear responsibility for your downloading these files or the way you use them.', 'transfer files notification')) !!}


{{ mailTemplates('Regards', 'transfer files notification') }},<br>
{{ $settings['website_name'] }}

@slot('subcopy')
{{ mailTemplates("If you're having trouble clicking the button, just copy and paste the URL below into your web browser", 'transfer files notification') }}
<span class="break-all">: <a href="{{ $details['transfer_link'] }}">{{ $details['transfer_link'] }}</a></span>
@endslot
@endcomponent