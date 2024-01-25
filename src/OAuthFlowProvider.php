<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use SixtyEightPublishers\OAuth\Exception\MissingOAuthFlowException;
use function array_keys;
use function array_map;
use function assert;

final class OAuthFlowProvider implements OAuthFlowProviderInterface
{
    /**
     * @param array<string, string> $flowServiceNames
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $flowServiceNames = [],
    ) {}

    public function get(string $name): OAuthFlowInterface
    {
        if (!isset($this->flowServiceNames[$name])) {
            throw MissingOAuthFlowException::create(
                flowName: $name,
            );
        }

        try {
            $flow = $this->container->getService($this->flowServiceNames[$name]);
        } catch (MissingServiceException $e) {
            throw MissingOAuthFlowException::create(
                flowName: $name,
                previous: $e,
            );
        }

        assert($flow instanceof OAuthFlowInterface);

        return $flow;
    }

    public function all(): array
    {
        return array_map(
            fn (string $name): OAuthFlowInterface => $this->get($name),
            array_keys($this->flowServiceNames),
        );
    }
}
