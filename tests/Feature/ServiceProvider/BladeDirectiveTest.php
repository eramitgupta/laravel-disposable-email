<?php

use Illuminate\Support\Facades\Blade;

todo('Blade: "@disposableEmail" does not flag a malformed email. Verify.');

test('Blade: "@disposableEmail" can validate emails', function (string $email, string $result) {
    expect(Blade::render("@disposableEmail('{$email}') <fail> @else <pass> @enddisposableEmail"))
        ->toContain("<{$result}>");
})->with([
    'default blacklisted email' => [default_blacklisted_email(), 'fail'],
    'custom main blacklist email' => [custom_blacklisted_email(), 'fail'],
    'whitelisted email' => [whitelisted_email(), 'pass'],
    'valid email' => [valid_email(), 'pass'],
    // 'malformed email' => [malformed_email(), 'fail'], //@todo
]);
