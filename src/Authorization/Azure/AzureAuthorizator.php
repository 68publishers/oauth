<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authorization\Azure;

use League\OAuth2\Client\Provider\AbstractProvider;
use SixtyEightPublishers\OAuth\Authorization\AbstractAuthorizator;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;
use function array_merge;
use function assert;

final class AzureAuthorizator extends AbstractAuthorizator
{
    public const OptClientId = 'clientId';
    public const OptClientSecret = 'clientSecret';
    public const OptOptions = 'options';

    protected function createClient(ConfigInterface $config): AbstractProvider
    {
        $options = array_merge(
            [
                'defaultEndPointVersion' => '2.0',
            ],
            $config->has(self::OptOptions) ? $config->get(self::OptOptions) : [],
            [
                self::OptClientId => (string) $config->get(self::OptClientId),
                self::OptClientSecret => (string) $config->get(self::OptClientSecret),
            ],
        );

        $client = new Azure($options);

        $baseGraphUri = $client->getRootMicrosoftGraphUri(null);
        $client->scope = 'openid profile email offline_access ' . $baseGraphUri . '/User.Read';

        return $client;
    }

    protected function modifyAuthorizationUrlOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
    {
        assert($client instanceof Azure);
        $options['scope'] = $client->scope;

        return $options;
    }

    protected function modifyAccessTokenOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
    {
        assert($client instanceof Azure);
        $options['scope'] = $client->scope;

        return $options;
    }
}
