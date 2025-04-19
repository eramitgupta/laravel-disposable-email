# Laravel Disposable Email Detection


<center>
    <img width="956" alt="Screenshot 2024-10-04 at 10 34 23 PM" src="https://github.com/user-attachments/assets/2b187de0-d5cc-4871-9c5d-ce0ffbb5a26c">
</center>
<div align="center">

[![Packagist License](https://img.shields.io/badge/Licence-MIT-blue)](https://github.com/eramitgupta/laravel-disposable-email/blob/main/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/erag/laravel-disposable-email?label=Stable)](https://packagist.org/packages/erag/laravel-disposable-email)
[![Total Downloads](https://img.shields.io/packagist/dt/erag/laravel-disposable-email.svg?label=Downloads)](https://packagist.org/packages/erag/laravel-disposable-email)

</div>


A Laravel package to detect and block disposable (temporary) email addresses during validation or runtime logic.

> **Already contains 106,580+ disposable email domains!** 🔥

---


## 🚀 Installation

```bash
composer require erag/laravel-disposable-email
```

---

## 🛠 Configuration

Publish the config file:

```bash
 php artisan erag:install-disposable-email  
```

This will create `config/disposable-email.php`.

---

## ⚙ Usage

### 1. **Form Request Validation**

#### ✅ Custom Rule:
```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

$request->validate([
    'email' => ['required', 'email', new DisposableEmailRule()],
]);
```

#### ✅ String-based Rule:
```php
$request->validate([
    'email' => 'required|email|disposable_email',
]);
```

---

### 2. **Direct Runtime Check**
```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

if (DisposableEmailRule::isDisposable('test@tempmail.com')) {
    // Do something if email is disposable
}
```

Or via facade:
```php
use DisposableEmail;

if (DisposableEmail::isDisposable('test@tempmail.com')) {
    // Do something
}
```

---

### 3. **Blade Directive**

```blade
@disposableEmail('amit@0-mail.com')
    <p class="text-red-600">Disposable email detected!</p>
@else
    <p class="text-green-600">Valid email.</p>
@enddisposableEmail
```

---

## 📦 Features

- 106,580+ known disposable domains out-of-the-box
- Uses latest community sources
- Supports auto-syncing from remote URLs
- Custom validation rule
- Facade and Blade support

---

## 🔄 Sync From Remote (Optional)

Update the list manually
```bash
php artisan erag:sync-disposable-email-list
```

## 🔗 Config Options (config/disposable-email.php)

```php
return [
    'blacklist_file' => storage_path('app/blacklist_file/disposable_domains.txt'),

    'remote_url' => [
        'https://raw.githubusercontent.com/disposable/disposable-email-domains/master/domains.txt',
        'https://raw.githubusercontent.com/7c/fakefilter/refs/heads/main/txt/data.txt',
    ],
];
```

> ✅ **Note:** The `.txt` files from `remote_url` must follow this format:  
> Each line should contain **only a domain name**, like:

```
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
00082cc.com
00082dd.com
00082ss.com
```

If the file contains anything other than plain domains (like comments or extra data), it may cause parsing issues.
