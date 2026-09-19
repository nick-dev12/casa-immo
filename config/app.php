<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Casa Immo'),
    'tagline' => env('APP_TAGLINE', 'Trouvez votre bien, en toute confiance'),
    'region' => env('APP_REGION', 'Casamance'),
    'default_city' => env('APP_DEFAULT_CITY', 'Ziguinchor'),
    'country' => env('APP_COUNTRY', 'Sénégal'),
    'env' => env('APP_ENV', 'production'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim(env('APP_URL', 'http://localhost'), '/'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'fr'),
    'csrf_token_name' => env('CSRF_TOKEN_NAME', '_token'),
    'session_lifetime' => (int) env('SESSION_LIFETIME', 7200),
    'session_name' => env('SESSION_NAME', 'casa_immo_session'),
    'property_images_min' => 4,
    'property_images_max' => 10,
    'property_video_max_mb' => 50,
    'image_webp_quality' => 82,
    'image_max_dimension' => 2400,
    'invoice' => [
        'company_name' => env('INVOICE_COMPANY', env('APP_NAME', 'Casa Immo')),
        'address' => env('INVOICE_ADDRESS', 'Avenue Casamance'),
        'city' => env('INVOICE_CITY', env('APP_DEFAULT_CITY', 'Ziguinchor')),
        'email' => env('INVOICE_EMAIL', 'contact@casaimmo.sn'),
        'phone' => env('INVOICE_PHONE', '+221 33 000 00 00'),
    ],
];
