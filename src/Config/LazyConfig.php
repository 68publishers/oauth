<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Config;

use Closure;

class LazyConfig implements ConfigInterface
{
    private ?ConfigInterface $config = null;

    /**
     * @param Closure(): ConfigInterface $configFactory
     */
    public function __construct(
        private readonly Closure $configFactory,
    ) {}

    public function isFlowEnabled(): bool
    {
        return $this->getConfig()->isFlowEnabled();
    }

    public function has(string $key): bool
    {
        return $this->getConfig()->has($key);
    }

    public function get(string $key): mixed
    {
        return $this->getConfig()->get($key);
    }

    private function getConfig(): ConfigInterface
    {
        return null !== $this->config
            ? $this->config
            : $this->config = ($this->configFactory)();
    }
}
