<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\DI;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use SixtyEightPublishers\OAuth\Authorization\Azure\AzureAuthorizator;

final class AzureOAuthExtension extends AbstractIntegrationExtension
{
    protected function getDefaultFlowName(): string
    {
        return 'azure';
    }

    protected function getFlowConfigOptions(): array
    {
        return [
            AzureAuthorizator::OptClientId => Expect::string()
                ->required()
                ->dynamic(),
            AzureAuthorizator::OptClientSecret => Expect::string()
                ->required()
                ->dynamic(),
            AzureAuthorizator::OptTenantId => Expect::string()
                ->nullable()
                ->dynamic(),
            AzureAuthorizator::OptOptions => Expect::array(),
        ];
    }

    protected function defineAuthorizatorService(): ServiceDefinition
    {
        return $this->getContainerBuilder()
            ->addDefinition($this->prefix('authorizator'))
            ->setAutowired(false)
            ->setFactory(AzureAuthorizator::class);
    }
}
