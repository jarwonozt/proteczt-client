<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proteczt Server URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Proteczt license server.
    | Example: https://license.tecnozt.com
    |
    */

    'api_url' => env('PROTECZT_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | Proteczt API Token
    |--------------------------------------------------------------------------
    |
    | The Bearer token issued by your Proteczt server.
    | Generate one via: php artisan token:generate (on the Proteczt server)
    | Keep this secret — never commit it to version control.
    |
    */

    'api_token' => env('PROTECZT_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Application Domain
    |--------------------------------------------------------------------------
    |
    | The domain this application runs on. Leave null to auto-detect from
    | the incoming request. Set explicitly in production for reliability.
    |
    */

    'domain' => env('PROTECZT_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Auto Registration
    |--------------------------------------------------------------------------
    |
    | When true, the package will attempt to register this application with
    | the Proteczt server on first web request (outside of console).
    | A marker file is stored in storage/app/.proteczt_registered.
    |
    */

    'auto_register' => env('PROTECZT_AUTO_REGISTER', true),

    /*
    |--------------------------------------------------------------------------
    | Skip License Check in Local/Testing Environments
    |--------------------------------------------------------------------------
    |
    | When true, license checks are bypassed when APP_ENV is "local" or
    | "testing". Useful for local development without hitting the server.
    |
    */

    'skip_local' => env('PROTECZT_SKIP_LOCAL', true),

    /*
    |--------------------------------------------------------------------------
    | License Status Cache Duration (seconds)
    |--------------------------------------------------------------------------
    |
    | How long the license status should be cached to reduce API calls.
    | Default: 0 (no caching - real-time status check every request).
    | 
    | Set to 0 for immediate license enforcement - when you disable a client
    | on the server, it will be blocked on the very next request.
    |
    | For high-traffic apps, consider setting to 60-300 to reduce API load.
    |
    */

    'cache_duration' => env('PROTECZT_CACHE_DURATION', 0),

    /*
    |--------------------------------------------------------------------------
    | HTTP Request Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Maximum time to wait for a response from the Proteczt server.
    |
    */

    'timeout' => env('PROTECZT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | Whether to verify SSL certificates when connecting to the Proteczt server.
    | Set to false ONLY in local development with self-signed certificates.
    | Always keep true in production.
    |
    */

    'verify_ssl' => env('PROTECZT_VERIFY_SSL', true),

];
