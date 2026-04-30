# Laravel Disposable Email Detection

<div align="center">

[![Packagist License](https://img.shields.io/badge/License-MIT-blue)](https://github.com/eramitgupta/laravel-disposable-email/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-disposable-email?label=Stable)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-disposable-email.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-disposable-email)

</div>

Laravel Disposable Email Detection is a Laravel package for detecting and blocking disposable email addresses during validation and runtime checks. It helps protect registrations, lead forms, and application workflows from temporary inbox abuse.

> **Already contains 110,646+ disposable email domains!** 🔥
---

## Quick runtime check

```php
use Disposable;

if (Disposable::Email('test@tempmail.com')) {
    // Handle disposable email
}

if (Disposable::domain('test@tempmail.com')) {
    // Handle disposable domain
}

$result = Disposable::check('test@tempmail.com');

$result->disposable(); // true
$result->domain(); // tempmail.com
$result->source(); // built-in, custom, or whitelist
```

## ✅ Features

* 🔥 **110,646+ known disposable domains** already included
* 🧠 **Smart validation rule** for form requests
* ⚙️ **Runtime email checking** via helper and facade
* 🧩 **Blade directive** support for conditionals
* 🌐 **Auto-sync with remote domain lists**
* 📝 **Add your own custom blacklist** with ease
* ✅ **Allow trusted domains** with a whitelist
* 🧱 **Block subdomains** of disposable parent domains
* 🔎 **Detailed runtime results** via `Disposable::check()`
* 📊 **Domain stats command** via `php artisan disposable:stats`
* 🧠 **Optional caching** for performance
* ⚡️ **Zero-configuration setup** with publishable config
* ✅ **Compatible with Laravel 10, 11, 12, and 13**
---

## Official Documentation

Complete documentation for installation, configuration, validation, syncing, caching, and troubleshooting is available at:

https://eramitgupta.github.io/laravel-disposable-email/

Deprecated and removed API notes for version `4.1.1`:

https://eramitgupta.github.io/laravel-disposable-email/deprecated-4-1-1

Maintainer script for updating the built-in `Email::domains()` array:

```bash
php scripts/update-built-in-domains.php
```
