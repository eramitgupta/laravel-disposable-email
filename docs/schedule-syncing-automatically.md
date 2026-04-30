---
title: Schedule Syncing Automatically
description: Schedule automatic disposable domain syncing in Laravel using the package sync command and Laravel scheduler.
head:
  - - meta
    - name: keywords
      content: laravel schedule disposable email sync, laravel scheduler disposable domains, auto sync disposable email list laravel, schedule sync command laravel
---

# Schedule Syncing Automatically

If you want the disposable domain list to stay updated without running the sync command manually, you can schedule it in Laravel.

## 1. Open `routes/console.php`

Add your scheduled command where your Laravel console schedule is defined.

## 2. Add the sync command

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('erag:sync-disposable-email-list')->daily();
```

## 3. Choose the schedule you want

You are not limited to `daily()`. You can choose whatever timing fits your application.

Examples:

```php
Schedule::command('erag:sync-disposable-email-list')->hourly();

Schedule::command('erag:sync-disposable-email-list')->daily();

Schedule::command('erag:sync-disposable-email-list')->weekly();
```

## 4. Make sure Laravel scheduling is running

Your server or local environment still needs Laravel's scheduler to run normally.

If Laravel scheduling is not running, the sync command will never execute automatically.

## 5. When this is useful

Automatic syncing is useful when:

- you want fresh disposable domains without manual updates
- your app depends on current domain data
- you do not want to rely only on package version updates

## 6. Update the built-in package array daily

The package repository also includes a maintainer script that updates the built-in `Email::domains()` array from:

```text
https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt
```

Run it from the package root:

```bash
php scripts/update-built-in-domains.php
```

The script:

- fetches the remote domain list
- normalizes email-style entries to domains
- removes invalid lines
- deduplicates and sorts domains
- rewrites `src/Support/Email.php`

This is for maintaining the package source array. For Laravel applications, use `php artisan erag:sync-disposable-email-list` to sync domains into storage.

## 7. GitHub Actions daily update

The repository includes a daily workflow:

```text
.github/workflows/update-built-in-domains.yml
```

It runs every day at `02:00 UTC`, updates `src/Support/Email.php`, and commits only when the built-in domain array changes.
