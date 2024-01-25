<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI;

use SixtyEightPublishers\OAuth\Authorization\Facebook\FacebookAuthorizator;
use SixtyEightPublishers\OAuth\Config\Config;
use SixtyEightPublishers\OAuth\Tests\Fixtures\AuthenticatorFixture;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class FacebookOAuthExtensionTest extends TestCase
{
    use IntegrationExtensionTestTrait;

    public function extensionShouldBeRegisteredDataProvider(): array
    {
        return [
            'minimal' => [
                __DIR__ . '/config/facebook/config.minimal.neon',
                'facebook',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'disabled' => [
                __DIR__ . '/config/facebook/config.disabled.neon',
                'facebook',
                false,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'custom flow name' => [
                __DIR__ . '/config/facebook/config.customFlowName.neon',
                'custom_facebook',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'config as statement' => [
                __DIR__ . '/config/facebook/config.configAsStatement.neon',
                'facebook',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'config as service' => [
                __DIR__ . '/config/facebook/config.configAsService.neon',
                'facebook',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
            'authenticator as service' => [
                __DIR__ . '/config/facebook/config.authenticatorAsService.neon',
                'facebook',
                true,
                AuthenticatorFixture::class,
                Config::class,
                [
                    FacebookAuthorizator::OptClientId => 'client',
                    FacebookAuthorizator::OptClientSecret => 'secret',
                ],
            ],
        ];
    }

    protected function getAuthorizatorClassname(): string
    {
        return FacebookAuthorizator::class;
    }
}

(new FacebookOAuthExtensionTest())->run();
