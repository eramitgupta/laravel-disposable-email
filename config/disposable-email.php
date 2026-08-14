<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local Domain Blacklist File
    |--------------------------------------------------------------------------
    |
    | This file will store the list of disposable email domains. You can update
    | it manually or via the `erag:sync-disposable-email-list` artisan command.
    |
    */
    'blacklist_file' => storage_path('app/blacklist_file'),

    /*
    |--------------------------------------------------------------------------
    | Remote Source URL (Optional)
    |--------------------------------------------------------------------------
    |
    | The package includes a built-in domain list. Add one or more URLs here
    | when you want to fetch additional or updated lists manually.
    |
    | Documentation: https://erag.in/laravel-disposable-email/
    |
    */
    'remote_url' => [],

    /*
    |--------------------------------------------------------------------------
    | Sync Request Timeout
    |--------------------------------------------------------------------------
    |
    | Set the timeout, in seconds, for HTTP requests made by the
    | `erag:sync-disposable-email-list` artisan command.
    |
    */
    'sync_timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    |
    | Add domains here when you want to force them to pass even if they appear
    | in the built-in or synced disposable domain lists.
    |
    */
    'whitelist' => [
        // 'example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Block Subdomains
    |--------------------------------------------------------------------------
    |
    | When enabled, a blocked parent domain also blocks its subdomains.
    | For example, "mail.tempmail.com" is blocked when "tempmail.com" exists.
    |
    */
    'block_subdomains' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable or Disable Caching
    |--------------------------------------------------------------------------
    |
    | Set to true to enable caching of the disposable email list.
    | Set too false to disable caching completely.
    | Default is disable
    */
    'cache_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Cache Time To Live (seconds)
    |--------------------------------------------------------------------------
    |
    | Set a custom time to live for the cached disposable email list.
    | This value is in seconds. Default is 60 (1 minute).
    |
    */
    'cache_ttl' => 60,
];
