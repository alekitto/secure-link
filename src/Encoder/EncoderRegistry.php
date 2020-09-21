<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Encoder;

use LogicException;

class EncoderRegistry
{
    /** @var EncoderInterface[] */
    private array $encoders;

    /**
     * @param iterable<EncoderInterface> $encoders
     */
    public function __construct(iterable $encoders)
    {
        $this->encoders = (static fn (EncoderInterface ...$encs): array => $encs)(...$encoders);
    }

    /**
     * Gets an encoder supporting the given dsn.
     */
    public function getEncoder(string $dsn): EncoderInterface
    {
        foreach ($this->encoders as $encoder) {
            if ($encoder->supports($dsn)) {
                return $encoder;
            }
        }

        throw new LogicException('Cannot retrieve encoder for given DSN');
    }
}
