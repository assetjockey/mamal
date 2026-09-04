class AltumCodeGoals {

    /* Create and initiate the class with the proper parameters */
    constructor(altumcode_events) {
        this.altumcode_events = altumcode_events;
    }

    async initiate() {

        /* Expose function to window */
        window[pixel_exposed_identifier] = {

            goal: async (key) => {
                await this.event_goal_conversion(key);
            }

        };

        let url_no_scheme = get_url_no_scheme();

        /* Iterate on all goals and initiate them if needed */
        for(let goal of pixel_goals) {

            /* Check if goal url matches the current url */
            if(goal.type == 'pageview' && this.goal_url_matches(goal.url, url_no_scheme)) {
                await this.event_goal_conversion(goal.key);

            }

        }
    }

    async event_goal_conversion(key, data = {}) {

        /* Iterate on all goals and initiate them if needed */
        for(let goal of pixel_goals) {

            /* Check if goal url matches the current url */
            if(goal.key == key && !localStorage.getItem(get_dynamic_var(`visitor_goal_${goal.key}`))) {

                let goal_conversion_data = {
                    visitor_uuid: this.altumcode_events.visitor_uuid,
                    visitor_session_uuid: this.altumcode_events.visitor_session_uuid,
                    visitor_session_event_uuid: this.altumcode_events.visitor_session_event_uuid,
                    type: 'goal_conversion',
                    goal_key: goal.key
                };

                /* Add scroll percentage */
                if(data.scroll_percentage !== undefined) {
                    goal_conversion_data.scroll_percentage = data.scroll_percentage;
                }

                /* Send the goal completion */
                await send_data_fetch(goal_conversion_data);

                /* Set it in the local storage to make sure to not send it again */
                localStorage.setItem(get_dynamic_var(`visitor_goal_${goal.key}`), true);

                break;
            }

        }
    }

    goal_url_matches(goal_url, url) {
        if(!goal_url) {
            return false;
        }

        let regex_safe_goal_url = goal_url.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');
        let goal_url_regex = new RegExp(`^${regex_safe_goal_url}$`);

        return goal_url_regex.test(url) || goal_url_regex.test(`www.${url}`);
    }

}
