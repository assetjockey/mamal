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

namespace Altum\Models;

use Altum\Uploads;

defined('ALTUMCODE') || die();

class Barcode extends Model {

    public function delete($barcode_id) {

        if(!$barcode = db()->where('barcode_id', $barcode_id)->getOne('barcodes', ['user_id', 'barcode_id', 'barcode'])) {
            return;
        }

        Uploads::delete_uploaded_file($barcode->barcode, 'barcodes');

        /* Delete from database */
        db()->where('barcode_id', $barcode_id)->delete('barcodes');

        /* Clear the cache */
        cache()->deleteItem('barcodes_total?user_id=' . $barcode->user_id);
        cache()->deleteItem('barcodes_dashboard?user_id=' . $barcode->user_id);
    }
}
