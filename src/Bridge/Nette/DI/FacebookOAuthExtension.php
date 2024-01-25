<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\DI;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use SixtyEightPublishers\OAuth\Authorization\Facebook\FacebookAuthorizator;

final class FacebookOAuthExtension extends AbstractIntegrationExtension
{
    protected function getDefaultFlowName(): string
    {
        return 'facebook';
    }

    protected function getFlowConfigOptions(): array
    {
        return [
            FacebookAuthorizator::OptClientId => Expect::string()
                ->required()
                ->dynamic(),
            FacebookAuthorizator::OptClientSecret => Expect::string()
                ->required()
                ->dynamic(),
            FacebookAuthorizator::OptGraphApiVersion => Expect::string()
                ->required()
                ->dynamic(),
            FacebookAuthorizator::OptOptions => Expect::array(),
        ];
    }

    protected function defineAuthorizatorService(): ServiceDefinition
    {
        return $this->getContainerBuilder()
            ->addDefinition($this->prefix('authorizator'))
            ->setAutowired(false)
            ->setFactory(FacebookAuthorizator::class);
    }
}
