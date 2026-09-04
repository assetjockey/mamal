@component('mail::message')
# {{ mailTemplates('Hello!', 'transfer cancellation notification') }}
{!! str_replace('{website_name}', '<a href="'.route('home').'" target="_blank">'.$settings['website_name'].'</a>', 
mailTemplates('Your transfer on {website_name} has been canceled', 'transfer cancellation notification')) !!}
<div class="transfer-details">
@component('mail::table')
| {{ mailTemplates('Details', 'transfer cancellation notification') }}      |
| -------------- |
| {{ mailTemplates('Transfer number', 'transfer cancellation notification') }} :  <strong>{{ $details['transfer_number'] }}</strong>|
@if(!is_null($details['transfer_subject']))
| {{ mailTemplates('Subject', 'transfer cancellation notification') }} :  <strong>{{ $details['transfer_subject'] }}</strong>|
@endif
| {{ mailTemplates('Sent by', 'transfer cancellation notification') }} :  <strong>{{ $details['sender'] }}</strong>|
| {{ mailTemplates('Total files', 'transfer cancellation notification') }} : <strong>{{ $details['total_files'] }}</strong> |
| {{ mailTemplates('Total files size', 'transfer cancellation notification') }} : <strong>{{ $details['total_size'] }}</strong> |
@endcomponent
</div>
<div class="message-box error">
    <i><strong>{{ mailTemplates('Reason for cancellation', 'transfer cancellation notification') }} : </strong>{{ $details['cancellation_reason'] }}</i>
</div>
@if(!is_null($details['transfer_link']))
<div class="transfer-btn">
    @component('mail::button', ['url' => $details['transfer_link'], 'color' => 'error'])
        {{ mailTemplates('View Transfer', 'transfer cancellation notification') }}
    @endcomponent
</div>
@endif
@component('mail::table')
| {{ mailTemplates('Transferred files', 'transfer cancellation notification') }}     |
| -------------- |
@foreach ($details['files'] as $file)
| {{ $file->name }}|
@endforeach
@endcomponent
{!! str_replace('{transfer_number}', '[#'.$details['transfer_number'].']', mailTemplates('To know more about the reason for canceling your transfer, please contact us with your transfer number {transfer_number}.', 'transfer cancellation notification')) !!}
{{ mailTemplates('Regards', 'transfer cancellation notification') }},<br>
{{ $settings['website_name'] }}
@endcomponent