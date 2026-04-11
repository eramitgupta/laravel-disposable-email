# Reference

## Package Surface

- Validation rule string: `disposable_email`
- Rule class: `EragLaravelDisposableEmail\Rules\DisposableEmailRule`
- Support helper: `EragLaravelDisposableEmail\Support\Email`
- Facade alias: `DisposableEmail`
- Blade conditional: `@disposableEmail(...)`
- Install command: `php artisan erag:install-disposable-email`
- Sync command: `php artisan erag:sync-disposable-email-list`
- Config file: `config/disposable-email.php`

## Installation

```bash
composer require erag/laravel-disposable-email
php artisan erag:install-disposable-email
```

Laravel 11, 12, and 13 can register the service provider in `bootstrap/providers.php` if needed:

```php
use EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider;

return [
    // ...
    LaravelDisposableEmailServiceProvider::class,
];
```

Laravel 10 can register it in `config/app.php`:

```php
'providers' => [
    // ...
    EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider::class,
],
```

## Validation Examples

String rule:

```php
$request->validate([
    'email' => 'required|email|disposable_email',
]);
```

Array rule:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

Explicit rule object:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

$request->validate([
    'email' => ['required', 'email', new DisposableEmailRule()],
]);
```

Form request rule set:

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email', 'disposable_email'],
    ];
}
```

## Runtime Examples

Static check:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

if (DisposableEmailRule::isDisposable($email)) {
    abort(422, 'Disposable email addresses are not allowed.');
}
```

Facade check:

```php
use DisposableEmail;

if (DisposableEmail::isDisposable($email)) {
    return back()->withErrors([
        'email' => 'Please use a permanent email address.',
    ]);
}
```

Service-style usage:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

class SignupGuard
{
    public function ensureRealMailbox(string $email): void
    {
        if (DisposableEmailRule::isDisposable($email)) {
            throw new InvalidArgumentException('Disposable email addresses are not allowed.');
        }
    }
}
```

Internal support class location:

```php
use EragLaravelDisposableEmail\Support\Email;
```

## Blade Example

```blade
@disposableEmail($user->email)
    <p class="text-red-600">Disposable email detected.</p>
@else
    <p class="text-green-600">Email looks acceptable.</p>
@enddisposableEmail
```

## Sync and Scheduling

Manual sync:

```bash
php artisan erag:sync-disposable-email-list
```

Scheduled sync:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('erag:sync-disposable-email-list')->daily();
```

## Config

```php
return [
    'blacklist_file' => storage_path('app/blacklist_file'),
    'remote_url' => [
        'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt',
    ],
    'cache_enabled' => false,
    'cache_ttl' => 60,
];
```

## Custom Blacklist

Store custom domains in `storage/app/blacklist_file/disposable_domains.txt` unless the config points elsewhere.

Valid content:

```text
abakiss.com
fakemail.org
trashbox.io
```

The loader also accepts entries like `user@example.com` in local files and will reduce them to `example.com`, but plain domains are the preferred format.

## AI Task Examples

Use this package skill for prompts like:

- Add disposable email validation to my registration form.
- Reject temporary email domains in a Laravel form request.
- Check whether an email is disposable before creating a user.
- Show a warning in Blade when a user's email is temporary.
- Schedule disposable domain list syncing.
- Enable caching for disposable email lookups.
- Add project-specific blocked domains on top of the package defaults.
