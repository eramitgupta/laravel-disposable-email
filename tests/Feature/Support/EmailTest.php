<?php

use EragLaravelDisposableEmail\Support\Email;

test('All built-in domains entries are valid domains')
    ->expect(Email::domains())
    ->each()
    ->toBeValidDomain();
