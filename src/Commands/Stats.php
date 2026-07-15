<?php

namespace LaravelDisposableEmail\Commands;

use Illuminate\Console\Command;
use LaravelDisposableEmail\Contracts\Loader;
use LaravelDisposableEmail\Support\Normalizer;

class Stats extends Command
{
    protected $signature = 'disposable:stats';

    protected $description = 'Show disposable email package domain and cache stats.';

    public function handle(Loader $loader): void
    {
        $blacklistDirectory = config('disposable-email.blacklist_file');
        $files = is_string($blacklistDirectory) ? glob($blacklistDirectory.'/*.txt') ?: [] : [];
        $customDomains = [];
        $latestSync = null;

        foreach ($files as $file) {
            $modifiedAt = filemtime($file);

            if ($modifiedAt !== false && ($latestSync === null || $modifiedAt > $latestSync)) {
                $latestSync = $modifiedAt;
            }

            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $domain) {
                $domain = Normalizer::domain($domain);

                if (Normalizer::isValid($domain)) {
                    $customDomains[$domain] = true;
                }
            }
        }

        $whitelist = config('disposable-email.whitelist', []);
        $remoteUrls = config('disposable-email.remote_url', []);

        $this->table(['Metric', 'Value'], [
            ['Built-in domains', number_format(count($loader->builtIn()))],
            ['Custom blacklist domains', number_format(count($customDomains))],
            ['Total domains', number_format(count($loader->all()))],
            ['Whitelist domains', number_format(is_array($whitelist) ? count($whitelist) : 0)],
            ['Remote sources', number_format(is_array($remoteUrls) ? count($remoteUrls) : 0)],
            ['Cache enabled', config('disposable-email.cache_enabled') ? 'yes' : 'no'],
            ['Cache TTL', (string) config('disposable-email.cache_ttl')],
            ['Block subdomains', config('disposable-email.block_subdomains', true) ? 'yes' : 'no'],
            ['Last synced', $latestSync === null ? 'never' : date('Y-m-d H:i:s', $latestSync)],
        ]);
    }
}
