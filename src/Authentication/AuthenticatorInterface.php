<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authentication;

use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\Authorization\AuthorizationResult;
use SixtyEightPublishers\OAuth\Exception\AuthenticationException;

interface AuthenticatorInterface
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(string $flowName, AuthorizationResult $authorizationResult): IIdentity;
}
