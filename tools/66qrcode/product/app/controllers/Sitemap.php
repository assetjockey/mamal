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

defined('ALTUMCODE') || die();

class Sitemap extends Controller {

    public function index() {

        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        $view = new \Altum\View('sitemap/sitemap_index', (array) $this);

        echo $view->run();

    }

    public function main() {
        /* Set the header as xml so the browser can read it properly */
        header('Content-Type: text/xml');

        $sitemap_urls = [
            '',
            'login',
            'lost-password',
        ];

        if(settings()->users->email_confirmation) {
            $sitemap_urls[] = 'resend-activation';
        }

        if(settings()->users->register_is_enabled) {
            $sitemap_urls[] = 'register';
        }

        if(\Altum\Plugin::is_active('affiliate') && settings()->affiliate->is_enabled) {
            $sitemap_urls[] = 'affiliate';
        }

        if(settings()->main->api_is_enabled) {
            $sitemap_urls[] = 'api-documentation';
        }

        if(settings()->email_notifications->contact && !empty(settings()->email_notifications->emails)) {
            $sitemap_urls[] = 'contact';
        }

		if(settings()->content->broadcasts_is_enabled && settings()->content->broadcasts_guests_is_enabled) {
			$sitemap_urls[] = 'broadcast-subscribe';
		}

        if(settings()->payment->is_enabled) {
            $sitemap_urls[] = 'plan';
			
			foreach((array) settings()->payment->currencies as $currency => $currency_data) {
				if(settings()->payment->default_currency != $currency) {
					$sitemap_urls[] = 'plan?currency=' . $currency;
				}
			}
        }

        if(settings()->content->pages_is_enabled) {
            $sitemap_urls[] = 'pages';
        }

        if(settings()->content->blog_is_enabled) {
            $sitemap_urls[] = 'blog';
        }

        /* Product specific */
        if(settings()->codes->qr_codes_is_enabled) {
            $sitemap_urls[] = 'qr';
        }

        if(settings()->codes->qr_reader_is_enabled) {
            $sitemap_urls[] = 'qr-reader';
        }

        if(settings()->codes->barcodes_is_enabled) {
            $sitemap_urls[] = 'barcode';
        }

        if(settings()->codes->barcode_reader_is_enabled) {
            $sitemap_urls[] = 'barcode-reader';
        }

		if(\Altum\Plugin::is_active('chrome-extension') && settings()->chrome_extension->is_enabled) {
			$sitemap_urls[] = 'chrome-extension';
		}

        /* Multilingual */
        $new_sitemap_urls = [];

        foreach(\Altum\Language::$active_languages as $language_name => $language_code) {
            foreach($sitemap_urls as $url) {
                $new_sitemap_urls[] = [
                    'url' => settings()->main->default_language == $language_name ? SITE_URL . $url : SITE_URL . $language_code . '/' . $url,
                    'lastmod' => null,
                ];
            }
        }

        if(settings()->content->pages_is_enabled) {
            $pages = db()->where('type', 'internal')->where('is_published', 1)->get('pages', null, ['url', 'language']);
            $pages_categories = db()->get('pages_categories', null, ['url', 'language']);

            foreach ($pages as $page) {
                $language_code = $page->language && settings()->main->default_language != $page->language ? \Altum\Language::$active_languages[$page->language] . '/' : '';
                $new_sitemap_urls[] = [
                    'url' => SITE_URL . $language_code . 'page/' . $page->url,
                    'lastmod' => null,
                ];
            }

            foreach ($pages_categories as $pages_category) {
                $language_code = $pages_category->language && settings()->main->default_language != $pages_category->language ? \Altum\Language::$active_languages[$pages_category->language] . '/' : '';
                $new_sitemap_urls[] = [
                    'url' => SITE_URL . $language_code . 'pages/' . $pages_category->url,
                    'lastmod' => null,
                ];
            }
        }

        if(settings()->content->blog_is_enabled) {
            $blog_posts = db()->where('is_published', 1)->get('blog_posts', null, ['url', 'language', 'datetime', 'last_datetime']);
            $blog_posts_categories = db()->get('blog_posts_categories', null, ['url', 'language']);

            foreach ($blog_posts as $blog_post) {
                $language_code = $blog_post->language && settings()->main->default_language != $blog_post->language ? \Altum\Language::$active_languages[$blog_post->language] . '/' : '';

                /* Get the last modification date */
                $lastmod = $blog_post->last_datetime ? $blog_post->last_datetime : $blog_post->datetime;

                $new_sitemap_urls[] = [
                    'url' => SITE_URL . $language_code . 'blog/' . $blog_post->url,
                    'lastmod' => $lastmod ? (new \DateTime($lastmod))->format('Y-m-d\TH:i:sP') : null,
                ];
            }

            foreach ($blog_posts_categories as $blog_posts_category) {
                $language_code = $blog_posts_category->language && settings()->main->default_language != $blog_posts_category->language ? \Altum\Language::$active_languages[$blog_posts_category->language] . '/' : '';
                $new_sitemap_urls[] = [
                    'url' => SITE_URL . $language_code . 'blog/category/' . $blog_posts_category->url,
                    'lastmod' => null,
                ];
            }
        }


        /* Main View */
        $data = [
            'sitemap_urls' => $new_sitemap_urls,
        ];

        $view = new \Altum\View('sitemap/sitemap_main', (array) $this);

        echo $view->run($data);

    }

}
