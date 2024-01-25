<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Config;

use SixtyEightPublishers\OAuth\Exception\InvalidConfigurationException;

final class Config implements ConfigInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly bool $flowEnabled,
        private readonly array $options,
    ) {}

    public function isFlowEnabled(): bool
    {
        return $this->flowEnabled;
    }

    public function has(string $key): bool
    {
        return isset($this->options[$key]);
    }

    public function get(string $key): mixed
    {
        if (!isset($this->options[$key])) {
            throw InvalidConfigurationException::missingOption(
                optionKey: $key,
            );
        }

        return $this->options[$key];
    }
}
