<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 *  View all other existing AltumCode projects via https://altumcode.com/
 *  Get in touch for support or general queries via https://altumcode.com/contact
 *  Download the latest version via https://altumcode.com/downloads
 *
 *  X/Twitter: https://x.com/AltumCode
 *  Facebook: https://facebook.com/altumcode
 *  Instagram: https://instagram.com/altumcode
 */

$features = ['links'];

/* Codes - AI QR Codes */
if(settings()->codes->ai_qr_codes_is_enabled) {
    $features[] = 'ai_qr_codes';
}

/* Codes - QR Codes */
if(settings()->codes->qr_codes_is_enabled) {
    $features[] = 'qr_codes';
}

/* Codes - Barcodes */
if(settings()->codes->barcodes_is_enabled) {
    $features[] = 'barcodes';
}

return $features;
