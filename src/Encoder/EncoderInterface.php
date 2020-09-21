<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Encoder;

interface EncoderInterface
{
    /**
     * Encrypt a message.
     *
     * @return string[]
     *
     * @phpstan-return array{string, string}
     */
    public function encrypt(string $content): array;

    /**
     * Decrypt a message.
     */
    public function decrypt(string $message, ?string $signature = null): string;

    /**
     * Checks if this encoder supports the configured dsn.
     */
    public function supports(string $dsn): bool;
}
