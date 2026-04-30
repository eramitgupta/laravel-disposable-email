<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class DisposableEmailResult
{
    public function __construct(
        protected bool $disposable,
        protected string $domain,
        protected ?string $matchedDomain = null,
        protected ?string $source = null
    ) {}

    public function disposable(): bool
    {
        return $this->disposable;
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function matchedDomain(): ?string
    {
        return $this->matchedDomain;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function whitelisted(): bool
    {
        return $this->source === 'whitelist';
    }

    public function toArray(): array
    {
        return [
            'disposable' => $this->disposable,
            'domain' => $this->domain,
            'matched_domain' => $this->matchedDomain,
            'source' => $this->source,
        ];
    }
}
