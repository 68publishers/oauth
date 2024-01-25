<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use RuntimeException;
use Throwable;
use function sprintf;

final class UnableToConstructAuthorizationUrlException extends RuntimeException implements OAuthExceptionInterface
{
    public static function create(string $reason, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Unable to construct authorization url: %s',
                $reason,
            ),
            previous: $previous,
        );
    }
}
