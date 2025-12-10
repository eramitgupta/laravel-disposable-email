<?php

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Services\EmailServices;
use Illuminate\Console\Command;

class InstallDisposableEmail extends Command
{
    protected $signature = 'erag:install-disposable-email';

    protected $description = 'Publish config and initialize disposable domain file.';

    public function handle()
    {
        EmailServices::clearCache();

        $this->call('vendor:publish', [
            '--tag' => 'erag:publish-disposable-config',
            '--force' => true,
        ]);

        $this->info('✅ Disposable Email Package Installed Successfully!');
    }
}
