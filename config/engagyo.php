<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Use Engagyo shared database
    |--------------------------------------------------------------------------
    |
    | When true, short links are read/written from Engagyo's `short_links`
    | table using the `engagyo` database connection (see config/database.php).
    |
    */
    'use_shared_database' => env('ENGAGYO_USE_SHARED_DB', false),

    'database_connection' => env('ENGAGYO_DB_CONNECTION', 'engagyo'),

    /*
    |--------------------------------------------------------------------------
    | Engagyo main app URL (for docs / future API integration)
    |--------------------------------------------------------------------------
    */
    'app_url' => env('ENGAGYO_APP_URL', 'http://localhost'),

];
