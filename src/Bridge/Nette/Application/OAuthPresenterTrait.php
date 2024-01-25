<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\Application;

use Exception;
use JsonException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Security\AuthenticationException as NetteAuthenticationException;
use SixtyEightPublishers\OAuth\Exception\MissingOAuthFlowException;
use SixtyEightPublishers\OAuth\Exception\OAuthExceptionInterface;
use SixtyEightPublishers\OAuth\OAuthFlowInterface;
use SixtyEightPublishers\OAuth\OAuthFlowProviderInterface;

trait OAuthPresenterTrait
{
    private OAuthFlowProviderInterface $oauthFlowProvider;

    /**
     * @internal
     */
    public function injectOAuthDependencies(
        OAuthFlowProviderInterface $oauthFlowProvider,
    ): void {
        $this->oauthFlowProvider = $oauthFlowProvider;
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws Exception
     */
    public function actionAuthorize(string $type, ?string $backLink = null): void
    {
        /** @var Presenter $this */
        try {
            $flow = $this->getOAuthFlow($type);
            $backLink = !empty($backLink) ? $backLink : null;

            $this->redirectUrl(
                url: $flow->getAuthorizationUrl(
                    redirectUri: $this->link('//authenticate', [
                        'type' => $type,
                    ]),
                    options: [
                        'state' => StateEncoder::encode([
                            'backLink' => $backLink,
                        ]),
                    ],
                ),
            );
        } catch (OAuthExceptionInterface $e) {
            $this->onAuthorizationRedirectFailed(
                flowName: $type,
                error: $e,
            );

            $this->redirectUrl('/');
        }
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     * @throws JsonException
     * @throws NetteAuthenticationException
     */
    public function actionAuthenticate(string $type): void
    {
        /** @var Presenter $this */
        try {
            $flow = $this->getOAuthFlow($type);

            $parameters = $this->getHttpRequest()->getUrl()->getQueryParameters();
            $state = StateEncoder::decode($parameters['state'] ?? '');
            $stateData = (array) ($state['data'] ?? []);
            $identity = $flow->run($parameters);

            $this->getUser()->login($identity);

            if (!empty($stateData['backLink'] ?? '')) {
                $this->restoreRequest($stateData['backLink']);
            }

            $this->onUserAuthenticated(
                flowName: $type,
            );
        } catch (OAuthExceptionInterface $e) {
            $this->onAuthenticationFailed(
                flowName: $type,
                error: $e,
            );
        }

        $this->redirectUrl('/');
    }

    /**
     * @throws AbortException
     */
    abstract protected function onAuthorizationRedirectFailed(string $flowName, OAuthExceptionInterface $error): void;

    /**
     * @throws AbortException
     */
    abstract protected function onAuthenticationFailed(string $flowName, OAuthExceptionInterface $error): void;

    /**
     * @throws AbortException
     */
    abstract protected function onUserAuthenticated(string $flowName): void;

    /**
     * @throws BadRequestException
     */
    private function getOAuthFlow(string $type): OAuthFlowInterface
    {
        /** @var Presenter $this */
        try {
            return $this->oauthFlowProvider->get(
                name: $type,
            );
        } catch (MissingOAuthFlowException $e) {
            $this->error(
                message: $e->getMessage(),
                httpCode: IResponse::S404_NotFound,
            );
        }
    }
}
