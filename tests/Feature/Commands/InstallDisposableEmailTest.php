<?php

it('can install the package via artisan command')
    ->artisan('erag:install-disposable-email')
    ->expectsOutputToContain('Publishing [erag:publish-disposable-config] assets.')
    ->expectsOutputToContain('✅ Disposable Email Package Installed Successfully!')
    ->assertExitCode(0);
