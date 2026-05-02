---
title: Validation and Runtime
description: Use Laravel Disposable Email from basic validation rules to advanced runtime checks, Blade directives, Form Requests, and API flows.
head:
  - - meta
    - name: keywords
      content: laravel disposable email validation, laravel runtime email check, laravel disposable email rule, laravel form request disposable email
---

# Validation and Runtime

This page walks through the package from the simplest validation setup to more advanced usage in real Laravel applications.

If you only need one thing, start here:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

That is enough to reject disposable email addresses in most forms.

## 1. Basic validation

The most direct way to use the package is in a controller or route action:

```php
$request->validate([
    'email' => 'required|email|disposable_email',
]);
```

Use this when you want a quick and readable validation rule with minimal setup.

## 2. Array rule syntax

If you prefer Laravel's array-based rule format, you can write the same validation like this:

```php
$request->validate([
    'email' => ['required', 'email', 'disposable_email'],
]);
```

This format is easier to extend when your field has several rules.

## 3. Full form validation example

A more realistic registration example might look like this:

```php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'disposable_email'],
    'password' => ['required', 'confirmed', 'min:8'],
]);
```

This is a good default for registration, onboarding, and trial signup flows.

## 4. Form Request example

If your application already uses Form Requests, the package fits naturally there:

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'disposable_email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }
}
```

This keeps controllers smaller and keeps all request rules in one place.

## 5. Custom rule object

If you prefer class-based rules, use the package rule directly:

```php
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;

$request->validate([
    'email' => ['required', 'email', new DisposableEmailRule()],
]);
```

This is useful when your team prefers explicit rule objects over string rules.

You can also create the rule through the facade:

```php
use Disposable;

$request->validate([
    'email' => ['required', 'email', Disposable::rule()],
]);
```

## 6. Controller example

Here is a complete controller example using request validation:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'disposable_email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        return User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
    }
}
```

## 7. Manual validator example

If you build validators manually, the rule still works the same way:

```php
use Illuminate\Support\Facades\Validator;

$validator = Validator::make($request->all(), [
    'email' => ['required', 'email', 'disposable_email'],
]);

if ($validator->fails()) {
    return back()->withErrors($validator)->withInput();
}
```

This is useful in service-heavy or custom validation flows.

## 8. API request validation

For API endpoints, you can validate first and return a clean JSON response:

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

## 9. Direct runtime check <span class="doc-new-badge">New</span> {#direct-runtime-check}

Use the short facade when you need a simple boolean result outside request validation:

```php
use Disposable;

if (Disposable::email('test@tempmail.com')) {
    // Handle disposable email
}

if (Disposable::domain('test@tempmail.com')) {
    // Handle disposable domain
}
```

You can also import the package facade namespace directly:

```php
use EragLaravelDisposableEmail\Facades\Disposable;

if (Disposable::email('test@tempmail.com')) {
    // Handle disposable email
}
```

## 10. Detailed runtime check <span class="doc-new-badge">New</span> {#detailed-runtime-check}

Use `Disposable::check()` when you need more than a boolean. `Disposable::Check()` also works as a case-insensitive alias:

```php
use Disposable;

$result = Disposable::check('test@tempmail.com');
$sameResult = Disposable::Check('test@tempmail.com');

$result->disposable(); // true
$result->domain(); // tempmail.com
$result->matchedDomain(); // tempmail.com
$result->source(); // built-in, custom, or whitelist
$result->toArray();
```

This is useful for API responses, admin tools, logging, and debugging domain matching.

## 11. Service class example

Runtime checks work well in business logic:

```php
<?php

namespace App\Services\Auth;

use Disposable;
use Illuminate\Validation\ValidationException;

class SignupPolicy
{
    public function assertAllowedEmail(string $email): void
    {
        if (Disposable::email($email)) {
            throw ValidationException::withMessages([
                'email' => 'Please use a permanent email address.',
            ]);
        }
    }
}
```

## 12. Facade usage

If you prefer the short facade style, you can use:

```php
use Disposable;

if (Disposable::email('amit@agedmail.com')) {
    // Handle disposable email
}
```

## 13. Runtime API check endpoint

You can also expose a lightweight API endpoint for live front-end checks:

```php
use Disposable;
use Illuminate\Http\Request;

Route::post('/email/check', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $email = $request->string('email')->toString();

    return response()->json([
        'email' => $email,
        'disposable' => Disposable::email($email),
    ]);
});
```

## 14. Blade directive

The package also includes a Blade directive for simple template checks:

```blade
@disposableEmail('amit@0-mail.com')
    <p class="text-red-600">Disposable email detected!</p>
@else
    <p class="text-green-600">Valid email.</p>
@enddisposableEmail
```

## 15. Blade form feedback example

Here is a more complete Blade example:

```blade
<form method="POST" action="{{ route('register') }}">
    @csrf

    <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        placeholder="name@example.com"
    >

    @error('email')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    @disposableEmail(old('email', ''))
        <p class="text-sm text-red-600">Disposable email detected.</p>
    @else
        <p class="text-sm text-green-600">Valid email address.</p>
    @enddisposableEmail
</form>
```

## 15. Recommended pattern

For most Laravel applications, the cleanest approach is:

1. Validate disposable emails at the request layer.
2. Reuse runtime checks in service code only where business rules need them.
3. Use Blade output only for UI feedback, not as your only protection.

That gives you early rejection, cleaner controllers, and consistent behaviour across your app.
