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

final class CrossXEye implements EyeInterface, Singleton
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

        /* SVG is 6x6, we center and scale it to QR eye space (-3 to 3) */
        $scale = 0.5; /* or tweak if too large/small */
        $offset = 3.0; /* to shift center from (0,0) in SVG */

        $points = [
            [3,5.1],[3.9,6],[6,6],[6,3.9],[5.1,3],[6,2.1],
            [6,0],[3.9,0],[3,0.9],[2.1,0],[0,0],[0,2.1],
            [0.9,3],[0,3.9],[0,6],[2.1,6]
        ];

        /* Normalize and build the path */
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
