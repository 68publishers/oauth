<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests;

use Mockery;
use Nette\DI\Container;
use SixtyEightPublishers\OAuth\Exception\MissingOAuthFlowException;
use SixtyEightPublishers\OAuth\OAuthFlowInterface;
use SixtyEightPublishers\OAuth\OAuthFlowProvider;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

final class OAuthFlowProviderTest extends TestCase
{
    public function testExceptionShouldBeThrownOnUnknownFlowName(): void
    {
        $provider = new OAuthFlowProvider(
            container: new Container(),
            flowServiceNames: [],
        );

        Assert::exception(
            static fn () => $provider->get('test'),
            MissingOAuthFlowException::class,
            'OAuth flow with the name "test" is missing.',
        );
    }

    public function testExceptionShouldBeThrownOnMissingFlowService(): void
    {
        $provider = new OAuthFlowProvider(
            container: new Container(),
            flowServiceNames: [
                'test' => 'flow_service.test',
            ],
        );

        Assert::exception(
            static fn () => $provider->get('test'),
            MissingOAuthFlowException::class,
            'OAuth flow with the name "test" is missing.',
        );
    }

    public function testFlowShouldBeReturned(): void
    {
        $container = new Container();
        $provider = new OAuthFlowProvider(
            container: $container,
            flowServiceNames: [
                'test' => 'flow_service.test',
            ],
        );
        $flow = Mockery::mock(OAuthFlowInterface::class);

        $container->addService('flow_service.test', $flow);

        Assert::same($flow, $provider->get('test'));
    }

    public function testAllProvidersShouldBeThrown(): void
    {
        $container = new Container();
        $provider = new OAuthFlowProvider(
            container: $container,
            flowServiceNames: [
                'a' => 'flow_service.a',
                'b' => 'flow_service.b',
            ],
        );
        $flowA = Mockery::mock(OAuthFlowInterface::class);
        $flowB = Mockery::mock(OAuthFlowInterface::class);

        $container->addService('flow_service.a', $flowA);
        $container->addService('flow_service.b', $flowB);

        Assert::same([$flowA, $flowB], $provider->all());
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new OAuthFlowProviderTest())->run();
