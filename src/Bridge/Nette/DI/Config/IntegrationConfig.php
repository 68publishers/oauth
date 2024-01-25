<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\DI\Config;

use Nette\DI\Definitions\Statement;

final class IntegrationConfig
{
    public string $flowName;

    /** @var Statement|array<string, mixed> */
    public Statement|array $config;

    public Statement $authenticator;
}
