<?php

return [
    'default_base_url' => env('LICENSEGATE_BASE_URL', 'https://api.licensegate.io'),

    'paths' => [
        'verify' => '/license/{userId}/{licenseKey}/verify',
        'admin_licenses' => '/admin/licenses',
        'admin_license' => '/admin/licenses/{id}',
    ],

    'verify_cache_seconds' => (int) env('LICENSEGATE_VERIFY_CACHE_SECONDS', 300),
];
