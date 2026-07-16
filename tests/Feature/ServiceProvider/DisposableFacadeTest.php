<?php

use EragLaravelDisposableEmail\Facades\Disposable;

test('Disposable Facade - Email Check')
    ->expect(fn () => Disposable::email(default_blacklisted_email()))->toBeTrue()
    ->expect(fn () => Disposable::email(custom_blacklisted_email()))->toBeTrue()
    ->expect(fn () => Disposable::email(whitelisted_email()))->toBeFalse()
    ->expect(fn () => Disposable::email(valid_email()))->toBeFalse()
    ->expect(fn () => Disposable::email(malformed_email()))->toBeFalse();

test('Disposable Facade Domain Check', function (string $email, bool $result) {
    $domain = str($email)->after('@')->toString();

    expect(Disposable::domain($email))->toBe($result);
    expect(Disposable::domain($domain))->toBe($result);
})->with([
    'whitelisted email' => [whitelisted_email(), false],
    'valid email' => [valid_email(), false],
    'default blacklisted email' => [default_blacklisted_email(), true],
    'custom blacklisted email' => [custom_blacklisted_email(), true],
]);

it('returns the matched source in detailed checks', function () {
    $builtIn = Disposable::check('person@0-mail.com');
    $custom = Disposable::check(custom_blacklisted_email());
    $whitelist = Disposable::check(whitelisted_email());

    expect($builtIn->source())->toBe('built-in')
        ->and($custom->source())->toBe('custom')
        ->and($whitelist->source())->toBe('whitelist')
        ->and($whitelist->whitelisted())->toBeTrue();
});
