<?php

declare(strict_types=1);

return [
    'driver' => env('TRANSLATION_DRIVER', 'libretranslate'),
    'cache_path' => base_path('storage/cache/translations'),
    'timeout' => (int) env('TRANSLATION_TIMEOUT', 8),
    'libretranslate' => [
        'url' => rtrim(env('LIBRETRANSLATE_URL', 'https://libretranslate.com'), '/'),
        'api_key' => env('LIBRETRANSLATE_API_KEY', ''),
    ],
    'mymemory' => [
        'email' => env('MYMEMORY_EMAIL', ''),
    ],
];
