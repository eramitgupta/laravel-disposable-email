<?php

use EragLaravelDisposableEmail\Support\Domain;
use EragLaravelDisposableEmail\Support\Matcher;

it('normalizes email and domain values', function () {
    expect(Domain::normalize(' User@Example.COM '))->toBe('example.com')
        ->and(Domain::normalize('EXAMPLE.COM'))->toBe('example.com')
        ->and(Domain::normalize('invalid'))->toBe('');
});

it('rejects malformed domains', function (string $domain) {
    expect(Domain::isValid($domain))->toBeFalse()
        ->and(Domain::normalize($domain))->toBe('');
})->with([
    'label starts with a hyphen' => '-example.com',
    'label ends with a hyphen' => 'example-.com',
    'consecutive dots' => 'example..com',
    'empty label' => '.example.com',
    'label longer than 63 characters' => str_repeat('a', 64).'.com',
    'domain longer than 253 characters' => str_repeat('a.', 126).'com',
]);

it('accepts valid domains at supported boundaries', function (string $domain) {
    expect(Domain::isValid($domain))->toBeTrue()
        ->and(Domain::normalize($domain))->toBe($domain);
})->with([
    'hyphens within a label' => 'mail-service.example.com',
    '63 character label' => str_repeat('a', 63).'.com',
]);

it('matches exact domains and optional parent domains', function () {
    $domains = ['example.com' => 'custom'];

    expect(Matcher::find('example.com', $domains))->toBe('example.com')
        ->and(Matcher::find('mail.example.com', $domains, true))->toBe('example.com')
        ->and(Matcher::find('mail.example.com', $domains, false))->toBeNull();
});
