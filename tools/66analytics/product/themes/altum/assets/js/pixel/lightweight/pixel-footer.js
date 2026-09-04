/* Start function */
let altumcodestart = () => {

    let this_script = document.querySelector(`script[src$="pixel/${pixel_key}"]`);

    /* Initiate the Events */
    let altumcodeevents = new AltumCodeEvents();

    /* Goals */
    if(typeof AltumCodeGoals === 'function') {
        let altumcode_goals = new AltumCodeGoals();
        altumcode_goals.initiate();

        /* Scroll goals */
        if(typeof AltumCodeGoalsScroll === 'function') {
            let altumcode_goals_scroll = new AltumCodeGoalsScroll(altumcode_goals);
            altumcode_goals_scroll.initiate();
        }
    }

    if (typeof track_outbound_links === 'function') {
        track_outbound_links();
    }

};

/* Make sure the page is fully loaded before initiating */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', altumcodestart);
} else {
    altumcodestart();
}


