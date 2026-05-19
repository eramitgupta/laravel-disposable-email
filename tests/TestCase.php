<?php

namespace Tests;

use EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDisposableEmailServiceProvider::class,
        ];
    }
}
