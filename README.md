# Laravel Disposable Email Detection

<div align="center">

[![Packagist License](https://img.shields.io/badge/License-MIT-blue)](https://github.com/eramitgupta/laravel-disposable-email/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-disposable-email?label=Stable)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/erag/laravel-disposable-email)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-disposable-email.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-disposable-email)

</div>

Laravel Disposable Email Detection is a Laravel package for detecting and blocking disposable email addresses during validation and runtime checks. It helps protect registrations, lead forms, and application workflows from temporary inbox abuse.

> **Already contains 124,220+ disposable email domains!**

---

## Installation

```bash
composer require erag/laravel-disposable-email
```

Publish the configuration:

```bash
php artisan erag:install-disposable-email
```

* 🔥 **110,880+ known disposable domains** already included
* 🔄 Daily auto-sync from self-maintained Disposable Email Blocklist repository
* 🧠 **Smart validation rule** for form requests
* 🛡️ **Optional RFC, DNS, spoof, and filter validation** modes
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

https://erag.in/laravel-disposable-email/


## 🔄 Data Sources & Daily Updates

This package uses the official **Disposable Email Blocklist**, a self-developed and actively maintained repository.

### Source

- 📦 Official Blocklist Repository  
  https://github.com/eramitgupta/disposable-email

### Automatic Updates

The blocklist repository is updated **every day** via GitHub Actions to:

- 📥 Fetch the latest disposable email domains
- 🧹 Normalize and clean domain names
- 🔍 Remove duplicates and invalid entries
- 📦 Generate the latest blocklist
- 🚀 Publish changes automatically

This package always uses the latest version of the maintained blocklist.


## ⭐ Support

If you like this package, give it a GitHub star.
