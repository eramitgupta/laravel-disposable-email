<?php

namespace LaravelDisposableEmail\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelDisposableEmail\Contracts\Checker;
use LaravelDisposableEmail\Rules\DisposableEmail;
use LaravelDisposableEmail\Support\Result;

/**
 * @method static bool email(string $email)
 * @method static bool domain(string $emailOrDomain)
 * @method static Result check(string $emailOrDomain)
 */
class Disposable extends Facade
{
    public static function rule(): DisposableEmail
    {
        return new DisposableEmail(app(Checker::class));
    }

    public static function make(): DisposableEmail
    {
        return self::rule();
    }

    protected static function getFacadeAccessor(): string
    {
        return Checker::class;
    }
}
