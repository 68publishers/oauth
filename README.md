<div align="center" style="text-align: center; margin-bottom: 50px">
<h1>OAuth</h1>
<p>:bust_in_silhouette: OAuth integration into Nette Framework</p>
</div>

<p align="center">
<a href="https://github.com/68publishers/oauth/actions"><img alt="Checks" src="https://badgen.net/github/checks/68publishers/oauth/main"></a>
<a href="https://coveralls.io/github/68publishers/oauth?branch=main"><img alt="Coverage Status" src="https://coveralls.io/repos/github/68publishers/oauth/badge.svg?branch=main"></a>
<a href="https://packagist.org/packages/68publishers/oauth"><img alt="Total Downloads" src="https://badgen.net/packagist/dt/68publishers/oauth"></a>
<a href="https://packagist.org/packages/68publishers/oauth"><img alt="Latest Version" src="https://badgen.net/packagist/v/68publishers/oauth"></a>
<a href="https://packagist.org/packages/68publishers/oauth"><img alt="PHP Version" src="https://badgen.net/packagist/php/68publishers/oauth"></a>
</p>

## Installation

```sh
$ composer require 68publishers/oauth
```

## Configuration

### Facebook

```sh
$ composer require league/oauth2-facebook
```

```neon
extensions:
    68publishers.oauth: SixtyEightPublishers\OAuth\Bridge\Nette\DI\OAuthExtension
    68publishers.facebook: SixtyEightPublishers\OAuth\Bridge\Nette\DI\FacebookOAuthExtension

68publishers.facebook:
    flowName: facebook # default, not necessary to define
    config:
        enabled: true # default, not necessary to define
        clientId: '<client id>'
        clientSecret: '<client id>'
        graphApiVersion: '<graph api version>'
        options: [] # additional options that are passed into the client
    authenticator: App\OAuth\FacebookAuthenticator
```

### Azure

```sh
$ composer require thenetworg/oauth2-azure
```

```neon
extensions:
    68publishers.oauth: SixtyEightPublishers\OAuth\Bridge\Nette\DI\OAuthExtension
    68publishers.azure: SixtyEightPublishers\OAuth\Bridge\Nette\DI\FacebookOAuthExtension

68publishers.azure:
    flowName: azure # default, not necessary to define
    config:
        enabled: true # default, not necessary to define
        clientId: '<client id>'
        clientSecret: '<client id>'
        options: [] # additional options that are passed into the client
    authenticator: App\OAuth\AzureAuthenticator
```

## Integration

### Lazy configuration

Sometimes it may be desirable to provide the configuration for an OAuth client dynamically if, for example, we have settings stored in a database.
We can do this with the following implementation:

```php
namespace App\OAuth\Config;

use SixtyEightPublishers\OAuth\Config\Config;
use SixtyEightPublishers\OAuth\Config\LazyConfig;
use App\SettingsProvider;

final class AzureConfig extends LazyConfig
{
    public function __construct(SettingsProvider $provider) {
        parent::__construct(
            configFactory: static function (): Config {
                return new Config(
                    flowEnabled: $provider->get('azure.enabled'),
                    options: [
                        'clientId' => $provider->get('azure.clientId'),
                        'clientSecret' => $provider->get('azure.clientSecret'),
                    ],
                );
            }
        );
    }
}
```

```neon
# ...

68publishers.azure:
    config: App\OAuth\Config\AzureConfig

# ...
```

### Implementing Authenticator

Authenticator is a class implementing the `AuthenticatorInterface` interface.
This class should return the identity of the user and throw an `AuthenticationException` exception in case of any problem.

```php
namespace App\OAuth;

use SixtyEightPublishers\OAuth\Authentication\AuthenticatorInterface;
use SixtyEightPublishers\OAuth\Exception\AuthenticationException;
use SixtyEightPublishers\OAuth\Authorization\AuthorizationResult;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

final class AzureAuthenticator implements AuthenticatorInterface
{
    public function authenticate(string $flowName, AuthorizationResult $authorizationResult): IIdentity
    {
        $accessToken = $authorizationResult->accessToken;
        $resourceOwner = $authorizationResult->resourceOwner;
        
        if ($userCannotBeAuthenticated) {
            throw new AuthenticationException('User can not be authenticated.');
        }
        
        return new SimpleIdentity(/* ... */);
    }
}
```

### Implementing OAuth Presenter

The `OAuthPresenterTrait` trait is used for simple implementation.
Next, you need to define three methods that determine what should happen if the authentication is successful or fails.
All three methods should redirect at the end.

```php
namespace App\Presenter;

use Nette\Application\UI\Presenter;
use SixtyEightPublishers\OAuth\Bridge\Nette\Application\OAuthPresenterTrait;
use SixtyEightPublishers\OAuth\Exception\OAuthExceptionInterface;

final class OAuthPresenter extends Presenter
{
    use OAuthPresenterTrait;
 
    protected function onAuthorizationRedirectFailed(string $flowName, OAuthExceptionInterface $error): void
    {
        $this->flashMessage('Authentication failed', 'error');
        $this->redirect('SignIn:');
    }

    abstract protected function onAuthenticationFailed(string $flowName, OAuthExceptionInterface $error): void
    {
        $this->flashMessage('Authentication failed', 'error');
        $this->redirect('SignIn:');
    }

    abstract protected function onUserAuthenticated(string $flowName): void
    {
        $this->flashMessage('You have been successfully logged in', 'success');
        $this->redirect('Homepage:');
    }
}
```

### Login button

The login button can be rendered simply as follows

```latte
<a n:href="OAuth:authorize, type => 'azure'">Login via Azure</a>
```

If you store the request (back link) using `Presenter::storeRequest()` you can also pass it the URL.
Your `OAuthPresenter` will then automatically redirect to this link after successful authentication.

```latte
<a n:href="OAuth:authorize, type => 'azure', backLink => $backLink">Login via Azure</a>
```

## License

The package is distributed under the MIT License. See [LICENSE](LICENSE.md) for more information.
