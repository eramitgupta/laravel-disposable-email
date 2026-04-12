# Reference

## Package Surface

- Validation rule string: `disposable_email`
- Rule class: `EragLaravelDisposableEmail\Rules\DisposableEmailRule`
- Facade alias: `DisposableEmail`
- Blade conditional: `@disposableEmail(...)`
- Install command: `php artisan erag:install-disposable-email`
- Sync command: `php artisan erag:sync-disposable-email-list`
- Config file: `config/disposable-email.php`
- Default blacklist directory: `storage/app/blacklist_file`

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

Publish the package configuration:

```bash
php artisan erag:install-disposable-email
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
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'disposable_email'],
        'password' => ['required', 'confirmed', 'min:8'],
    ];
}
```

Manual validator:

```php
use Illuminate\Support\Facades\Validator;

$validator = Validator::make($request->all(), [
    'email' => ['required', 'email', 'disposable_email'],
]);

if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}
```

API validation:

```php
use Illuminate\Http\Request;

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email', 'disposable_email'],
    ]);

    return response()->json([
        'email' => $validated['email'],
        'message' => 'Email accepted.',
    ]);
});
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

## Blade Example

```blade
@disposableEmail($user->email)
    <p class="text-red-600">Disposable email detected.</p>
@else
    <p class="text-green-600">Email looks acceptable.</p>
@enddisposableEmail
```

## Configuration

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

Configuration keys:

- `blacklist_file`: directory that stores local custom `.txt` blacklist files
- `remote_url`: remote sources used by the sync command
- `cache_enabled`: enables caching for the compiled domain list
- `cache_ttl`: cache lifetime in minutes

## Sync and Blacklist

Manual sync:

```bash
php artisan erag:sync-disposable-email-list
```

What the sync command does:

- clears the package cache
- reads URLs from `config('disposable-email.remote_url')`
- normalizes the remote response into domains
- stores the updated list in the configured blacklist directory

Custom blacklist file:

```text
storage/app/blacklist_file/disposable_domains.txt
```

Valid content:

```text
abakiss.com
fakemail.org
trashbox.io
```

Notes:

- the package reads every `.txt` file in the configured blacklist directory
- use one domain per line
- keep files plain text with no comments or metadata

## Schedule Sync

Laravel scheduler example:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('erag:sync-disposable-email-list')->daily();
```

Other valid schedules include `hourly()`, `daily()`, and `weekly()`.

## Caching

Enable caching in `config/disposable-email.php`:

```php
'cache_enabled' => true,
'cache_ttl' => 60,
```

Clear Laravel's cache after domain file changes when needed:

```bash
php artisan cache:clear
```

Use caching when the application performs frequent email checks across forms, APIs, jobs, or admin tools.

## Troubleshooting

If the rule is not working:

- make sure the field also uses Laravel's `email` rule
- make sure the rule name is exactly `disposable_email`
- confirm the package is installed and loaded

If a disposable address passes validation:

- clear cache if caching is enabled
- sync the remote domain list again
- check whether the domain exists in built-in or custom sources

If custom domains are ignored:

- place the file inside the configured `blacklist_file` directory
- use the `.txt` extension
- keep one domain per line

Useful commands:

```bash
php artisan cache:clear
php artisan erag:sync-disposable-email-list
```

## AI Task Examples

Use this package skill for prompts like:

- Add disposable email validation to my registration form.
- Reject temporary email domains in a Laravel form request.
- Check whether an email is disposable before creating a user.
- Show a warning in Blade when a user's email is temporary.
- Schedule disposable domain list syncing.
- Enable caching for disposable email lookups.
- Add project-specific blocked domains on top of the package defaults.
- Troubleshoot why disposable addresses are still passing validation.
