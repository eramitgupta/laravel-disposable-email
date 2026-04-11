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
