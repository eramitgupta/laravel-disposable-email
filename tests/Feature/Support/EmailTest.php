<?php

use LaravelDisposableEmail\Data\BuiltInDomains;

test('All built-in domains entries are valid domains')
    ->expect(BuiltInDomains::all())
    ->each()
    ->toBeValidDomain();
