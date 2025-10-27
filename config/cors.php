<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Aqui você define quais rotas terão CORS liberado.
    | Exemplo: ['api/*'] -> todas rotas que começam com /api
    | ['*'] -> todas as rotas
    |
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Métodos HTTP permitidos. Use ['*'] para liberar todos.
    |
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Quais domínios podem consumir sua API. Use ['*'] para liberar todos.
    | Exemplo: ['http://localhost:3000', 'https://meusite.com']
    |
    */
    'allowed_origins' => [
        'https://api-interajai.maisaqui.com.br',
        'https://api-aeroclube.maisaqui.com.br',
        'http://maisaqui1.localhost:8080',
        'http://localhost:3000',
        'http://localhost:8000',
        'https://maisaqui.com.br'
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Você pode usar regex para liberar origens.
    |
    */
    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.localhost:8000$/',
        '/^https:\/\/.*\.maisaqui\.com\.br$/',
        '/^http:\/\/localhost:.*$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Cabeçalhos permitidos. Use ['*'] para liberar todos.
    |
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Cabeçalhos que podem ser expostos para o navegador.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Tempo em segundos que o navegador deve cachear as requisições preflight.
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Se true, permite envio de cookies/autenticação cross-origin.
    |
    */
    'supports_credentials' => false,

];
