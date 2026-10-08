<?php

use EragLaravelDisposableEmail\Commands\Sync;
use EragLaravelDisposableEmail\Support\ResponseParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

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

it('leaves the list untouched when every remote domain already exists', function () {
    $original = File::get(synced_list_path());
    clearstatcache();
    $inode = fileinode(synced_list_path());

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('No new domains found')
        ->expectsOutputToContain('Sync complete. Synced: 1. Failed: 0.')
        ->assertExitCode(0);

    clearstatcache();

    expect(File::get(synced_list_path()))->toBe($original)
        ->and(fileinode(synced_list_path()))->toBe($inode)
        ->and(temporary_list_files())->toBe([]);
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

it('creates the list from the remote source on the first sync', function () {
    File::delete(synced_list_path());

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 4 new domains')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))
        ->toBe(implode(PHP_EOL, ResponseParser::parse(fixture_load('github_disposable_email.txt'))).PHP_EOL);
});

it('adds a domain only once when the remote list repeats it', function () {
    fake_remote_response("New.TEST\nnew.test\nuser@new.test\n  new.test  \n");

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 1 new domains')
        ->assertExitCode(0);

    expect(substr_count(File::get(synced_list_path()), 'new.test'))->toBe(1);
});

it('keeps the existing list when the remote response is unusable', function (mixed $response) {
    Sleep::fake();

    $original = File::get(synced_list_path());

    fake_remote_response($response);

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Sync complete. Synced: 0. Failed: 1.')
        ->assertExitCode(1);

    expect(File::get(synced_list_path()))->toBe($original)
        ->and(temporary_list_files())->toBe([]);
})->with([
    'network timeout' => fn () => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    'server error' => fn () => Http::response('Service Unavailable', 503),
    'not found' => fn () => Http::response('404: Not Found', 404),
    'empty body' => fn () => Http::response(''),
    'whitespace body' => fn () => Http::response("\n \r\n\t\n"),
    'html error page' => fn () => Http::response("<!DOCTYPE html>\n<html>\n<body>\nportal.example.com\n</body>\n</html>"),
    'truncated json' => fn () => Http::response('["mailinator.com", "guerrillamail.c'),
    'json error payload' => fn () => Http::response(['message' => 'API rate limit exceeded']),
    'binary garbage' => fn () => Http::response("\x00\xFF\xFE\x1F garbage ### not a domain list"),
]);

it('never creates a list from an html page', function () {
    File::delete(synced_list_path());

    fake_remote_response("<html>\n<body>\nportal.example.com\n</body>\n</html>");

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Received an HTML/XML page instead of a domain list')
        ->assertExitCode(1);

    expect(synced_list_path())->not->toBeFile();
});

it('keeps the original list when the temporary file is only partly written', function () {
    $original = File::get(synced_list_path());

    fake_remote_response('new.test');

    File::partialMock()
        ->shouldReceive('put')
        ->once()
        ->andReturnUsing(fn (string $path, string $contents): int => file_put_contents($path, substr($contents, 0, 3)));

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Unable to write the temporary file')
        ->assertExitCode(1);

    expect(File::get(synced_list_path()))->toBe($original)
        ->and(temporary_list_files())->toBe([]);
});

it('keeps the original list and removes the temporary file when the rename fails', function () {
    $original = File::get(synced_list_path());

    fake_remote_response('new.test');

    File::partialMock()
        ->shouldReceive('move')
        ->once()
        ->andReturnFalse();

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Unable to replace the existing list')
        ->assertExitCode(1);

    expect(File::get(synced_list_path()))->toBe($original)
        ->and(temporary_list_files())->toBe([]);
});

it('never replaces an existing list it cannot read', function () {
    File::delete(synced_list_path());
    File::makeDirectory(synced_list_path());

    try {
        $this->artisan('erag:sync-disposable-email-list')
            ->expectsOutputToContain('The existing list is not a readable file')
            ->assertExitCode(1);

        expect(synced_list_path())->toBeDirectory()
            ->and(temporary_list_files())->toBe([]);
    } finally {
        File::deleteDirectory(synced_list_path());
    }
});

it('keeps the permissions of the existing list', function () {
    chmod(synced_list_path(), 0640);

    fake_remote_response('new.test');

    $this->artisan('erag:sync-disposable-email-list')
        ->assertExitCode(0);

    clearstatcache();

    expect(fileperms(synced_list_path()) & 0777)->toBe(0640);
});

it('waits for a concurrent sync and never writes while it holds the lock', function () {
    Sleep::fake();

    $original = File::get(synced_list_path());

    fake_remote_response('new.test');

    $lock = fopen(synced_list_path().'.lock', 'c');
    flock($lock, LOCK_EX);

    try {
        $this->artisan('erag:sync-disposable-email-list')
            ->expectsOutputToContain('Another sync is currently updating this list')
            ->assertExitCode(1);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    expect(File::get(synced_list_path()))->toBe($original);

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 1 new domains')
        ->assertExitCode(0);

    $lock = fopen(synced_list_path().'.lock', 'c');

    expect(flock($lock, LOCK_EX | LOCK_NB))->toBeTrue();

    fclose($lock);
});

it('keeps domains another sync wrote while the remote list was downloading', function () {
    $original = File::get(synced_list_path());

    fake_remote_response(function () {
        File::append(synced_list_path(), 'concurrent.test'.PHP_EOL);

        return Http::response("concurrent.test\nremote.test");
    });

    $this->artisan('erag:sync-disposable-email-list')
        ->expectsOutputToContain('Added 1 new domains')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))
        ->toBe($original.'concurrent.test'.PHP_EOL.'remote.test'.PHP_EOL);
});

it('preserves local domains the remote source no longer lists', function () {
    $localList = implode(PHP_EOL, ['# kept as written', 'removed-upstream.test', 'Manual.Entry.test']).PHP_EOL;

    File::put(synced_list_path(), $localList);

    fake_remote_response('remote.test');

    $this->artisan('erag:sync-disposable-email-list')
        ->assertExitCode(0);

    expect(File::get(synced_list_path()))->toBe($localList.'remote.test'.PHP_EOL);
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
function fake_remote_response(mixed $response): void
{
    Http::swap(new Factory);
    Http::fake(['://github.local*' => is_string($response) ? Http::response($response) : $response]);
}

function synced_list_path(): string
{
    return fixture('blacklist').DIRECTORY_SEPARATOR.'disposable_email.txt';
}

/**
 * @return array<int, string>
 */
function temporary_list_files(): array
{
    return glob(fixture('blacklist').DIRECTORY_SEPARATOR.'*.tmp') ?: [];
}
