<?php

use LaravelDisposableEmail\Contracts\Checker;
use LaravelDisposableEmail\Contracts\Loader;
use LaravelDisposableEmail\Contracts\Matcher;
use LaravelDisposableEmail\Data\BuiltInDomains;
use LaravelDisposableEmail\Rules\DisposableEmail;
use LaravelDisposableEmail\Services\DomainChecker;
use LaravelDisposableEmail\Services\DomainLoader;
use LaravelDisposableEmail\Services\DomainMatcher;
use LaravelDisposableEmail\Support\Result;

it('binds the package contracts to replaceable services', function () {
    expect(app(Checker::class))->toBeInstanceOf(DomainChecker::class)
        ->and(app(Loader::class))->toBeInstanceOf(DomainLoader::class)
        ->and(app(Matcher::class))->toBeInstanceOf(DomainMatcher::class);
});

it('exposes the new focused package classes', function () {
    expect(BuiltInDomains::all())->not->toBeEmpty()
        ->and(app(Checker::class)->check('user@example.com'))->toBeInstanceOf(Result::class)
        ->and(new DisposableEmail(app(Checker::class)))->toBeInstanceOf(DisposableEmail::class);
});
