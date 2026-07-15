<?php

namespace LaravelDisposableEmail\Commands;

use Illuminate\Console\Command;
use LaravelDisposableEmail\Support\Cache;

class Install extends Command
{
    protected $signature = 'erag:install-disposable-email';

    protected $description = 'Publish config and initialize disposable domain file.';

    public function handle(): void
    {
        Cache::flush();

        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-disposable-config',
            '--force' => true,
        ]);

        $this->info('✅ Disposable Email Package Installed Successfully!');
    }
}
