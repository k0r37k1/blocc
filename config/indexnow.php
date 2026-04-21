<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IndexNow API key (optional)
    |--------------------------------------------------------------------------
    |
    | When empty, no IndexNow requests are sent. Deployers opt in by placing
    | the verification file at {APP_URL}/{key}.txt and setting this value.
    |
    */
    'key' => env('INDEXNOW_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Key file URL
    |--------------------------------------------------------------------------
    |
    | Full HTTPS URL to the key verification file. When empty, defaults to the
    | scheme + host of the first submitted URL plus "/{key}.txt" so it stays
    | consistent with URLs generated from the current request (not only APP_URL).
    |
    */
    'key_location' => env('INDEXNOW_KEY_LOCATION'),

    /*
    |--------------------------------------------------------------------------
    | API endpoint
    |--------------------------------------------------------------------------
    */
    'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),

];
