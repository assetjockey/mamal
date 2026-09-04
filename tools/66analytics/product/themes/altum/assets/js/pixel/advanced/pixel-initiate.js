/* Start function */
let altumcodestart = async () => {

    let this_script = document.querySelector(`script[src$="pixel/${pixel_key}"]`);

    /* Check for manual opt out */
    let optout = is_optout();

    /* Check if we should supress the DNT or not */
    let should_track = !optout;

    if(should_track) {
        /* Initiate the Visitor */
        let altumcode_visitor = new AltumCodeVisitor();

        await altumcode_visitor.initiate();

        /* Initiate the Events */
        let altumcode_events = new AltumCodeEvents();

        await altumcode_events.initiate();

        /* Goals */
        if(typeof AltumCodeGoals === 'function') {
            let altumcode_goals = new AltumCodeGoals(altumcode_events);
            await altumcode_goals.initiate();

            /* Scroll goals */
            if(typeof AltumCodeGoalsScroll === 'function') {
                let altumcode_goals_scroll = new AltumCodeGoalsScroll(altumcode_goals);
                await altumcode_goals_scroll.initiate();
            }
        }

        /* Initiate events children tracking if needed */
        if(pixel_track_events_children) {1
            let altumcode_events_children = new AltumCodeEventsChildren();

            await altumcode_events_children.initiate();
        }

        /* Initiate heatmaps or replays if needed */
        if(pixel_track_sessions_replays || pixel_heatmaps.length) {
            let altumcode_heatmaps_and_replays_core = new AltumCodeHeatmapsAndReplaysCore();
            await altumcode_heatmaps_and_replays_core.initiate();
        }

        /* Tracking for heatmaps */
        if(pixel_heatmaps.length) {
            let altumcode_heatmaps = new AltumCodeHeatmaps();

            await altumcode_heatmaps.initiate();
        }

        /* Outbound clicks */
        if (typeof track_outbound_links === 'function') {
            track_outbound_links({
                visitor_uuid: altumcode_events.visitor_uuid,
                visitor_session_uuid: altumcode_events.visitor_session_uuid,
                visitor_session_event_uuid: altumcode_events.visitor_session_event_uuid,
            });
        }

    } else {

        if(optout) {
            console.log(`${pixel_url_base}: ${pixel_key_optout_message}`);
        }
    }
};

/* Make sure the page is fully loaded before initiating */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', altumcodestart);
} else {
    altumcodestart();
}

