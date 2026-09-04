@component('mail::message')
# {{ mailTemplates('Hello!', 'transfer expired notification') }}
{!! str_replace('{website_name}', '<a href="'.route('home').'" target="_blank">'.$settings['website_name'].'</a>', 
mailTemplates('Your transfer on {website_name} has been expired', 'transfer expired notification')) !!}
<div class="transfer-details">
@component('mail::table')
| {{ mailTemplates('Details', 'transfer expired notification') }}      |
| -------------- |
| {{ mailTemplates('Transfer number', 'transfer expired notification') }} :  <strong>{{ $details['transfer_number'] }}</strong>|
@if(!is_null($details['transfer_subject']))
| {{ mailTemplates('Subject', 'transfer expired notification') }} :  <strong>{{ $details['transfer_subject'] }}</strong>|
@endif
| {{ mailTemplates('Sent by', 'transfer expired notification') }} :  <strong>{{ $details['sender'] }}</strong>|
| {{ mailTemplates('Total files', 'transfer expired notification') }} : <strong>{{ $details['total_files'] }}</strong> |
| {{ mailTemplates('Total files size', 'transfer expired notification') }} : <strong>{{ $details['total_size'] }}</strong> |
@if(!is_null($details['downloaded_at']))
| {{ mailTemplates('Files downloaded at', 'transfer expired notification') }} : <strong>{{ $details['downloaded_at'] }}</strong> |
@endif
@endcomponent
</div>
@if(!is_null($details['transfer_link']))
<div class="transfer-btn">
    @component('mail::button', ['url' => $details['transfer_link'], 'color' => 'error'])
        {{ mailTemplates('View Transfer', 'transfer expired notification') }}
    @endcomponent
</div>
@endif
@component('mail::table')
| {{ mailTemplates('Transferred files', 'transfer expired notification') }}     |
| -------------- |
@foreach ($details['files'] as $file)
| {{ $file->name }}|
@endforeach
@endcomponent

{{ mailTemplates('Regards', 'transfer expired notification') }},<br>
{{ $settings['website_name'] }}

@endcomponent