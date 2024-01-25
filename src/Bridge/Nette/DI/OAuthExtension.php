<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use SixtyEightPublishers\OAuth\OAuthFlowProvider;
use SixtyEightPublishers\OAuth\OAuthFlowProviderInterface;
use function assert;
use function is_string;

final class OAuthExtension extends CompilerExtension
{
    public const TagOAuthFlow = 'oauth_flow';

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('flow_provider'))
            ->setAutowired(OAuthFlowProviderInterface::class)
            ->setType(OAuthFlowProviderInterface::class)
            ->setFactory(new Reference($this->prefix('flow_provider.default')));

        $builder->addDefinition($this->prefix('flow_provider.default'))
            ->setAutowired(false)
            ->setFactory(OAuthFlowProvider::class);
    }

    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();
        $defaultFlowProvider = $builder->getDefinition($this->prefix('flow_provider.default'));
        assert($defaultFlowProvider instanceof ServiceDefinition);

        $flowServiceNames = [];

        foreach ($builder->findByTag(self::TagOAuthFlow) as $serviceName => $tag) {
            if (!is_string($tag)) {
                continue;
            }

            $flowServiceNames[$tag] = $serviceName;
        }

        $defaultFlowProvider->setArgument('flowServiceNames', $flowServiceNames);
    }
}
