<?php

namespace Tests;

use LaravelDisposableEmail\DisposableEmailServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            DisposableEmailServiceProvider::class,
        ];
    }
}
