<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use DateTimeInterface;
use JsonSerializable;

class Operation implements JsonSerializable
{
    public ?DateTimeInterface $validUntil;
    public string $type;
    public string $target;

    public function __construct(string $type, string $target, ?DateTimeInterface $validUntil = null)
    {
        $this->type = $type;
        $this->target = $target;
        $this->validUntil = $validUntil;
    }

    /**
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'until' => $this->validUntil !== null ? $this->validUntil->format(DateTimeInterface::ATOM) : null,
            'target' => $this->target,
            'type' => $this->type,
        ];
    }
}
