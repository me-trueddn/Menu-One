<?php

return [
    'trendyol' => [
        'label' => 'Trendyol Yemek',
        'icon' => 'bi-bag-check',
        'docs_url' => 'https://developers.trendyol.com/',
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'supplier_id', 'label' => 'Supplier ID', 'type' => 'text'],
            ['key' => 'store_id', 'label' => 'Store ID', 'type' => 'text'],
        ],
    ],
    'fiyuu' => [
        'label' => 'Fiyuu',
        'icon' => 'bi-truck',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text'],
        ],
    ],
    'yemeksepeti' => [
        'label' => 'Yemeksepeti',
        'icon' => 'bi-shop',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'restaurant_id', 'label' => 'Restaurant ID', 'type' => 'text'],
        ],
    ],
    'maxijett' => [
        'label' => 'MAXIJETT',
        'icon' => 'bi-lightning',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'store_code', 'label' => 'Store Code', 'type' => 'text'],
        ],
    ],
    'fuudy' => [
        'label' => 'Fuudy',
        'icon' => 'bi-basket',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'restaurant_id', 'label' => 'Restaurant ID', 'type' => 'text'],
        ],
    ],
    'hizir' => [
        'label' => 'Hizir',
        'icon' => 'bi-speedometer2',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'branch_id', 'label' => 'Branch ID', 'type' => 'text'],
        ],
    ],
    'migros_yemek' => [
        'label' => 'Migros Yemek',
        'icon' => 'bi-cart4',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'store_id', 'label' => 'Store ID', 'type' => 'text'],
        ],
    ],
    'paket_taxi' => [
        'label' => 'Paket Taxi',
        'icon' => 'bi-taxi-front',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text'],
        ],
    ],
    'getir' => [
        'label' => 'Getir',
        'icon' => 'bi-bicycle',
        'docs_url' => null,
        'fields' => [
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'secret' => true],
            ['key' => 'restaurant_secret_key', 'label' => 'Restaurant Secret Key', 'type' => 'password', 'secret' => true],
            ['key' => 'store_id', 'label' => 'Store ID', 'type' => 'text'],
        ],
    ],
];
