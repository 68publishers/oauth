<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use RuntimeException;
use Throwable;
use function sprintf;

final class AuthorizationException extends RuntimeException implements OAuthExceptionInterface
{
    public static function missingParameter(string $name, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Authorization failed: Missing parameter with the name "%s".',
                $name,
            ),
            previous: $previous,
        );
    }

    public static function possibleCsrfAttack(?Throwable $previous = null): self
    {
        return new self(
            message: 'Authorization failed: Possible CSRF attack.',
            previous: $previous,
        );
    }

    public static function wrap(Throwable $previous): self
    {
        return new self(
            message: sprintf(
                'Authorization failed: %s',
                $previous->getMessage(),
            ),
            previous: $previous,
        );
    }
}
