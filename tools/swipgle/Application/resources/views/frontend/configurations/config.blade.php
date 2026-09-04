<script>
    "use strict";
    const config = {
        lang: "{{ getLang() }}",
        baseURL: "{{ url('/') }}",
        countryCode: "{{ vIpInfo()->country_code == 'Unknown' ? 'US' : vIpInfo()->country_code }}",
        primaryColor: "{{ $settings['website_secondary_color'] }}",
        secondaryColor: "{{ $settings['website_primary_color'] }}",
        alertActionTitle: "{{ lang('Are you sure?', 'user') }}",
        alertActionText: "{{ lang('Confirm that you want do this action', 'user') }}",
        alertActionConfirmButton: "{{ lang('Confirm', 'user') }}",
        alertActionCancelButton: "{{ lang('Cancel', 'user') }}",
        ticketsMaxFilesError: "{{ lang('Max 5 files can be uploaded', 'tickets') }}",
        copiedToClipboardSuccess: "{{ lang('Copied to clipboard', 'user') }}",
        LoadingOverlayColor: "{{ $settings['website_secondary_color'] }}",
    };
</script>
@stack('config')
<script>
    "use strict";
    let configObjects = JSON.stringify(config),
        getConfig = JSON.parse(configObjects);
</script>
