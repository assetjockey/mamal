class AltumCodeGoals {

    /* Create and initiate the class with the proper parameters */
    initiate() {

        /* Expose function to window */
        window[pixel_exposed_identifier] = {
            goal: (key) => {
                this.event_goal_conversion(key);
            }
        };

        let url_no_scheme = get_url_no_scheme();

        /* Iterate on all goals and initiate them if needed */
        for(let goal of pixel_goals) {

            /* Check if goal url matches the current url */
            if(goal.type == 'pageview' && this.goal_url_matches(goal.url, url_no_scheme)) {

                this.event_goal_conversion(goal.key);

            }

        }

    }

    event_goal_conversion(key, data = {}) {

        /* Iterate on all goals and initiate them if needed */
        for(let goal of pixel_goals) {

            /* Check if goal url matches the current url */
            if(goal.key == key) {

                let goal_conversion_data = {
                    type: 'goal_conversion',
                    url: window.location.href,
                    goal_key: goal.key
                };

                /* Add scroll percentage */
                if(data.scroll_percentage !== undefined) {
                    goal_conversion_data.scroll_percentage = data.scroll_percentage;
                }

                /* Send the goal completion */
                send_data_beacon(goal_conversion_data);

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
