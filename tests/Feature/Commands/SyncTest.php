<?php

use EragLaravelDisposableEmail\Commands\Sync;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\File;
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

it('creates safe filenames for remote urls', function () {
    expect(sync_filename('https://example.com/lists/Disposable Emails.json'))
        ->toBe('disposable-emails.txt')
        ->and(sync_filename('https://example.com/'))
        ->toBe('disposable-domains.txt');
});

it('appends only remote domains missing from the local list', function () {
    File::put(synced_list_path(), implode(PHP_EOL, ['zeta.test', 'local-only.test', 'alpha.test']).PHP_EOL);

    fake_remote_response("alpha.test\nbeta.test\nzeta.test\ngamma.test");

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 2 new domains')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))
        ->toBe(implode(PHP_EOL, ['zeta.test', 'local-only.test', 'alpha.test', 'beta.test', 'gamma.test']).PHP_EOL);
});

it('does not duplicate domains when synced again', function () {
    $original = File::get(synced_list_path());

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('No new domains found')
        ->expectsOutputToContain('Sync complete. Synced: 1. Failed: 0.')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))->toBe($original);
});

it('compares remote domains with normalized local entries', function () {
    File::put(synced_list_path(), "Alpha.TEST\nuser@beta.test\n");

    fake_remote_response("alpha.test\nbeta.test\ngamma.test");

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 1 new domains')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))->toBe("Alpha.TEST\nuser@beta.test\ngamma.test".PHP_EOL);
});

it('appends on a new line when the local list has no trailing newline', function () {
    File::put(synced_list_path(), 'alpha.test');

    fake_remote_response("alpha.test\nbeta.test");

    $this->artisan('erag:sync-disposable-email-list')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))->toBe('alpha.test'.PHP_EOL.'beta.test'.PHP_EOL);
});

function sync_timeout(mixed $timeout = null): int
{
    if (func_num_args() > 0) {
        config()->set('disposable-email.sync_timeout', $timeout);
    }

    $command = new Sync;
    $method = new ReflectionMethod($command, 'syncTimeout');
    $method->setAccessible(true);

    return $method->invoke($command);
}

function sync_filename(string $url): string
{
    $command = new Sync;
    $method = new ReflectionMethod($command, 'filename');
    $method->setAccessible(true);

    return $method->invoke($command, $url);
}

/**
 * Replace the default remote stub registered in tests/Pest.php, since
 * stacked Http::fake() stubs resolve to the first registered match.
 */
function fake_remote_response(string $body): void
{
    Http::swap(new Factory);
    Http::fake(['://github.local*' => Http::response($body)]);
}

function synced_list_path(): string
{
    return fixture('blacklist').DIRECTORY_SEPARATOR.'disposable_email.txt';
}
