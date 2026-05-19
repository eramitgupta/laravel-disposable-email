<?php

test('toBeValidDomain - detects valid domains')
    ->expect(['foo.com', 'foo.bar.com', '123-foobar.com', 'xn--i-7iq.ws'])
    ->each()
    ->toBeValidDomain();

test('toBeValidDomain - detects invalid domains')
    ->expect(['', 'foobar', 'example.com ', "'example.com'", "example.com'", '-example.com', 'http://example.com'])
    ->each()
    ->not->toBeValidDomain();
