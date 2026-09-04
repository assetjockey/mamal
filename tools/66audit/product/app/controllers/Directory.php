<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;

use Altum\Meta;

defined('ALTUMCODE') || die();

class Directory extends Controller {

    public function index() {
        if(!settings()->audits->directory_is_enabled) {
            throw_404();
        }

        if(settings()->audits->directory_access == 'users') {
            \Altum\Authentication::guard();
        }

		$total_websites = \Altum\Cache::cache_function_result('directory_websites_total', null, function() {
			return db()->where('directory_is_enabled_and_verified', '1')->getValue('websites', 'count(*)');
		});

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], ['host'], ['host', 'score']));
        $filters->set_default_order_by('score', 'DESC');
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `websites` WHERE `websites`.`directory_is_enabled_and_verified` = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('top-websites?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the websites */
        $websites_result = database()->query("
            SELECT 
                `websites`.`website_id`,
                `websites`.`scheme`,
                `websites`.`host`,
                `websites`.`score`,
                `websites`.`settings`,
                `websites`.`last_datetime`,
				`websites`.`last_audit_datetime`,
				`websites`.`datetime`,
				`websites`.`last_datetime`,
				`websites`.`total_tests`
            FROM 
                `websites`
            LEFT JOIN 
                `users` ON `websites`.`user_id` = `users`.`user_id`
            WHERE 
                `websites`.`directory_is_enabled_and_verified` = 1
                {$filters->get_sql_where('websites')}
                {$filters->get_sql_order_by('websites')}
            {$paginator->get_sql_limit()}
        ");

        /* Iterate over the websites */
        $websites = [];

        while($row = $websites_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $websites[] = $row;
        }

        /* Export handler */
        process_export_csv($websites, ['scheme', 'host', 'score', 'last_audit_datetime'], sprintf(l('websites.title')));
        process_export_json($websites, ['scheme', 'host', 'score', 'last_audit_datetime'], sprintf(l('websites.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

		/* Canonical */
		Meta::set_canonical_url(url('top-websites') . '?' . http_build_query($filters->get + ['page' => $paginator->getCurrentPage()]));

        /* Prepare the view */
        $data = [
            'websites'          => $websites,
            'pagination'        => $pagination,
            'filters'           => $filters,
			'total_websites'    => $total_websites,
        ];

        $view = new \Altum\View('directory/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}


