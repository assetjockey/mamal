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

use Altum\Meta;
use Altum\Title;

defined('ALTUMCODE') || die();

class AdminPagePreview extends Controller {

    public function index() {

        $page_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        /* Check if resource exists */
        if(!$page = db()->where('page_id', $page_id)->getOne('pages')) {
            redirect('admin/pages');
        }

        if($page->type == 'external') {
            header('Location: ' . $page->url);
            die();
        }

        /* Get the page category */
        $pages_category = $page->pages_category_id ? db()->where('pages_category_id', $page->pages_category_id)->getOne('pages_categories') : null;

        /* Transform content if needed */
        $page->content = json_decode($page->content) ? convert_editorjs_json_to_html($page->content) : output_blog_post_content($page->content);

        /* Prepare the view */
        $data = [
            'page'  => $page,
            'pages_category' => $pages_category,
        ];

        $view = new \Altum\View('page/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

        /* Set a custom title */
        Title::set($page->title);

        /* Meta */
        Meta::set_description($page->description);
        Meta::set_keywords($page->keywords);
        Meta::set_robots('noindex');
        Meta::set_link_alternate(false);

        /* Use the public wrapper */
        \Altum\Router::$path = '';
    }

}
