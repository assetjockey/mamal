<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    */
    'php_version' => '8.2',

    'extensions' => [
        'php' => [
            'bcmath',
            'ctype',
            'curl',
            'dom',
            'fileinfo',
            'gd',
            'iconv',
            'intl',
            'json',
            'mbstring',
            'mysqli',
            'openssl',
            'PDO',
            'pdo_mysql',
            'tokenizer',
            'xml',
            'zip',
        ],
        'apache' => [
            'mod_rewrite',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Required PHP functions
    |--------------------------------------------------------------------------
    | These must be callable and not listed in php.ini "disable_functions".
    */
    'functions' => [
        'shell_exec',
        'exec',
    ],

    /*
    |--------------------------------------------------------------------------
    | File permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'Files' => [
            '.env',
        ],
        'Folders' =>
        [
            'bootstrap/cache',
            'public/uploads',
            'lang',
            'storage',
            'storage/framework/',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ],
    ]
];
