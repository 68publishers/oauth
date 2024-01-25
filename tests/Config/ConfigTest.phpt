<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Config;

use SixtyEightPublishers\OAuth\Config\Config;
use SixtyEightPublishers\OAuth\Exception\InvalidConfigurationException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class ConfigTest extends TestCase
{
    public function testFlowIsEnabled(): void
    {
        $config = new Config(
            flowEnabled: true,
            options: [],
        );

        Assert::true($config->isFlowEnabled());
    }

    public function testFlowIsDisabled(): void
    {
        $config = new Config(
            flowEnabled: false,
            options: [],
        );

        Assert::false($config->isFlowEnabled());
    }

    public function testConfigHaveOption(): void
    {
        $config = new Config(
            flowEnabled: true,
            options: [
                'test' => '123',
            ],
        );

        Assert::true($config->has('test'));
    }

    public function testConfigDoesNotHaveOption(): void
    {
        $config = new Config(
            flowEnabled: true,
            options: [],
        );

        Assert::false($config->has('test'));
    }

    public function testExceptionShouldBeThrownWhenOptionCanNotBeReturned(): void
    {
        $config = new Config(
            flowEnabled: true,
            options: [],
        );

        Assert::exception(
            static fn () => $config->get('test'),
            InvalidConfigurationException::class,
            'Missing configuration option "test".',
        );
    }

    public function testOptionShouldBeReturned(): void
    {
        $config = new Config(
            flowEnabled: true,
            options: [
                'test' => '123',
            ],
        );

        Assert::same('123', $config->get('test'));
    }
}

(new ConfigTest())->run();
