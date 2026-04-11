---
title: Configuration
description: Configure blacklist paths, remote domain sources, and caching options for Laravel Disposable Email.
head:
  - - meta
    - name: keywords
      content: laravel disposable email configuration, disposable email config laravel, laravel blacklist config, remote domain list config
---

# Configuration

The configuration file gives you control over where domains are loaded from, where custom blacklist files live, and whether domain lookups should be cached.

## Default configuration

```php
return [
    'blacklist_file' => storage_path('app/blacklist_file'),

    'remote_url' => [
       'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt',
    ],

    'cache_enabled' => false,
    'cache_ttl' => 60,
];
```

## Configuration options

### `blacklist_file`

This is the directory where your custom blacklist files live. The package reads every `.txt` file inside it.

### `remote_url`

This is the list of remote files the package will pull when you run the sync command.

### `cache_enabled`

This enables caching for the compiled disposable domain list.

### `cache_ttl`

This sets the cache lifetime in minutes.

## Example configuration

```php
return [
    'blacklist_file' => storage_path('app/disposable-domains'),

    'remote_url' => [
        'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt',
        'https://example.com/internal-disposable-domains.txt',
    ],

    'cache_enabled' => true,
    'cache_ttl' => 120,
];
```

## Remote file format

Each remote file must contain one domain per line:

```text
0-00.usa.cc
0-30-24.com
0-attorney.com
0-mail.com
00-tv.com
00.msk.ru
00.pe
00000000000.pro
000728.xyz
000777.info
```

::: warning
Do not include comments, metadata, or extra text in these files. Each line should contain only a domain name.
:::
