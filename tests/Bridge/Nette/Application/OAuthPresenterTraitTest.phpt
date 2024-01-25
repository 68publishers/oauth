<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\Application;

use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Container;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;
use Nette\Routing\Router;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use SixtyEightPublishers\OAuth\Bridge\Nette\Application\StateEncoder;
use SixtyEightPublishers\OAuth\Exception\AuthenticationException;
use SixtyEightPublishers\OAuth\Exception\AuthorizationException;
use SixtyEightPublishers\OAuth\Exception\OAuthExceptionInterface;
use SixtyEightPublishers\OAuth\OAuthFlowInterface;
use SixtyEightPublishers\OAuth\Tests\Bridge\Nette\DI\ContainerFactory;
use SixtyEightPublishers\OAuth\Tests\Fixtures\OAuthFlowFixture;
use Tester\Assert;
use Tester\TestCase;
use function assert;
use function rawurlencode;

require __DIR__ . '/../../../bootstrap.php';

final class OAuthPresenterTraitTest extends TestCase
{
    public function testUserShouldBeRedirectedToAuthorizationUrl(): void
    {
        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function (string $redirectUri, array $options) {
                Assert::same('https://www.example.com/o-auth/authenticate?type=test', $redirectUri);
                Assert::hasKey('state', $options);

                $decodedState = StateEncoder::decode($options['state']);

                Assert::hasKey('uniq', $decodedState);
                Assert::hasKey('data', $decodedState);
                Assert::type('string', $decodedState['uniq']);
                Assert::same(['backLink' => null], $decodedState['data']);

                return 'https://oauth.service.com';
            },
            runHandler: static function () {},
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authorize?type=test',
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('https://oauth.service.com', $response->getUrl());
    }

    public function testUserShouldBeRedirectedToAuthorizationUrlWithBackLinkParameter(): void
    {
        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function (string $redirectUri, array $options) {
                Assert::same('https://www.example.com/o-auth/authenticate?type=test', $redirectUri);
                Assert::hasKey('state', $options);

                $decodedState = StateEncoder::decode($options['state']);

                Assert::hasKey('uniq', $decodedState);
                Assert::hasKey('data', $decodedState);
                Assert::type('string', $decodedState['uniq']);
                Assert::same(['backLink' => 'abc'], $decodedState['data']);

                return 'https://oauth.service.com';
            },
            runHandler: static function () {},
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authorize?type=test&backLink=abc',
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('https://oauth.service.com', $response->getUrl());
    }

    public function testAuthorizationActionFailed(): void
    {
        $exception = new AuthorizationException('Authorization failed.');
        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function () use ($exception) {
                throw $exception;
            },
            runHandler: static function () {},
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authorize?type=test',
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );

        $handlerCalled = false;
        $presenter->onAuthorizationRedirectFailedHandler = static function (string $flowName, OAuthExceptionInterface $error) use ($exception, &$handlerCalled): void {
            Assert::same('test', $flowName);
            Assert::same($exception, $error);

            $handlerCalled = true;
        };

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('/', $response->getUrl());
        Assert::true($handlerCalled);
    }

    public function testUserShouldBeAuthenticated(): void
    {
        $identity = new SimpleIdentity(
            id: 1,
        );
        $state = StateEncoder::encode([]);

        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function () {},
            runHandler: static function (array $parameters) use ($identity, $state) {
                Assert::same(
                    [
                        'type' => 'test',
                        'code' => '__code__',
                        'state' => $state,
                    ],
                    $parameters,
                );

                return $identity;
            },
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authenticate?type=test&code=__code__&state=' . rawurlencode($state),
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );
        $user = $container->getByType(User::class);

        $handlerCalled = false;
        $presenter->onUserAuthenticated = static function (string $flowName) use (&$handlerCalled): void {
            Assert::same('test', $flowName);

            $handlerCalled = true;
        };

        Assert::false($user->isLoggedIn());

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('/', $response->getUrl());
        Assert::null($presenter->restoredRequest);
        Assert::true($handlerCalled);
        Assert::true($user->isLoggedIn());
        Assert::same($identity, $user->getIdentity());
    }

    public function testUserShouldBeAuthenticatedWithRestoringBackLinkRequest(): void
    {
        $identity = new SimpleIdentity(
            id: 1,
        );
        $state = StateEncoder::encode([
            'backLink' => 'abc',
        ]);

        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function () {},
            runHandler: static function (array $parameters) use ($identity, $state) {
                Assert::same(
                    [
                        'type' => 'test',
                        'code' => '__code__',
                        'state' => $state,
                    ],
                    $parameters,
                );

                return $identity;
            },
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authenticate?type=test&code=__code__&state=' . rawurlencode($state),
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );
        $user = $container->getByType(User::class);

        $handlerCalled = false;
        $presenter->onUserAuthenticated = static function (string $flowName) use (&$handlerCalled): void {
            Assert::same('test', $flowName);

            $handlerCalled = true;
        };

        Assert::false($user->isLoggedIn());

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('/', $response->getUrl());
        Assert::same('abc', $presenter->restoredRequest);
        Assert::true($handlerCalled);
        Assert::true($user->isLoggedIn());
        Assert::same($identity, $user->getIdentity());
    }

    public function testUserAuthenticationFailed(): void
    {
        $exception = new AuthenticationException('Authentication failed.');
        $flow = new OAuthFlowFixture(
            name: 'test',
            enabled: true,
            getAuthorizationUrlHandler: static function () {},
            runHandler: static function () use ($exception) {
                throw $exception;
            },
        );

        $container = $this->createContainer(
            flow: $flow,
            url: new UrlScript(
                url: 'https://www.example.com/o-auth/authenticate?type=test&code=__code__&state=' . rawurlencode(StateEncoder::encode([])),
            ),
        );
        $presenter = $this->createPresenter(
            container: $container,
        );

        $handlerCalled = false;
        $presenter->onAuthenticationFailedHandler = static function (string $flowName, OAuthExceptionInterface $error) use ($exception, &$handlerCalled): void {
            Assert::same('test', $flowName);
            Assert::same($exception, $error);

            $handlerCalled = true;
        };

        $response = $presenter->run(
            request: $this->createApplicationRequest(
                container: $container,
            ),
        );

        Assert::type(RedirectResponse::class, $response);
        assert($response instanceof RedirectResponse);

        Assert::same('/', $response->getUrl());
        Assert::true($handlerCalled);
    }

    private function createContainer(OAuthFlowInterface $flow, UrlScript $url): Container
    {
        $container = ContainerFactory::create(__DIR__ . '/config/config.neon');

        $container->addService(
            name: 'flow.test',
            service: $flow,
        );

        if ($container->hasService('http.request')) {
            $container->removeService('http.request');
        }

        $container->addService(
            name: 'http.request',
            service: new HttpRequest(
                url: $url,
            ),
        );

        return $container;
    }

    private function createPresenter(Container $container): OAuthPresenter
    {
        $presenterFactory = $container->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter('OAuth');
        assert($presenter instanceof OAuthPresenter);

        return $presenter;
    }

    private function createApplicationRequest(Container $container): Request
    {
        $httpRequest = $container->getByType(HttpRequest::class);
        $router = $container->getByType(Router::class);

        $params = $router->match($httpRequest);
        $presenter = $params['presenter'] ?? null;

        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [Request::SECURED => $httpRequest->isSecured()],
        );
    }
}

(new OAuthPresenterTraitTest())->run();
