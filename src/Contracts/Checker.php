<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Contracts;

use LaravelDisposableEmail\Support\Result;

interface Checker
{
    public function check(string $emailOrDomain): Result;

    public function email(string $email): bool;

    public function domain(string $domain): bool;
}
