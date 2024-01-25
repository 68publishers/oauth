<?php

declare(strict_types=1);

namespace SixtyEightPublishers\OAuth\Bridge\Nette\Application;

use Exception;
use JsonException;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function json_decode;
use function json_encode;
use function random_bytes;
use function strtr;

final class StateEncoder
{
    private function __construct() {}

    /**
     * @param array<string, mixed> $customData
     *
     * @throws Exception
     */
    public static function encode(array $customData): string
    {
        $stateData = json_encode(
            value: [
                'uniq' => bin2hex(random_bytes(16)),
                'data' => $customData,
            ],
            flags: JSON_THROW_ON_ERROR,
        );

        return strtr(
            string: base64_encode(
                string: $stateData,
            ),
            from: '+/=',
            to: '-_,',
        );
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    public static function decode(string $state): array
    {
        $stateData = json_decode(
            json: base64_decode(
                string: strtr(
                    string: $state,
                    from: '-_,',
                    to: '+/=',
                ),
            ),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        return (array) $stateData;
    }
}
