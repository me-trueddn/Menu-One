<?php

return [
    'active' => env('ACTIVE_THEME', 'adminlte4'),

    'themes' => [
        'adminlte4' => [
            'name' => 'AdminLTE 4',
            'version' => '4.0.0',
            'path' => base_path('themes/adminlte4'),
            'css' => 'resources/css/themes/adminlte4.css',
            'js' => 'resources/js/themes/adminlte4.js',
        ],
    ],
];
