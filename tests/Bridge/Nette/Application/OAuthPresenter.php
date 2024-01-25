<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Bridge\Nette\Application;

use Closure;
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\OAuth\Bridge\Nette\Application\OAuthPresenterTrait;
use SixtyEightPublishers\OAuth\Exception\OAuthExceptionInterface;

final class OAuthPresenter extends Presenter
{
    use OAuthPresenterTrait;

    public ?string $restoredRequest = null;

    /** @var null|Closure(string $flowName, OAuthExceptionInterface $error, self $presenter): void */
    public ?Closure $onAuthorizationRedirectFailedHandler = null;

    /** @var null|Closure(string $flowName, OAuthExceptionInterface $error, self $presenter): void */
    public ?Closure $onAuthenticationFailedHandler = null;

    /** @var null|Closure(string $flowName, self $presenter): void */
    public ?Closure $onUserAuthenticated = null;

    public function restoreRequest(string $key): void
    {
        $this->restoredRequest = $key;
    }

    protected function onAuthorizationRedirectFailed(string $flowName, OAuthExceptionInterface $error): void
    {
        $this->onAuthorizationRedirectFailedHandler && ($this->onAuthorizationRedirectFailedHandler)($flowName, $error, $this);
    }

    protected function onAuthenticationFailed(string $flowName, OAuthExceptionInterface $error): void
    {
        $this->onAuthenticationFailedHandler && ($this->onAuthenticationFailedHandler)($flowName, $error, $this);
    }

    protected function onUserAuthenticated(string $flowName): void
    {
        $this->onUserAuthenticated && ($this->onUserAuthenticated)($flowName, $this);
    }
}
