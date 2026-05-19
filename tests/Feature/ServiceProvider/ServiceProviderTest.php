<?php

it('registers the package service provider')
    ->expect(fn () => app()->getLoadedProviders())
    ->toHaveKey('EragLaravelDisposableEmail\LaravelDisposableEmailServiceProvider');
