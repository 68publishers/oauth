<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery;
use Nette\Security\IIdentity;
use SixtyEightPublishers\OAuth\Authentication\AuthenticatorInterface;
use SixtyEightPublishers\OAuth\Authorization\AuthorizationResult;
use SixtyEightPublishers\OAuth\Authorization\AuthorizatorInterface;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\Exception\OAuthFlowIsDisabledException;
use SixtyEightPublishers\OAuth\OAuthFlow;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

final class OAuthFlowTest extends TestCase
{
    public function testNameShouldBeReturned(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: Mockery::mock(ConfigInterface::class),
            authorizator: Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );

        Assert::same('test', $flow->getName());
    }

    public function testFlowShouldBeEnabled(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(true);

        Assert::true($flow->isEnabled());
    }

    public function testFlowShouldBeDisabled(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(false);

        Assert::false($flow->isEnabled());
    }

    public function testExceptionShouldBeThrownIfFlowIsDisabledWhenReturningAuthorizationUrl(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(false);

        Assert::exception(
            static fn () => $flow->getAuthorizationUrl('https://www.example.com'),
            OAuthFlowIsDisabledException::class,
            'OAuth flow with the name "test" is disabled.',
        );
    }

    public function testAuthorizationUrlShouldBeReturned(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: $authorizator = Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );
        $redirectUrl = 'https://www.example.com';
        $authorizationUrl = 'https://oauth.service.com';
        $options = [
            'state' => '__state__',
        ];

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(true);

        $authorizator
            ->shouldReceive('getAuthorizationUrl')
            ->once()
            ->with($config, $redirectUrl, $options)
            ->andReturn($authorizationUrl);

        Assert::same($authorizationUrl, $flow->getAuthorizationUrl($redirectUrl, $options));
    }

    public function testExceptionShouldBeThrownIfFlowIsDisabledWhenFlowRun(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: Mockery::mock(AuthorizatorInterface::class),
            authenticator: Mockery::mock(AuthenticatorInterface::class),
        );

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(false);

        Assert::exception(
            static fn () => $flow->run([]),
            OAuthFlowIsDisabledException::class,
            'OAuth flow with the name "test" is disabled.',
        );
    }

    public function testFlowShouldRun(): void
    {
        $flow = new OAuthFlow(
            name: 'test',
            config: $config = Mockery::mock(ConfigInterface::class),
            authorizator: $authorizator = Mockery::mock(AuthorizatorInterface::class),
            authenticator: $authenticator = Mockery::mock(AuthenticatorInterface::class),
        );
        $parameters = [
            'code' => '__code__',
        ];
        $authorizationResult = new AuthorizationResult(
            resourceOwner: Mockery::mock(ResourceOwnerInterface::class),
            accessToken: Mockery::mock(AccessTokenInterface::class),
        );
        $identity = Mockery::mock(IIdentity::class);

        $config
            ->shouldReceive('isFlowEnabled')
            ->once()
            ->andReturn(true);

        $authorizator
            ->shouldReceive('authorize')
            ->once()
            ->with($config, $parameters)
            ->andReturn($authorizationResult);

        $authenticator
            ->shouldReceive('authenticate')
            ->once()
            ->with('test', $authorizationResult)
            ->andReturn($identity);

        Assert::same($identity, $flow->run($parameters));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new OAuthFlowTest())->run();
