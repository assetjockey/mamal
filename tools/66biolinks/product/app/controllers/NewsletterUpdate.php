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

use Altum\Alerts;

defined('ALTUMCODE') || die();

class NewsletterUpdate extends Controller {

    public function index() {

        if(!\Altum\Plugin::is_active('newsletters') || !settings()->links->biolinks_is_enabled || !(settings()->newsletters->is_enabled ?? false)) {
            throw_404();
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.newsletters')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('newsletters');
        }

        $newsletter_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$newsletter = db()->where('newsletter_id', $newsletter_id)->where('user_id', $this->user->user_id)->getOne('newsletters')) {
            redirect('newsletters');
        }

        if($newsletter->status == 'processing') {
            Alerts::add_error(l('newsletter_update.error_message.processing'));
            redirect('newsletters');
        }

        $newsletter->settings = json_decode($newsletter->settings ?? '');
        $newsletter->subscribers_ids = implode(',', json_decode($newsletter->subscribers_ids ?? '[]'));

        /* Prepare selectable filters */
        $projects = settings()->links->projects_is_enabled ? db()->where('user_id', $this->user->user_id)->orderBy('project_id', 'DESC')->get('projects', null, ['project_id', 'name']) : [];
        $links = db()->where('user_id', $this->user->user_id)->where('type', 'biolink')->orderBy('link_id', 'DESC')->get('links', null, ['link_id', 'url']);
        $biolink_blocks = db()->where('user_id', $this->user->user_id)->where('type', ['email_collector', 'contact_collector', 'appointment_calendar'], 'IN')->orderBy('biolink_block_id', 'DESC')->get('biolinks_blocks', null, ['biolink_block_id', 'link_id', 'type', 'settings']);
        foreach($biolink_blocks as $biolink_block) {
            $biolink_block->settings = json_decode($biolink_block->settings ?? '');
        }

        if(!empty($_POST)) {
            /* Filter some of the variables */
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['subject'] = input_clean($_POST['subject'], 128);
            $_POST['segment'] = in_array($_POST['segment'], ['all', 'project', 'link', 'biolink_block', 'custom']) ? input_clean($_POST['segment']) : 'all';
            $_POST['project_id'] = (int) ($_POST['project_id'] ?? 0);
            $_POST['link_id'] = (int) ($_POST['link_id'] ?? 0);
            $_POST['biolink_block_id'] = (int) ($_POST['biolink_block_id'] ?? 0);

            /* Subscribers ids */
            $_POST['subscribers_ids'] = trim($_POST['subscribers_ids'] ?? '');
            $_POST['subscribers_ids'] = array_filter(array_map('intval', explode(',', $_POST['subscribers_ids'])));
            $_POST['subscribers_ids'] = array_values(array_unique($_POST['subscribers_ids']));
            $_POST['subscribers_ids'] = $_POST['subscribers_ids'] ?: [0];

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Preview email */
            if(isset($_POST['preview'])) {
                $_POST['preview_email'] = mb_substr(filter_var($_POST['preview_email'], FILTER_SANITIZE_EMAIL), 0, 320);

                $required_fields = ['subject', 'content', 'preview_email'];
                foreach($required_fields as $field) {
                    if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                        Alerts::add_field_error($field, l('global.error_message.empty_field'));
                    }
                }

                if(filter_var($_POST['preview_email'], FILTER_VALIDATE_EMAIL) == false) {
                    Alerts::add_field_error('preview_email', l('global.error_message.invalid_email'));
                }
            }

            /* Save draft or send */
            else {
                $required_fields = ['name', 'subject', 'content'];
                foreach($required_fields as $field) {
                    if(!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                        Alerts::add_field_error($field, l('global.error_message.empty_field'));
                    }
                }
            }

            /* Check custom SMTP requirement */
            if((isset($_POST['preview']) || isset($_POST['send'])) && !empty($this->user->plan_settings->force_newsletters_custom_smtp_is_enabled) && !newsletters_get_custom_smtp_settings($this->user)) {
                Alerts::add_error(sprintf(l('newsletter_create.error_message.custom_smtp_required'), '<a href="' . url('account-preferences') . '" class="font-weight-bold text-reset">', '</a>'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Preview email */
                if(isset($_POST['preview'])) {
                    $vars = [
                        '{{SUBSCRIBER:NAME}}' => $this->user->name,
                        '{{SUBSCRIBER:EMAIL}}' => $_POST['preview_email'],
                        '{{USER:NAME}}' => $this->user->name,
                        '{{USER:EMAIL}}' => $this->user->email,
                    ];

                    $email_template = get_email_template(
                        $vars,
                        htmlspecialchars_decode($_POST['subject']),
                        $vars,
                        convert_editorjs_json_to_html($_POST['content'])
                    );

					/* Append unsub details */
					$email_template->body .= '
					<hr />
					<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
						<tr>
							<td class="note align-center">
								<small>' . sprintf(l('global.emails.is_newsletter'), $this->user->name, '<a href="' . url('newsletter-subscribers') . '">', '</a>') . '</small>
							</td>
						</tr>
					</table>';

                    newsletters_send_mail($this->user, $_POST['preview_email'], $email_template->subject, $email_template->body, [
                        'is_system_email' => false,
                        'anti_phishing_code' => false,
                        'language' => $this->user->language,
                        'unsubscribe_url' => url('newsletter-subscribers'),
                        'newsletter_sender_name' => $this->user->name,
                    ]);

                    /* Set a nice success message */
                    Alerts::add_success(sprintf(l('newsletter_create.success_message.preview'), '<strong>' . $_POST['preview_email'] . '</strong>'));
                }

                if(isset($_POST['save']) || isset($_POST['send'])) {
                    $settings = [
                        'project_id' => $_POST['project_id'],
                        'link_id' => $_POST['link_id'],
                        'biolink_block_id' => $_POST['biolink_block_id'],
                    ];

                    /* Get all subscribers ids */
                    $subscribers_ids = newsletters_get_subscribers_ids_by_segment($this->user->user_id, $_POST['segment'], $settings, $_POST['subscribers_ids']);

                    /* Check the monthly email plan limit */
                    $newsletter_emails_per_month_limit = (int) ($this->user->plan_settings->newsletter_emails_per_month_limit ?? -1);
                    if(isset($_POST['send']) && $newsletter_emails_per_month_limit != -1 && (int) ($this->user->newsletter_emails_current_month ?? 0) >= $newsletter_emails_per_month_limit) {
                        Alerts::add_error(l('global.info_message.plan_feature_limit') . (settings()->payment->is_enabled ? ' <a href="' . url('plan') . '" class="font-weight-bold text-reset">' . l('global.info_message.plan_upgrade') . '.</a>' : null));
                        redirect('newsletter-update/' . $newsletter_id);
                    }

                    if($newsletter->status == 'sent') {
                        /* Database query */
                        db()->where('newsletter_id', $newsletter->newsletter_id)->where('user_id', $this->user->user_id)->update('newsletters', [
                            'name' => $_POST['name'],
                            'last_datetime' => get_date(),
                        ]);
                    }

                    else {
                        /* Database query */
                        db()->where('newsletter_id', $newsletter->newsletter_id)->where('user_id', $this->user->user_id)->update('newsletters', [
                            'name' => $_POST['name'],
                            'subject' => $_POST['subject'],
                            'content' => $_POST['content'],
                            'segment' => $_POST['segment'],
                            'settings' => json_encode($settings),
                            'subscribers_ids' => json_encode($subscribers_ids),
                            'sent_subscribers_ids' => '[]',
                            'sent_emails' => 0,
                            'total_emails' => count($subscribers_ids),
                            'status' => isset($_POST['save']) ? 'draft' : 'processing',
                            'last_datetime' => get_date(),
                        ]);
                    }

                    if(isset($_POST['save'])) {
                        /* Set a nice success message */
                        Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));
                    } else {
                        /* Set a nice success message */
                        Alerts::add_success(sprintf(l('newsletter_create.success_message.send'), '<strong>' . $_POST['name'] . '</strong>'));

                        redirect('newsletters');
                    }

                }

                /* Refresh the page */
                redirect('newsletter-update/' . $newsletter_id);

            }

        }

        /* Main View */
        $data = [
            'newsletter_id' => $newsletter_id,
            'newsletter' => $newsletter,
            'projects' => $projects,
            'links' => $links,
            'biolink_blocks' => $biolink_blocks,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('newsletters')->path . 'views/newsletter-update/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));

    }

}
