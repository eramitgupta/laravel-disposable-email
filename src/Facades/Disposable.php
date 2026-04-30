<?php

namespace EragLaravelDisposableEmail\Facades;

use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use EragLaravelDisposableEmail\Support\DisposableEmailResult;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool Email(string $email)
 * @method static bool domain(string $emailOrDomain)
 * @method static DisposableEmailResult check(string $emailOrDomain)
 * @method static DisposableEmailRule rule()
 * @method static DisposableEmailRule make()
 */
class Disposable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'disposable-email';
    }
}
