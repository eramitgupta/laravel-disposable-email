---
title: Installation
description: Install Laravel Disposable Email, register the service provider when needed, and publish the package configuration.
head:
  - - meta
    - name: keywords
      content: laravel disposable email installation, install laravel disposable email, composer require erag laravel disposable email, laravel package setup
---

# Installation

Laravel Disposable Email is quick to install and easy to add to an existing Laravel app.

## Install the package

```bash
composer require erag/laravel-disposable-email
```

## Service provider registration

### Laravel 11, 12, and 13

Laravel package discovery should normally handle registration. If you want to register it manually, add the provider to `bootstrap/providers.php`:

```php
use EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider;

return [
    // ...
    LaravelDisposableEmailServiceProvider::class,
];
```

### Laravel 10

If you need manual registration on Laravel 10, add the provider to `config/app.php`:

```php
'providers' => [
    // ...
    EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider::class,
],
```

## Publish the configuration

```bash
php artisan erag:install-disposable-email
```

This command publishes `config/disposable-email.php`, clears the package cache, and gets the package ready for custom domain management.
