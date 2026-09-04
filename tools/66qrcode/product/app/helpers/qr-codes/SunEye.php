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

final class SunEye implements EyeInterface, Singleton
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

        $scale = 0.5;
        $offset = 3.0;

        $points = [
            [3,0], [3.4,0.7], [4,0.2], [4.1,0.9], [4.9,0.7], [4.8,1.5],
            [5.6,1.5], [5.2,2.2], [5.9,2.5], [5.3,3], [5.9,3.5], [5.2,3.8],
            [5.6,4.5], [4.8,4.5], [4.9,5.3], [4.1,5.1], [4,5.8], [3.4,5.3],
            [3,6], [2.5,5.3], [1.9,5.8], [1.8,5.1], [1,5.3], [1.1,4.5],
            [0.4,4.5], [0.7,3.8], [0,3.5], [0.6,3], [0,2.5], [0.7,2.2],
            [0.4,1.5], [1.1,1.5], [1,0.7], [1.8,0.9], [1.9,0.2], [2.5,0.7]
        ];

        $first = true;

        foreach ($points as [$x, $y]) {
            $x = ($x - $offset) * $scale;
            $y = ($y - $offset) * $scale;

            if($first) {
                $path = $path->move($x, $y);
                $first = false;
            } else {
                $path = $path->line($x, $y);
            }
        }

        $path = $path->close();

        return $path;
    }
}
