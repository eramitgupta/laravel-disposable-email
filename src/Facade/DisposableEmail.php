<?php

namespace EragLaravelDisposableEmail\Facade;

use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isDisposable(string $email)
 * @method static DisposableEmailRule make()
 */
class DisposableEmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'disposable-email';
    }
}
