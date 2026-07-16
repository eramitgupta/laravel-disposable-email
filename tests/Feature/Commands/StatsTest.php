<?php

it('displays stats via artisan command')
    ->artisan('disposable:stats')
    ->expectsOutputToContain('Built-in domains')
    ->expectsOutputToContain('Custom blacklist domains')
    ->expectsOutputToContain('Total domains')
    ->expectsOutputToContain('Whitelist domains')
    ->expectsOutputToContain('Remote sources')
    ->expectsOutputToContain('Cache enabled')
    ->expectsOutputToContain('Cache TTL')
    ->expectsOutputToContain('Block subdomains')
    ->expectsOutputToContain('Last synced')
    ->assertExitCode(0);
