<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Tests\Authorization\Azure;

use Mockery;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use SixtyEightPublishers\OAuth\Authorization\Facebook\FacebookAuthorizator;
use SixtyEightPublishers\OAuth\Config\Config;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class FacebookAuthorizatorTest extends TestCase
{
    public function testAuthorizationUrlShouldBeReturned(): void
    {
        $session = Mockery::mock(Session::class);
        $sessionSection = Mockery::mock(SessionSection::class);

        $session
            ->shouldReceive('getSection')
            ->once()
            ->with(FacebookAuthorizator::class)
            ->andReturn($sessionSection);

        $sessionSection
            ->shouldReceive('set');

        $config = new Config(
            flowEnabled: true,
            options: [
                FacebookAuthorizator::OptClientId => '498bb796-0410-4a6c-ba95-b4855ea0c900',
                FacebookAuthorizator::OptClientSecret => 'secret',
                FacebookAuthorizator::OptGraphApiVersion => 'v3.2',
            ],
        );

        $authorizator = new FacebookAuthorizator($session);

        $url = 'https://www.facebook.com/v3.2/dialog/oauth?'
            . 'state=__state__'
            . '&redirect_uri=https%3A%2F%2Fwww.example.com'
            . '&scope=public_profile%2Cemail'
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

(new FacebookAuthorizatorTest())->run();
