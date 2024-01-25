<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use RuntimeException;

final class AuthenticationException extends RuntimeException implements OAuthExceptionInterface
{
}
