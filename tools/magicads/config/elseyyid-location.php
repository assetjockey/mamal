<?php

return [
        'name' => 'Localization Manager',
        /**
         * Views
         */
        'layout' => 'langs::layouts.app',
        'content_section' => 'content_languages',
        'scripts_section' => 'scripts',
        'message_success_variable' => 'flash_success',
        /**
         * Routes
         */
        'prefix' => '/admin/languages',
        'middlewares' => ['web'],
];
