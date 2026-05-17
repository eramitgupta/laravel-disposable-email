<?php

test('validation passes with valid e-mail addresses')
    ->expect(fn ($email) => DisposableEmailRule($email))
    ->passes()
    ->toBeTrue()
    ->with(fn () => valid_emails());

test('validation fails with default blacklisted domains')
    ->expect(fn ($email) => DisposableEmailRule($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => default_blacklisted_emails());

test('validation fails with custom main blacklist domains')
    ->expect(fn ($email) => DisposableEmailRule($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => custom_blacklisted_emails());

test('validation fails with malformed email')
    ->expect(fn ($email) => DisposableEmailRule($email))
    ->passes()
    ->toBeFalse()
    ->with(fn () => [malformed_email()]);

test('validation fails with whitelisted domains')
    ->expect(fn ($email) => DisposableEmailRule($email))
    ->passes()
    ->toBeTrue()
    ->with(fn () => whitelisted_emails());

it('can block subdomains according to configuration', function () {
    $email = get_random_fixture_email_address('blacklist/blacklist_subdomain.txt');
    $emailWithSubdomain = str_replace('@', '@mail.', $email);

    // Set Blocking subdomains ON
    config()->set('disposable-email.block_subdomains', true);

    // Main domains should be flagged as disposable
    expect(DisposableEmailRule($email))->passes()->toBeFalse();

    // Sub-domain should be flagged as disposable
    expect(DisposableEmailRule($emailWithSubdomain))->passes()->toBeFalse();

    // Set Blocking subdomains OFF
    config()->set('disposable-email.block_subdomains', false);

    // Main domains should be flagged as disposable
    expect(DisposableEmailRule($email))->passes()->toBeFalse();

    // Sub-domain is accepted as valid email
    expect(DisposableEmailRule($emailWithSubdomain))->passes()->toBeTrue();
});
