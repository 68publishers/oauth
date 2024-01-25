<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authorization\Facebook;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Facebook;
use SixtyEightPublishers\OAuth\Authorization\AbstractAuthorizator;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use function array_merge;

final class FacebookAuthorizator extends AbstractAuthorizator
{
    public const OptClientId = 'clientId';
    public const OptClientSecret = 'clientSecret';
    public const OptGraphApiVersion = 'graphApiVersion';
    public const OptOptions = 'options';

    protected function createClient(ConfigInterface $config): AbstractProvider
    {
        $options = array_merge(
            $config->has(self::OptOptions) ? $config->get(self::OptOptions) : [],
            [
                self::OptClientId => (string) $config->get(self::OptClientId),
                self::OptClientSecret => (string) $config->get(self::OptClientSecret),
                self::OptGraphApiVersion => (string) $config->get(self::OptGraphApiVersion),
            ],
        );

        return new Facebook($options);
    }
}
