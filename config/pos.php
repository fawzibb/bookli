<?php
return [
    'offline_days' => 7,
    'issuer' => 'bookli-pos',
    'audience' => 'bookli-pos-desktop',
    'timezone' => 'Asia/Beirut',
    'private_key_path' => env('POS_PRIVATE_KEY_PATH', storage_path('app/pos-licensing/private.pem')),
    'public_key_path' => env('POS_PUBLIC_KEY_PATH', storage_path('app/pos-licensing/public.pem')),
    'openssl_config' => base_path('config/pos-openssl.cnf'),
];
