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

defined('ALTUMCODE') || die();


function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function input_clean($string, $max_characters = null) {
    $wrapper_function = $max_characters ? function($string) use ($max_characters) { return mb_substr($string, 0, $max_characters); } : fn($string) => $string;
    return $wrapper_function(trim(strip_tags(filter_var_filter_string_polyfill($string ?? ''))));
}

function input_clean_name($string, $max_characters = null) {
    /* Allow valid name chars */
    $string = preg_replace('/[^\p{L}\p{M}\s\'\.\-]/u', '', $string);

    /* Remove domain-like patterns */
    $string = preg_replace('/\b\w+\.\w{2,}\b/u', '', $string);

    /* trim to maximum length if needed */
    if ($max_characters !== null) {
        $string = mb_substr($string, 0, $max_characters);
    }

    return $string;
}

function input_clean_email($string) {
    return mb_substr(mb_strtolower(filter_var($string, FILTER_SANITIZE_EMAIL)), 0, 320);
}

function query_clean($string, $max_characters = null) {
    return mysql_escape_stringg(input_clean($string, $max_characters));
}

function array_query_clean($array) {
    return array_map('query_clean', $array);
}

function mysql_escape_stringg($unescaped_string) {
    $replacementMap = [
        "\0" => "\\0",
        "\n" => "\\n",
        "\r" => "\\r",
        "\t" => "\\t",
        chr(26) => "\\Z",
        chr(8) => "\\b",
        '"' => '\"',
        "'" => "\'",
        '\\' => '\\\\'
    ];

    return \strtr($unescaped_string, $replacementMap);
}

function filter_var_filter_string_polyfill($string) {
    $str = preg_replace('/\x00|<[^>]*>?/', '', $string);
    return str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
}

function string_truncate($string, $maxchar, $ending = '..') {
    $length = mb_strlen($string ?? '');
    if($length > $maxchar) {
        $cutsize = -($length-$maxchar);
        $string  = mb_substr($string, 0, $cutsize);
        $string  = $string . $ending;
    }
    return $string;
}

function string_filter_alphanumeric($string) {

    $string = preg_replace('/[^a-zA-Z0-9\s]+/', '', $string);

    $string = preg_replace('/\s+/', ' ', $string);

    return $string;
}

function string_generate($length) {
    $characters = str_split('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
    $content = '';

    for($i = 1; $i <= $length; $i++) {
        $content .= $characters[array_rand($characters, 1)];
    }

    return $content;
}

function string_starts_with($needle, $haystack) {
    return str_starts_with($haystack, $needle);
}

function string_ends_with($needle, $haystack) {
    return str_ends_with($haystack, $needle);
}

function string_estimate_reading_time($string) {
    $total_words = str_word_count(strip_tags($string));

    /* 200 is the total amount of words read per minute */
    $minutes = floor($total_words / 200);
    $seconds = floor($total_words / 200 / (200 / 60));

    return (object) [
        'minutes' => $minutes,
        'seconds' => $seconds
    ];
}

function process_spintax($string) {
    return preg_replace_callback('/\{[^{}]*\|[^{}]*\}/', function ($match) {
        $content = substr($match[0], 1, -1);
        $words = explode('|', $content);
        return $words[array_rand($words)];
    }, $string);
}

/* validate and sanitize a hex color string */
function verify_hex_color($color) {
    /* check if input matches allowed hex color formats */
    if(preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{4}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color)) {
        return $color;
    }

    return false;
}

function output_blog_post_content($blog_post_content) {
    if (strip_tags($blog_post_content) != $blog_post_content) {
        /* Content has HTML, output as is */
        return $blog_post_content;
    } else {
        /* Content is plain text, nl2br */
        return nl2br($blog_post_content);
    }
}

function get_blog_post_table_of_contents($blog_post_content) {
    $result = (object) [
        'content' => $blog_post_content,
        'items' => [],
    ];

    if(!trim($blog_post_content)) {
        return $result;
    }

    /* Parse the content */
    $previous_errors = libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $is_loaded = $dom->loadHTML('<?xml encoding="UTF-8"><div id="blog-post-content-wrapper">' . $blog_post_content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    if(!$is_loaded) {
        return $result;
    }

    /* Find headings */
    $xpath = new \DOMXPath($dom);
    $headings = $xpath->query('//*[@id="blog-post-content-wrapper"]//*[self::h2 or self::h3 or self::h4 or self::h5 or self::h6]');

    if(!$headings->length) {
        return $result;
    }

    $used_ids = [];

    foreach($headings as $heading) {
        $heading_title = input_clean($heading->textContent, 256);

        if(!$heading_title) {
            continue;
        }

        /* Generate a unique id */
        $base_heading_id = get_slug($heading_title);
        $base_heading_id = $base_heading_id ? $base_heading_id : 'heading';
        $heading_id = $base_heading_id;
        $counter = 1;

        while(isset($used_ids[$heading_id])) {
            $counter++;
            $heading_id = $base_heading_id . '-' . $counter;
        }

        $used_ids[$heading_id] = true;
        $heading->setAttribute('id', $heading_id);

        $result->items[] = [
            'id' => $heading_id,
            'title' => $heading_title,
            'level' => (int) str_replace('h', '', $heading->nodeName),
        ];
    }

    if(!count($result->items)) {
        return $result;
    }

    /* Get the updated content */
    $wrapper = $dom->getElementById('blog-post-content-wrapper');
    $content = '';

    foreach($wrapper->childNodes as $child_node) {
        $content .= $dom->saveHTML($child_node);
    }

    $result->content = $content;

    return $result;
}
