<?php

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use Illuminate\Console\Command;

class DisposableEmailStats extends Command
{
    protected $signature = 'disposable:stats';

    protected $description = 'Show disposable email package domain and cache stats.';

    public function handle(): void
    {
        $blacklistDirectory = config('disposable-email.blacklist_file');
        $files = glob($blacklistDirectory.'/*.txt') ?: [];
        $customDomains = [];
        $latestSync = null;

        foreach ($files as $file) {
            $modifiedAt = filemtime($file);

            if ($modifiedAt !== false && ($latestSync === null || $modifiedAt > $latestSync)) {
                $latestSync = $modifiedAt;
            }

            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

            foreach ($lines as $line) {
                $domain = strtolower(trim($line));

                if (str_contains($domain, '@')) {
                    [, $domain] = explode('@', $domain, 2);
                    $domain = trim($domain);
                }

                if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
                    $customDomains[$domain] = true;
                }
            }
        }

        $whitelist = config('disposable-email.whitelist', []);
        $remoteUrls = config('disposable-email.remote_url', []);

        $rows = [
            ['Built-in domains', number_format(count(DisposableEmailRule::getBuiltInProviders()))],
            ['Custom blacklist domains', number_format(count($customDomains))],
            ['Total domains', number_format(count(DisposableEmailRule::getDefaultUnauthorizedProviders()))],
            ['Whitelist domains', number_format(is_array($whitelist) ? count($whitelist) : 0)],
            ['Remote sources', number_format(is_array($remoteUrls) ? count($remoteUrls) : 0)],
            ['Cache enabled', config('disposable-email.cache_enabled') ? 'yes' : 'no'],
            ['Cache TTL', (string) config('disposable-email.cache_ttl')],
            ['Block subdomains', config('disposable-email.block_subdomains', true) ? 'yes' : 'no'],
            ['Last synced', $latestSync === null ? 'never' : date('Y-m-d H:i:s', $latestSync)],
        ];

        $this->table(['Metric', 'Value'], $rows);
    }
}
