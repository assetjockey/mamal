<?php

declare(strict_types=1);

return [
    'scopes' => [
        'https://graph.microsoft.com/Mail.ReadWrite',
        'https://graph.microsoft.com/Mail.Send',
        'https://graph.microsoft.com/Calendars.ReadWrite',
        'https://graph.microsoft.com/Contacts.ReadWrite',
        'offline_access',
        'openid',
        'profile',
        'email',
    ],
];
