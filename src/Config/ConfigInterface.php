<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Config;

use SixtyEightPublishers\OAuth\Exception\InvalidConfigurationException;

interface ConfigInterface
{
    public function isFlowEnabled(): bool;

    public function has(string $key): bool;

    /**
     * @throws InvalidConfigurationException
     */
    public function get(string $key): mixed;
}
