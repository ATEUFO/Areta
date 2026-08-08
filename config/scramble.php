<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAPI Specification & Scramble Configuration — Areta API v1
    |--------------------------------------------------------------------------
    */

    'api_path' => 'api/v1',

    'api_domain' => null,

    'info' => [
        'title'       => 'Areta API v1',
        'description' => 'API REST officielle d\'Areta pour le recrutement de répétiteurs à domicile, le suivi pédagogique automatisé et les diagnostics par IA.',
        'version'     => '1.0.0',
    ],

    'servers' => [
        'Production'  => 'https://api.areta.com/v1',
        'Développement' => 'http://localhost:8000/api/v1',
    ],

    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
