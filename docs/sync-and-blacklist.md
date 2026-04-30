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

This command updates application storage. It does not rewrite the package's built-in `Email::domains()` array.

To update the package source array, run:

```bash
php scripts/update-built-in-domains.php
```

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

## 5. Add trusted domains to the whitelist <span class="doc-new-badge">New</span> {#whitelist}

Use the whitelist when a domain should always pass:

```php
'whitelist' => [
    'trusted-test-domain.com',
],
```

Whitelisted domains override the built-in and custom blacklist.

## 6. Block subdomains <span class="doc-new-badge">New</span> {#block-subdomains}

Subdomain blocking is enabled by default:

```php
'block_subdomains' => true,
```

With this option enabled, `mail.tempmail.com` is blocked when `tempmail.com` exists in the disposable list.

## 7. View package stats <span class="doc-new-badge">New</span> {#stats-command}

Use the stats command to inspect the loaded domain lists and package settings:

```bash
php artisan disposable:stats
```

The command shows:

- Built-in domain count
- Custom blacklist domain count
- Total domain count
- Whitelist count
- Remote source count
- Cache status
- Subdomain blocking status
- Last synced file time
