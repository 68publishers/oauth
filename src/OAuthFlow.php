<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth;

use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\Authentication\AuthenticatorInterface;
use SixtyEightPublishers\OAuth\Authorization\AuthorizatorInterface;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\Exception\OAuthFlowIsDisabledException;

final class OAuthFlow implements OAuthFlowInterface
{
    public function __construct(
        private readonly string $name,
        private readonly ConfigInterface $config,
        private readonly AuthorizatorInterface $authorizator,
        private readonly AuthenticatorInterface $authenticator,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->config->isFlowEnabled();
    }

    public function getAuthorizationUrl(string $redirectUri, array $options = []): string
    {
        $this->throwExceptionIfDisabled();

        return $this->authorizator->getAuthorizationUrl(
            config: $this->config,
            redirectUri: $redirectUri,
            options: $options,
        );
    }

    public function run(array $parameters): IIdentity
    {
        $this->throwExceptionIfDisabled();

        return $this->authenticator->authenticate(
            flowName: $this->name,
            authorizationResult: $this->authorizator->authorize(
                config: $this->config,
                parameters: $parameters,
            ),
        );
    }

    /**
     * @throws OAuthFlowIsDisabledException
     */
    private function throwExceptionIfDisabled(): void
    {
        if (!$this->isEnabled()) {
            throw OAuthFlowIsDisabledException::create(
                flowName: $this->name,
            );
        }
    }
}
