<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authorization;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

final class AuthorizationResult
{
    public function __construct(
        public readonly AbstractProvider $client,
        public readonly ResourceOwnerInterface $resourceOwner,
        public readonly AccessTokenInterface $accessToken,
    ) {}
}
