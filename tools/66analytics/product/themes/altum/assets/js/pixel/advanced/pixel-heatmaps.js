class AltumCodeHeatmaps {

    /* Create and initiate the class with the proper parameters */
    async initiate() {
        this.visitor_uuid = localStorage.getItem(get_dynamic_var('visitor_uuid'));
        this.visitor_session_uuid = localStorage.getItem(get_dynamic_var('visitor_session_uuid'));
        this.visitor_session_event_uuid = localStorage.getItem(get_dynamic_var('visitor_session_event_uuid'));

        let valid_heatmap_found = parseInt(localStorage.getItem(get_dynamic_var('valid_heatmap_found')));

        if(valid_heatmap_found && valid_heatmap_found !== 0) {
            this.heatmap_initiate_event_handler_click(valid_heatmap_found);

            this.heatmap_initiate_event_handler_scroll(valid_heatmap_found);
        }
    }

    /* Mouse click event handler */
    heatmap_initiate_event_handler_click(heatmap_id = null) {
        /* Last click data for comparison */
        let click_data = '';

        /* Count of the clicks when the click is simply the same */
        let clicks_count = 1;

        /* Timer for the click so we dont spam the server */
        let timer = false;

        document.addEventListener('click', event => {

            /* Make sure the event was fired by the actual user and not programatically */
            if(!event.isTrusted) {
                return false;
            }

            /* Timeout depending on the element that has been clicked so that we can detect actual url changes clicks */
            let timeout = event.target.tagName == 'A' && !event.target.getAttribute('href').startsWith('#') ? 0 : 500;

            let page_x = event.pageX;
            let page_y = event.pageY;

            let viewport_w = window.innerWidth || document.documentElement.clientWidth || 1;
            let doc_h = Math.max(document.documentElement.scrollHeight || 0, document.body.scrollHeight || 0, 1);

            let scroll_x = window.pageXOffset || document.documentElement.scrollLeft || 0;
            let view_x = page_x - scroll_x;

            let data = {
                x_normalized: viewport_w ? (view_x / viewport_w) : 0,
                y_normalized: doc_h ? (page_y / doc_h) : 0
            };

            let new_click_data = JSON.stringify(data);

            /* Check if the click is the same with the previous */
            if(new_click_data == click_data) {
                clicks_count++;

                clearTimeout(timer);
            }

            click_data = new_click_data;

            timer = setTimeout(() => {

                /* Send data */
                send_data_beacon({
                    type: 'heatmap_snapshot_click',
                    x_normalized: data.x_normalized,
                    y_normalized: data.y_normalized,
                    count: clicks_count,
                    heatmap_id,
                });

                /* Reset clicks count after submission */
                clicks_count = 1;

            }, timeout);

        });
    }

    /* Mouse scroll event handler */
    heatmap_initiate_event_handler_scroll(heatmap_id = null) {
        /* Highest scroll percentage reached so far */
        let max_scroll_percentage = 0;

        /* Last sent value to avoid duplicates */
        let last_sent_scroll_percentage = null;

        /* Timer so we do not spam the server */
        let timer = false;

        let get_scroll_percentage = () => {
            let scroll_top = document.documentElement['scrollTop'] || document.body['scrollTop'] || 0;
            let scroll_height = document.documentElement['scrollHeight'] || document.body['scrollHeight'] || 0;
            let client_height = document.documentElement.clientHeight || 1;

            let denominator = (scroll_height - client_height);

            /* If there is no scrollable area, treat it as 0% */
            if(denominator <= 0) {
                return 0;
            }

            let percentage = parseInt((scroll_top / denominator) * 100);

            /* Clamp to 0..100 */
            if(percentage < 0) percentage = 0;
            else if(percentage > 100) percentage = 100;

            /* Group into 10% increments */
            let bucket = Math.floor(percentage / 10) * 10;

            /* Ensure 100 stays 100 */
            if(bucket > 100) bucket = 100;

            return bucket;
        };

        let send_scroll_max = () => {
            if(last_sent_scroll_percentage !== null && last_sent_scroll_percentage === max_scroll_percentage) {
                return;
            }

            last_sent_scroll_percentage = max_scroll_percentage;

            send_data_beacon({
                type: 'heatmap_snapshot_scroll',
                visitor_session_event_uuid: this.visitor_session_event_uuid,
                max_scroll: max_scroll_percentage,
                heatmap_id,
            });
        };

        /* Create the row even if the user never scrolls (delay reduces instant bounces) */
        setTimeout(() => {
            if(last_sent_scroll_percentage !== null) {
                return;
            }

            max_scroll_percentage = 0;
            send_scroll_max();
        }, 1500);

        document.addEventListener('scroll', event => {

            /* Make sure the event was fired by the actual user and not programatically */
            if(event && event.isTrusted === false) {
                return false;
            }

            let current_scroll_percentage = get_scroll_percentage();

            if(current_scroll_percentage > max_scroll_percentage) {
                max_scroll_percentage = current_scroll_percentage;

                clearTimeout(timer);
                timer = setTimeout(() => {
                    send_scroll_max();
                }, 500);
            }

        }, { passive: true });

        /* Flush on close / navigation */
        let flush = () => {
            clearTimeout(timer);
            send_scroll_max();
        };

        document.addEventListener('visibilitychange', () => {
            if(document.visibilityState === 'hidden') {
                flush();
            }
        });

        window.addEventListener('pagehide', flush);
    }
}
