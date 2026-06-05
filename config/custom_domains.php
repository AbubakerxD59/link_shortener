<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Branded short-link domain scheme
    |--------------------------------------------------------------------------
    |
    | Short URLs on a verified custom domain use this scheme (https recommended).
    |
    */

    'scheme' => env('CUSTOM_DOMAIN_SCHEME', 'https'),

    /*
    |--------------------------------------------------------------------------
    | CNAME target hostname
    |--------------------------------------------------------------------------
    |
    | Users point their branded subdomain CNAME to this host (defaults to APP_URL).
    | Example: go.brand.com CNAME → short.example.com
    |
    */

    'cname_target' => env('CUSTOM_DOMAIN_CNAME_TARGET'),

    /*
    |--------------------------------------------------------------------------
    | DNS verification TXT prefix
    |--------------------------------------------------------------------------
    */

    'verification_prefix' => '_shrtlnk-verify',

];
