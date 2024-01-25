<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use InvalidArgumentException;
use Throwable;
use function sprintf;

final class MissingOAuthFlowException extends InvalidArgumentException implements OAuthExceptionInterface
{
    public static function create(string $flowName, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'OAuth flow with the name "%s" is missing.',
                $flowName,
            ),
            previous: $previous,
        );
    }
}
