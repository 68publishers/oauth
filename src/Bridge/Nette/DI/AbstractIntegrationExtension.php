<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use SixtyEightPublishers\OAuth\Bridge\Nette\DI\Config\IntegrationConfig;
use SixtyEightPublishers\OAuth\Config\Config;
use SixtyEightPublishers\OAuth\OAuthFlow;
use function array_merge;
use function assert;
use function is_string;

abstract class AbstractIntegrationExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        $clientConfigSchema = Expect::structure(
            array_merge(
                $this->getFlowConfigOptions(),
                [
                    'enabled' => Expect::bool(true)->dynamic(),
                ],
            ),
        )->castTo('array');

        return Expect::structure([
            'flowName' => Expect::string($this->getDefaultFlowName()),
            'config' => Expect::anyOf(Expect::string(), Expect::type(Statement::class), $clientConfigSchema)
                ->required()
                ->before(static function (mixed $factory): Statement|array {
                    if (is_string($factory)) {
                        $factory = new Statement($factory);
                    }

                    return $factory;
                }),
            'authenticator' => Expect::anyOf(Expect::string(), Expect::type(Statement::class))
                ->required()
                ->before(static function (mixed $factory): Statement {
                    return $factory instanceof Statement ? $factory : new Statement($factory);
                }),
        ])->castTo(IntegrationConfig::class);
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();
        assert($config instanceof IntegrationConfig);

        $builder->addDefinition($this->prefix('config'))
            ->setAutowired(false)
            ->setFactory(
                $config->config instanceof Statement
                    ? $config->config
                    : $this->createConfigFromArray($config->config),
            );

        $builder->addDefinition($this->prefix('authenticator'))
            ->setAutowired(false)
            ->setFactory($config->authenticator);

        $authorizatorServiceName = $this->defineAuthorizatorService()->getName();
        assert(is_string($authorizatorServiceName));

        $builder->addDefinition($this->prefix('flow'))
            ->setAutowired(false)
            ->setFactory(
                factory: OAuthFlow::class,
                args: [
                    'name' => $config->flowName,
                    'config' => new Reference($this->prefix('config')),
                    'authorizator' => new Reference($authorizatorServiceName),
                    'authenticator' => new Reference($this->prefix('authenticator')),
                ],
            )
            ->addTag(
                tag: OAuthExtension::TagOAuthFlow,
                attr: $config->flowName,
            );
    }

    abstract protected function getDefaultFlowName(): string;

    /**
     * @return array<string, Schema>
     */
    abstract protected function getFlowConfigOptions(): array;

    abstract protected function defineAuthorizatorService(): ServiceDefinition;

    /**
     * @param array<string, mixed> $config
     */
    private function createConfigFromArray(array $config): Statement
    {
        $flowEnabled = $config['enabled'];
        unset($config['enabled']);

        return new Statement(
            entity: Config::class,
            arguments: [
                'flowEnabled' => $flowEnabled,
                'options' => $config,
            ],
        );
    }
}
