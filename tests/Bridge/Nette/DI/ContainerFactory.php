<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI;

use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Tester\Helpers;
use function sys_get_temp_dir;
use function uniqid;

final class ContainerFactory
{
    private function __construct() {}

    /**
     * @param string|array<string> $configFiles
     */
    public static function create(string|array $configFiles): Container
    {
        $tempDir = sys_get_temp_dir() . '/' . uniqid('68publishers:AmpClientPhp', true);

        Helpers::purge($tempDir);

        $configurator = new Configurator();
        $configurator->setTempDirectory($tempDir);
        $configurator->setDebugMode(false);
        $configurator->addStaticParameters([
            'resources' => __DIR__ . '/../../../resources',
        ]);

        foreach ((array) $configFiles as $configFile) {
            $configurator->addConfig($configFile);
        }

        return $configurator->createContainer();
    }
}
