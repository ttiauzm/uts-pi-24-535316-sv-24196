<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aktif untuk path 'api/*' supaya Vue (dijalankan terpisah di laptop,
    | biasanya di http://localhost:5173) boleh melakukan fetch ke Laravel.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Bisa diubah lewat env CORS_ALLOWED_ORIGINS (pisahkan dengan koma).
    // 5173 = Vite dev server, 8080 = frontend di Docker Compose.
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:5173,http://127.0.0.1:5173,http://localhost:8080,http://127.0.0.1:8080'
    ))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
