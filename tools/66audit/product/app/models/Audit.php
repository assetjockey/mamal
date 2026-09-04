<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
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

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Audit extends Model {

    public function get_stats() {

        return $audits = \Altum\Cache::cache_function_result('audits_stats', null, function() {
            return db()->where('is_queued', 0)->getOne('audits', '
                ROUND(AVG(`score`)) as `average_score`,
                COUNT(*) as `total_audits`,
                SUM(`total_tests`) as `total_tests`
            ');
        }, 86400);

    }

}
