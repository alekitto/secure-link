<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use JsonSerializable;

final class Payload implements JsonSerializable
{
    public string $payload;
    public string $signature;

    public function __construct(string $payload, string $signature)
    {
        $this->payload = $payload;
        $this->signature = $signature;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'payload' => $this->payload,
            'sign' => $this->signature,
        ];
    }
}
