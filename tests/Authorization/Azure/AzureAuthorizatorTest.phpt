<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Authorization\Azure;

use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use SixtyEightPublishers\OAuth\Authorization\Azure\AzureAuthorizator;
use SixtyEightPublishers\OAuth\Config\Config;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class AzureAuthorizatorTest extends TestCase
{
    public function testAuthorizationUrlShouldBeReturned(): void
    {
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with(AzureAuthorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('set');

        $config = new Config(
            flowEnabled: true,
            options: [
                AzureAuthorizator::OptClientId => '498bb796-0410-4a6c-ba95-b4855ea0c900',
                AzureAuthorizator::OptClientSecret => 'secret',
            ],
        );

        $authorizator = new AzureAuthorizator($session);

        $url = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?'
            . 'state=__state__'
            . '&redirect_uri=https%3A%2F%2Fwww.example.com'
            . '&scope=openid%20profile%20email%20offline_access%20https%3A%2F%2Fgraph.microsoft.com%2FUser.Read'
            . '&response_type=code'
            . '&approval_prompt=auto'
            . '&client_id=498bb796-0410-4a6c-ba95-b4855ea0c900';

        Assert::same(
            $url,
            $authorizator->getAuthorizationUrl(
                config: $config,
                redirectUri: 'https://www.example.com',
                options: [
                    'state' => '__state__',
                ],
            ),
        );
    }
}

(new AzureAuthorizatorTest())->run();
