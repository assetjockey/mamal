class AltumCodeGoalsScroll {

    /* Create and initiate the class with the proper parameters */
    constructor(altumcode_goals) {
        this.altumcode_goals = altumcode_goals;
    }

    async initiate() {
        let url_no_scheme = get_url_no_scheme();

        /* Iterate on all goals and initiate them if needed */
        for(let goal of pixel_goals) {

            /* Initiate scroll goals */
            if(goal.type == 'scroll' && this.altumcode_goals.goal_url_matches(goal.url, url_no_scheme)) {
                this.initiate_scroll_goal(goal);

            }

        }
    }

    initiate_scroll_goal(goal) {
        let scroll_percentage = parseInt(goal.scroll_percentage);

        /* Make sure the scroll percentage is valid */
        if(!scroll_percentage || scroll_percentage < 1 || scroll_percentage > 100) {
            return;
        }

        if(localStorage.getItem(get_dynamic_var(`visitor_goal_${goal.key}`))) {
            return;
        }

        let is_sent = false;

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

            return percentage;
        };

        let check_scroll_goal = event => {
            /* Make sure the event was fired by the actual user */
            if(event && event.isTrusted === false) {
                return false;
            }

            if(is_sent) {
                return;
            }

            let current_scroll_percentage = get_scroll_percentage();

            if(current_scroll_percentage >= scroll_percentage) {
                is_sent = true;
                document.removeEventListener('scroll', check_scroll_goal);

                this.altumcode_goals.event_goal_conversion(goal.key, {
                    scroll_percentage: current_scroll_percentage
                });
            }
        };

        document.addEventListener('scroll', check_scroll_goal, { passive: true });

        check_scroll_goal();
    }

}
