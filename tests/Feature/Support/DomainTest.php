<?php

use EragLaravelDisposableEmail\Support\Domain;
use EragLaravelDisposableEmail\Support\Matcher;

it('normalizes email and domain values', function () {
    expect(Domain::normalize(' User@Example.COM '))->toBe('example.com')
        ->and(Domain::normalize('EXAMPLE.COM'))->toBe('example.com')
        ->and(Domain::normalize('invalid'))->toBe('');
});

it('matches exact domains and optional parent domains', function () {
    $domains = ['example.com' => 'custom'];

    expect(Matcher::find('example.com', $domains))->toBe('example.com')
        ->and(Matcher::find('mail.example.com', $domains, true))->toBe('example.com')
        ->and(Matcher::find('mail.example.com', $domains, false))->toBeNull();
});
