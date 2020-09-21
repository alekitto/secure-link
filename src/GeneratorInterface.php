<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

/**
 * A generator which encodes an operation object into a payload/signature pair.
 */
interface GeneratorInterface
{
    /**
     * Generates an encoded payload with signature.
     */
    public function generate(Operation $operation): Payload;
}
