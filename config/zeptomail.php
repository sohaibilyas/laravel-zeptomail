<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZeptoMail API Token
    |--------------------------------------------------------------------------
    |
    | This token is used as the value for ZeptoMail's Authorization header. You
    | may provide either the raw token or the full "Zoho-enczapikey ..." value.
    |
    */

    'token' => env('ZEPTOMAIL_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | ZeptoMail API Endpoint
    |--------------------------------------------------------------------------
    |
    | The default endpoint matches ZeptoMail's REST API host and version. Set a
    | custom endpoint only when Zoho gives your account a different API URL.
    |
    */

    'host' => env('ZEPTOMAIL_HOST', 'api.zeptomail.com'),

    'version' => env('ZEPTOMAIL_API_VERSION', 'v1.1'),

    'endpoint' => env('ZEPTOMAIL_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | Request Options
    |--------------------------------------------------------------------------
    */

    'timeout' => env('ZEPTOMAIL_TIMEOUT', 30),
];
