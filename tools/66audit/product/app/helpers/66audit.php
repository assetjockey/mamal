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

defined('ALTUMCODE') || die();

function get_spf_records($domain) {
    static $cached = [];

    if(isset($cached[$domain])) {
        return $cached[$domain];
    }

    $txt_records = dns_get_record($domain, DNS_TXT);
    $spf_records = [];

    foreach ($txt_records as $record) {
        if (!empty($record['txt']) && stripos($record['txt'], 'v=spf1') === 0) {
            $spf_records[] = trim($record['txt']);
        }
    }

    $cached[$domain] = $spf_records;

    return $spf_records;
}

function validate_spf_syntax($spf_record) {
    $errors = [];

    // must start correctly
    if (stripos($spf_record, 'v=spf1') !== 0) {
        $errors[] = 'spf_missing_v_prefix';
        return $errors;
    }

    // must be space-separated, not comma-separated
    if (strpos($spf_record, ',') !== false) {
        $errors[] = 'spf_invalid_separator';
    }

    $parts = preg_split('/\s+/', $spf_record);

    // must contain all mechanism
    $has_all = false;

    foreach ($parts as $index => $part) {
        if ($index === 0) {
            continue;
        }

        // qualifier
        $qualifier = '';
        if (preg_match('/^[+\-~?]/', $part)) {
            $qualifier = $part[0];
            $part = substr($part, 1);
        }

        // all
        if ($part === 'all') {
            $has_all = true;
            continue;
        }

        // valid mechanisms
        if (
            !preg_match(
                '/^(ip4|ip6|a|mx|include|exists|redirect)(:|=)?.+$/',
                $part
            )
        ) {
            $errors[] = 'spf_invalid_mechanism:' . $part;
        }
    }

    if (!$has_all) {
        $errors[] = 'spf_missing_all';
    }

    return $errors;
}

function convert_array_to_utf8($data) {
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $key = is_string($key) ? mb_convert_encoding($key, 'UTF-8', 'auto') : $key;
            $result[$key] = convert_array_to_utf8($value);
        }
        return $result;
    } elseif (is_string($data)) {
        return mb_convert_encoding($data, 'UTF-8', 'auto');
    }
    return $data;
}

function get_deprecated_html_tags_array () {
    return [
        'acronym',
        'applet',
        'basefont',
        'bgsound',
        'big',
        'blink',
        'center',
        'command',
        'content',
        'dir',
        'element',
        'font',
        'frame',
        'frameset',
        'isindex',
        'keygen',
        'marquee',
        'menuitem',
        'nobr',
        'noembed',
        'noframes',
        'plaintext',
        'shadow',
        'spacer',
        'strike',
        'tt',
        'u',
        'xmp'
    ];
}

function get_full_url_from_relative_paths($base_url, $potential_relative_path) {
    if(str_starts_with($potential_relative_path, 'http://') || str_starts_with($potential_relative_path, 'https://')) {
        return $potential_relative_path;
    }
    // Parse the base URL to extract its components.
    $base = parse_url($base_url);

    // Handle protocol-relative URLs (starting with '//')
    if(str_starts_with($potential_relative_path, '//')) {
        return $base['scheme'] . ':' . $potential_relative_path;
    }

    // If the URL starts with a "/", it's a root-relative URL.
    if(str_starts_with($potential_relative_path, '/')) {
        return $base['scheme'] . '://' . $base['host'] . $potential_relative_path;
    }

    // If the URL starts with "./" or does not start with a "/", it's relative to the base URL's path.
    $path = isset($base['path']) ? dirname($base['path']) : '';

    // Combine and normalize the path.
    $full_url = $base['scheme'] . '://' . $base['host'] . rtrim($path, '/') . '/' . ltrim($potential_relative_path, './');

    return $full_url;
}

function get_approximate_text_dimensions($text, $fontSize) {
    // Approximate character width to be 0.6 times the font size
    $charWidth = 0.6 * $fontSize;
    $width = strlen($text) * $charWidth;
    $height = $fontSize;
    return array('width' => $width, 'height' => $height);
}

function get_audit_score_circle($attributes) {
    extract($attributes);

    // Calculate radius
    $radius = ($size / 2) - 10;

    // Calculate circumference
    $circumferenceValue = 2 * pi() * $radius;
    $circumference = $circumferenceValue;

    // Calculate percentage offset
    $percentageOffset = ($circumferenceValue * (100 - $progress)) / 100;

    // Determine suffix for text
    $suffix = $percentageToggle ? '%' : '';

    // Prepare text content
    if($valueToggle) {
        $displayText = $custom_display_text ?? ($progress . $suffix);

        // Approximate text dimensions
        $textDimensions = get_approximate_text_dimensions($displayText, $textSize['fontSize']);
        $textSize['width'] = $textDimensions['width'];
        $textSize['height'] = $textDimensions['height'];

        // Calculate text position
        $textX = round(($size / 2) - ($textSize['width'] / 2)) . "px";
        $textY = round(($size / 2) + ($textSize['height'] / 4)) . "px";

        // Transform for text orientation
        $transform = "rotate(90deg) translate(0px, -" . ($size - 4) . "px)";

        // Create text element
        $text = "\n    <text x=\"$textX\" y=\"$textY\" fill=\"$textColor\" font-size=\"{$textSize['fontSize']}px\" font-weight=\"bold\" style=\"transform:$transform\">$displayText</text>";
    } else {
        $text = '';
    }

    // SVG content with CSS animation
    $svg = "
<svg width=\"$size\" height=\"$size\" viewBox=\"-" . ($size * 0.125) . " -" . ($size * 0.125) . " " . ($size * 1.25) . " " . ($size * 1.25) . "\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" style=\"transform:rotate(-90deg)\">
    <style>
        .progress-circle {
            animation: progress-animation 1s ease-out forwards;
        }
        @keyframes progress-animation {
            from {
                stroke-dashoffset: {$circumference}px;
            }
            to {
                stroke-dashoffset: {$percentageOffset}px;
            }
        }
    </style>
    <!-- Background Circle -->
    <circle
        r=\"$radius\"
        cx=\"" . ($size / 2) . "\"
        cy=\"" . ($size / 2) . "\"
        fill=\"transparent\"
        stroke=\"$circleColor\"
        stroke-width=\"$circleWidth\"
        stroke-dasharray=\"{$circumference}px\"
        stroke-dashoffset=\"0\">
    </circle>
    <!-- Progress Circle -->
    <circle
        class=\"progress-circle\"
        r=\"$radius\"
        cx=\"" . ($size / 2) . "\"
        cy=\"" . ($size / 2) . "\"
        stroke=\"$progressColor\"
        stroke-width=\"$progressWidth\"
        stroke-linecap=\"$progressShape\"
        fill=\"transparent\"
        stroke-dasharray=\"{$circumference}px\"
        stroke-dashoffset=\"{$circumference}px\">
    </circle>$text
</svg>
    ";

    return $svg;
}

function get_audit_test_icon($key, $issues) {
    if(!empty($issues->major->{$key})) {
        return '<span data-toggle="tooltip" title="' . l('audits.major_issue') . '"><i class="fas fa-fw fa-sm fa-exclamation-circle text-danger mr-1"></i></span>';
    }

    if(!empty($issues->moderate->{$key})) {
        return '<span data-toggle="tooltip" title="' . l('audits.moderate_issue') . '"><i class="fas fa-fw fa-sm fa-exclamation-triangle text-warning mr-1"></i></span>';
    }

    if(!empty($issues->minor->{$key})) {
        return '<span data-toggle="tooltip" title="' . l('audits.minor_issue') . '"><i class="fas fa-fw fa-sm fa-circle text-muted mr-1"></i></span>';
    }

    return '<span data-toggle="tooltip" title="' . l('audits.passed_test') . '"><i class="fas fa-fw fa-sm fa-check-circle text-success mr-1"></i></span>';
}

function get_audit_score_display($audit) {
    if($audit->is_external_redirected) {
        return l('global.na');
    }

    if($audit->is_queued ?? false) {
        return '?';
    }

    return nr($audit->score) . '%';
}

function get_website_score_display($website) {
    if($website->total_tests == 0) {
        return l('global.na');
    }

    return nr($website->score) . '%';
}
