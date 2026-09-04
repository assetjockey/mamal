class AltumCodeEventsChildren {

    /* Create and initiate the class with the proper parameters */
    async initiate() {
        this.visitor_uuid = localStorage.getItem(get_dynamic_var('visitor_uuid'));
        this.visitor_session_uuid = localStorage.getItem(get_dynamic_var('visitor_session_uuid'));
        this.visitor_session_event_uuid = localStorage.getItem(get_dynamic_var('visitor_session_event_uuid'));

        this.initiate_event_handler_click();

        this.initiate_event_handler_scroll();

        this.initiate_event_handler_forms();

        this.initiate_event_handler_resize();
    }

    /* Mouse click event handler */
    initiate_event_handler_click() {
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

            /* Get the text of the area that the user clicked */
            let text = (typeof event.target.textContent === 'string' && event.target.textContent.length > 61)
                ? `${event.target.textContent.substr(0, 61)}...`
                : event.target.textContent || '';

            let page_x = event.pageX;
            let page_y = event.pageY;

            let data = {
                mouse: {
                    x: page_x,
                    y: page_y,
                },

                text: text,
                element: event.target.tagName.toLowerCase()
            };

            let new_click_data = JSON.stringify(data);

            /* Check if the click is the same with the previous */
            if(new_click_data == click_data) {
                clicks_count++;

                clearTimeout(timer);
            }

            click_data = new_click_data;

            timer = setTimeout(() => {

                this.send_event_child('click', data, clicks_count);

                clicks_count = 1;

            }, timeout);

        });
    }

    /* Scroll event handler */
    initiate_event_handler_scroll() {

        /* Timer for the scroll so we dont spam the server */
        let timer = false;

        document.addEventListener('scroll', event => {

            /* Make sure the event was fired by the actual user and not programatically */
            if(!event.isTrusted) {
                return false;
            }

            let data = {
                scroll: {
                    percentage: parseInt((document.documentElement['scrollTop'] || document.body['scrollTop']) / ((document.documentElement['scrollHeight'] || document.body['scrollHeight']) - document.documentElement.clientHeight) * 100)

                    /* Do not store the top value, store the percentage of scrolling instead */
                    /* top: window.pageYOffset || document.documentElement.scrollTop, */

                    /* Most websites do not have a horizontal scroll */
                    /* left: window.pageXOffset || document.documentElement.scrollLeft */
                }
            };


            clearTimeout(timer);

            timer = setTimeout(() => {

                this.send_event_child('scroll', data, 1);

            }, 500);

        }, { passive: true });
    }

    /* Inputs event handler */
    initiate_event_handler_forms() {

        let event_handler_form = event => {

            /* Store data from the form */
            let data = {
                form: {

                }
            };

            /* INPUT VALUES ARE NOT STORED ANYMORE FOR PRIVACY REASONS */
            /* let form_element = event.target; */
            /*
            form_element.querySelectorAll('input').forEach(input_element => {
                if(input_element.type == 'password' || input_element.type == 'hidden') {
                    return;
                }
                if(input_element.name.indexOf('captcha') !== -1) {
                    return;
                }
                data.form[input_element.name] = input_element.value;
            });
            */

            /* Submit the event */
            this.send_event_child('form', data);

        };

        /* Make sure to know all of the form submissions on the page */
        document.querySelectorAll('form').forEach(form_element => {

            form_element.addEventListener('submit', event_handler_form);

        });

    }

    /* Window resize event handler */
    initiate_event_handler_resize() {

        /* Timer for the scroll so we dont spam the server */
        let timer = false;

        window.addEventListener('resize', event => {

            /* Make sure the event was fired by the actual user and not programatically */
            if(!event.isTrusted) {
                return false;
            }

            let data = {
                viewport: {
                    width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
                    height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
                }
            };


            clearTimeout(timer);

            timer = setTimeout(() => {

                this.send_event_child('resize', data);

            }, 500);

        });

    }

    send_event_child(type, data = {}, count = 1) {

        send_data_beacon({
            visitor_uuid: this.visitor_uuid,
            visitor_session_uuid: this.visitor_session_uuid,
            visitor_session_event_uuid: this.visitor_session_event_uuid,
            type,
            data,
            count,
        });

    }

}
