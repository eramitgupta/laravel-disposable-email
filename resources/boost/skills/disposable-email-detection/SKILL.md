---
name: disposable-email-detection
description: Add and maintain laravel-disposable-email validation, runtime checks, sync flows, scheduling, caching, troubleshooting, and Blade conditionals in Laravel applications. Use when working on forms, Form Requests, APIs, services, middleware, jobs, or custom blacklist workflows.
---

# Laravel Disposable Email Detection

Use this skill when a task involves the package's validation rule, rule object, facade, Blade conditional, install command, sync command, scheduler setup, config, caching, or blacklist files.

## Read First

Read `reference.md` in this folder before making changes. It mirrors the current package docs and keeps examples aligned with the package API and Laravel usage patterns.

## Working Rules

- Prefer the built-in validation rule name `disposable_email` for standard request validation in controllers, Form Requests, APIs, and manual validators.
- Use `LaravelDisposableEmail\Rules\DisposableEmail` when an explicit rule object is clearer.
- Use the `Disposable` facade for runtime checks, detailed checks, domain checks, and facade-style validation rules.
- Use `@disposableEmail(...)` only for Blade branching, not as a replacement for request validation.
- Use `php artisan erag:install-disposable-email` before instructing users to edit `config/disposable-email.php`.
- Use `php artisan erag:sync-disposable-email-list` when the task is about refreshing remote domain lists from configured sources.
- Use `LaravelDisposableEmail\Data\BuiltInDomains::all()` when inspecting the built-in domain source.
- Use `php artisan disposable:stats` when the task is about inspecting loaded domains, whitelist count, cache status, remote source count, or last synced time.
- Treat `config('disposable-email.remote_url')` as the source of truth for sync inputs.
- Treat `config('disposable-email.whitelist')` as the source of truth for trusted domains that should always pass.
- Treat `config('disposable-email.block_subdomains')` as the source of truth for parent-domain subdomain blocking.
- Put custom domains in the configured blacklist directory as plain domains, one per line, in `.txt` files.
- Mention scheduling separately when the user wants automatic syncs. Use Laravel's scheduler with `erag:sync-disposable-email-list`.
- Mention caching separately when the user wants repeated lookups optimized or config changes reflected.
- If caching is enabled, include cache clearing as part of troubleshooting and rollout steps.

## Implementation Notes

- The package registers the string validation rule as `disposable_email`.
- The Blade conditional name is `disposableEmail`.
- The config file is `config/disposable-email.php`.
- The default blacklist directory is `storage/app/blacklist_file`.
- Remote sync sources are configured through `remote_url`.
- Built-in domains are stored in `src/Data/BuiltInDomains.php` and exposed through `BuiltInDomains::all()`.
- `DomainLoader` loads built-in and configured custom domains, while `DomainMatcher` handles exact and subdomain matching.
- `DomainChecker` implements the `Checker` contract and returns the `Support\Result` object.
- `Contracts\Loader`, `Contracts\Matcher`, and `Contracts\Checker` can be rebound to application-specific implementations.
- Trusted domains are configured through `whitelist`.
- Subdomain matching is configured through `block_subdomains`.
- The package reads every `.txt` file in the configured blacklist directory.
- The package accepts plain domains and also normalizes `user@domain.tld` style entries down to their domain when loading local text files.
- `Disposable::email($email)` returns a boolean for email checks. `Disposable::Email($email)` also works as a case-insensitive alias.
- `Disposable::domain($domainOrEmail)` returns a boolean for domain checks.
- `Disposable::check($domainOrEmail)` returns a detailed result object with `disposable()`, `domain()`, `matchedDomain()`, `source()`, `whitelisted()`, and `toArray()`. `Disposable::Check($domainOrEmail)` also works as a case-insensitive alias.
- `Disposable::rule()` and `Disposable::make()` return a validation rule instance.

## Output Expectations

- Start with the simplest Laravel-native example, then move to more advanced usage only if needed.
- Keep examples in Laravel style and match current docs terminology: Installation, Configuration, Validation and Runtime, Sync and Blacklist, Schedule Sync, Caching, Troubleshooting.
- When documenting setup, mention the exact Artisan commands exposed by the package.
- If the task is about a bug or package behavior, include the fastest troubleshooting step first.
