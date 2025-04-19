<?php

// config/disposable-email.php

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
    'blacklist_file' => storage_path('app/blacklist_file/disposable_domains.txt'),

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
];
