<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Authorization;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Nette\Http\Session;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\Exception\AuthorizationException;
use SixtyEightPublishers\OAuth\Exception\UnableToConstructAuthorizationUrlException;
use Throwable;
use function array_filter;
use function assert;

abstract class AbstractAuthorizator implements AuthorizatorInterface
{
    public function __construct(
        private readonly Session $session,
    ) {}

    public function getAuthorizationUrl(ConfigInterface $config, string $redirectUri, array $options = []): string
    {
        try {
            $client = $this->createClient(
                config: $config,
            );
            $session = $this->session->getSection(static::class);

            $options['redirect_uri'] = $redirectUri;
            $options = $this->modifyAuthorizationUrlOptions(
                client: $client,
                config: $config,
                options: $options,
            );

            $url = $client->getAuthorizationUrl($options);

            $session->set('state', $client->getState());
            $session->set('redirect_uri', $options['redirect_uri']);

            return $url;
        } catch (Throwable $e) {
            throw UnableToConstructAuthorizationUrlException::create(
                reason: $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function authorize(ConfigInterface $config, array $parameters): AuthorizationResult
    {
        if (empty($parameters['code'] ?? '')) {
            throw AuthorizationException::missingParameter(
                name: 'code',
            );
        }

        if (empty($parameters['state'] ?? '')) {
            throw AuthorizationException::missingParameter(
                name: 'state',
            );
        }

        try {
            $client = $this->createClient(
                config: $config,
            );
            $session = $this->session->getSection(static::class);
            $state = $session->get('state');

            if (null === $state || $parameters['state'] !== $state) {
                $session->remove('state');
                $session->remove('redirect_uri');

                throw AuthorizationException::possibleCsrfAttack();
            }

            $options = array_filter([
                'code' => $parameters['code'],
                'redirect_uri' => $session->get('redirect_uri'),
            ]);
            $options = $this->modifyAccessTokenOptions(
                client: $client,
                config: $config,
                options: $options,
            );

            $token = $client->getAccessToken('authorization_code', $options);
            assert($token instanceof AccessToken);

            $resourceOwner = $client->getResourceOwner($token);

            return new AuthorizationResult(
                client: $client,
                resourceOwner: $resourceOwner,
                accessToken: $token,
            );
        } catch (AuthorizationException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw AuthorizationException::wrap($e);
        }
    }

    abstract protected function createClient(ConfigInterface $config): AbstractProvider;

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function modifyAuthorizationUrlOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
    {
        return $options;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    protected function modifyAccessTokenOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
    {
        return $options;
    }
}
