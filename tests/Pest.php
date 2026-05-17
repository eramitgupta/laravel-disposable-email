<?php

use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;
use Tests\TestCase;

use function Pest\Laravel\artisan;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature')
    ->beforeEach(function () {
        /*
        |--------------------------------------------------------------------------
        | Configure app with testing settings
        |--------------------------------------------------------------------------
        */

        config()->set('disposable-email.remote_url', ['http://github.local/disposable_email.txt']);

        config()->set('disposable-email.blacklist_file', fixture('blacklist'));

        config()->set(
            'disposable-email.whitelist',
            array_merge(
                explode(PHP_EOL, fixture_load('whitelist/main_whitelist.txt')),
                explode(PHP_EOL, fixture_load('whitelist/another_whitelist.txt'))
            ),
        );

        /*
        |--------------------------------------------------------------------------
        | HTTP Fake request
        |--------------------------------------------------------------------------
        */

        Http::fake([
            '://github.local*' => Http::response(fixture_load('github_disposable_email.txt'), 200),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Updating domain list
        |--------------------------------------------------------------------------
        | Update the e-mail list before each test to elimate
        | any possible interdependencies.
        | The update command must be called after the `Http::fake`.
        |--------------------------------------------------------------------------
        */
        artisan('erag:sync-disposable-email-list');

    })->afterEach(
        function () {
            // Clean up default list file
            $disposableListPath = fixture('blacklist/disposable_email.txt');

            if (file_exists($disposableListPath)) {
                unlink($disposableListPath);
            }

            // Ensure the list was fetched from the Http fake data.
            Http::assertSent(fn ($request) => $request->url() === 'http://github.local/disposable_email.txt');
        }
    );

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
*/

/**
 * @see tests/Unit/CustomExpectationsTest.php
 */
expect()->extend('toBeValidDomain', function () {
    return $this->toMatch('/^(?!\-)(?:[a-zA-Z0-9\-]{0,62}[a-zA-Z0-9]\.)+[a-zA-Z]{2,63}$/');
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
*/

if (! function_exists('fixture')) {
    /**
     * Returns fixture file or directory path
     *
     * @throws RuntimeException if the file or directory does not exist.
     */
    function fixture(string $fixture): string
    {
        $path = __DIR__.str_replace('/', DIRECTORY_SEPARATOR, "/Fixtures/{$fixture}");

        if (! file_exists($path)) {
            throw new RuntimeException("Directory or file missing {$path}]");
        }

        return $path;
    }
}

if (! function_exists('fixture_load')) {
    /**
     * Returns fixture file content
     *
     * @throws RuntimeException if the file or directory does not exist.
     */
    function fixture_load(string $fixture): string
    {
        return file_get_contents(fixture($fixture));
    }
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
*/

if (! function_exists('custom_blacklisted_emails')) {
    /**
     * Generates email addresses based on custom blacklists
     */
    function custom_blacklisted_emails(): array
    {
        return array_map(
            fn (string $domain) => fake()->username()."@{$domain}",
            explode(PHP_EOL, fixture_load('blacklist/main_blacklist.txt'))
        );
    }
}

if (! function_exists('default_blacklisted_emails')) {
    /**
     * Generates email addresses based on package default domains
     */
    function default_blacklisted_emails(): array
    {
        return array_map(
            fn (string $domain) => fake()->username()."@{$domain}",
            explode(PHP_EOL, fixture_load('github_disposable_email.txt'))
        );
    }
}

if (! function_exists('whitelisted_emails')) {
    /**
     * Generates email addresses based on package default domains
     */
    function whitelisted_emails(): array
    {
        return array_map(
            fn (string $domain) => fake()->username()."@{$domain}",
            explode(PHP_EOL, fixture_load('whitelist/main_whitelist.txt'))
        );
    }
}

if (! function_exists('get_random_fixture_domain')) {
    /**
     * Returns a valid email addresses using faker
     */
    function get_random_fixture_domain(string $fixture): string
    {
        $emails = explode(PHP_EOL, fixture_load($fixture));

        return $emails[array_rand($emails)];
    }
}

if (! function_exists('get_random_fixture_email_address')) {
    /**
     * Generates an email address from a random fixture domain
     */
    function get_random_fixture_email_address(string $fixture): string
    {
        return fake()->username().'@'.get_random_fixture_domain($fixture);
    }
}

if (! function_exists('whitelisted_email')) {
    /**
     * Returns a random email address with a random whitelist domain.
     */
    function whitelisted_email(): string
    {
        return get_random_fixture_email_address('whitelist/main_whitelist.txt');
    }
}

if (! function_exists('custom_blacklisted_email')) {
    /**
     * Returns a random email address with a
     * random domain from the custom blacklist.
     */
    function custom_blacklisted_email(): string
    {
        return get_random_fixture_email_address('blacklist/main_blacklist.txt');
    }
}

if (! function_exists('default_blacklisted_email')) {
    /**
     * Returns a random email address with a
     * random domain from package default blacklist.
     */
    function default_blacklisted_email(): string
    {
        return get_random_fixture_email_address('github_disposable_email.txt');
    }
}

if (! function_exists('valid_emails')) {
    /**
     * Generates many valid email addresses with faker()
     */
    function valid_emails(int $count = 10): array
    {
        return array_map(fn () => fake()->email(), range(1, $count));
    }
}

if (! function_exists('valid_email')) {
    /**
     * Generates a valid email addresses with faker()
     */
    function valid_email(): string
    {
        return fake()->email();
    }
}

if (! function_exists('malformed_email')) {
    /**
     * Returns a malformed email address
     */
    function malformed_email(): string
    {
        return 'malformed email';
    }
}

if (! function_exists('DisposableEmailRule')) {

    /**
     * Laravel Validator with DisposableEmailRule for email field
     */
    function DisposableEmailRule(string $emailAddress): IlluminateValidator
    {
        return Validator::make(
            ['email' => $emailAddress],
            ['email' => new DisposableEmailRule]
        );
    }
}
