<?php

declare(strict_types=1);

return [
    'mailer' => env('MAIL_MAILER', 'smtp'),
    'host' => env('MAIL_HOST', 'premium35.web-hosting.com'),
    'port' => (int) env('MAIL_PORT', 465),
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'username' => env('MAIL_USERNAME', 'no-replaye@casa-blog-immo.com'),
    'password' => env('MAIL_PASSWORD', ''),
    'from_address' => env('MAIL_FROM_ADDRESS', 'no-replaye@casa-blog-immo.com'),
    'from_name' => env('MAIL_FROM_NAME', 'Casa-blog Immo'),
    'verify_peer' => filter_var(env('MAIL_VERIFY_PEER', true), FILTER_VALIDATE_BOOLEAN),
    /** PNG joint en inline (cid:casa-blog-logo) — même icône que l’écran de connexion */
    'inline_brand_image' => env('MAIL_INLINE_BRAND_IMAGE', 'assets/images/brand/icon-casa-immo.png'),
];
