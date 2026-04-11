---
title: Sync and Blacklist
description: Sync disposable email domains from remote sources and manage custom blacklist files in Laravel Disposable Email.
head:
  - - meta
    - name: keywords
      content: laravel disposable email sync, laravel disposable blacklist, sync disposable domains laravel, custom blacklist domains laravel
---

# Sync and Blacklist

This page covers the two ways to extend the package after installation:

1. Sync the disposable domain list from a remote source
2. Add your own blacklist domains manually

## 1. Sync from a remote source

Run the sync command:

```bash
php artisan erag:sync-disposable-email-list
```

When the command runs, it:

1. Clears the package cache
2. Reads the URLs from `config('disposable-email.remote_url')`
3. Normalizes the remote response into a clean domain list
4. Stores the updated list in your configured blacklist directory

## 2. Add your own blacklist file

Create or edit this file:

```text
storage/app/blacklist_file/disposable_domains.txt
```

Add one domain per line:

```text
abakiss.com
fakemail.org
trashbox.io
```

## 3. Use multiple blacklist files if needed

You are not limited to a single file. The package reads every `.txt` file in the configured blacklist directory.

For example:

```text
storage/app/blacklist_file/marketing-abuse-domains.txt
```

```text
campaign-throwaway.example
promo-abuse-mail.example
referral-fraud-mail.example
```

This makes it easier to separate custom lists by use case.

## 4. Follow the blacklist file rules

Your custom blacklist file should follow these rules:

| Rule | Requirement |
| --- | --- |
| File location | Must match the configured `blacklist_file` path |
| File format | Plain text |
| File content | One domain per line |
| File extension | `.txt` |

If the file path does not match your configuration, the package will not load your custom domains.
