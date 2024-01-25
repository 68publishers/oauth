<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authorization;

use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\Exception\AuthorizationException;
use SixtyEightPublishers\OAuth\Exception\UnableToConstructAuthorizationUrlException;

interface AuthorizatorInterface
{
    /**
     * @param array<string, scalar> $options
     *
     * @throws UnableToConstructAuthorizationUrlException
     */
    public function getAuthorizationUrl(ConfigInterface $config, string $redirectUri, array $options = []): string;

    /**
     * @param array<string, scalar> $parameters
     *
     * @throws AuthorizationException
     */
    public function authorize(ConfigInterface $config, array $parameters): AuthorizationResult;
}
