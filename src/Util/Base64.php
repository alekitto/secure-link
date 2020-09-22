<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Util;

use function base64_encode;
use function Safe\base64_decode;
use function str_replace;

final class Base64
{
    /**
     * Encodes data to base64.
     * Throws an Exception on failure.
     */
    public static function encode(string $data): string
    {
        return base64_encode($data);
    }

    /**
     * Decodes data from base64.
     * Throws an Exception on failure.
     */
    public static function decode(string $encoded): string
    {
        return base64_decode($encoded, true);
    }

    /**
     * Encodes data to base64 and replaces url-unsafe characters.
     * Throws an Exception on failure.
     */
    public static function urlEncode(string $data): string
    {
        $base64 = self::encode($data);
        $base64 = str_replace(['+', '/', "\r", "\n", '='], ['-', '_'], $base64);

        return $base64;
    }

    /**
     * Decodes data from url-safe-base64.
     * Throws an Exception on failure.
     */
    public static function urlDecode(string $encoded): string
    {
        return self::decode(str_replace(['-', '_'], ['+', '/'], $encoded));
    }
}
