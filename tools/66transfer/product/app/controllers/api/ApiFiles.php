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
use Altum\Uploads;

defined('ALTUMCODE') || die();

class ApiFiles extends Controller {
    use Apiable;

    public function index() {

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

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['user_id', 'transfer_id', 'transfer_request_id'], ['original_name'], ['file_id', 'datetime', 'original_name'], allowed_datetime_fields: ['datetime']));
        $filters->set_default_order_by('file_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);
        $filters->process();

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `files` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('api/files?' . $filters->get_get() . '&page={{PAGE}}')));

        /* Get the data */
        $data = [];
        $data_result = database()->query("
            SELECT
                *
            FROM
                `files`
            WHERE
                `user_id` = {$this->user->user_id}
                {$filters->get_sql_where()}
                {$filters->get_sql_order_by()}
                  
            {$paginator->get_sql_limit()}
        ");
        while($row = $data_result->fetch_object()) {

            /* Prepare the data */
            $row = [
                'id' => (int) $row->file_id,
                'user_id' => (int) $row->user_id,
                'transfer_id' => (int) $row->transfer_id,
                'transfer_request_id' => (int) $row->transfer_request_id,
                'file_uuid' => bin2hex($row->file_uuid),
                'uploader_id' => $row->uploader_id,
                'name' => $row->name,
                'original_name' => $row->original_name,
                'size' => (int) $row->size,
                'status' => $row->status,
                'is_encrypted' => (bool) $row->is_encrypted,
                'datetime' => $row->datetime,
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

        $file_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $file = db()->where('file_id', $file_id)->where('user_id', $this->user->user_id)->getOne('files');

        /* We haven't found the resource */
        if(!$file) {
            $this->return_404();
        }

        /* Prepare the data */
        $data = [
            'id' => (int) $file->file_id,
            'user_id' => (int) $file->user_id,
            'transfer_id' => (int) $file->transfer_id,
            'transfer_request_id' => (int) $file->transfer_request_id,
            'file_uuid' => bin2hex($file->file_uuid),
            'uploader_id' => $file->uploader_id,
            'name' => $file->name,
            'original_name' => $file->original_name,
            'size' => (int) $file->size,
            'status' => $file->status,
            'is_encrypted' => (bool) $file->is_encrypted,
            'datetime' => $file->datetime,
        ];

        Response::jsonapi_success($data);

    }

    private function delete() {

        $file_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Try to get details about the resource id */
        $file = db()->where('file_id', $file_id)->where('user_id', $this->user->user_id)->getOne('files');

        /* We haven't found the resource */
        if(!$file) {
            $this->return_404();
        }

        /* Delete uploaded file */
        Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

        /* Delete the resource */
        db()->where('file_id', $file->file_id)->delete('files');

        /* Update the user */
        (new \Altum\Models\Files())->calculate_and_update_file_usage($file->user_id);

        /* Clear the cache */
        cache()->deleteItem('files?transfer_id=' . $file->transfer_id);

        http_response_code(200);
        die();

    }

}
