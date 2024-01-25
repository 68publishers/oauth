<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Exception;

use InvalidArgumentException;
use Throwable;
use function sprintf;

final class InvalidConfigurationException extends InvalidArgumentException implements OAuthExceptionInterface
{
    public static function missingOption(string $optionKey, ?Throwable $previous = null): self
    {
        return new self(
            message: sprintf(
                'Missing configuration option "%s".',
                $optionKey,
            ),
            previous: $previous,
        );
    }
}
