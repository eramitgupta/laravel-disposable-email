<?php

test('validator rule "disposable_email"', function (string $email, bool $shouldFail) {
    expect(validator(['email' => $email], ['email' => ['disposable_email']]))
        ->fails()
        ->toBe($shouldFail);
})->with([
    'whitelisted email' => [whitelisted_email(), false],
    'valid email' => [valid_email(), false],
    'default blacklisted email' => [default_blacklisted_email(), true],
    'custom blacklisted email' => [custom_blacklisted_email(), true],
    'malformed email' => [malformed_email(), true],
]);
