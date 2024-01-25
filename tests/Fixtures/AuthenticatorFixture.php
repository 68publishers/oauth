<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Fixtures;

use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\Authentication\AuthenticatorInterface;
use SixtyEightPublishers\OAuth\Authorization\AuthorizationResult;
use SixtyEightPublishers\OAuth\Exception\AuthenticationException;

final class AuthenticatorFixture implements AuthenticatorInterface
{
    public function __construct(
        private readonly ?IIdentity $identity = null,
    ) {}

    public function authenticate(string $flowName, AuthorizationResult $authorizationResult): IIdentity
    {
        if (null === $this->identity) {
            throw new AuthenticationException();
        }

        return $this->identity;
    }
}
