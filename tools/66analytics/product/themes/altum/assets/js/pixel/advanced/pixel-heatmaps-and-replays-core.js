class AltumCodeHeatmapsAndReplaysCore {

    /* Create and initiate the class with the proper parameters */
    async initiate() {
        this.visitor_uuid = localStorage.getItem(get_dynamic_var('visitor_uuid'));
        this.visitor_session_uuid = localStorage.getItem(get_dynamic_var('visitor_session_uuid'));
        this.visitor_session_event_uuid = localStorage.getItem(get_dynamic_var('visitor_session_event_uuid'));

        /* Heatmap snapshot buffer, will be used if a heatmap for this page is detected */
        let heatmap_events = null;
        let heatmap_should_capture = false;
        let heatmap_id_active = null;
        let valid_heatmap_found = 0;

        /* Helpers to capture exactly one [meta, fullsnapshot] pair for heatmap */
        let heatmap_meta_event = null;
        let heatmap_snapshot_sent = false;

        /* Heatmap detection */
        if(pixel_heatmaps.length) {
            let device = get_device_type();
            let url_no_scheme = get_url_no_scheme();

            /* Iterate on all heatmaps and initiate them if needed */
            for(let heatmap of pixel_heatmaps) {

                /* Check if heatmap url matches the current url */
                if(heatmap.url == url_no_scheme || heatmap.url == 'www.'+url_no_scheme) {

                    /* If needed, snapshot the page and send the data */
                    if(!heatmap[`snapshot_id_${device}`]) {
                        heatmap_events = [];
                        heatmap_should_capture = true;
                        heatmap_id_active = heatmap.heatmap_id;

                        /* Reset helpers to ensure a single snapshot is sent */
                        heatmap_meta_event = null;
                        heatmap_snapshot_sent = false;
                    }

                    /* Initiate the events handlers for heatmaps */
                    valid_heatmap_found = heatmap.heatmap_id;

                    /* No need to continue the loop if found the heatmap */
                    break;
                }
            }
        }

        localStorage.setItem(get_dynamic_var('valid_heatmap_found'), valid_heatmap_found);

        /* Use ONE rrwebRecord for both replay and heatmap */
        if(pixel_track_sessions_replays || heatmap_should_capture) {
            /* Track if tab is active */
            let tab_is_active = !document.hidden;

            document.addEventListener('visibilitychange', () => { tab_is_active = !document.hidden; });
            window.addEventListener('focus', () => { tab_is_active = true; });
            window.addEventListener('blur', () => { tab_is_active = false; });

            let events = [];

            rrweb.record({
                sampling: {
                    mouseInteraction: {
                        MouseUp: true,
                        MouseDown: true,
                        Click: true,
                        Focus: true,
                        Blur: true,
                        DblClick: false,
                        ContextMenu: false,
                        TouchStart: false,
                        TouchEnd: false
                    },
                    mousemove: 200,
                    scroll: 200,
                    media: 200,
                },

                /* Convert all text inputs to *** for privacy reasons */
                maskAllInputs: true,

                /* Remove unnecessary parts of the page */
                slimDOMOptions: {
                    comment: true,
                    headFavicon: true,
                    headWhitespace: true,
                    headMetaDescKeywords: true,
                    headMetaSocial: true,
                    headMetaRobots: true,
                    headMetaHttpEquiv: true,
                    headMetaAuthorship: true,
                    headMetaVerification: true
                },
                emit: event => {
                    /* Handle heatmap FIRST and synchronously */
                    if(heatmap_should_capture && !heatmap_snapshot_sent) {
                        /* Remember the very first META event */
                        if(event.type === 4 && !heatmap_meta_event) {
                            heatmap_meta_event = event;
                        }

                        /* On the first FULL SNAPSHOT after META, send snapshot */
                        if(event.type === 2 && heatmap_meta_event) {
                            /* Send only the minimal [meta, fullsnapshot] pair */
                            send_data_fetch({
                                type: 'heatmap_snapshot',
                                heatmap_id: heatmap_id_active,
                                data: [heatmap_meta_event, event]
                            }).catch(() => { /* swallow to avoid breaking rrweb emit */ });

                            heatmap_snapshot_sent = true;
                            heatmap_should_capture = false; /* Only send once */
                            heatmap_events = null; /* free memory */
                        }
                    }

                    /* Always push to session replay if enabled */
                    if(pixel_track_sessions_replays) {
                        if(!tab_is_active) return;

                        /* Block selection-change spam: incremental event with source = 14 */
                        if(event.type === 3 && event.data && event.data.source === 14) {
                            return;
                        }

                        events.push(event);
                    }
                }
            });

            let send_sessions_replays = async (use_beacon = false) => {
                if(!tab_is_active) return;

                if(events.length) {
                    let request_data = {
                        visitor_uuid: this.visitor_uuid,
                        visitor_session_uuid: this.visitor_session_uuid,
                        visitor_session_event_uuid: this.visitor_session_event_uuid,
                        type: 'replays',
                        data: events
                    };

                    if(use_beacon) {
                        send_data_beacon(request_data)
                    } else {
                        await send_data_fetch(request_data);
                    }

                    events = [];
                }

            };

            setInterval(send_sessions_replays, 1000);

            /* Always flush session replay buffer on pagehide and beforeunload */
            const termination_event = 'onpagehide' in self ? 'pagehide' : 'unload';
            window.addEventListener(termination_event, () => { send_sessions_replays(true); }, {capture: true});
            window.addEventListener('beforeunload', () => { send_sessions_replays(true); });
        }
    }
}
