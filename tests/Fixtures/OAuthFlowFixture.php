<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Fixtures;

use BadMethodCallException;
use Closure;
use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\OAuthFlowInterface;

final class OAuthFlowFixture implements OAuthFlowInterface
{
    /**
     * @param null|Closure(string $redirectUri, array $options): string $getAuthorizationUrlHandler
     * @param null|Closure(array $parameters): IIdentity                $runHandler
     */
    public function __construct(
        private readonly string $name,
        private readonly bool $enabled,
        private readonly ?Closure $getAuthorizationUrlHandler = null,
        private readonly ?Closure $runHandler = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getAuthorizationUrl(string $redirectUri, array $options = []): string
    {
        if (null === $this->getAuthorizationUrlHandler) {
            throw new BadMethodCallException(
                message: 'Handler for method getAuthorizationUrl() not provided.',
            );
        }

        return ($this->getAuthorizationUrlHandler)($redirectUri, $options);
    }

    public function run(array $parameters): IIdentity
    {
        if (null === $this->runHandler) {
            throw new BadMethodCallException(
                message: 'Handler for method run() not provided.',
            );
        }

        return ($this->runHandler)($parameters);
    }
}
