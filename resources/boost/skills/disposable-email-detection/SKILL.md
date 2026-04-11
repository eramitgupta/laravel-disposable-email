---
name: disposable-email-detection
description: Build and work with laravel-disposable-email validation, runtime checks, Blade conditionals, config, sync commands, and custom blacklist workflows. Use when adding or updating disposable email validation in Laravel forms, services, middleware, controllers, Blade views, or scheduled tasks.
---

# Laravel Disposable Email Detection

Use this skill when a task involves this package's validation rule, facade, Blade directive, install command, sync command, or config.

## Read First

Read `reference.md` in this folder before making changes. It contains the package API, conventions, and implementation examples that match the package README and source.

## Working Rules

- Prefer the built-in validation rule name `disposable_email` for standard request validation.
- Use `EragLaravelDisposableEmail\Rules\DisposableEmailRule` when an explicit rule object is clearer.
- Use `EragLaravelDisposableEmail\Support\Email` when the task is about package internals or shared support logic.
- Use `DisposableEmailRule::isDisposable($email)` or the `DisposableEmail` facade for runtime checks.
- Use the `@disposableEmail(...)` Blade conditional for view-only branching.
- Use `php artisan erag:install-disposable-email` to publish config before instructing users to edit `config/disposable-email.php`.
- Use `php artisan erag:sync-disposable-email-list` when the task is about refreshing remote domain lists.
- Put custom domains in the configured blacklist directory as plain domains, one per line.
- If caching is enabled, remember cache invalidation when domain sources change.

## Implementation Notes

- The package registers the string validation rule as `disposable_email`.
- The Blade conditional name is `disposableEmail`.
- The config file is `config/disposable-email.php`.
- The default blacklist directory is `storage/app/blacklist_file`.
- The package accepts plain domains and also strips `user@domain.tld` entries down to their domain when loading local text files.

## Output Expectations

- Show package-native examples first.
- Keep examples in Laravel style.
- When documenting setup, mention the exact Artisan commands exposed by the package.
