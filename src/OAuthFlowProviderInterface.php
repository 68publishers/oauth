<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth;

use SixtyEightPublishers\OAuth\Exception\MissingOAuthFlowException;

interface OAuthFlowProviderInterface
{
    /**
     * @throws MissingOAuthFlowException
     */
    public function get(string $name): OAuthFlowInterface;

    /**
     * @return array<int, OAuthFlowInterface>
     */
    public function all(): array;
}
