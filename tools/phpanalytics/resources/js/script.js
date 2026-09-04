! function(w) {
    'use strict';

    /**
     * Send the request
     * @param event
     * @param referrer Needed for SPAs dynamic history push
     */
    function sendRequest(event, referrer) {
        // Tracking code element
        var trackingCode = document.getElementById('ZwSg9rf6GA');

        if (trackingCode.getAttribute('data-dnt') === 'true') {
            // If the user's has DNT enabled
            if (navigator.doNotTrack) {
                // Cancel the request
                return false;
            }
        }

        // Request parameters
        var params = {};

        // If a referrer is set
        if (referrer) {
            params.referrer = referrer;
        } else {
            // Get the referrer
            params.referrer = w.document.referrer;
        }

        // Get the current page
        params.page = w.location.href.replace(/#.+$/,'');

        // Get the screen resolution
        params.screen_resolution = screen.width + 'x' + screen.height;

        if (event) {
            params.event = event;
        }

        // Send the request
        var request = new XMLHttpRequest();
        request.open("POST", trackingCode.getAttribute('data-host') + "/api/event", true),
        request.setRequestHeader("Content-Type", "application/json; charset=utf-8"),
        request.send(JSON.stringify(params));
    }

    try {
        // Rewrite the push state function to detect path changes in SPAs
        var pushState = history.pushState;
        history.pushState = function () {
            var referrer = w.location.href.replace(/#.+$/,'');
            pushState.apply(history, arguments);
            sendRequest(null, referrer);
        };

        // Listen to the browser's back & forward buttons
        w.onpopstate = function(event) {
            sendRequest(null);
        };

        // Define the event method
        w.pa = {}; w.pa.track = sendRequest;

        // Send the initial request
        sendRequest(null);
    } catch (e) {
        console.log(e.message);
    }
}(window);
