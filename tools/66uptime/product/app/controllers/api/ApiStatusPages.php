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

use Altum\Response;
use Altum\Traits\Apiable;

defined('ALTUMCODE') || die();

class ApiStatusPages extends Controller {
    use Apiable;

    public function index() {

        if(!settings()->status_pages->status_pages_is_enabled) {
            throw_404();
        }

        $this->verify_request();

        /* Decide what to continue with */
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                /* Detect if we only need an object, or the whole list */
                if(isset($this->params[0])) {
                    $this->get();
                } else {
                    $this->get_all();
                }

            break;

            case 'DELETE':
                $this->delete();
                break;
        }

        $this->return_404();
    }

    private function get_all() {

        /* Get available custom domains */
        $domains = (new \Altum\Models\Domain())->get_available_domains_by_user($this->user, false);

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_enabled', 'project_id', 'domain_id', 'pixels_ids'], ['name'], ['status_page_id', 'pageviews', 'last_datetime', 'datetime', 'name'], [], ['pixels_ids' => 'json_contains'], allowed_datetime_fields: ['datetime', 'last_datetime']));
        $filters->set_default_order_by($this->user->preferences->status_pages_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `status_pages` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/status-pages?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `status_pages`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Generate the status page full URL base */
            $row->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($row, $this->user, $domains);

            /* Prepare the data */
            $row = [
                'id' => (int) $row->status_page_id,
                'user_id' => (int) $row->user_id,
                'domain_id' => (int) $row->domain_id,
                'monitors_ids' => json_decode($row->monitors_ids),
                'heartbeats_ids' => json_decode($row->heartbeats_ids),
                'project_id' => (int) $row->project_id,
                'url' => $row->url,
                'full_url' => $row->full_url,
                'name' => $row->name,
                'description' => $row->description,
                'settings' => json_decode($row->settings),
                'socials' => json_decode($row->socials),
                'logo_url' => $row->logo_url ? \Altum\Uploads::get_full_url('status_pages_logos') . $row->logo : null,
                'favicon_url' => $row->favicon ? \Altum\Uploads::get_full_url('status_pages_favicons') . $row->favicon : null,
                'opengraph_url' => $row->opengraph ? \Altum\Uploads::get_full_url('status_pages_opengraph') . $row->opengraph : null,
                'password' => (bool) $row->password,
                'timezone' => $row->timezone,
                'custom_js' => $row->custom_js,
                'custom_css' => $row->custom_css,
                'pageviews' => (int) $row->pageviews,
                'is_se_visible' => (bool) $row->is_se_visible,
                'is_removed_branding' => (bool) $row->is_removed_branding,
                'is_enabled' => (bool) $row->is_enabled,
                'datetime' => $row->datetime,
                'last_datetime' => $row->datetime,
            ];

            $data[] = $row;
        }

        /* Prepare the data */
        $meta = [
            'page' => $_GET['page'] ?? 1,
            'total_pages' => $paginator->getNumPages(),
            'results_per_page' => $filters->get_results_per_page(),
            'total_results' => (int) $total_rows,
        ];

        /* Prepare the pagination links */
        $others = ['links' => [
            'first' => $paginator->getPageUrl(1),
            'last' => $paginator->getNumPages() ? $paginator->getPageUrl($paginator->getNumPages()) : null,
            'next' => $paginator->getNextUrl(),
            'prev' => $paginator->getPrevUrl(),
            'self' => $paginator->getPageUrl($_GET['page'] ?? 1)
        ]];

        Response::jsonapi_success($data, $meta, 200, $others);
    }

    private function get() {

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages');

        /* We haven't found the resource */
        if(!$status_page) {
            $this->return_404();
        }

        /* Generate the status page full URL base */
        $status_page->full_url = (new \Altum\Models\StatusPage())->get_status_page_full_url($status_page, $this->user);

        /* Prepare the data */
        $data = [
            'id' => (int) $status_page->status_page_id,
            'user_id' => (int) $status_page->user_id,
            'domain_id' => (int) $status_page->domain_id,
            'monitors_ids' => json_decode($status_page->monitors_ids),
            'heartbeats_ids' => json_decode($status_page->heartbeats_ids),
            'project_id' => (int) $status_page->project_id,
            'url' => $status_page->url,
            'full_url' => $status_page->full_url,
            'name' => $status_page->name,
            'description' => $status_page->description,
            'settings' => json_decode($status_page->settings),
            'socials' => json_decode($status_page->socials),
            'logo_url' => $status_page->logo ? \Altum\Uploads::get_full_url('status_pages_logos') . $status_page->logo : null,
            'favicon_url' => $status_page->favicon ? \Altum\Uploads::get_full_url('status_pages_favicons') . $status_page->favicon : null,
            'opengraph_url' => $status_page->opengraph ? \Altum\Uploads::get_full_url('status_pages_opengraph') . $status_page->opengraph : null,
            'password' => (bool) $status_page->password,
            'timezone' => $status_page->timezone,
            'custom_js' => $status_page->custom_js,
            'custom_css' => $status_page->custom_css,
            'pageviews' => (int) $status_page->pageviews,
            'is_se_visible' => (bool) $status_page->is_se_visible,
            'is_removed_branding' => (bool) $status_page->is_removed_branding,
            'is_enabled' => (bool) $status_page->is_enabled,
            'datetime' => $status_page->datetime,
            'last_datetime' => $status_page->last_datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function delete() {

        $status_page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $status_page = db()->where('status_page_id', $status_page_id)->where('user_id', $this->user->user_id)->getOne('status_pages');

        /* We haven't found the resource */
        if(!$status_page) {
            $this->return_404();
        }

        /* Delete the resource */
        (new \Altum\Models\StatusPage())->delete($status_page->status_page_id);

        http_response_code(200);
        die();

    }

}
