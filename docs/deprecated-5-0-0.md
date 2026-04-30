---
title: Deprecated in 5.0.0
description: Deprecated and removed API notes for Laravel Disposable Email 5.0.0.
head:
  - - meta
    - name: keywords
      content: laravel disposable email 5.0.0 deprecated, DisposableEmail removed, Disposable facade migration
---

# Deprecated in 5.0.0

Version `5.0.0` moves runtime checks to the shorter `Disposable` facade API.

## What changed

The old `DisposableEmail` facade has been removed from package discovery.

```php
use DisposableEmail;

DisposableEmail::isDisposable('test@tempmail.com');
```

Use `Disposable` instead:

```php
use Disposable;

Disposable::Email('test@tempmail.com');
Disposable::domain('test@tempmail.com');
```

Or import the package facade by namespace:

```php
use EragLaravelDisposableEmail\Facades\Disposable;

Disposable::Email('test@tempmail.com');
Disposable::domain('test@tempmail.com');
```

## Removed public facade

These facade entry points are no longer documented or registered:

```php
DisposableEmail::isDisposable($email);
```

The package now registers this alias:

```php
Disposable::Email($email);
Disposable::domain($emailOrDomain);
```

The same methods are available through the namespaced facade:

```php
EragLaravelDisposableEmail\Facades\Disposable::Email($email);
EragLaravelDisposableEmail\Facades\Disposable::domain($emailOrDomain);
```

## Migration

Replace old runtime checks:

```php
use DisposableEmail;

if (DisposableEmail::isDisposable($email)) {
    // Handle disposable email
}
```

With:

```php
use Disposable;

if (Disposable::Email($email)) {
    // Handle disposable email
}
```

Replace API responses:

```php
'disposable' => DisposableEmail::isDisposable($email),
```

With:

```php
'disposable' => Disposable::Email($email),
```

## Domain checks

Use `Disposable::domain()` when you want to check a domain directly or extract the domain from an email address:

```php
Disposable::domain('tempmail.com');
Disposable::domain('test@tempmail.com');
```

Both calls check the same domain.

## Validation is unchanged

Validation rules still work the same way:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

Class-based validation also remains available:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

$request->validate([
    'email' => ['required', 'email', new DisposableEmailRule()],
]);
```

## Blade is unchanged

The Blade conditional still works:

```blade
@disposableEmail($email)
    <p>Disposable email detected.</p>
@else
    <p>Email looks good.</p>
@enddisposableEmail
```

## Composer alias

Laravel package discovery now exposes only the `Disposable` facade alias:

```json
{
  "extra": {
    "laravel": {
      "aliases": {
        "Disposable": "EragLaravelDisposableEmail\\Facades\\Disposable"
      }
    }
  }
}
```
