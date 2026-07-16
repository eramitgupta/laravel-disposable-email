<?php

use EragLaravelDisposableEmail\Facades\Disposable;

it('keeps plain disposable validation backward compatible', function () {
    expect(validator(
        ['email' => 'user..name@example.com'],
        ['email' => ['disposable_email']]
    )->passes())->toBeTrue();
});

it('supports a single email validation mode', function () {
    expect(validator(
        ['email' => 'user..name@example.com'],
        ['email' => ['disposable_email:filter']]
    )->fails())->toBeTrue();
});

it('supports combined email validation modes', function () {
    expect(validator(
        ['email' => 'person@example.com'],
        ['email' => ['disposable_email:rfc,filter']]
    )->passes())->toBeTrue();
});

it('supports dns validation mode', function () {
    expect(validator(
        ['email' => 'person@example.invalid'],
        ['email' => ['disposable_email:dns']]
    )->fails())->toBeTrue();
});

it('validates format before applying whitelist', function () {
    config()->set('disposable-email.whitelist', ['example.com']);

    expect(validator(
        ['email' => 'user..name@example.com'],
        ['email' => ['disposable_email:filter']]
    )->fails())->toBeTrue();
});

it('supports modes through facade rule helpers', function () {
    expect(validator(
        ['email' => 'user..name@example.com'],
        ['email' => [Disposable::rule('filter')]]
    )->fails())->toBeTrue();
});

it('rejects unknown validation modes', function () {
    validator(
        ['email' => 'person@example.com'],
        ['email' => ['disposable_email:unknown']]
    )->passes();
})->throws(InvalidArgumentException::class);
