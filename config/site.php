<?php

return [
    'name' => env('SITE_NAME', env('APP_NAME', 'Menu-One')),
    'panel_url' => env('PANEL_URL', env('APP_URL', 'http://127.0.0.1:8000')),
    'main_site_url' => env('MAIN_SITE_URL', env('APP_URL', 'http://127.0.0.1:8000')),
    'contact_phone' => env('SITE_CONTACT_PHONE', ''),
    'support_email' => env('SITE_SUPPORT_EMAIL', ''),
    'default_locale' => env('APP_LOCALE', 'tr'),
];
