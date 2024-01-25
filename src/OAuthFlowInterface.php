<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth;

use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\Exception\AuthenticationException;
use SixtyEightPublishers\OAuth\Exception\AuthorizationException;
use SixtyEightPublishers\OAuth\Exception\OAuthFlowIsDisabledException;
use SixtyEightPublishers\OAuth\Exception\UnableToConstructAuthorizationUrlException;

interface OAuthFlowInterface
{
    public function getName(): string;

    public function isEnabled(): bool;

    /**
     * @param array<string, scalar> $options
     *
     * @throws OAuthFlowIsDisabledException
     * @throws UnableToConstructAuthorizationUrlException
     */
    public function getAuthorizationUrl(string $redirectUri, array $options = []): string;

    /**
     * @param array<string, scalar> $parameters
     *
     * @throws OAuthFlowIsDisabledException
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function run(array $parameters): IIdentity;
}
