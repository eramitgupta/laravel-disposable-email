<?php

use EragLaravelDisposableEmail\Commands\UpdateDisposableEmailList;
use Illuminate\Support\Facades\Http;

it('updates emails database via artisan command')
    ->artisan('erag:sync-disposable-email-list')
    ->expectsOutputToContain('Sync complete. Synced: 1. Failed: 0.')
    ->assertExitCode(0);

it('uses a configurable sync request timeout', function () {
    config()->set('disposable-email.sync_timeout', 15);

    $timeout = null;

    Http::fake(function ($request, array $options) use (&$timeout) {
        $timeout = $options['timeout'] ?? null;

        return Http::response(fixture_load('github_disposable_email.txt'), 200);
    });

    $this->artisan('erag:sync-disposable-email-list')
        ->assertExitCode(0);

    expect($timeout)->toBe(15);
});

it('falls back to the default sync request timeout for invalid values')
    ->with([0, -1, 'invalid', null])
    ->expect(fn (mixed $timeout) => sync_timeout($timeout))
    ->toBe(30);

function sync_timeout(mixed $timeout = null): int
{
    if (func_num_args() > 0) {
        config()->set('disposable-email.sync_timeout', $timeout);
    }

    $command = new UpdateDisposableEmailList;
    $method = new ReflectionMethod($command, 'syncTimeout');
    $method->setAccessible(true);

    return $method->invoke($command);
}
