<?php

/**
 * laravel-ffmpeg configuration.
 *
 * The studio ships with two bundled FFmpeg static builds so the same
 * codebase works on Linux production hosts and on Windows dev machines
 * without needing FFmpeg installed system-wide:
 *
 *   Linux:   vendor/ffmpeg/ffmpeg         (and ffprobe — root of folder)
 *   Windows: vendor/ffmpeg-windows/bin/ffmpeg.exe  (gyan.dev layout)
 *
 * The two builds use different folder layouts because that's how their
 * upstream archives ship — keeping the original layout means a future
 * `tar -xJf` on Linux or `Expand-Archive` on Windows still drops in cleanly.
 *
 * Override via env if you have a different install location.
 */

$isWindows = PHP_OS_FAMILY === 'Windows';

$bundledFfmpeg = $isWindows
    ? base_path('vendor/ffmpeg-windows/bin/ffmpeg.exe')
    : base_path('vendor/ffmpeg/ffmpeg');

$bundledFfprobe = $isWindows
    ? base_path('vendor/ffmpeg-windows/bin/ffprobe.exe')
    : base_path('vendor/ffmpeg/ffprobe');

return [
    'ffmpeg' => [
        // Prefer bundled binary; fall back to PATH lookup if it doesn't
        // exist (e.g. user removed the bundle or runs on a host with a
        // system-wide install).
        'binaries' => env('FFMPEG_BINARIES', file_exists($bundledFfmpeg) ? $bundledFfmpeg : 'ffmpeg'),

        'threads' => 4,   // set to false to disable the default 'threads' filter
    ],

    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', file_exists($bundledFfprobe) ? $bundledFfprobe : 'ffprobe'),
    ],

    'timeout' => 600,

    'log_channel' => env('LOG_CHANNEL', 'stack'),   // set to false to completely disable logging

    'temporary_files_root' => env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir()),

    'temporary_files_encrypted_hls' => env('FFMPEG_TEMPORARY_ENCRYPTED_HLS', env('FFMPEG_TEMPORARY_FILES_ROOT', sys_get_temp_dir())),
];
