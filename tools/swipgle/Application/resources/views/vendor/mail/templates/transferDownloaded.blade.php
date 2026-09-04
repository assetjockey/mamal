@component('mail::message')
# {{ mailTemplates('Hello!', 'transferred files downloaded notification') }}
{!! str_replace('{website_name}', '<a href="'.route('home').'" target="_blank">'.$settings['website_name'].'</a>', 
mailTemplates('Your transferred files on {website_name} has been downloaded', 'transferred files downloaded notification')) !!}
<div class="transfer-details">
@component('mail::table')
| {{ mailTemplates('Details', 'transferred files downloaded notification') }}      |
| -------------- |
| {{ mailTemplates('Transfer number', 'transferred files downloaded notification') }} :  <strong>{{ $details['transfer_number'] }}</strong>|
@if(!is_null($details['transfer_subject']))
| {{ mailTemplates('Subject', 'transferred files downloaded notification') }} :  <strong>{{ $details['transfer_subject'] }}</strong>|
@endif
| {{ mailTemplates('Sent by', 'transferred files downloaded notification') }} :  <strong>{{ $details['sender'] }}</strong>|
| {{ mailTemplates('Total files', 'transferred files downloaded notification') }} : <strong>{{ $details['total_files'] }}</strong> |
| {{ mailTemplates('Total files size', 'transferred files downloaded notification') }} : <strong>{{ $details['total_size'] }}</strong> |
| {{ mailTemplates('Files downloaded at', 'transferred files downloaded notification') }} : <strong>{{ $details['downloaded_at'] }}</strong> |
@if(!is_null($details['expiry_at']))
| {{ mailTemplates('Files available until', 'transferred files downloaded notification') }} : <strong>{{ $details['expiry_at'] }}</strong> |
@endif
@endcomponent
</div>
@if(!is_null($details['transfer_link']))
<div class="transfer-btn">
    @component('mail::button', ['url' => $details['transfer_link']])
        {{ mailTemplates('View Transfer', 'transferred files downloaded notification') }}
    @endcomponent
</div>
@endif
@component('mail::table')
| {{ mailTemplates('Transferred files', 'transferred files downloaded notification') }}     |
| -------------- |
@foreach ($details['files'] as $file)
| {{ $file->name }}|
@endforeach
@endcomponent
{{ mailTemplates('Regards', 'transferred files downloaded notification') }},<br>
{{ $settings['website_name'] }}
@endcomponent