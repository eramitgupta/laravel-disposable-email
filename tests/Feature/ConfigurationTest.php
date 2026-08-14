<?php

use EragLaravelDisposableEmail\Support\Email;

it('keeps remote list syncing opt-in by default', function () {
    $configuration = require dirname(__DIR__, 2).'/config/disposable-email.php';

    expect($configuration['remote_url'])->toBe([]);
});

it('loads the built-in list over http and caches it for one day', function () {
    config()->set('disposable-email.cache_enabled', false);

    expect(Email::domains())
        ->toContain('defaultblockeddomain.com');

    Http::assertSent(fn ($request): bool => str_contains($request->url(), 'raw.githubusercontent.com/eramitgupta/disposable-email'));
});

it('returns the default list when the built-in http list fails', function () {
    Http::fake([
        'https://raw.githubusercontent.com/eramitgupta/disposable-email/main/disposable_email.txt' => Http::response('', 500),
    ]);

    Email::clearCache();

    expect(Email::domains())->toContain('0-mail.com');
});

it('returns the default list when the built-in http request throws', function () {
    Http::fake(function (): never {
        throw new RuntimeException('Connection failed.');
    });

    Email::clearCache();

    expect(Email::domains())->toContain('0-mail.com');
});

it('can disable remote list syncing', function () {
    config()->set('disposable-email.remote_url', []);

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('No valid remote URLs configured')
        ->assertExitCode(1);
});
