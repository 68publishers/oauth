<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI;

use Closure;
use SixtyEightPublishers\OAuth\Authentication\AuthenticatorInterface;
use SixtyEightPublishers\OAuth\Authorization\AuthorizatorInterface;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\OAuthFlow;
use SixtyEightPublishers\OAuth\OAuthFlowProviderInterface;
use Tester\Assert;
use function assert;
use function call_user_func;

trait IntegrationExtensionTestTrait
{
    /**
     * @dataProvider extensionShouldBeRegisteredDataProvider
     */
    public function testExtensionShouldBeRegistered(
        string $configFile,
        string $flowName,
        bool $enabled,
        string $authenticatorClassname,
        string $configClassname,
        array $configOptions,
    ): void {
        $container = ContainerFactory::create(
            configFiles: $configFile,
        );

        $provider = $container->getByType(OAuthFlowProviderInterface::class);
        $flow = $provider->get($flowName);

        Assert::type(OAuthFlow::class, $flow);
        assert($flow instanceof OAuthFlow);

        Assert::same($flowName, $flow->getName());
        Assert::same($enabled, $flow->isEnabled());

        [$authorizator, $authenticator, $config] = call_user_func(
            callback: Closure::bind(
                closure: static fn (): array => [
                    $flow->authorizator,
                    $flow->authenticator,
                    $flow->config,
                ],
                newThis: null,
                newScope: OAuthFlow::class,
            ),
        );

        Assert::type($this->getAuthorizatorClassname(), $authorizator);
        Assert::type($authenticatorClassname, $authenticator);
        Assert::type($configClassname, $config);

        foreach ($configOptions as $key => $option) {
            Assert::same($option, $config->get($key));
        }
    }

    /**
     * @return array<int|string, array{
     *     0: string,
     *     1: string,
     *     2: bool,
     *     3: class-string<AuthenticatorInterface>,
     *     4: class-string<ConfigInterface>,
     *     5: array<string, mixed>,
     * }>
     */
    abstract public function extensionShouldBeRegisteredDataProvider(): array;

    /**
     * @return class-string<AuthorizatorInterface>
     */
    abstract protected function getAuthorizatorClassname(): string;
}
