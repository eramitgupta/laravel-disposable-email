<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Contracts;

interface Loader
{
    /**
     * @return array<int, string>
     */
    public function all(): array;

    /**
     * @return array<string, string>
     */
    public function sourceMap(): array;

    /**
     * @return array<int, string>
     */
    public function builtIn(): array;
}
