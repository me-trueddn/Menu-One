<?php

return [
    /*
    | Platform panel menü modülleri.
    | Her modül için platform.{key}.view ve platform.{key}.edit izinleri oluşturulur.
    */
    'modules' => [
        'users' => [
            'label' => 'menu.users',
            'icon' => 'bi-people',
            'route' => 'platform.users.index',
            'route_patterns' => [
                'platform.users.*',
                'platform.user-groups.*',
            ],
        ],
        'customers' => [
            'label' => 'menu.customers',
            'icon' => 'bi-person-badge',
            'route' => 'platform.customers.index',
            'route_patterns' => ['platform.customers.*'],
        ],
        'site' => [
            'label' => 'menu.site_management',
            'icon' => 'bi-gear',
            'route' => 'platform.settings.site',
            'route_patterns' => ['platform.settings.site*'],
        ],
        'mail' => [
            'label' => 'menu.mail_configuration',
            'icon' => 'bi-envelope',
            'route' => 'platform.settings.mail',
            'route_patterns' => ['platform.settings.mail*'],
        ],
        'cafes' => [
            'label' => 'menu.cafes',
            'icon' => 'bi-shop',
            'route' => 'platform.tenants.index',
            'route_patterns' => ['platform.tenants.*'],
        ],
        'licenses' => [
            'label' => 'menu.licenses',
            'icon' => 'bi-key',
            'route' => 'platform.licenses.index',
            'route_patterns' => ['platform.licenses.*'],
        ],
    ],
];
