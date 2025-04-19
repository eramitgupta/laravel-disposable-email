<?php

namespace EragLaravelDisposableEmail\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isDisposable(string $email)
 * @method static \Erag\LaravelDisposableEmail\Rules\DisposableEmailRule make()
 */
class DisposableEmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'disposable-email';
    }
}
