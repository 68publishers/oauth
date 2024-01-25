<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI;

use SixtyEightPublishers\OAuth\OAuthFlowProvider;
use SixtyEightPublishers\OAuth\OAuthFlowProviderInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

final class OAuthExtensionTest extends TestCase
{
    public function testExtensionShouldBeRegistered(): void
    {
        $container = ContainerFactory::create(
            configFiles: __DIR__ . '/config/oauth/config.neon',
        );
        $provider = $container->getByType(OAuthFlowProviderInterface::class);

        Assert::type(OAuthFlowProvider::class, $provider);
        Assert::same([], $provider->all());
    }
}

(new OAuthExtensionTest())->run();
