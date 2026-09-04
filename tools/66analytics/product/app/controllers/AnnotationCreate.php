<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */
namespace Altum\Controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class AnnotationCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!$this->website || !settings()->analytics->annotations_is_enabled) {
            redirect('annotations');
        }

        /* Team checks */
        if($this->team) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('annotations');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `annotations` WHERE `website_id` = {$this->website->website_id} AND `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->annotations_limit != -1 && $total_rows >= $this->user->plan_settings->annotations_limit) {
            Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
            redirect('annotations');
        }

        if(!empty($_POST)) {
            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'chart_datetime'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* :) */
            $_POST['name'] = input_clean($_POST['name'], 64);

            /* Convert date */
            if(isset($_POST['chart_datetime']) && \Altum\Date::validate($_POST['chart_datetime'], 'Y-m-d H:i:s')) {
                $_POST['chart_datetime'] = (new \DateTime($_POST['chart_datetime'], new \DateTimeZone($this->user->timezone)))->setTimezone(new \DateTimeZone(\Altum\Date::$default_timezone))->format('Y-m-d H:i:s');
            } else {
                $_POST['chart_datetime'] = get_date();
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Database query */
                $annotation_id = db()->insert('annotations', [
                    'user_id' => $this->user->user_id,
                    'website_id' => $this->website->website_id,
                    'name' => $_POST['name'],
                    'chart_datetime' => $_POST['chart_datetime'],
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItemsByTag('annotations?website_id=' . $this->website->website_id);

                redirect('annotations');
            }

        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'chart_datetime' => $_POST['chart_datetime'] ?? '',
        ];

        /* Prepare the view */
        $data = [
            'values' => $values
        ];

        $view = new \Altum\View('annotation-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
