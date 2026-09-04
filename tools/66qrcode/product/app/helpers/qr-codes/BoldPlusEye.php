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

final class BoldPlusEye implements EyeInterface, Singleton
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
    }

    public function getInternalPath() : Path
    {
        $path = new Path();

        /* SVG is 6x6 — center and scale it into QR eye space */
        $scale = 0.5;
        $offset = 3.0;

        $points = [
            [6,1.5],[4.5,1.5],[4.5,0],[1.5,0],
            [1.5,1.5],[0,1.5],[0,4.5],[1.5,4.5],
            [1.5,6],[4.5,6],[4.5,4.5],[6,4.5]
        ];

        $first = true;
        foreach ($points as [$x, $y]) {
            $nx = ($x - $offset) * $scale;
            $ny = ($y - $offset) * $scale;

            if($first) {
                $path = $path->move($nx, $ny);
                $first = false;
            } else {
                $path = $path->line($nx, $ny);
            }
        }

        $path = $path->close();

        return $path;
    }
}
