<?php

it('updates emails database via artisan command')
    ->artisan('erag:sync-disposable-email-list')
    ->expectsOutputToContain('Sync complete. Synced: 1. Failed: 0.')
    ->assertExitCode(0);
