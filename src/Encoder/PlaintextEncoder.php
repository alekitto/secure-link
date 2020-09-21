<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Encoder;

use Kcs\SecureLink\Exception\InvalidSignatureException;
use Kcs\SecureLink\Util\Base64;
use function hash;
use function strpos;

class PlaintextEncoder implements EncoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function encrypt(string $content): array
    {
        return [Base64::urlEncode($content), hash('sha1', $content, false)];
    }

    public function decrypt(string $message, ?string $signature = null): string
    {
        $message = Base64::urlDecode($message);
        $hash = hash('sha1', $message, false);

        if ($hash !== $signature) {
            throw new InvalidSignatureException('Signature does not match');
        }

        return $message;
    }

    public function supports(string $dsn): bool
    {
        return strpos($dsn, 'plaintext://') === 0;
    }
}
