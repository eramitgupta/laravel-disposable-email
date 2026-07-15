<?php

test('validation passes with valid e-mail addresses')
    ->expect(fn ($email) => DisposableEmail($email))
    ->passes()
    ->toBeTrue()
    ->with(fn () => valid_emails());

test('validation fails with default blacklisted domains')
    ->expect(fn ($email) => DisposableEmail($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => default_blacklisted_emails());

test('validation fails with custom main blacklist domains')
    ->expect(fn ($email) => DisposableEmail($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => custom_blacklisted_emails());

test('validation fails with malformed email')
    ->expect(fn ($email) => DisposableEmail($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => [malformed_email()]);

test('validation fails with whitelisted domains')
    ->expect(fn ($email) => DisposableEmail($email))
    ->passes()
    ->toBeTrue()
    ->with(fn () => whitelisted_emails());

it('can block subdomains according to configuration', function () {
    $email = get_random_fixture_email_address('blacklist/blacklist_subdomain.txt');
    $emailWithSubdomain = str_replace('@', '@mail.', $email);

    // Set Blocking subdomains ON
    config()->set('disposable-email.block_subdomains', true);

    // Main domains should be flagged as disposable
    expect(DisposableEmail($email))->passes()->toBeFalse();

    // Sub-domain should be flagged as disposable
    expect(DisposableEmail($emailWithSubdomain))->passes()->toBeFalse();

    // Set Blocking subdomains OFF
    config()->set('disposable-email.block_subdomains', false);

    // Main domains should be flagged as disposable
    expect(DisposableEmail($email))->passes()->toBeFalse();

    // Sub-domain is accepted as valid email
    expect(DisposableEmail($emailWithSubdomain))->passes()->toBeTrue();
});

it('applies Laravel email validation styles before disposable email detection', function (string $email, string $validation) {
    $native = validator(
        ['email' => $email],
        ['email' => "email:{$validation}"]
    );

    $disposable = validator(
        ['email' => $email],
        ['email' => "disposable_email:{$validation}"]
    );

    expect($disposable->passes())->toBe($native->passes());
})->with([
    'rfc validation' => ['user@example.com', 'rfc'],
    'strict validation' => ['user.@example.com', 'strict'],
    'dns validation' => ['user@gmail.com', 'dns'],
    'spoof validation' => ['user@еxample.com', 'spoof'],
    'filter validation' => ['user@example.com', 'filter'],
    'unicode filter validation' => ['user@example.com', 'filter_unicode'],
]);

it('supports multiple email validation styles', function () {
    $native = validator(
        ['email' => 'user@example.com'],
        ['email' => 'email:rfc,filter']
    );

    $disposable = validator(
        ['email' => 'user@example.com'],
        ['email' => 'disposable_email:rfc,filter']
    );

    expect($disposable->passes())->toBe($native->passes());
});

it('ignores duplicate email validation styles', function () {
    $withoutDuplicates = validator(
        ['email' => 'user@example.com'],
        ['email' => 'disposable_email:rfc,filter']
    );

    $withDuplicates = validator(
        ['email' => 'user@example.com'],
        ['email' => 'disposable_email:rfc,rfc,filter,filter']
    );

    expect($withDuplicates->passes())->toBe($withoutDuplicates->passes());
});

it('reports unsupported email validation styles', function () {
    $validator = validator(
        ['email' => 'user@example.com'],
        ['email' => 'disposable_email:unknown']
    );

    expect(fn () => $validator->fails())
        ->toThrow(InvalidArgumentException::class, 'Unsupported email validation parameter(s): unknown.');
});
