<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI;

use SixtyEightPublishers\OAuth\Authorization\Azure\AzureAuthorizator;
use SixtyEightPublishers\OAuth\Config\Config;
use SixtyEightPublishers\OAuth\Tests\Fixtures\AuthenticatorFixture;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class AzureOAuthExtensionTest extends TestCase
{
    use IntegrationExtensionTestTrait;

    protected string $authorizatorClassname = AzureAuthorizator::class;

    public function extensionShouldBeRegisteredDataProvider(): array
    {
        return [
            'minimal' => [
                __DIR__ . '/config/azure/config.minimal.neon',
                'azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'disabled' => [
                __DIR__ . '/config/azure/config.disabled.neon',
                'azure',
                false,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'custom flow name' => [
                __DIR__ . '/config/azure/config.customFlowName.neon',
                'custom_azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'with tenant id' => [
                __DIR__ . '/config/azure/config.withTenantId.neon',
                'azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                    AzureAuthorizator::OptTenantId => '123',
                ],
            ],
            'config as statement' => [
                __DIR__ . '/config/azure/config.configAsStatement.neon',
                'azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'config as service' => [
                __DIR__ . '/config/azure/config.configAsService.neon',
                'azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'authenticator as service' => [
                __DIR__ . '/config/azure/config.authenticatorAsService.neon',
                'azure',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    AzureAuthorizator::OptClientId => 'client',
                    AzureAuthorizator::OptClientSecret => 'secret',
                ],
            ],
        ];
    }

    protected function getAuthorizatorClassname(): string
    {
        return AzureAuthorizator::class;
    }
}

(new AzureOAuthExtensionTest())->run();
