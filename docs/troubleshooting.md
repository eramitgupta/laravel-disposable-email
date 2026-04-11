---
title: Troubleshooting
description: Troubleshoot validation, syncing, cache, blacklist, and service provider issues in Laravel Disposable Email.
head:
  - - meta
    - name: keywords
      content: laravel disposable email troubleshooting, disposable email validation issue laravel, sync issue disposable domains, laravel blacklist problem
---

# Troubleshooting

This page focuses on common problems you might run into while using the package and how to fix them.

## 1. The validation rule is not working

If `disposable_email` is not rejecting disposable addresses:

- Make sure the field also passes Laravel's `email` rule
- Make sure the rule is spelled exactly as `disposable_email`
- Make sure the package is installed correctly
- Make sure your request is actually reaching the validation logic

Example:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

## 2. A disposable email is passing validation

If a disposable address is not being blocked:

- Check whether the domain exists in the built-in list or your custom blacklist
- Make sure the email contains a real domain after `@`
- Clear the cache if caching is enabled
- Sync the remote domain list again if you depend on updated sources

Useful commands:

```bash
php artisan cache:clear
php artisan erag:sync-disposable-email-list
```

## 3. Custom blacklist domains are not loading

If your own domains are being ignored:

- Make sure the file is inside the configured `blacklist_file` directory
- Make sure the file extension is `.txt`
- Make sure each line contains only one domain
- Do not add comments, extra text, or invalid characters

Correct example:

```text
abakiss.com
fakemail.org
trashbox.io
```

## 4. The sync command does not update domains

If syncing does not seem to work:

- Check the URLs in `config/disposable-email.php`
- Make sure the remote source returns a valid domain list
- Make sure your application can reach the remote source
- Run the sync command again after updating the config

Command:

```bash
php artisan erag:sync-disposable-email-list
```

## 5. Cache changes are not reflected

If you change domain files but the old result still appears:

- Clear Laravel's cache
- Confirm `cache_enabled` is set the way you expect
- Lower `cache_ttl` if you want faster refreshes

Command:

```bash
php artisan cache:clear
```

## 6. The service provider does not seem to load

If the package is not behaving as expected in older Laravel projects:

- Check whether package discovery is working
- Register the service provider manually if needed

Laravel 11, 12, and 13 manual registration:

```php
use EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider;

return [
    // ...
    LaravelDisposableEmailServiceProvider::class,
];
```

Laravel 10 manual registration:

```php
'providers' => [
    // ...
    EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider::class,
],
```

## 7. Blade checks do not behave as expected

If the Blade directive output looks wrong:

- Make sure the value passed to `@disposableEmail(...)` is a full email address
- Make sure the variable is not empty
- Confirm the domain is valid before expecting a positive match

Example:

```blade
@disposableEmail($email)
    <p>Disposable email detected.</p>
@else
    <p>Valid email.</p>
@enddisposableEmail
```

## 8. Local validation demo or live checks fail in docs

If the docs demo shows a temporary validation issue:

- Refresh the page
- Confirm the local docs assets were built correctly
- Make sure the generated JSON asset exists in the docs public directory

## 9. You want to verify a domain manually

If you want to confirm a result in your own code, use a direct runtime check:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

$isDisposable = DisposableEmailRule::isDisposable('test@tempmail.com');
```

This is often the fastest way to confirm whether the package is seeing the domain the way you expect.
