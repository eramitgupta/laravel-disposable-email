# Laravel Disposable Email Address Validator

<div align="center">

[![Packagist License](https://img.shields.io/badge/License-MIT-blue)](https://github.com/eramitgupta/laravel-disposable-email/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-disposable-email?label=Stable)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/erag/laravel-disposable-email)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-disposable-email.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-disposable-email)

<a href="https://trendshift.io/repositories/16587?utm_source=trendshift-badge&amp;utm_medium=badge&amp;utm_campaign=badge-trendshift-16587" target="_blank" rel="noopener noreferrer"><img src="https://trendshift.io/api/badge/trendshift/repositories/16587/daily?language=PHP" alt="eramitgupta/laravel-disposable-email | Trendshift" width="250" height="55"/></a>

[Documentation](https://erag.in/laravel-disposable-email/) · [GitHub](https://github.com/eramitgupta/laravel-disposable-email) · [Packagist](https://packagist.org/packages/erag/laravel-disposable-email)

</div>

Laravel Disposable Email Address Validator helps you detect and block temporary, disposable, and throwaway email addresses in your Laravel applications.

Whether you're building a registration form, SaaS application, or lead generation system, the package helps prevent disposable email addresses from being used where genuine email addresses are needed.

It works with Laravel's built-in validation system, supports runtime checks, and uses a regularly maintained domain blocklist without requiring an external email validation API.

> **124,220+ known disposable email domains included**, with daily updates to the maintained blocklist.

---

## Installation

Install the package using Composer:

```bash
composer require erag/laravel-disposable-email
```

Run the installation command:

```bash
php artisan erag:install-disposable-email
```

For validation examples and configuration options, check the [official documentation](https://erag.in/laravel-disposable-email/).

## Features

- 🔥 **124,220+ known disposable domains** already included
- 🔄 **Daily blocklist updates** through a self-maintained repository
- 🧠 **Laravel validation rule** for form requests
- 🛡️ **Optional RFC, DNS, spoof, and filter validation** modes
- ⚙️ **Runtime email checking** using a helper or facade
- 🧩 **Blade directive** support for conditionals
- 🌐 **Sync support** for remote domain lists
- 📝 **Custom blacklist** to block additional domains
- ✅ **Whitelist support** to allow trusted domains
- 🧱 **Subdomain detection** for blocked parent domains
- 🔎 **Detailed runtime results** using `Disposable::check()`
- 📊 **Domain statistics** using `php artisan disposable:stats`
- 🧠 **Optional caching** to improve performance
- ⚡ **Simple setup** with a publishable configuration
- ✅ **Compatible with Laravel 10, 11, 12, and 13**

---

## How It Works

The package checks the domain part of an email address against its disposable email blocklist.

For example, if `temp-mail.example` is included in the blocklist, all these addresses will be detected as disposable:

```text
john@temp-mail.example
hello@temp-mail.example
random123@temp-mail.example
```

The username before `@` doesn't matter. If the domain is on the blocklist, the email will be detected.

### No External API Required

Disposable email detection uses a locally maintained domain list, so your application doesn't need to send email addresses to an external API for every check.

This means:

- No API keys or third-party validation accounts
- No per-request API charges
- No network request for local blocklist lookups
- No external API dependency during disposable-domain checks

Optional validation modes, such as DNS checks, may still perform network operations.

---

## Official Documentation

Complete documentation for installation, configuration, validation rules, runtime checks, syncing, caching, and troubleshooting is available here:

**https://erag.in/laravel-disposable-email/**

---

## 🔄 Data Sources & Daily Updates

This package uses the **Disposable Email Blocklist**, a separate open-source repository that I maintain and update regularly.

### Official Blocklist Repository

📦 https://github.com/eramitgupta/disposable-email

### Automatic Updates

GitHub Actions runs daily to keep the blocklist up to date.

The update process:

- 📥 Fetches disposable email domains from maintained sources
- 🧹 Normalizes and cleans domain names
- 🔍 Filters duplicate and invalid entries
- 📦 Updates the maintained domain blocklist
- 🚀 Automatically commits changes when updates are available

The goal is to keep the list current as new disposable email services appear.

### Sync Your Local Blocklist

To manually sync the latest available disposable email domains into your Laravel application, run:

```bash
php artisan erag:sync-disposable-email-list
```

The sync process adds newly discovered domains without removing existing domains from your local blocklist.

The upstream repository receives daily updates. Your Laravel application can receive those updates through local synchronization.

---

## Where Can You Use It?

The package can be useful anywhere your application collects email addresses.

Some common examples include:

- **User registration:** Reduce fake signups using disposable inboxes.
- **SaaS applications:** Help limit free trial abuse.
- **Contact forms:** Filter submissions using temporary email addresses.
- **Lead generation:** Reduce low-quality leads.
- **Newsletter subscriptions:** Prevent signups from known disposable domains.
- **APIs:** Validate incoming email addresses before processing requests.

Disposable email detection won't prevent every type of spam or fake registration, but it adds another layer of protection alongside email verification and rate limiting.

---

## Contributing

Contributions are welcome.

If you find a bug, have a feature suggestion, or notice something that could be improved, feel free to open an issue or submit a pull request.

- [Report an Issue](https://github.com/eramitgupta/laravel-disposable-email/issues)
- [Contribute to the Laravel Package](https://github.com/eramitgupta/laravel-disposable-email)
- [Contribute to the Disposable Email Blocklist](https://github.com/eramitgupta/disposable-email)

If you discover a missing disposable email domain, you can also report it in the blocklist repository.

---

## ⭐ Support

I maintain this package as an open-source project and continue working on improving its accuracy, performance, and developer experience.

If it helps your project, consider giving it a [GitHub star](https://github.com/eramitgupta/laravel-disposable-email).

It helps other Laravel developers discover the package.

---

## License

This package is open-source software licensed under the [MIT License](https://github.com/eramitgupta/laravel-disposable-email/blob/main/LICENSE).
