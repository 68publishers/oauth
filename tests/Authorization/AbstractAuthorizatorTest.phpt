<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Authorization;

use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use SixtyEightPublishers\OAuth\Authorization\AbstractAuthorizator;
use SixtyEightPublishers\OAuth\Config\ConfigInterface;
use SixtyEightPublishers\OAuth\Exception\AuthorizationException;
use SixtyEightPublishers\OAuth\Exception\UnableToConstructAuthorizationUrlException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class AbstractAuthorizatorTest extends TestCase
{
    public function testAuthorizationUrlShouldBeReturned(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);
        $config = Mockery::mock(ConfigInterface::class);
        $authorizationUrl = 'https://oauth.service.com';

        $authorizator = new class($client, $config, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                private readonly ConfigInterface $config,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                Assert::same($this->config, $config);

                return $this->client;
            }

            protected function modifyAuthorizationUrlOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
            {
                Assert::same($this->client, $client);
                Assert::same($this->config, $config);
                Assert::same([
                    'state' => '__state__',
                    'redirect_uri' => 'https://www.example.com',
                ], $options);

                $options['test'] = '123';

                return $options;
            }
        };

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with($authorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('set')
            ->once()
            ->with('state', '__state__');

        $sessionSection
            ->shouldReceive('set')
            ->once()
            ->with('redirect_uri', 'https://www.example.com');

        $client
            ->shouldReceive('getAuthorizationUrl')
            ->once()
            ->with([
                'state' => '__state__',
                'redirect_uri' => 'https://www.example.com',
                'test' => '123',
            ])
            ->andReturn($authorizationUrl);

        $client
            ->shouldReceive('getState')
            ->once()
            ->andReturn('__state__');

        Assert::same(
            $authorizationUrl,
            $authorizator->getAuthorizationUrl(
                config: $config,
                redirectUri: 'https://www.example.com',
                options: [
                    'state' => '__state__',
                ],
            ),
        );
    }

    public function testUnableToConstructAuthorizationUrlExceptionShouldByThrownIfAnyExceptionIsThrownDuringAuthorizationUrlCreation(): void
    {
        $session = Mockery::mock(Session::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($session) extends AbstractAuthorizator {
            public function __construct(
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                throw new Exception('Unable to create the client.');
            }
        };

        Assert::exception(
            static fn () => $authorizator->getAuthorizationUrl($config, 'https://www.example.com', ['state' => '__state__']),
            UnableToConstructAuthorizationUrlException::class,
            'Unable to construct authorization url: Unable to create the client.',
        );
    }

    public function testUserShouldBeAuthorized(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);
        $config = Mockery::mock(ConfigInterface::class);

        $resourceOwner = Mockery::mock(ResourceOwnerInterface::class);
        $accessToken = Mockery::mock(AccessToken::class);

        $authorizator = new class($client, $config, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                private readonly ConfigInterface $config,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                Assert::same($this->config, $config);

                return $this->client;
            }

            protected function modifyAccessTokenOptions(AbstractProvider $client, ConfigInterface $config, array $options): array
            {
                Assert::same($this->client, $client);
                Assert::same($this->config, $config);
                Assert::same([
                    'code' => '__code__',
                    'redirect_uri' => 'https://www.example.com',
                ], $options);

                $options['test'] = '123';

                return $options;
            }
        };

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with($authorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('get')
            ->once()
            ->with('state')
            ->andReturn('__state__');

        $sessionSection
            ->shouldReceive('get')
            ->once()
            ->with('redirect_uri')
            ->andReturn('https://www.example.com');

        $client
            ->shouldReceive('getAccessToken')
            ->once()
            ->with('authorization_code', ['code' => '__code__', 'redirect_uri' => 'https://www.example.com', 'test' => '123'])
            ->andReturn($accessToken);

        $client
            ->shouldReceive('getResourceOwner')
            ->once()
            ->with($accessToken)
            ->andReturn($resourceOwner);

        $authorizationResult = $authorizator->authorize(
            config: $config,
            parameters: [
                'code' => '__code__',
                'state' => '__state__',
            ],
        );

        Assert::same($accessToken, $authorizationResult->accessToken);
        Assert::same($resourceOwner, $authorizationResult->resourceOwner);
    }

    public function testAuthorizationExceptionShouldBeThrownIfCodeParameterIsMissingDuringAuthorization(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($client, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                return $this->client;
            }
        };

        Assert::exception(
            static fn () => $authorizator->authorize($config, ['state' => '__state__']),
            AuthorizationException::class,
            'Authorization failed: Missing parameter with the name "code".',
        );
    }

    public function testAuthorizationExceptionShouldBeThrownIfStateParameterIsMissingDuringAuthorization(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($client, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                return $this->client;
            }
        };

        Assert::exception(
            static fn () => $authorizator->authorize($config, ['code' => '__code__']),
            AuthorizationException::class,
            'Authorization failed: Missing parameter with the name "state".',
        );
    }

    public function testAuthorizationExceptionShouldBeThrownIfAnyExceptionIsThrownDuringAuthorization(): void
    {
        $session = Mockery::mock(Session::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($session) extends AbstractAuthorizator {
            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                throw new Exception('Unable to create the client.');
            }
        };

        Assert::exception(
            static fn () => $authorizator->authorize($config, ['code' => '__code__', 'state' => '__state__']),
            AuthorizationException::class,
            'Authorization failed: Unable to create the client.',
        );
    }

    public function testAuthorizationExceptionShouldBeThrownIfStateIsMissingInSessionDuringAuthorization(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($client, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                return $this->client;
            }
        };

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with($authorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('get')
            ->once()
            ->with('state')
            ->andReturn(null);

        $sessionSection
            ->shouldReceive('remove')
            ->once()
            ->with('state');

        $sessionSection
            ->shouldReceive('remove')
            ->once()
            ->with('redirect_uri');

        Assert::exception(
            static fn () => $authorizator->authorize($config, ['code' => '__code__', 'state' => '__state__']),
            AuthorizationException::class,
            'Authorization failed: Possible CSRF attack.',
        );
    }

    public function testAuthorizationExceptionShouldBeThrownIfStateInSessionIsDifferentDuringAuthorization(): void
    {
        $client = Mockery::mock(AbstractProvider::class);
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);
        $config = Mockery::mock(ConfigInterface::class);

        $authorizator = new class($client, $session) extends AbstractAuthorizator {
            public function __construct(
                private readonly AbstractProvider $client,
                Session $session,
            ) {
                parent::__construct($session);
            }

            protected function createClient(ConfigInterface $config): AbstractProvider
            {
                return $this->client;
            }
        };

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with($authorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('get')
            ->once()
            ->with('state')
            ->andReturn(null);

        $sessionSection
            ->shouldReceive('remove')
            ->once()
            ->with('state');

        $sessionSection
            ->shouldReceive('remove')
            ->once()
            ->with('redirect_uri');

        Assert::exception(
            static fn () => $authorizator->authorize($config, ['code' => '__code__', 'state' => '__different_state__']),
            AuthorizationException::class,
            'Authorization failed: Possible CSRF attack.',
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new AbstractAuthorizatorTest())->run();
