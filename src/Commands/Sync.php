<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Support\Cache;
use EragLaravelDisposableEmail\Support\ResponseParser;
use EragLaravelDisposableEmail\Support\UrlList;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class Sync extends Command
{
    protected $signature = 'erag:sync-disposable-email-list';

    protected $description = 'Fetch and update the disposable email domains list';

    public function handle(): int
    {
        $remoteUrls = $this->remoteUrls();
        $directory = config('disposable-email.blacklist_file');

        if (! is_string($directory) || trim($directory) === '') {
            $this->error('Invalid disposable-email.blacklist_file config value.');

            return self::FAILURE;
        }

        if ($remoteUrls === []) {
            $this->error('No valid remote URLs configured in disposable-email.remote_url.');

            return self::FAILURE;
        }

        if (! $this->ensureDirectoryExists($directory)) {
            return self::FAILURE;
        }

        $synced = 0;
        $failed = 0;

        foreach ($remoteUrls as $url) {
            $result = $this->syncUrl($url, $directory);

            if ($result) {
                $synced++;
            } else {
                $failed++;
            }
        }

        // Cleared after writing so a lookup during the sync cannot re-cache the old list.
        Cache::clear();

        $this->newLine();
        $this->info("Sync complete. Synced: {$synced}. Failed: {$failed}.");

        return $synced > 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, string>
     */
    protected function remoteUrls(): array
    {
        return UrlList::from(config('disposable-email.remote_url', []));
    }

    protected function ensureDirectoryExists(string $directory): bool
    {
        try {
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directory created at: {$directory}");
            }

            return true;
        } catch (Throwable $exception) {
            $this->error("Unable to create blacklist directory [{$directory}]: {$exception->getMessage()}");

            return false;
        }
    }

    protected function syncUrl(string $url, string $directory): bool
    {
        $this->line("Fetching: {$url}");

        $remoteDomains = $this->download($url);

        if ($remoteDomains === null) {
            return false;
        }

        $filePath = $directory.DIRECTORY_SEPARATOR.$this->filename($url);

        try {
            $addedCount = $this->merge($filePath, $remoteDomains);
        } catch (Throwable $exception) {
            $this->error("Unable to write [{$filePath}]: {$exception->getMessage()}");

            return false;
        }

        if ($addedCount === 0) {
            $this->info("No new domains found in [{$url}]. {$filePath} is already up to date.");

            return true;
        }

        $this->info('Added '.number_format($addedCount)." new domains to {$filePath}");

        return true;
    }

    protected function syncTimeout(): int
    {
        $timeout = config('disposable-email.sync_timeout', 30);

        if (! is_numeric($timeout)) {
            return 30;
        }

        $timeout = (int) $timeout;

        return $timeout > 0 ? $timeout : 30;
    }

    private function filename(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $name = is_string($path) ? pathinfo($path, PATHINFO_FILENAME) : '';
        $name = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name));
        $name = trim($name, '-_');

        return ($name === '' ? 'disposable-domains' : $name).'.txt';
    }

    private function fetch(string $url): Response
    {
        return Http::timeout($this->syncTimeout())->retry(2, 500)->get($url);
    }

    /**
     * Download and validate a remote list before any local file is touched.
     *
     * @return array<int, string>|null
     */
    private function download(string $url): ?array
    {
        try {
            $response = $this->fetch($url);
        } catch (Throwable $exception) {
            $this->error("Request failed for [{$url}]: {$exception->getMessage()}");

            return null;
        }

        if (! $response->successful()) {
            $this->error("Failed to fetch [{$url}]. HTTP status: {$response->status()}.");

            return null;
        }

        $body = $response->body();

        if ($this->isMarkup($body)) {
            $this->error("Received an HTML/XML page instead of a domain list from [{$url}].");

            return null;
        }

        $domains = ResponseParser::parse($body);

        if ($domains === []) {
            $this->warn("No valid domains found in [{$url}]. Skipping write.");

            return null;
        }

        return $domains;
    }

    private function isMarkup(string $body): bool
    {
        return str_starts_with(ltrim($body, "\xEF\xBB\xBF \t\n\r\0\x0B"), '<');
    }

    /**
     * Add the remote domains missing from the local list. The list is read and
     * replaced under an exclusive lock so overlapping syncs cannot drop entries.
     *
     * @param  array<int, string>  $remoteDomains
     */
    private function merge(string $filePath, array $remoteDomains): int
    {
        $lock = $this->lock($filePath);

        try {
            $localList = $this->readLocalList($filePath);
            $newDomains = $this->newDomains($localList, $remoteDomains);

            if ($newDomains !== []) {
                $this->replace($filePath, $this->appendDomains($localList, $newDomains));
            }

            return count($newDomains);
        } finally {
            $this->unlock($lock);
        }
    }

    /**
     * Lock a sidecar file rather than the list itself, because the list is
     * swapped out by rename and a lock on the old file would not carry over.
     *
     * @return resource
     */
    private function lock(string $filePath)
    {
        $lockPath = $filePath.'.lock';
        $handle = fopen($lockPath, 'c');

        if ($handle === false) {
            throw new RuntimeException("Unable to open lock file [{$lockPath}].");
        }

        try {
            retry(50, function () use ($handle): void {
                if (! flock($handle, LOCK_EX | LOCK_NB)) {
                    throw new RuntimeException('Another sync is currently updating this list.');
                }
            }, 100);
        } catch (Throwable $exception) {
            fclose($handle);

            throw $exception;
        }

        return $handle;
    }

    /**
     * @param  resource  $handle
     */
    private function unlock($handle): void
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * An existing list that cannot be read aborts the sync, so it is never
     * mistaken for an empty list and replaced.
     */
    private function readLocalList(string $filePath): string
    {
        if (! File::exists($filePath)) {
            return '';
        }

        if (! File::isFile($filePath) || ! File::isReadable($filePath)) {
            throw new RuntimeException('The existing list is not a readable file.');
        }

        return File::get($filePath);
    }

    /**
     * Remote domains that are not yet in the local list.
     *
     * @param  array<int, string>  $remoteDomains
     * @return array<int, string>
     */
    private function newDomains(string $localList, array $remoteDomains): array
    {
        $localDomains = array_flip(ResponseParser::parse($localList));
        $newDomains = [];

        foreach ($remoteDomains as $domain) {
            if (! isset($localDomains[$domain])) {
                $newDomains[] = $domain;
            }
        }

        return $newDomains;
    }

    /**
     * Keep the existing list byte-for-byte and add the new domains at the end.
     *
     * @param  array<int, string>  $domains
     */
    private function appendDomains(string $localList, array $domains): string
    {
        // Start on a fresh line when the existing list does not end with one.
        if ($localList !== '' && ! str_ends_with($localList, "\n") && ! str_ends_with($localList, "\r")) {
            $localList .= PHP_EOL;
        }

        return $localList.implode(PHP_EOL, $domains).PHP_EOL;
    }

    /**
     * Write the complete list to a temporary file in the same directory and
     * rename it over the original, so readers only ever see a whole file.
     */
    private function replace(string $filePath, string $contents): void
    {
        $temporaryPath = $filePath.'.'.bin2hex(random_bytes(8)).'.tmp';

        try {
            if (File::put($temporaryPath, $contents) !== strlen($contents)) {
                throw new RuntimeException('Unable to write the temporary file.');
            }

            $permissions = File::exists($filePath) ? fileperms($filePath) : false;

            if ($permissions !== false && ! File::chmod($temporaryPath, $permissions & 0777)) {
                throw new RuntimeException('Unable to copy the existing file permissions.');
            }

            if (! File::move($temporaryPath, $filePath)) {
                throw new RuntimeException('Unable to replace the existing list.');
            }
        } finally {
            if (File::exists($temporaryPath)) {
                File::delete($temporaryPath);
            }
        }
    }
}
