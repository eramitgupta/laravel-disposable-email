<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class Stats
{
    public function __construct(private readonly Domain $domains = new Domain) {}

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public function rows(): array
    {
        $remoteUrls = config('disposable-email.remote_url', []);

        return [
            ['Built-in domains', number_format(count(Email::domains()))],
            ['Custom blacklist domains', number_format(count($this->domains->custom()))],
            ['Total domains', number_format(count($this->domains->domains()))],
            ['Whitelist domains', number_format(count($this->domains->whitelist()))],
            ['Remote sources', number_format(is_array($remoteUrls) ? count($remoteUrls) : 0)],
            ['Cache enabled', config('disposable-email.cache_enabled') ? 'yes' : 'no'],
            ['Cache TTL', (string) config('disposable-email.cache_ttl')],
            ['Block subdomains', config('disposable-email.block_subdomains', true) ? 'yes' : 'no'],
            ['Last synced', $this->latestSync()],
        ];
    }

    private function latestSync(): string
    {
        $directory = config('disposable-email.blacklist_file');
        $latest = null;

        if (! is_string($directory)) {
            return 'never';
        }

        foreach (glob($directory.'/*.txt') ?: [] as $file) {
            $modifiedAt = filemtime($file);

            if ($modifiedAt !== false && ($latest === null || $modifiedAt > $latest)) {
                $latest = $modifiedAt;
            }
        }

        return $latest === null ? 'never' : date('Y-m-d H:i:s', $latest);
    }
}
