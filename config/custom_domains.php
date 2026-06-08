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
    | Users create a CNAME on their subdomain pointing to this host.
    | Example: go.brand.com CNAME → cname.yourshortener.com
    |
    | Set this to a stable hostname in production (not localhost).
    | Defaults to the host from APP_URL when unset.
    |
    */

    'cname_target' => env('CUSTOM_DOMAIN_CNAME_TARGET'),

];
