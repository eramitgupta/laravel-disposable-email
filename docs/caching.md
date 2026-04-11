---
title: Caching
description: Enable and manage caching for Laravel Disposable Email to improve repeated domain lookup performance.
head:
  - - meta
    - name: keywords
      content: laravel disposable email caching, cache disposable domains laravel, laravel email validation cache, cache ttl disposable email
---

# Caching

This page covers the caching setup for the package.

## 1. Enable caching

Update `config/disposable-email.php`:

```php
'cache_enabled' => true,
'cache_ttl' => 60,
```

## 2. Choose a cache lifetime

Set `cache_ttl` to the number of minutes you want the compiled domain list to stay cached.

Example:

```php
'cache_ttl' => 120,
```

Use a shorter value if you want faster refreshes, or a longer value if you want fewer rebuilds.

## 3. Use a full config example

```php
return [
    'blacklist_file' => storage_path('app/blacklist_file'),
    'remote_url' => [
        'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt',
    ],
    'cache_enabled' => true,
    'cache_ttl' => 60,
];
```

## 4. Clear the cache when needed

If you update domain files manually and want a fresh reload, clear Laravel's cache:

```bash
php artisan cache:clear
```

## 5. Know when to use caching

Caching is a good fit when your application checks email addresses often, such as:

- signup forms
- API endpoints
- background jobs
- admin review tools
