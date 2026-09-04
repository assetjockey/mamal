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

namespace Altum\QrCodes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;
use SimpleSoftwareIO\QrCode\Singleton;

final class FlowerEye implements EyeInterface, Singleton
{
    private static $instance;

    private function __construct()
    {
    }

    public static function instance() : self
    {
        return self::$instance ?: self::$instance = new self();
    }

    public function getExternalPath() : Path
    {
        return (new Path())
            ->move(-3.5, 0.)
            ->curve(-3.5, -3.5, -3.5, -3.5, 0., -3.5)
            ->move(-3.5, 0.)
            ->curve(-3.5, 3.5, -3.5, 3.5, 0, 3.5)
            ->move(3.5, 0.)
            ->curve(3.5, -3.5, 3.5, -3.5, 0, -3.5)
            ->move(3.5, 0.)
            ->line(3.5, 0.)
            ->line(0, 3.5)
            ->line(3.5,3.5)
            ->close()
            ->ellipticArc(0., 0., 0., false, true, 0., 3.5)
            ->ellipticArc(0., 0., 0., false, true, -3.5, 0.)
            ->ellipticArc(0., 0., 0., false, true, 0., -3.5)
            ->ellipticArc(0., 0., 0., false, true, 3.5, 0.)
            ->move(-2.5, 0.)
            ->curve(-2.5, -2.5, -2.5, -2.5, 0., -2.5)
            ->move(-2.5, 0.)
            ->curve(-2.5, 2.5, -2.5, 2.5, 0, 2.5)
            ->move(2.5, 0.)
            ->curve(2.5, -2.5, 2.5, -2.5, 0, -2.5)
            ->move(2.5, 0.)
            ->line(2.5, 0.)
            ->line(0, 2.5)
            ->line(2.5,2.5)
            ->close()
            ->ellipticArc(0., 0., 0., false, true, 0., 2.5)
            ->ellipticArc(0., 0., 0., false, true, -2.5, 0.)
            ->ellipticArc(0., 0., 0., false, true, 0., -2.5)
            ->ellipticArc(0., 0., 0., false, true, 2.5, 0.)
            ;
    }

    public function getInternalPath() : Path
    {
        return (new Path())
            ->move(-1.5, 0.)
            ->curve(-1.5, -1.5, -1.5, -1.5, 0., -1.5)
            ->move(-1.5, 0.)
            ->curve(-1.5, 1.5, -1.5, 1.5, 0, 1.5)
            ->move(1.5, 0.)
            ->curve(1.5, -1.5, 1.5, -1.5, 0, -1.5)
            ->move(1.5, 0.)
            ->line(1.5, 0.)
            ->line(0, 1.5)
            ->line(1.5,1.5)
            ->close()
            ->ellipticArc(0., 0., 0., false, true, 0., 1.5)
            ->ellipticArc(0., 0., 0., false, true, -1.5, 0.)
            ->ellipticArc(0., 0., 0., false, true, 0., -1.5)
            ->ellipticArc(0., 0., 0., false, true, 1.5, 0.)
            ;
    }
}
