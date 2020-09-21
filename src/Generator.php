<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use Kcs\SecureLink\Encoder\EncoderInterface;
use Safe\DateTimeImmutable;
use function Safe\json_encode;

class Generator implements GeneratorInterface
{
    private EncoderInterface $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function generate(Operation $operation): Payload
    {
        if (! $operation->validUntil) {
            $operation->validUntil = (new DateTimeImmutable($operation->validUntil))->modify('+30 days');
        }

        $serialized = json_encode($operation);
        [$payload, $signature] = $this->encoder->encrypt($serialized);

        return new Payload($payload, $signature);
    }
}
