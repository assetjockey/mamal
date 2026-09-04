@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
# @lang('Hello!')
@endif

{{-- Intro Lines --}}
@isset($introLines)
@foreach ($introLines as $line)
{{ $line }}

@endforeach
@endisset

{{-- Action Button --}}
@isset($actionText)
@component('mail::button', ['url' => $actionUrl, 'color' => $color ?? null])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@isset($outroLines)
@foreach ($outroLines as $line)
{{ $line }}

@endforeach
@endisset

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Regards'),

{{ config('settings.title') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
"If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser: [:actionURL](:actionURL)",
[
'actionText' => $actionText,
'actionURL' => $actionUrl,
]
)


@lang('If you do not wish to receive these emails in the future, they can be disabled from the Settings of the monitor.')
@endslot
@endisset
@endcomponent
