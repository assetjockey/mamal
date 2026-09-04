/* Visitor class */
class AltumCodeVisitor {

    /* Create and initiate the class with the proper parameters */
    async initiate() {

        /* Check if we already have the visitor id */
        if(localStorage.getItem(get_dynamic_var('visitor_uuid')) && localStorage.getItem(get_dynamic_var('visitor_uuid')).trim() != '') {
            this.visitor_uuid = localStorage.getItem(get_dynamic_var('visitor_uuid')).trim();

            let custom_parameters = this.get_custom_parameters();

            /* If custom parameters are set after the initiation of the visitor, update the visitor with the new parameters */
            if(
                custom_parameters && (
                    !localStorage.getItem(get_dynamic_var('visitor_custom_parameters')) ||
                    (localStorage.getItem(get_dynamic_var('visitor_custom_parameters')) && localStorage.getItem(get_dynamic_var('visitor_custom_parameters')) != btoa(JSON.stringify(custom_parameters)))
                )
            ) {
                /* Generate the details for the visitor */
                let details = this.get_extra_details();

                /* Send the details */
                await send_data_fetch({
                    visitor_uuid: this.visitor_uuid,
                    type: 'initiate_visitor',
                    data: details
                });
            }

        } else {

            /* Generate it randomly */
            let visitor_uuid = get_random_id();

            this.visitor_uuid = visitor_uuid;

            /* Save it to localstorage */
            localStorage.setItem(get_dynamic_var('visitor_uuid'), this.visitor_uuid);

            /* Generate the details for the visitor */
            let details = this.get_extra_details();

            /* Send the details */
            await send_data_fetch({
                visitor_uuid,
                type: 'initiate_visitor',
                data: details
            });
        }

    }

    get_extra_details() {

        let data = {
            resolution: {
                width: window.screen.width,
                height: window.screen.height
            },

            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            theme: window?.matchMedia?.('(prefers-color-scheme:dark)')?.matches ? 'dark' : 'light'

            /* Extra detection based on the browser is made directly on the server side */
        };

        let custom_parameters = this.get_custom_parameters();

        if(custom_parameters) {
            data.custom_parameters = custom_parameters;

            /* Save it to localstorage */
            localStorage.setItem(get_dynamic_var('visitor_custom_parameters'), btoa(JSON.stringify(custom_parameters)));
        }

        return data;
    }

    get_custom_parameters() {
        /* Check for extra parameters */
        let this_script = document.querySelector(`script[src$="pixel/${pixel_key}"]`);

        if(this_script.dataset.customParameters) {

            try {
                let custom_parameters = JSON.parse(this_script.dataset.customParameters);

                return custom_parameters;

            } catch(error) {
                return false;
            }

        } else {

            return false;

        }
    }

}


/* Main session class */
class AltumCodeEvents {

    /* Create and initiate the class with the proper parameters */
    async initiate() {

        this.visitor_uuid = localStorage.getItem(get_dynamic_var('visitor_uuid'));
        this.visitor_session_uuid = localStorage.getItem(get_dynamic_var('visitor_session_uuid'));

        /* Store UUID of the last event for page loads */
        this.visitor_session_event_uuid = get_random_id();
        localStorage.setItem(get_dynamic_var('visitor_session_event_uuid'), this.visitor_session_event_uuid);

        /* Get the session date if existing to detect the current session */
        let visitor_session_date = localStorage.getItem(get_dynamic_var('visitor_session_date'));
        let date = new Date();

        /* Check if the current page is the first for this session */
        if(!visitor_session_date || (visitor_session_date && date - (new Date(visitor_session_date)) > 30*60*1000)) {

            /* Generate the session uuid */
            this.visitor_session_uuid = get_random_id();
            localStorage.setItem(get_dynamic_var('visitor_session_uuid'), this.visitor_session_uuid);

            /* Emit the landing page event */
            await this.event_landing_page();

        } else {

            /* Emit the pageview event */
            await this.event_pageview()

        }

        /* Set the new session date */
        localStorage.setItem(get_dynamic_var('visitor_session_date'), date.toJSON());
    }

    async event_landing_page() {
        let query_parameters = new URL(document.location.toString()).searchParams.toString();

        let data = {
            path: window.location.pathname + (pixel_query_parameters_tracking_is_enabled && query_parameters.length ? '?' + query_parameters : ''),
            title: document.title,
            referrer: document.referrer.includes(`${location.protocol}//${location.host}${location.pathname}`) ? null : document.referrer,

            utm: this.get_utm_params(),

            viewport: this.get_viewport()
        };

        await this.send_data({
            visitor_uuid: this.visitor_uuid,
            visitor_session_uuid: this.visitor_session_uuid,
            visitor_session_event_uuid: this.visitor_session_event_uuid,
            type: 'landing_page',
            data
        });

    }

    async event_pageview() {
        let query_parameters = new URL(document.location.toString()).searchParams.toString();

        let data = {
            path: window.location.pathname + (pixel_query_parameters_tracking_is_enabled && query_parameters.length ? '?' + query_parameters : ''),
            title: document.title,
            referrer: document.referrer.includes(`${location.protocol}//${location.host}${location.pathname}`) ? null : document.referrer,

            utm: this.get_utm_params(),

            viewport: this.get_viewport()
        };

        await this.send_data({
            visitor_uuid: this.visitor_uuid,
            visitor_session_uuid: this.visitor_session_uuid,
            visitor_session_event_uuid: this.visitor_session_event_uuid,
            type: 'pageview',
            data
        });

    }

    async send_data(data) {

        let response = await send_data_fetch(data);

        /* Handle potential needs to refresh the visitor or session */
        if(response && response.details && response.details.refresh) {

            switch(response.details.refresh) {
                case 'visitor':

                    localStorage.removeItem(get_dynamic_var('visitor_uuid'));

                    /* reInitiate the Visitor */
                    let altumcode_visitor = new AltumCodeVisitor();

                    await altumcode_visitor.initiate();

                    break;

                case 'session':

                    localStorage.removeItem(get_dynamic_var('visitor_session_uuid'));
                    localStorage.removeItem(get_dynamic_var('visitor_session_date'));

                    /* reInitiate the Events */
                    let altumcode_events = new AltumCodeEvents();

                    await altumcode_events.initiate();

                    break;
            }

        }

    }

    get_viewport() {
        return {
            width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
            height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
        }
    }

    get_utm_params() {
        let url_params = new URLSearchParams(window.location.search);

        return {
            source: url_params.get('utm_source'),
            medium: url_params.get('utm_medium'),
            campaign: url_params.get('utm_campaign'),
        };
    }

}
