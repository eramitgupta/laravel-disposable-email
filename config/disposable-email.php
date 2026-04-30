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
    | If you'd like to fetch a disposable domain list from a remote location,
    | you can set that URL here and call the update command.
    |
    */
    'remote_url' => [
        'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt',
    ],

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
