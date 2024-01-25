<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use RuntimeException;
use Throwable;
use function sprintf;

final class OAuthFlowIsDisabledException extends RuntimeException implements OAuthExceptionInterface
{
    public static function create(string $flowName, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'OAuth flow with the name "%s" is disabled.',
                $flowName,
            ),
            previous: $previous,
        );
    }
}
