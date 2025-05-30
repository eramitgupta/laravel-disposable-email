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
        'https://raw.githubusercontent.com/disposable/disposable-email-domains/master/domains.txt',
        'https://raw.githubusercontent.com/7c/fakefilter/refs/heads/main/txt/data.txt',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable or Disable Caching
    |--------------------------------------------------------------------------
    |
    | Set to true to enable caching of the disposable email list.
    | Set to false to disable caching completely.
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
