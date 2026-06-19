<?php

return [
    'google' => [
        'label' => 'Google (Gmail)',
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
    ],
    'yandex' => [
        'label' => 'Yandex Mail (Türkiye)',
        'host' => 'smtp.yandex.com.tr',
        'port' => 465,
        'encryption' => 'ssl',
        'username_hint' => 'yandex_custom_domain',
    ],
    'yandex_tls' => [
        'label' => 'Yandex Mail — TLS (587)',
        'host' => 'smtp.yandex.com.tr',
        'port' => 587,
        'encryption' => 'tls',
        'username_hint' => 'yandex_custom_domain',
    ],
    'microsoft' => [
        'label' => 'Microsoft (Outlook / Office 365)',
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => 'tls',
    ],
];
